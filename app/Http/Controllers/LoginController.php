<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Response as InertiaResponse;

class LoginController extends Controller
{
    public function loginPage(): InertiaResponse
    {
        return inertia('Login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        if (Auth::attempt(['username' => $validated['username'], 'password' => $validated['password']])) {
            $request->session()->regenerate();
            $request->session()->regenerateToken();
        } else {
            return back()->withErrors(['username' => 'Username atau password salah.']);
        }

        return response()->redirectTo(route('home'));
    }

    public function logout(Request $request) {
        Auth::logout();
        $request->session()->regenerate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
