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
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user()->load('profesional'),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /** Datos públicos del profesional (especialidad, biografía, experiencia). */
    public function updateProfesional(Request $request): RedirectResponse
    {
        $profesional = $request->user()->profesional;
        abort_unless($profesional, 403);

        $profesional->update($request->validate([
            'especialidad' => ['required', 'string', 'max:120'],
            'anios_experiencia' => ['required', 'integer', 'min:0', 'max:60'],
            'biografia' => ['nullable', 'string', 'max:1500'],
        ]));

        return Redirect::route('profile.edit')->with('status', 'profesional-updated');
    }

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
