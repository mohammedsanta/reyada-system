<?php

// app/Http/Controllers/BankImportController.php  (static data)
// "استيراد النطاق": upload the monthly Excel file of a bank.

namespace App\Http\Controllers;

use App\Support\StaticBanks;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BankImportController extends Controller
{
    private const MONTHS = [
        1 => 'يناير', 2 => 'فبراير', 3 => 'مارس', 4 => 'أبريل', 5 => 'مايو', 6 => 'يونيو',
        7 => 'يوليو', 8 => 'أغسطس', 9 => 'سبتمبر', 10 => 'أكتوبر', 11 => 'نوفمبر', 12 => 'ديسمبر',
    ];

    private const MODES = [
        'upsert'  => ['label' => 'تحديث الموجود وإضافة الجديد', 'hint' => 'تتم مطابقة العميل بالرقم القومي ورقم القرض'],
        'skip'    => ['label' => 'إضافة الجديد فقط',            'hint' => 'يتم تجاهل الحالات الموجودة بدون تعديل'],
        'replace' => ['label' => 'استبدال النطاق بالكامل',      'hint' => 'تحذير: يحذف حالات هذا الشهر قبل الاستيراد'],
    ];

    // required columns first, then optional ones (names must match the Excel header row)
    private const REQUIRED_COLUMNS = ['national_id', 'name', 'phone', 'loan_type', 'total_debt', 'overdue_amount', 'bucket'];
    private const OPTIONAL_COLUMNS = [
        'loan_number', 'phone2', 'email', 'governorate', 'address', 'installment_value', 'min_installment_diff',
        'dpd', 'next_due_date', 'late_fee', 'loan_start_date', 'loan_end_date', 'last_payment_date',
        'last_payment_amount', 'employer', 'job_title',
    ];

    // TODO (DB): PortfolioImport::where('portfolio_id', ...)->latest()
    private function history(): array
    {
        // file, period, by, total rows, success rows, failed rows, status, when
        $rows = [
            ['scope_september.xlsx', 'سبتمبر 2026', 'Test Account', 6,  6,  0, 'completed', 'اليوم 09:30'],
            ['scope_august.xlsx',    'أغسطس 2026',  'Beshoy nople', 52, 50, 2, 'completed', '01/08/2026'],
            ['scope_july_fix.csv',   'يوليو 2026',  'Beshoy nople', 10, 0,  10, 'failed',    '03/07/2026'],
        ];

        return collect($rows)->map(fn (array $r) => (object) array_combine(
            ['file', 'period', 'by', 'total', 'success', 'failed', 'status', 'at'], $r
        ))->all();
    }

    // GET /banks/{bank}/scope/import
    public function create(int $bank): View
    {
        return view('banks.import', [
            'bank'     => StaticBanks::find($bank),
            'months'   => self::MONTHS,
            'years'    => array_combine(range(now()->year - 1, now()->year + 1), range(now()->year - 1, now()->year + 1)),
            'defaults' => ['month' => now()->month, 'year' => now()->year],
            'modes'    => self::MODES,
            'required' => self::REQUIRED_COLUMNS,
            'optional' => self::OPTIONAL_COLUMNS,
            'history'  => $this->history(),
        ]);
    }

    // POST /banks/{bank}/scope/import
    public function store(Request $request, int $bank): RedirectResponse
    {
        StaticBanks::find($bank);

        $request->validate([
            'file'  => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:10240'],   // 10 MB
            'month' => ['required', 'integer', 'between:1,12'],
            'year'  => ['required', 'integer', 'between:2020,2100'],
            'mode'  => ['required', Rule::in(array_keys(self::MODES))],
        ]);

        // TODO (DB + Excel): save the file, create portfolio + portfolio_imports rows and
        // dispatch a queued job (e.g. with maatwebsite/excel) that creates clients and debt_cases.
        $name = $request->file('file')->getClientOriginalName();

        return redirect()
            ->route('banks.scope.import', $bank)
            ->with('success', "تم استلام الملف ({$name}) لشهر " . self::MONTHS[(int) $request->month] . ' (بيانات تجريبية: لم تتم قراءة الملف بعد).');
    }
}