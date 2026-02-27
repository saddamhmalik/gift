<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\Controller;
use App\Http\Resources\V1\UserResource;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function __construct(
        protected AuthService $authService
    ) {}

    public function register(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => ['required', 'string', 'max:20', 'regex:/^\+?[1-9]\d{6,14}$/'],
            'password' => 'required|string|min:8|confirmed',
        ]);
        if ($v->fails()) {
            return $this->error('Validation failed', 422, $v->errors());
        }

        $user = $this->authService->register(
            $request->first_name,
            $request->last_name,
            $request->email,
            $request->phone,
            $request->password
        );
        $token = $user->createToken('api')->plainTextToken;

        return $this->success([
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ], 'Registered successfully', 201);
    }

    public function login(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if ($v->fails()) {
            return $this->error('Validation failed', 422, $v->errors());
        }

        $user = $this->authService->login($request->email, $request->password);
        if (!$user) {
            return $this->error('Invalid credentials', 401);
        }

        $user->tokens()->delete();
        $token = $user->createToken('api')->plainTextToken;

        return $this->success([
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function sendOtp(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'phone' => 'required|string|max:20',
        ]);
        if ($v->fails()) {
            return $this->error('Validation failed', 422, $v->errors());
        }

        $result = $this->authService->sendOtp($request->phone);
        return $this->success($result);
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'phone' => 'required|string|max:20',
            'otp' => 'required|string|size:6',
        ]);
        if ($v->fails()) {
            return $this->error('Validation failed', 422, $v->errors());
        }

        $user = $this->authService->verifyOtp($request->phone, $request->otp);
        if (!$user) {
            return $this->error('Invalid or expired OTP', 401);
        }

        $user->tokens()->delete();
        $token = $user->createToken('api')->plainTextToken;

        return $this->success([
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function google(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'access_token' => 'required|string',
        ]);
        if ($v->fails()) {
            return $this->error('Validation failed', 422, $v->errors());
        }

        try {
            $socialUser = Socialite::driver('google')->userFromToken($request->access_token);
        } catch (\Throwable $e) {
            return $this->error('Invalid Google token', 401);
        }

        $user = $this->authService->findOrCreateFromGoogle($socialUser);
        $user->tokens()->delete();
        $token = $user->createToken('api')->plainTextToken;

        return $this->success([
            'user' => new UserResource($user),
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(null, 'Logged out successfully');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success(new UserResource($request->user()));
    }
}
