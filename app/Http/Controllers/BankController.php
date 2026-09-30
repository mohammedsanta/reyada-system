<?php

// app/Http/Controllers/BankController.php  (UPDATED: real show() page with clients)

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class BankController extends Controller
{
    public const LOAN_TYPES = ['قرض شخصي', 'تمويل عقاري', 'قرض سلع معمرة'];
    public const STATUSES   = ['ACTIVE', 'INACTIVE', 'PAID', 'LEGAL'];
    public const PER_PAGE   = [10, 25, 50, 100];

    /* ---------------------------------------------------------------
     | STATIC DATA (temporary). Replace with Eloquent when the DB is ready.
     * ------------------------------------------------------------- */

    private function banks(): Collection
    {
        return collect([
            ['id' => 1, 'name' => 'Emirates NBD',        'code' => 'ENBD', 'is_active' => true,  'logo' => null],
            ['id' => 2, 'name' => 'بنك مصر',             'code' => 'BM',   'is_active' => true,  'logo' => null],
            ['id' => 3, 'name' => 'البنك التجاري الدولي', 'code' => 'CIB',  'is_active' => true,  'logo' => null],
            ['id' => 4, 'name' => 'بنك القاهرة',          'code' => 'BDC',  'is_active' => false, 'logo' => null],
            ['id' => 5, 'name' => 'بنك فيصل الإسلامي',    'code' => 'FIB',  'is_active' => true,  'logo' => null],
        ])->map(fn (array $bank) => (object) $bank);
    }

    private function findOrFail(int $id): object
    {
        return $this->banks()->firstWhere('id', $id) ?? abort(404);
    }

    /** Only bank #1 has sample clients; the others show an empty state. */
    private function clientsFor(int $bankId): Collection
    {
        if ($bankId !== 1) {
            return collect();
        }

        $defaults = [
            'status' => 'ACTIVE', 'total_debt' => 7000, 'overdue_amount' => 1500, 'collected' => 0,
            'processed' => false, 'installment_value' => 12000, 'min_installment_diff' => 8000, 'dpd' => 30,
            'next_due_date' => '2026-10-10', 'loan_start_date' => '2020-03-01', 'loan_end_date' => '2027-03-01',
            'last_payment_date' => '2026-06-15', 'last_payment_amount' => 3000,
            'employer' => 'شركة الدلتا للتجارة', 'job_title' => 'موظف',
            'work_phone' => '0223456789', 'work_address' => 'القاهرة',
        ];

        $rows = [
            ['code' => '1000001', 'name' => 'مصطفى خالد حسن', 'national_id' => '27204244494993',
             'address' => '86 شارع عباس العقاد - فيصل - أسيوط', 'governorate' => 'أسيوط',
             'phone' => '01080672586', 'phone2' => '01196230946', 'email' => 'user_1@example.com',
             'loan_type' => 'قرض شخصي', 'bucket' => 2, 'late_fee' => 948.55, 'employee' => 'HOOL'],

            ['code' => '1000002', 'name' => 'حسن زينب الهواري', 'national_id' => '28112243502255',
             'address' => '82 شارع الهرم - الدقي - سوهاج', 'governorate' => 'سوهاج',
             'phone' => '01299928200', 'phone2' => '01054355112', 'email' => 'user_2@example.com',
             'loan_type' => 'تمويل عقاري', 'bucket' => 2, 'late_fee' => 1509.57, 'employee' => 'HOOL',
             'dpd' => 73, 'installment_value' => 22287.9, 'min_installment_diff' => 14431.52,
             'next_due_date' => '2026-09-17', 'loan_start_date' => '2019-01-27', 'loan_end_date' => '2026-02-18',
             'last_payment_date' => '2024-11-27', 'last_payment_amount' => 8871.83,
             'employer' => 'مصنع نسيج', 'job_title' => 'مدرس',
             'work_phone' => '023886219', 'work_address' => '95 شارع عباس - مصر الجديدة - القليوب'],

            ['code' => '1000004', 'name' => 'إيمان هبة راضي', 'national_id' => '25002158210174',
             'address' => '123 شارع البطل أحمد عبدالعزيز - شبرا - الإسماعيلية', 'governorate' => 'الإسماعيلية',
             'phone' => '01174820823', 'phone2' => '01050135467', 'email' => 'user_4@example.com',
             'loan_type' => 'قرض شخصي', 'bucket' => 1, 'late_fee' => 1000, 'employee' => 'Ahmed Borlsy',
             'processed' => true, 'dpd' => 12],

            ['code' => '1000003', 'name' => 'منى هبة صالح', 'national_id' => '29611268968147',
             'address' => '58 شارع رمسيس - المعادي - القاهرة', 'governorate' => 'القاهرة',
             'phone' => '01097389540', 'phone2' => '01086125272', 'email' => 'user_3@example.com',
             'loan_type' => 'تمويل عقاري', 'bucket' => 2, 'late_fee' => 2076.98, 'employee' => 'Ahmed Borlsy', 'dpd' => 58],

            ['code' => '1000005', 'name' => 'أحمد يوسف توفيق', 'national_id' => '28011048181918',
             'address' => '147 شارع الهرم - المهندسين - الدقهلية', 'governorate' => 'الدقهلية',
             'phone' => '01096103142', 'phone2' => '01176674345', 'email' => 'user_5@example.com',
             'loan_type' => 'قرض شخصي', 'bucket' => 1, 'late_fee' => 956.58, 'employee' => 'Ahmed Borlsy', 'dpd' => 20],

            ['code' => '1000000', 'name' => 'فاطمة زينب السيد', 'national_id' => '29203065580669',
             'address' => '117 شارع التسعين - المعادي - المنيا', 'governorate' => 'المنيا',
             'phone' => '01055111338', 'phone2' => '01245398004', 'email' => 'user_0@example.com',
             'loan_type' => 'قرض سلع معمرة', 'bucket' => 0, 'late_fee' => 472.95, 'employee' => 'HOOL', 'dpd' => 5],
        ];

        return collect($rows)->map(function (array $row) use ($defaults, $bankId) {
            $client = array_merge($defaults, $row);
            $client['id']      = (int) $client['code'];
            $client['bank_id'] = $bankId;

            return (object) $client;
        });
    }

    /* ---------------------------------------------------------------
     | Actions
     * ------------------------------------------------------------- */

    // GET /banks
    public function index(): View
    {
        return view('banks.index', ['banks' => $this->banks()]);
    }

    // GET /banks/create
    public function create(): View
    {
        return view('banks.create');
    }

    // POST /banks
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20'],
        ]);

        // TODO (DB): Bank::create($validated);

        return redirect()
            ->route('banks.index')
            ->with('success', 'تمت إضافة البنك (بيانات تجريبية، لم يتم الحفظ).');
    }

    // GET /banks/{bank}?search=&loan_type=&bucket=&employee=&per_page=&page=
    public function show(Request $request, int $bank): View
    {
        $bank = $this->findOrFail($bank);
        $all  = $this->clientsFor($bank->id);

        $perPage = (int) $request->query('per_page', 50);
        $perPage = in_array($perPage, self::PER_PAGE, true) ? $perPage : 50;
        $page    = max(1, (int) $request->query('page', 1));

        $filters = [
            'search'    => trim((string) $request->query('search', '')),
            'loan_type' => (string) $request->query('loan_type', ''),
            'bucket'    => (string) $request->query('bucket', ''),
            'employee'  => (string) $request->query('employee', ''),
            'per_page'  => $perPage,
        ];

        $filtered = $all
            ->when($filters['search'] !== '', function (Collection $c) use ($filters) {
                $needle = mb_strtolower($filters['search']);

                return $c->filter(fn ($client) => str_contains(
                    mb_strtolower(implode(' ', [
                        $client->code, $client->name, $client->national_id,
                        $client->phone, $client->phone2, $client->email,
                    ])),
                    $needle
                ));
            })
            ->when($filters['loan_type'] !== '', fn (Collection $c) => $c->where('loan_type', $filters['loan_type']))
            ->when($filters['bucket'] !== '',    fn (Collection $c) => $c->where('bucket', (int) $filters['bucket']))
            ->when($filters['employee'] !== '',  fn (Collection $c) => $c->where('employee', $filters['employee']))
            ->values();

        $clients = new LengthAwarePaginator(
            $filtered->forPage($page, $perPage)->values(),
            $filtered->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $arrears   = $all->sum('overdue_amount');
        $collected = $all->sum('collected');

        return view('banks.show', [
            'bank'        => $bank,
            'clients'     => $clients,
            'clientsJson' => (object) $all->keyBy('id')->all(),   // used by the edit modal
            'filters'     => $filters,
            'caseCount'   => $all->count(),
            'stats'       => [
                'processed' => $all->where('processed', true)->count(),
                'arrears'   => $arrears,
                'collected' => $collected,
                'remaining' => $arrears - $collected,
            ],
            'loanTypes'   => self::LOAN_TYPES,
            'statuses'    => self::STATUSES,
            'buckets'     => $all->pluck('bucket')->unique()->sort()->values(),
            'employees'   => $all->pluck('employee')->unique()->values(),
            'perPageOptions' => self::PER_PAGE,
        ]);
    }

    // GET /banks/{bank}/edit
    public function edit(int $bank): View
    {
        return view('banks.edit', ['bank' => $this->findOrFail($bank)]);
    }

    // PUT /banks/{bank}
    public function update(Request $request, int $bank): RedirectResponse
    {
        $this->findOrFail($bank);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:20'],
        ]);

        // TODO (DB): $bank->update($validated);

        return redirect()
            ->route('banks.index')
            ->with('success', 'تم تعديل البنك (بيانات تجريبية، لم يتم الحفظ).');
    }

    // DELETE /banks/{bank}
    public function destroy(int $bank): RedirectResponse
    {
        $this->findOrFail($bank);

        // TODO (DB): $bank->delete();

        return redirect()
            ->route('banks.index')
            ->with('success', 'تم حذف البنك (بيانات تجريبية، لم يتم الحذف).');
    }
}