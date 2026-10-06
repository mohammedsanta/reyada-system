<?php

// app/Http/Controllers/ClientController.php  (static data)
// "العملاء": ONE list of the clients of ALL banks, with a global search.
// (The clients of a single bank, with the edit dialog, are on the bank's own page: BankController@show.)

namespace App\Http\Controllers;

use App\Support\StaticBanks;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class ClientController extends Controller
{
    private const PER_PAGE = [10, 25, 50];

    /** TODO (DB): DebtCase::with(['client.governorate', 'bank', 'loanType', 'assignedUser']) of the active portfolios */
    private function clients(): Collection
    {
        $banks = StaticBanks::all()->keyBy('id');

        // code, name, national id, phone, governorate, bank id, loan type, bucket, debt, collected, employee, status
        $rows = [
            ['1000001', 'مصطفى خالد حسن',   '27204244494993', '01080672586', 'أسيوط',       1, 'قرض شخصي',      2, 7000,  1500, 'HOOL',         'ACTIVE'],
            ['1000002', 'حسن زينب الهواري',  '28112243502255', '01299928200', 'سوهاج',       1, 'تمويل عقاري',   2, 7000,  0,    'HOOL',         'ACTIVE'],
            ['1000004', 'إيمان هبة راضي',    '25002158210174', '01174820823', 'الإسماعيلية', 1, 'قرض شخصي',      1, 7000,  1500, 'Ahmed Borlsy', 'ACTIVE'],
            ['1000003', 'منى هبة صالح',      '29611268968147', '01097389540', 'القاهرة',     1, 'تمويل عقاري',   2, 7000,  3000, 'Ahmed Borlsy', 'ACTIVE'],
            ['1000005', 'أحمد يوسف توفيق',   '28011048181918', '01096103142', 'الدقهلية',    1, 'قرض شخصي',      1, 7000,  7000, null,           'PAID'],
            ['1000000', 'فاطمة زينب السيد',  '29203065580669', '01055111338', 'المنيا',      1, 'قرض سلع معمرة', 0, 7000,  1200, null,           'ACTIVE'],

            ['2000001', 'عمرو سامي إبراهيم',  '28905121400231', '01011223344', 'القاهرة',     2, 'قرض شخصي',      3, 18000, 2000, 'HOOL',         'ACTIVE'],
            ['2000002', 'سارة محمود علي',     '29407081301142', '01122334455', 'الجيزة',      2, 'تمويل عقاري',   2, 42000, 9000, 'Ahmed Borlsy', 'ACTIVE'],
            ['2000003', 'كريم حسام الدين',    '28603151601987', '01233445566', 'الإسكندرية',  2, 'قرض سلع معمرة', 1, 9500,  9500, 'HOOL',         'PAID'],

            ['3000001', 'ياسمين طارق فؤاد',   '29112051102356', '01544556677', 'الجيزة',      3, 'قرض شخصي',      2, 27000, 4000, 'Ahmed Borlsy', 'ACTIVE'],
            ['3000002', 'محمد عادل منصور',    '28208221503471', '01055667788', 'الدقهلية',    3, 'تمويل عقاري',   3, 65000, 0,    'HOOL',         'LEGAL'],

            ['5000001', 'هدى رمضان عبدالله',  '29509091204568', '01166778899', 'القاهرة',     5, 'قرض شخصي',      1, 12000, 3000, null,           'ACTIVE'],
        ];

        return collect($rows)->map(function (array $r) use ($banks) {
            $c = (object) array_combine(
                ['code', 'name', 'national_id', 'phone', 'governorate', 'bank_id', 'loan_type', 'bucket', 'debt', 'collected', 'employee', 'status'], $r
            );

            $c->bank      = $banks[$c->bank_id]->name;
            $c->remaining = $c->debt - $c->collected;
            // opens the bank's clients page already searching for this client (that page has the edit dialog)
            $c->url = Route::has('banks.show') ? route('banks.show', ['bank' => $c->bank_id, 'search' => $c->code]) : '#';

            return $c;
        });
    }

    // GET /clients?search=&bank=&loan_type=&governorate=&bucket=&status=&per_page=&page=
    public function index(Request $request): View
    {
        $all = $this->clients();

        $perPage = (int) $request->query('per_page', 10);
        $perPage = in_array($perPage, self::PER_PAGE, true) ? $perPage : 10;
        $page    = max(1, (int) $request->query('page', 1));

        $filters = [
            'search'      => trim((string) $request->query('search', '')),
            'bank'        => (string) $request->query('bank', ''),
            'loan_type'   => (string) $request->query('loan_type', ''),
            'governorate' => (string) $request->query('governorate', ''),
            'bucket'      => (string) $request->query('bucket', ''),
            'status'      => (string) $request->query('status', ''),
            'per_page'    => $perPage,
        ];

        $filtered = $all
            ->when($filters['search'] !== '', function (Collection $c) use ($filters) {
                $needle = mb_strtolower($filters['search']);

                return $c->filter(fn ($x) => str_contains(mb_strtolower($x->code . ' ' . $x->name . ' ' . $x->national_id . ' ' . $x->phone), $needle));
            })
            ->when($filters['bank'] !== '',        fn (Collection $c) => $c->where('bank_id', (int) $filters['bank']))
            ->when($filters['loan_type'] !== '',   fn (Collection $c) => $c->where('loan_type', $filters['loan_type']))
            ->when($filters['governorate'] !== '', fn (Collection $c) => $c->where('governorate', $filters['governorate']))
            ->when($filters['bucket'] !== '',      fn (Collection $c) => $c->where('bucket', (int) $filters['bucket']))
            ->when($filters['status'] !== '',      fn (Collection $c) => $c->where('status', $filters['status']))
            ->values();

        $clients = new LengthAwarePaginator(
            $filtered->forPage($page, $perPage)->values(),
            $filtered->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('clients.index', [
            'clients'     => $clients,
            'filters'     => $filters,
            'stats'       => [
                ['label' => 'عدد العملاء',      'value' => $filtered->count(),                          'unit' => null,  'icon' => 'fa-address-book',    'color' => 'info'],
                ['label' => 'حالات نشطة',       'value' => $filtered->where('status', 'ACTIVE')->count(), 'unit' => null, 'icon' => 'fa-user-check',      'color' => 'brand'],
                ['label' => 'إجمالي المديونية', 'value' => number_format($filtered->sum('debt')),       'unit' => 'EGP', 'icon' => 'fa-wallet',          'color' => 'accent'],
                ['label' => 'المتبقي للتحصيل',  'value' => number_format($filtered->sum('remaining')),  'unit' => 'EGP', 'icon' => 'fa-hourglass-half',  'color' => 'warning'],
            ],
            'banks'       => StaticBanks::all()->pluck('name', 'id'),
            'loanTypes'   => BankController::LOAN_TYPES,
            'statuses'    => BankController::STATUSES,
            'governorates' => $all->pluck('governorate')->unique()->sort()->values(),
            'buckets'     => $all->pluck('bucket')->unique()->sort()->values(),
            'perPageOptions' => self::PER_PAGE,
        ]);
    }
}