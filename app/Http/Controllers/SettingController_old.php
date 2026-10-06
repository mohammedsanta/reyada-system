<?php

// app/Http/Controllers/SettingController.php  (static data)
// "إعدادات النظام": company data, collection rules, security and notification switches.
// Stored in the key-value `settings` table (see the migration 2026_10_01_000029_create_settings_table.php).

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingController extends Controller
{
    private const PER_PAGE = [10 => 10, 25 => 25, 50 => 50, 100 => 100];

    /** TODO (DB): Setting::pluck('value', 'key')->all() merged over these defaults */
    private function settings(): array
    {
        return [
            'company_name' => 'Collex', 'company_phone' => '0227000000', 'company_email' => 'info@collex.com', 'company_address' => 'القاهرة، مصر',

            'default_per_page' => 25, 'max_cases_per_employee' => 50, 'ptp_default_days' => 2, 'ptp_overdue_alert_days' => 1,

            'login_max_attempts' => 5, 'session_minutes' => 120,

            'notify_payment' => true, 'notify_complaint' => true, 'notify_broken_promise' => true, 'daily_digest' => false,
        ];
    }

    // GET /settings
    public function index(): View
    {
        return view('settings.index', ['s' => $this->settings(), 'perPage' => self::PER_PAGE]);
    }

    // PUT /settings
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'company_name'           => ['required', 'string', 'max:150'],
            'company_phone'          => ['nullable', 'string', 'max:30'],
            'company_email'          => ['nullable', 'email', 'max:150'],
            'company_address'        => ['nullable', 'string', 'max:255'],

            'default_per_page'       => ['required', Rule::in(array_keys(self::PER_PAGE))],
            'max_cases_per_employee' => ['required', 'integer', 'between:1,500'],
            'ptp_default_days'       => ['required', 'integer', 'between:0,30'],
            'ptp_overdue_alert_days' => ['required', 'integer', 'between:0,30'],

            'login_max_attempts'     => ['required', 'integer', 'between:3,10'],
            'session_minutes'        => ['required', 'integer', 'between:15,1440'],

            'notify_payment'         => ['nullable', 'boolean'],
            'notify_complaint'       => ['nullable', 'boolean'],
            'notify_broken_promise'  => ['nullable', 'boolean'],
            'daily_digest'           => ['nullable', 'boolean'],
        ]);

        // TODO (DB): foreach ($validated as $key => $value) Setting::updateOrCreate(['key' => $key], ['value' => $value, 'group' => ...]);
        //            clear the settings cache, and write an activity_logs row (event = updated).
        return redirect()->route('settings.index')->with('success', 'تم حفظ الإعدادات (بيانات تجريبية، لم يتم الحفظ).');
    }
}