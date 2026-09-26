<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    /**
     * Show account settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Customer/Account', [
            'status' => session('status'),
        ]);
    }

    /**
     * Update user account info.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'locale' => ['required', 'string', Rule::in(['ar', 'fr', 'en'])],
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->fill($validated);
        $user->save();

        session(['locale' => $validated['locale']]);

        return back()->with('status', 'account-updated');
    }
}
