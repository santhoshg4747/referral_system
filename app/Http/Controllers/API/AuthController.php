<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Register a new user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function register(Request $request)
    {
        $isAdmin = $request->input('is_admin', false);
        
        // First, validate basic fields
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'is_admin' => 'sometimes|boolean',
            'referral_code' => $isAdmin ? 'nullable' : 'nullable|string|exists:users,referral_code',
        ]);

        // Then check email uniqueness for the specific user type
        $existingUser = User::where('email', $validated['email'])
            ->where('is_admin', $isAdmin)
            ->first();

        if ($existingUser) {
            return response()->json([
                'message' => 'Validation error',
                'errors' => [
                    'email' => ["The email has already been taken by another " . ($isAdmin ? 'admin' : 'user') . "."],
                ],
            ], 422);
        }

        // Start database transaction
        $user = \DB::transaction(function () use ($validated) {
            // Find referrer if referral code is provided
            $referredBy = null;
            if (!empty($validated['referral_code'])) {
                $referredBy = User::where('referral_code', $validated['referral_code'])->first();
            }

            // Create the user
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ];

            // Only set referred_by if it's not an admin registration
            if (empty($validated['is_admin'])) {
                $userData['referred_by'] = $referredBy?->id;
            }

            // Set admin status if specified
            $userData['is_admin'] = $validated['is_admin'] ?? false;

            // Only create referral record if not an admin and was referred
            if ($referredBy && empty($validated['is_admin'])) {
                $referralService = app(\App\Services\ReferralService::class);
                $referralService->createReferral($referredBy, $user, $validated['referral_code']);
            }

            return $user;
        });

        // Generate API token
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->sendResponse([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 'User registered successfully', 201);
    }

    /**
     * Login user and create token.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'remember_me' => 'boolean',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->sendError('Invalid login credentials', [], 401);
        }

        $user = $request->user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->sendResponse([
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Logout user (Revoke the token).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->sendResponse([], 'Successfully logged out');
    }
}
