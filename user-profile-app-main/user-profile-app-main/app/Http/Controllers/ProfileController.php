<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        // Validate and handle the avatar upload
        if ($request->hasFile('avatar')) {
            // Validate the avatar file
            $request->validate([
                'avatar' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // 2MB max
            ]);

            // Store the avatar file
            $avatarPath = $request->file('avatar')->store('avatars', 'public');

            // Set the new avatar path on the user
            $request->user()->avatar = $avatarPath;
        }

        // Update other profile information
        $request->user()->fill($request->validated());

        // If the email has changed, reset email verification
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        // Save the user changes
        $request->user()->save();

        // Redirect back with a success message
        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }


    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
