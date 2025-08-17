<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        return Inertia::render('customer/profile/Edit', [
            'data' => Customer::findOrFail(Auth::guard('customer')->user()->id)
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'phone' => 'required|max:100',
            'address' => 'required|max:1000',
        ]);

        $customer = Customer::findOrFail(Auth::guard('customer')->user()->id);
        $customer->update($validated);

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

        $user = Auth::guard('customer')->user();        

        if (! $user || ! Hash::check($request->input('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah.']);
        }

        $customer = Customer::findOrFail($user->id);
        $customer->update([
            'password' => Hash::make($request->input('password')),
        ]);

        return back()->with('success', 'Password berhasil diperbarui.');
    }
}
