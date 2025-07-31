<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Throwable;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'User registered successfully',
                'user' => $user,
                'token' => $token
            ], 201);
            
        } catch (Throwable $exception) {
            return $this->handleException($exception, 'User Registration');
        }
    }

    /**
     * Login user.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            if (!Auth::attempt($request->only('email', 'password'))) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            $user = User::where('email', $request->email)->firstOrFail();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'token' => $token
            ], 200);
            
        } catch (Throwable $exception) {
            return $this->handleException($exception, 'User Login');
        }
    }

    /**
     * Logout user.
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return $this->successResponse(null, 'Logged out successfully');
            
        } catch (Throwable $exception) {
            return $this->handleException($exception, 'User Logout');
        }
    }

    /**
     * Get authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        try {
            return response()->json([
                'user' => $request->user()
            ], 200);
            
        } catch (Throwable $exception) {
            return $this->handleException($exception, 'Get User Profile');
        }
    }
}
