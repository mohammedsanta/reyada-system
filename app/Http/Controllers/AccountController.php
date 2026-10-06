<?php

// app/Http/Controllers/AccountController.php  (REAL: works on the logged in user)
// "حسابي": every employee edits his own name / phone and changes his own password.

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AccountController extends Controller
{
    // GET /account
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('account.edit', [
            'user'      => $user,
            'role'      => data_get($user, 'role.label', 'موظف'),      // works once the User model has a role() relation
            'code'      => data_get($user, 'employee_code', '-'),
            'lastLogin' => data_get($user, 'last_login_at') ? Carbon::parse($user->last_login_at)->format('d/m/Y H:i') : '-',
            'lastIp'    => data_get($user, 'last_login_ip', '-'),
        ]);
    }

    // PUT /account : name + phone (the e-mail is changed by an administrator only)
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'phone' => ['required', 'regex:/^01[0-9]{9}$/', Rule::unique('users', 'phone')->ignore($user->id)],
        ], [
            'phone.regex'  => 'رقم الهاتف يجب أن يكون رقم موبايل مصري (11 رقماً يبدأ بـ 01).',
            'phone.unique' => 'رقم الهاتف مستخدم لموظف آخر.',
        ]);

        $user->update($data);

        // TODO: write an activity_logs row (event = updated, subject = this user)
        return redirect()->route('account.edit')->with('success', 'تم حفظ بياناتك.');
    }

    // PUT /account/password
    public function password(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],            // must match the password he has now
            'password'         => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ], [
            'current_password.current_password' => 'كلمة المرور الحالية غير صحيحة.',
            'password.different'                => 'كلمة المرور الجديدة يجب أن تختلف عن الحالية.',
        ]);

        $request->user()->update(['password' => Hash::make($request->password)]);

        // TODO: write an activity_logs row (event = updated, "password changed")
        return redirect()->route('account.edit')->with('success', 'تم تغيير كلمة المرور.');
    }
}