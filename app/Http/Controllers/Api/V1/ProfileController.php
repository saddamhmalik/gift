<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Mail\VerifyEmailOtpMail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * PUT /api/v1/profile
     * Update name fields.
     */
    public function update(Request $request): JsonResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
        ]);

        $user = $request->user();
        $user->update([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'name'       => trim($request->first_name . ' ' . $request->last_name),
        ]);

        return $this->success(new UserResource($user->fresh()), 'Profile updated successfully.');
    }

    /**
     * POST /api/v1/profile/avatar
     * Upload a profile photo.
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $user = $request->user();

        // Delete old avatar if it was an uploaded file (not a URL)
        if ($user->avatar && ! str_starts_with($user->avatar, 'http')) {
            Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        return $this->success(new UserResource($user->fresh()), 'Avatar updated.');
    }

    /**
     * DELETE /api/v1/profile/avatar
     * Remove profile photo (revert to generated avatar).
     */
    public function removeAvatar(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->avatar && ! str_starts_with($user->avatar, 'http')) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->update(['avatar' => null]);

        return $this->success(new UserResource($user->fresh()), 'Avatar removed.');
    }

    /**
     * POST /api/v1/profile/email
     * Request an email address change — sends 6-digit OTP to the new email.
     */
    public function requestEmailChange(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|unique:users,email,' . $request->user()->id,
        ]);

        $user = $request->user();
        $otp  = (string) random_int(100000, 999999);

        $user->update([
            'pending_email'          => $request->email,
            'email_change_token'     => $otp,
            'email_change_expires_at'=> now()->addMinutes(10),
        ]);

        Mail::to($request->email)->send(new VerifyEmailOtpMail(
            $request->email,
            $user->first_name ?? $user->name,
            $otp,
            true,
            10
        ));

        return $this->success(
            ['message' => 'Verification code sent to ' . $request->email],
            'Verification code sent.'
        );
    }

    /**
     * POST /api/v1/auth/verify-email (public)
     * Verify email using the 6-digit OTP. No auth required — finds user by pending_email or email.
     */
    public function verifyEmailWithOtp(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => ['required', 'string', 'regex:/^\d{6}$/'],
        ]);

        $email = strtolower(trim($request->email));
        $otp   = (string) preg_replace('/\D/', '', $request->otp);

        $user = User::whereRaw('LOWER(TRIM(pending_email)) = ?', [$email])->first();
        if (! $user) {
            $user = User::whereRaw('LOWER(TRIM(email)) = ?', [$email])->first();
        }

        if (! $user) {
            return $this->error('No pending verification found for this email. Request a new code from your profile.', 422);
        }

        if (! $user->email_change_token || ! $user->email_change_expires_at) {
            return $this->error('No pending verification or the code has expired. Request a new code from your profile.', 422);
        }

        if ($user->email_change_expires_at->isPast()) {
            $user->update([
                'pending_email' => null,
                'email_change_token' => null,
                'email_change_expires_at' => null,
            ]);
            return $this->error('This verification code has expired. Please request a new one from your profile.', 422);
        }

        $storedOtp = (string) $user->email_change_token;
        if ($storedOtp !== $otp) {
            return $this->error('Invalid verification code. Please check and try again.', 422);
        }

        $user->update([
            'email' => $user->pending_email,
            'email_verified_at' => now(),
            'pending_email' => null,
            'email_change_token' => null,
            'email_change_expires_at' => null,
        ]);

        return $this->success(new UserResource($user->fresh()), 'Email verified successfully.');
    }

    /**
     * POST /api/v1/profile/email/verify (authenticated)
     * Confirm the email change using the OTP.
     */
    public function verifyEmailChange(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => ['required', 'string', 'regex:/^\d{6}$/'],
        ]);

        $user = $request->user();
        $otp  = (string) preg_replace('/\D/', '', $request->otp);

        if (
            ! $user->pending_email ||
            ! $user->email_change_token ||
            ! $user->email_change_expires_at ||
            $user->email_change_expires_at->isPast()
        ) {
            return $this->error('No pending verification or the code has expired. Request a new code.', 422);
        }

        $storedOtp = (string) $user->email_change_token;
        if ($storedOtp !== $otp) {
            return $this->error('Invalid verification code.', 422);
        }

        $emailMatch = strtolower(trim($user->pending_email)) === strtolower(trim($request->email));
        if (! $emailMatch) {
            return $this->error('Email mismatch. Use the code sent to your pending email.', 422);
        }

        $user->update([
            'email'                  => $user->pending_email,
            'email_verified_at'      => now(),
            'pending_email'          => null,
            'email_change_token'     => null,
            'email_change_expires_at'=> null,
        ]);

        return $this->success(new UserResource($user->fresh()), 'Email updated and verified successfully.');
    }

    /**
     * POST /api/v1/profile/phone
     * Request a phone number change — sends OTP to the new phone.
     */
    public function requestPhoneChange(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string', 'max:20', 'regex:/^\+?[1-9]\d{6,14}$/', 'unique:users,phone,' . $request->user()->id],
        ]);

        $user = $request->user();
        $otp  = (string) random_int(100000, 999999);

        $user->update([
            'pending_phone'  => $request->phone,
            'otp'            => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        // In a real app, send via SMS gateway
        // For now, log it and return in local dev
        \Illuminate\Support\Facades\Log::info('Phone change OTP', ['phone' => $request->phone, 'otp' => $otp]);

        return $this->success(
            ['message' => 'OTP sent to ' . $request->phone],
            'OTP sent to new phone number.'
        );
    }

    /**
     * POST /api/v1/profile/phone/verify
     * Confirm the phone change using the OTP.
     */
    public function verifyPhoneChange(Request $request): JsonResponse
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $user = $request->user();

        if (! $user->pending_phone || ! $user->otp) {
            return $this->error('No pending phone change found.', 422);
        }

        if (! $user->otp_expires_at || $user->otp_expires_at->isPast()) {
            return $this->error('OTP has expired. Please request a new one.', 422);
        }

        if ($user->otp !== $request->otp) {
            return $this->error('Invalid OTP. Please try again.', 422);
        }

        $user->update([
            'phone'            => $user->pending_phone,
            'phone_verified_at'=> now(),
            'pending_phone'    => null,
            'otp'              => null,
            'otp_expires_at'   => null,
        ]);

        return $this->success(new UserResource($user->fresh()), 'Phone number updated and verified successfully.');
    }

    /**
     * POST /api/v1/profile/password
     * Change password (requires current password).
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (! $user->password || ! Hash::check($request->current_password, $user->password)) {
            return $this->error('Current password is incorrect.', 422);
        }

        $user->update(['password' => $request->password]);

        return $this->success(null, 'Password changed successfully.');
    }

    /**
     * POST /api/v1/profile/email/resend
     * Resend the email verification OTP for the current (unverified) email.
     */
    public function resendEmailVerification(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->email_verified_at) {
            return $this->error('Your email is already verified.', 422);
        }

        $otp = (string) random_int(100000, 999999);
        $user->update([
            'email_change_token'     => $otp,
            'email_change_expires_at'=> now()->addMinutes(10),
            'pending_email'          => $user->email,
        ]);

        Mail::to($user->email)->send(new VerifyEmailOtpMail(
            $user->email,
            $user->first_name ?? $user->name,
            $otp,
            false,
            10
        ));

        return $this->success(['message' => 'Verification code sent. Check your inbox.'], 'Verification code sent.');
    }
}
