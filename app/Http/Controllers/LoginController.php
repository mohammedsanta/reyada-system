<?php

// app/Http/Controllers/LoginController.php  (REAL authentication: uses the users table)

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    // GET /login
    public function create(): View
    {
        return view('auth.login');
    }

    // POST /login
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // brute-force protection: 5 failed attempts per e-mail + IP, then wait
        $throttleKey = Str::lower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "محاولات كثيرة. حاول مرة أخرى بعد {$seconds} ثانية.",
            ]);
        }

        $credentials = $request->only('email', 'password');

        // only ACTIVE employees can log in (suspended / inactive accounts are refused)
        if (Schema::hasColumn('users', 'status')) {
            $credentials['status'] = 'active';
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 60);

            throw ValidationException::withMessages([
                'email' => 'بيانات الدخول غير صحيحة أو الحساب غير نشط.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();   // protects against session fixation

        // remember when and from where the user logged in
        if (Schema::hasColumn('users', 'last_login_at')) {
            $request->user()->forceFill(['last_login_at' => now(), 'last_login_ip' => $request->ip()])->saveQuietly();
        }

        // TODO: write an activity_logs row (event = login)
        return redirect()->intended(route('dashboard'));
    }

    // POST /logout
    public function destroy(Request $request): RedirectResponse
    {
        // TODO: write an activity_logs row (event = logout)
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}