<?php

namespace App\Services\Auth;

use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class AuthService
{
    private const OTP_LENGTH = 6;
    private const OTP_EXPIRY_MINUTES = 10;
    private const PASSWORD_RESET_EXPIRY_MINUTES = 60;

    public function register(string $firstName, string $lastName, string $email, string $phone, string $password): User
    {
        $user = User::create([
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'name'       => trim($firstName . ' ' . $lastName),
            'email'      => $email,
            'phone'      => $this->normalizePhone($phone),
            'password'   => Hash::make($password),
        ]);

        \Illuminate\Support\Facades\Mail::to($user->email)->send(new WelcomeMail($user));

        return $user;
    }

    /**
     * Normalize a phone number to E164 format (+[country][number]).
     * Assumes India (+91) when no country code is present.
     */
    protected function normalizePhone(string $phone): string
    {
        $phone = trim($phone);
        if (preg_match('/^\+[1-9]\d{6,14}$/', $phone)) {
            return $phone; // already E164
        }
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) === 10) {
            return '+91' . $digits;
        }
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return '+' . $digits;
        }
        if (strlen($digits) >= 11) {
            return '+' . $digits;
        }
        return $phone; // return as-is if we can't parse it
    }

    public function login(string $email, string $password): ?User
    {
        $user = User::where('email', $email)->first();
        if (!$user || !Hash::check($password, $user->password)) {
            return null;
        }
        return $user;
    }

    public function sendOtp(string $phone): array
    {
        $otp = (string) random_int(10 ** (self::OTP_LENGTH - 1), 10 ** self::OTP_LENGTH - 1);
        $expiresAt = now()->addMinutes(self::OTP_EXPIRY_MINUTES);

        $user = User::firstOrCreate(
            ['phone' => $phone],
            [
                'name' => 'User ' . substr($phone, -4),
                'email' => 'otp_' . preg_replace('/\D/', '', $phone) . '@temp.gift',
                'password' => Hash::make(bin2hex(random_bytes(16))),
            ]
        );

        $user->update([
            'otp' => $otp,
            'otp_expires_at' => $expiresAt,
        ]);

        // TODO: Integrate with SMS provider (Twilio, etc.)
        // For development, return OTP in response - remove in production
        return [
            'message' => 'OTP sent successfully',
            'otp' => config('app.env') === 'local' ? $otp : null,
            'expires_in' => self::OTP_EXPIRY_MINUTES * 60,
        ];
    }

    public function verifyOtp(string $phone, string $otp): ?User
    {
        $user = User::where('phone', $phone)->first();
        if (!$user || $user->otp !== $otp || !$user->otp_expires_at || $user->otp_expires_at->isPast()) {
            return null;
        }
        $user->update(['otp' => null, 'otp_expires_at' => null]);
        return $user;
    }

    public function findOrCreateFromGoogle(SocialiteUser $socialUser): User
    {
        $user = User::where('google_id', $socialUser->getId())->first();
        if ($user) {
            $user->update([
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'avatar' => $socialUser->getAvatar(),
            ]);
            return $user;
        }

        $user = User::where('email', $socialUser->getEmail())->first();
        if ($user) {
            $user->update([
                'google_id' => $socialUser->getId(),
                'name' => $socialUser->getName(),
                'avatar' => $socialUser->getAvatar(),
            ]);
            return $user;
        }

        return User::create([
            'name' => $socialUser->getName(),
            'email' => $socialUser->getEmail(),
            'google_id' => $socialUser->getId(),
            'avatar' => $socialUser->getAvatar(),
            'password' => Hash::make(bin2hex(random_bytes(16))),
        ]);
    }

    /**
     * Create a password reset token and return the raw token for the link.
     * Returns null if user not found.
     */
    public function forgotPassword(string $email): ?string
    {
        $user = User::where('email', $email)->first();
        if (!$user) {
            return null;
        }

        $token = Str::random(64);
        $hashed = Hash::make($token);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => $hashed, 'created_at' => now()]
        );

        return $token;
    }

    /**
     * Reset password using token from email. Returns true on success.
     */
    public function resetPassword(string $email, string $token, string $password): bool
    {
        $row = DB::table('password_reset_tokens')->where('email', $email)->first();
        if (!$row || !Hash::check($token, $row->token)) {
            return false;
        }

        $createdAt = $row->created_at ? \Carbon\Carbon::parse($row->created_at) : null;
        if ($createdAt && $createdAt->addMinutes(self::PASSWORD_RESET_EXPIRY_MINUTES)->isPast()) {
            DB::table('password_reset_tokens')->where('email', $email)->delete();
            return false;
        }

        User::where('email', $email)->update(['password' => Hash::make($password)]);
        DB::table('password_reset_tokens')->where('email', $email)->delete();

        return true;
    }
}
