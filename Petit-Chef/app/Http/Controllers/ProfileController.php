<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', [
            'user' => auth()->user(),
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'regex:/^[\pL\s\'\-]+$/u'],
            'email' => ['required', 'email:rfc,dns', 'unique:users,email,'.$user->id],
            'phone' => ['required', 'string', 'regex:/^(?:\+228\d{8}|\d{9})$/', 'unique:users,phone,'.$user->id],
            'profile_photo' => ['nullable', 'image', 'max:2048', 'dimensions:min_width=500,min_height=500'],
        ]);

        if ($request->hasFile('profile_photo')) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }

            $validated['profile_photo_path'] = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        $emailChanged = $validated['email'] !== $user->email;

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'profile_photo_path' => $validated['profile_photo_path'] ?? $user->profile_photo_path,
        ]);

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        return back()->with('status', 'Profil mis à jour.');
    }

    public function password(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $request->user()->update([
            'password' => $validated['password'],
        ]);

        return back()->with('status', 'Mot de passe mis à jour.');
    }
}