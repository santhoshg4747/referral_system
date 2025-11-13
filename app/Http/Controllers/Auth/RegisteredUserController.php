<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register', [
            'isAdminRegistration' => request()->is('admin/register')
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $isAdmin = $request->is('admin/register');
        \Log::info('Registration attempt', [
            'isAdmin' => $isAdmin,
            'path' => $request->path(),
            'url' => $request->url(),
            'fullUrl' => $request->fullUrl()
        ]);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'referral_code' => $isAdmin ? 'nullable' : 'nullable|string|exists:users,referral_code',
        ]);

        // Start database transaction
        $user = \DB::transaction(function () use ($validated, $isAdmin) {
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'is_admin' => $isAdmin,
            ];
            
            // Only process referral for non-admin users
            if (!$isAdmin && !empty($validated['referral_code'])) {
                $referredBy = User::where('referral_code', $validated['referral_code'])->first();
                if ($referredBy) {
                    $userData['referred_by'] = $referredBy->id;
                }
            }

            // Create the user
            $user = User::create($userData);

            // Create referral record if user was referred and not an admin
            if (!$isAdmin && !empty($validated['referral_code']) && isset($referredBy)) {
                $referralService = app(\App\Services\ReferralService::class);
                $referralService->createReferral($referredBy, $user, $validated['referral_code']);
            }

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        if ($isAdmin) {
            \Log::info('Redirecting to admin dashboard');
            return redirect()->route('admin.dashboard');
        }

        \Log::info('Redirecting to user dashboard');
        return redirect(route('dashboard', absolute: false));
    }
}
