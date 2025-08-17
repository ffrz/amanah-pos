<?php

namespace Modules\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('admin/profile/Edit');
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|min:2|max:100',
        ]);

        $request->user()->update([
            'name' => $validated['name'],
        ]);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required'],
            'password' => 'required|confirmed|min:5',
        ]);

        $user = $request->user();

        if (! $user || ! Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah.']);
        }

        $user->update([
            'password' => Hash::make($request->input('password')),
        ]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
