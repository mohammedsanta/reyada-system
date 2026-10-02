<?php

// app/Http/Controllers/DcrController.php  (static data)
// "DCR": the Daily Collection Report. One row per employee per day:
// the employee submits it, a supervisor approves or rejects it.

namespace App\Http\Controllers;

use App\Support\StaticBanks;
use App\Support\StaticData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DcrController extends Controller
{
    private const WEEKDAYS = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];

    private function parseDate(?string $value): Carbon
    {
        return $value && preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) && strtotime($value)
            ? Carbon::createFromFormat('!Y-m-d', $value)
            : Carbon::today();
    }

    /** Sample report rows for one day. TODO (DB): DailyCollectionReport::with('user')->whereDate('report_date', $date) */
    private function rows(Carbon $date): Collection
    {
        if ($date->isFuture()) {
            return collect();   // no report exists for a day that did not happen yet
        }

        $seed = (int) $date->format('j') % 5;   // makes the numbers change a little from day to day

        // user id => cases worked, calls, visits, promises, promised amount, collected
        $base = [
            2 => [8, 22, 1, 2, 3000, 1500],
            3 => [12, 35, 2, 3, 4500, 1000],
            4 => [14, 41, 3, 4, 6000, 2500],
        ];

        // today: mixed statuses so every button can be tried. Older days: everything is approved.
        $statuses = $date->isToday() ? [2 => 'approved', 3 => 'submitted', 4 => 'draft'] : [2 => 'approved', 3 => 'approved', 4 => 'approved'];

        return StaticData::employees()->map(function ($user) use ($base, $statuses, $seed) {
            [$cases, $calls, $visits, $promises, $promised, $collected] = $base[$user->id] ?? [0, 0, 0, 0, 0, 0];

            return (object) [
                'user'      => $user,
                'cases'     => $cases,
                'calls'     => $calls + $seed,
                'visits'    => $visits,
                'promises'  => $promises,
                'promised'  => $promised,
                'collected' => $collected + $seed * 100,
                'status'    => $statuses[$user->id] ?? 'draft',
            ];
        });
    }

    // GET /banks/{bank}/dcr?date=YYYY-MM-DD
    public function index(Request $request, int $bank): View
    {
        $date = $this->parseDate($request->query('date'));
        $rows = $this->rows($date);

        return view('banks.dcr', [
            'bank'    => StaticBanks::find($bank),
            'date'    => $date,
            'dayName' => self::WEEKDAYS[$date->dayOfWeek],
            'prev'    => $date->copy()->subDay()->toDateString(),
            'next'    => $date->copy()->addDay()->toDateString(),
            'isToday' => $date->isToday(),
            'rows'    => $rows,
            'totals'  => [
                'calls'     => $rows->sum('calls'),
                'visits'    => $rows->sum('visits'),
                'promises'  => $rows->sum('promises'),
                'promised'  => $rows->sum('promised'),
                'collected' => $rows->sum('collected'),
                'cases'     => $rows->sum('cases'),
            ],
        ]);
    }

    // PUT /banks/{bank}/dcr/{report}      ({report} = employee id in this static version)
    public function update(Request $request, int $bank, int $report): RedirectResponse
    {
        StaticBanks::find($bank);
        $employee = StaticData::user($report);

        $data = $request->validate([
            'date'   => ['required', 'date_format:Y-m-d'],
            'status' => ['required', Rule::in(['submitted', 'approved', 'rejected'])],
        ]);

        $message = [
            'submitted' => "تم إرسال تقرير {$employee->name} للاعتماد",
            'approved'  => "تم اعتماد تقرير {$employee->name}",
            'rejected'  => "تم رفض تقرير {$employee->name} وإعادته للموظف",
        ][$data['status']];

        // TODO (DB): update daily_collection_reports.status (+ submitted_at / approved_by / approved_at)
        return redirect()
            ->route('banks.dcr.index', ['bank' => $bank, 'date' => $data['date']])
            ->with('success', $message . ' (بيانات تجريبية، لم يتم الحفظ).');
    }
}