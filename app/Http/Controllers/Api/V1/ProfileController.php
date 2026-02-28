<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\V1\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
     * Request an email address change — sends verification link to the new email.
     */
    public function requestEmailChange(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|unique:users,email,' . $request->user()->id,
        ]);

        $user  = $request->user();
        $token = Str::random(64);

        $user->update([
            'pending_email'          => $request->email,
            'email_change_token'     => hash('sha256', $token),
            'email_change_expires_at'=> now()->addHours(24),
        ]);

        // Send verification email
        $verifyUrl = config('app.frontend_url', 'http://localhost:3001')
            . '/profile/verify-email?token=' . $token
            . '&email=' . urlencode($request->email);

        Mail::raw(
            "Hello {$user->first_name},\n\nPlease verify your new email address by clicking the link below:\n\n{$verifyUrl}\n\nThis link expires in 24 hours.\n\nIf you did not request this change, please ignore this email.",
            function ($m) use ($request, $user) {
                $m->to($request->email)
                  ->subject('Verify your new email address — ' . config('app.name'));
            }
        );

        return $this->success(
            ['message' => 'Verification email sent to ' . $request->email],
            'Verification email sent.'
        );
    }

    /**
     * POST /api/v1/profile/email/verify
     * Confirm the email change using the token.
     */
    public function verifyEmailChange(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required|string|size:64',
            'email' => 'required|email',
        ]);

        $user = $request->user();

        if (
            ! $user->pending_email ||
            ! $user->email_change_token ||
            ! $user->email_change_expires_at ||
            $user->email_change_expires_at->isPast()
        ) {
            return $this->error('No pending email change or the link has expired.', 422);
        }

        if (! hash_equals($user->email_change_token, hash('sha256', $request->token))) {
            return $this->error('Invalid verification token.', 422);
        }

        if ($user->pending_email !== $request->email) {
            return $this->error('Email mismatch. Please use the latest verification link.', 422);
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
     * Resend the email verification for the current (unverified) email.
     */
    public function resendEmailVerification(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->email_verified_at) {
            return $this->error('Your email is already verified.', 422);
        }

        $token = Str::random(64);
        $user->update([
            'email_change_token'     => hash('sha256', $token),
            'email_change_expires_at'=> now()->addHours(24),
            'pending_email'          => $user->email,
        ]);

        $verifyUrl = config('app.frontend_url', 'http://localhost:3001')
            . '/profile/verify-email?token=' . $token
            . '&email=' . urlencode($user->email);

        Mail::raw(
            "Hello {$user->first_name},\n\nPlease verify your email address:\n\n{$verifyUrl}\n\nThis link expires in 24 hours.",
            fn ($m) => $m->to($user->email)->subject('Verify your email — ' . config('app.name'))
        );

        return $this->success(['message' => 'Verification email resent.'], 'Verification email sent.');
    }
}
