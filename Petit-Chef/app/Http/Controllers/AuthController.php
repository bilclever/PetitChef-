<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function create()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function register()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    public function store(Request $request)
    {
        $this->ensureNotRateLimited($request, 'login', 5, 60);

        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'remember' => ['sometimes', 'boolean'],
            'website' => ['nullable', 'string'],
        ]);

        if (! empty($validated['website'] ?? null)) {
            throw ValidationException::withMessages([
                'website' => 'Connexion refusée.',
            ]);
        }

        if (! Auth::attempt([
            'email' => $validated['email'],
            'password' => $validated['password'],
        ], false)) {
            RateLimiter::hit($this->rateLimitKey($request, 'login'), 60);

            throw ValidationException::withMessages([
                'email' => 'Identifiants invalides.',
            ]);
        }

        // Vérifier le statut du compte après authentification
        $user = Auth::user();
        if (in_array($user->account_status ?? 'active', ['suspended', 'banned'], true)) {
            Auth::logout();
            $reason = $user->account_status_reason ? ' Raison : ' . $user->account_status_reason : '';
            throw ValidationException::withMessages([
                'email' => 'Votre compte a été ' . ($user->account_status === 'banned' ? 'banni' : 'suspendu') . '.' . $reason,
            ]);
        }

        RateLimiter::clear($this->rateLimitKey($request, 'login'));

        $request->session()->regenerate();
        $request->session()->put('_bound_auth_user_id', (int) Auth::id());

        return redirect()->route('dashboard');
    }

    public function registerStore(Request $request)
    {
        $this->ensureNotRateLimited($request, 'register', 5, 3600);
        RateLimiter::hit($this->rateLimitKey($request, 'register'), 3600);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\'\-]+$/u'],
            'email' => ['required', 'email:rfc,dns', 'unique:users,email'],
            'phone' => ['required', 'string', 'regex:/^(?:\+228\d{8}|\d{9})$/', 'unique:users,phone'],
            'role' => ['required', 'in:client,cook'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'profile_photo' => ['nullable', 'image', 'max:2048', 'dimensions:min_width=500,min_height=500'],
            'website' => ['nullable', 'string'],
        ]);

        if (! empty($validated['website'] ?? null)) {
            throw ValidationException::withMessages([
                'website' => 'Inscription refusée.',
            ]);
        }

        $profilePhotoPath = null;

        if ($request->hasFile('profile_photo')) {
            $profilePhotoPath = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'role' => $validated['role'],
            'approval_status' => $validated['role'] === 'cook' ? 'pending' : 'approved',
            'profile_photo_path' => $profilePhotoPath,
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('_bound_auth_user_id', (int) Auth::id());
        RateLimiter::clear($this->rateLimitKey($request, 'register'));

        return redirect()->route('dashboard')->with('status', 'Compte créé avec succès.');
    }

    public function destroy(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    protected function ensureNotRateLimited(Request $request, string $action, int $maxAttempts, int $decaySeconds): void
    {
        $key = $this->rateLimitKey($request, $action);

        if (! RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return;
        }

        throw ValidationException::withMessages([
            'email' => 'Trop de tentatives. Réessaie dans '.RateLimiter::availableIn($key).' secondes.',
        ]);
    }

    protected function rateLimitKey(Request $request, string $action): string
    {
        if ($action === 'register') {
            return $request->ip().'|'.$action;
        }

        return Str::lower($request->input('email', 'guest')).'|'.$request->ip().'|'.$action;
    }
}