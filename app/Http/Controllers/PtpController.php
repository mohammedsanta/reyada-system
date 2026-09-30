<?php

// app/Http/Controllers/PtpController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PtpController extends Controller
{
    /** Kanban columns, in order (first = right side in RTL). */
    public const STATUSES = [
        'active'  => ['label' => 'وعود نشطة',   'icon' => 'fa-calendar-check',    'color' => 'warning'],
        'review'  => ['label' => 'قيد المراجعة', 'icon' => 'fa-hourglass-half',    'color' => 'cyan'],
        'kept'    => ['label' => 'محقق كلياً',   'icon' => 'fa-circle-check',      'color' => 'brand'],
        'partial' => ['label' => 'محقق جزئياً',  'icon' => 'fa-circle-half-stroke', 'color' => 'info'],
        'broken'  => ['label' => 'مكسور',        'icon' => 'fa-circle-xmark',      'color' => 'danger'],
    ];

    private const SCOPES = [
        ''        => 'كل الوعود',
        'today'   => 'مستحقة اليوم',
        'overdue' => 'متأخرة',
        'week'    => 'خلال 7 أيام',
    ];

    private const MONTHS = [
        1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل', 5 => 'مايو', 6 => 'يونيو',
        7 => 'يوليو', 8 => 'أغسطس', 9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
    ];

    /* ---------------------------------------------------------------
     | STATIC DATA (temporary)
     * ------------------------------------------------------------- */

    // TODO (DB): Bank::findOrFail($id)
    private function bank(int $id): object
    {
        $banks = [
            1 => 'Emirates NBD',
            2 => 'بنك مصر',
            3 => 'البنك التجاري الدولي',
            4 => 'بنك القاهرة',
            5 => 'بنك فيصل الإسلامي',
        ];

        abort_unless(isset($banks[$id]), 404);

        return (object) ['id' => $id, 'name' => $banks[$id], 'logo' => null];
    }

    /** Only bank #1 has sample promises; other banks show empty columns. */
    private function promises(int $bankId): Collection
    {
        if ($bankId !== 1) {
            return collect();
        }

        // [client code, client name, promised amount, paid so far, due in N days, status, employee]
        $rows = [
            ['1000001', 'مصطفى خالد حسن',   1500, 0,    2,   'active',  'HOOL'],
            ['1000002', 'حسن زينب الهواري',  2000, 0,    0,   'active',  'HOOL'],
            ['1000003', 'منى هبة صالح',      1500, 0,    -1,  'active',  'Ahmed Borlsy'],
            ['1000004', 'إيمان هبة راضي',    1500, 0,    5,   'review',  'Ahmed Borlsy'],
            ['1000005', 'أحمد يوسف توفيق',   1500, 1500, -3,  'kept',    'Ahmed Borlsy'],
            ['1000000', 'فاطمة زينب السيد',  1000, 400,  -6,  'partial', 'HOOL'],
            ['1000002', 'حسن زينب الهواري',  3000, 0,    -10, 'broken',  'HOOL'],
        ];

        $today = Carbon::today();

        return collect($rows)->map(function (array $row, int $index) use ($today) {
            [$code, $name, $amount, $paid, $days, $status, $employee] = $row;

            $date = $today->copy()->addDays($days);
            [$dueLabel, $dueTone] = $this->due($status, $days);

            return (object) [
                'id'                 => $index + 1,
                'client_code'        => $code,
                'client_name'        => $name,
                'amount'             => $amount,
                'paid'               => $paid,
                'paid_percent'       => $amount > 0 ? (int) round($paid / $amount * 100) : 0,
                'promise_date'       => $date->toDateString(),        // Y-m-d (used for filters/calendar)
                'promise_date_label' => $date->format('d/m/Y'),
                'status'             => $status,
                'employee'           => $employee,
                'due_label'          => $dueLabel,
                'due_tone'           => $dueTone,                     // muted | warning | danger | brand | info
            ];
        });
    }

    /** Text under each card: "بعد 3 يوم", "متأخر 2 يوم", ... */
    private function due(string $status, int $days): array
    {
        return match (true) {
            $status === 'kept'    => ['تم السداد', 'brand'],
            $status === 'partial' => ['سُدد جزئياً', 'info'],
            $days < 0             => ['متأخر ' . abs($days) . ' يوم', 'danger'],
            $days === 0           => ['اليوم', 'warning'],
            default               => ['بعد ' . $days . ' يوم', 'muted'],
        };
    }

    /* ---------------------------------------------------------------
     | Action
     * ------------------------------------------------------------- */

    // GET /banks/{bank}/ptp?view=board|calendar&search=&scope=&month=YYYY-MM
    public function index(Request $request, int $bank): View
    {
        $bank = $this->bank($bank);
        $all  = $this->promises($bank->id);

        $filters = [
            'view'   => $request->query('view') === 'calendar' ? 'calendar' : 'board',
            'search' => trim((string) $request->query('search', '')),
            'scope'  => array_key_exists((string) $request->query('scope', ''), self::SCOPES) ? (string) $request->query('scope', '') : '',
            'month'  => (string) $request->query('month', ''),
        ];

        $today    = Carbon::today()->toDateString();
        $nextWeek = Carbon::today()->addDays(7)->toDateString();

        $filtered = $all
            ->when($filters['search'] !== '', function (Collection $c) use ($filters) {
                $needle = mb_strtolower($filters['search']);

                return $c->filter(fn ($p) => str_contains(mb_strtolower($p->client_name . ' ' . $p->client_code), $needle));
            })
            ->when($filters['scope'] === 'today',   fn (Collection $c) => $c->where('promise_date', $today))
            ->when($filters['scope'] === 'overdue', fn (Collection $c) => $c->filter(
                fn ($p) => in_array($p->status, ['active', 'review'], true) && $p->promise_date < $today
            ))
            ->when($filters['scope'] === 'week',    fn (Collection $c) => $c->filter(
                fn ($p) => $p->promise_date >= $today && $p->promise_date <= $nextWeek
            ))
            ->sortBy('promise_date')
            ->values();

        // Kanban columns
        $columns = collect(self::STATUSES)->map(fn (array $config, string $key) => $config + [
            'key'   => $key,
            'items' => $filtered->where('status', $key)->values(),
        ])->values();

        return view('ptp.index', [
            'bank'     => $bank,
            'filters'  => $filters,
            'scopes'   => self::SCOPES,
            'statuses' => self::STATUSES,
            'columns'  => $columns,
            'stats'    => $this->stats($all),
            'active'   => $all->where('status', 'active')->count(),
            'calendar' => $filters['view'] === 'calendar' ? $this->calendar($filtered, $filters['month']) : null,
        ]);
    }

    /** Cards on top (calculated from ALL promises of the bank, not the filtered ones). */
    private function stats(Collection $all): array
    {
        $kept     = $all->where('status', 'kept')->count();
        $resolved = $kept + $all->where('status', 'partial')->count() + $all->where('status', 'broken')->count();

        // Fulfillment rate = fully kept / resolved promises (placeholder formula)
        $rate = $resolved > 0 ? (int) round($kept / $resolved * 100) : 0;

        return [
            ['label' => 'وعود نشطة',    'value' => $all->where('status', 'active')->count(), 'icon' => 'fa-calendar-check', 'color' => 'warning'],
            ['label' => 'نسبة التحقيق', 'value' => $rate . '%',                              'icon' => 'fa-bullseye',       'color' => 'brand'],
            ['label' => 'قيد المراجعة', 'value' => $all->where('status', 'review')->count(), 'icon' => 'fa-hourglass-half', 'color' => 'cyan'],
            ['label' => 'مكسور',        'value' => $all->where('status', 'broken')->count(), 'icon' => 'fa-circle-xmark',   'color' => 'danger'],
        ];
    }

    /** Month grid (week starts on Saturday). */
    private function calendar(Collection $promises, string $monthParam): array
    {
        $month = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $monthParam)
            ? Carbon::createFromFormat('!Y-m', $monthParam)
            : Carbon::today()->startOfMonth();

        $todayStr = Carbon::today()->toDateString();
        $days     = [];

        for ($day = 1; $day <= $month->daysInMonth; $day++) {
            $date = $month->copy()->day($day)->toDateString();

            $days[] = [
                'day'       => $day,
                'is_today'  => $date === $todayStr,
                'promises'  => $promises->where('promise_date', $date)->values(),
            ];
        }

        return [
            'title'  => self::MONTHS[$month->month] . ' ' . $month->year,
            'prev'   => $month->copy()->subMonthNoOverflow()->format('Y-m'),
            'next'   => $month->copy()->addMonthNoOverflow()->format('Y-m'),
            'offset' => ($month->dayOfWeek + 1) % 7,   // empty cells before day 1 (Saturday = 0)
            'days'   => $days,
        ];
    }
}