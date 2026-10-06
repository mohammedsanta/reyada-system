<?php

// app/Http/Controllers/PasswordResetController.php  (REAL: Laravel's password broker, needs working MAIL_* settings in .env)

namespace App\Http\Controllers;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    // GET /forgot-password
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    // POST /forgot-password : e-mail the reset link
    public function store(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_THROTTLED) {
            throw ValidationException::withMessages(['email' => 'انتظر قليلاً قبل طلب رابط جديد.']);
        }

        // the same answer for every e-mail, so nobody can find out which e-mails are registered
        return back()->with('success', 'إذا كان البريد مسجلاً لدينا فستصلك رسالة تحتوي على رابط استعادة كلمة المرور.');
    }

    // GET /reset-password/{token}
    public function edit(Request $request, string $token): View
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->query('email')]);
    }

    // POST /reset-password
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => 'رابط الاستعادة غير صالح أو منتهي. اطلب رابطاً جديداً.']);
        }

        return redirect()->route('login')->with('success', 'تم تغيير كلمة المرور، سجّل الدخول بها الآن.');
    }
}