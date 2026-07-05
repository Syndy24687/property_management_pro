<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * Register a new user.
     *
     * POST /api/v1/auth/register
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'phone'    => $request->phone,
        ]);

        $user->assignRole('tenant');

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful.',
            'data'    => [
                'user' => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'roles' => $user->getRoleNames(),
                ],
                'authorization' => [
                    'token'      => $token,
                    'type'       => 'Bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60,
                ],
            ],
        ], 201);
    }

    /**
     * Authenticate user and return JWT.
     *
     * POST /api/v1/auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        $token = auth('api')->attempt($credentials);

        if (!$token) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $user = auth('api')->user();

        if ($user->status !== 'active') {
            auth('api')->logout();
            throw ValidationException::withMessages([
                'email' => ['Your account has been ' . $user->status . '.'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Login successful.',
            'data'    => [
                'user' => [
                    'id'          => $user->id,
                    'name'        => $user->name,
                    'email'       => $user->email,
                    'phone'       => $user->phone,
                    'roles'       => $user->getRoleNames(),
                    'permissions' => $user->getAllPermissions()->pluck('name'),
                ],
                'authorization' => [
                    'token'      => $token,
                    'type'       => 'Bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60,
                ],
            ],
        ]);
    }

    /**
     * Invalidate JWT (logout).
     *
     * POST /api/v1/auth/logout
     */
    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return response()->json([
            'success' => true,
            'message' => 'Successfully logged out.',
        ]);
    }

    /**
     * Refresh JWT token.
     *
     * POST /api/v1/auth/refresh
     */
    public function refresh(): JsonResponse
    {
        $token = auth('api')->refresh();
        $user  = auth('api')->user();

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed.',
            'data'    => [
                'user' => [
                    'id'    => $user->id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'roles' => $user->getRoleNames(),
                ],
                'authorization' => [
                    'token'      => $token,
                    'type'       => 'Bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60,
                ],
            ],
        ]);
    }

    /**
     * Get authenticated user profile.
     *
     * GET /api/v1/auth/me
     */
    public function me(): JsonResponse
    {
        $user = auth('api')->user();

        return response()->json([
            'success' => true,
            'data'    => [
                'id'                      => $user->id,
                'name'                    => $user->name,
                'email'                   => $user->email,
                'phone'                   => $user->phone,
                'status'                  => $user->status,
                'company_id'              => $user->company_id,
                'emergency_contact_name'  => $user->emergency_contact_name,
                'emergency_contact_phone' => $user->emergency_contact_phone,
                'date_of_birth'           => $user->date_of_birth?->format('Y-m-d'),
                'roles'                   => $user->getRoleNames(),
                'permissions'             => $user->getAllPermissions()->pluck('name'),
                'created_at'              => $user->created_at,
            ],
        ]);
    }

    /**
     * Update authenticated user's profile.
     *
     * PUT /api/v1/auth/profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        $validated = $request->validate([
            'name'                    => ['sometimes', 'string', 'max:255'],
            'phone'                   => ['sometimes', 'string', 'max:20'],
            'emergency_contact_name'  => ['sometimes', 'nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'date_of_birth'           => ['sometimes', 'nullable', 'date'],
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data'    => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ]);
    }

    /**
     * Change password.
     *
     * PUT /api/v1/auth/change-password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = auth('api')->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        // Invalidate current token and issue a new one
        auth('api')->logout();
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully. A new token has been issued.',
            'data'    => [
                'authorization' => [
                    'token'      => $token,
                    'type'       => 'Bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60,
                ],
            ],
        ]);
    }
}
