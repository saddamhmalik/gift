<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Contracts\User as SocialiteUser;

class AuthService
{
    private const OTP_LENGTH = 6;
    private const OTP_EXPIRY_MINUTES = 10;

    public function register(string $name, string $email, string $password): User
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        return $user;
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
}
