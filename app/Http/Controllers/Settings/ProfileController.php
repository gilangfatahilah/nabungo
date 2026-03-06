<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Delete avatar reference (UploadThing URL)
        // Note: Implement UploadThing file deletion API if needed

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Update the user's avatar.
     * Now accepts UploadThing URL instead of file upload.
     */
    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar_url' => ['required', 'url'],
        ]);

        $user = $request->user();

        // Note: Old avatar on UploadThing should be deleted via their API if needed
        // You can implement UploadThing file deletion here if required

        // Store the UploadThing URL
        $user->update(['avatar' => $request->avatar_url]);

        return to_route('profile.edit');
    }

    /**
     * Delete the user's avatar.
     */
    public function deleteAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->avatar) {
            // Note: If you want to delete the file from UploadThing,
            // you need to implement their deletion API here
            // For now, we just remove the reference from database
            $user->update(['avatar' => null]);
        }

        return to_route('profile.edit');
    }
}
