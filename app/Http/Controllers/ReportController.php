<?php

// app/Http/Controllers/ReportController.php  (static data)
// "التقارير": ask for a report (Excel / PDF / CSV) and see the history of everything that was exported.

namespace App\Http\Controllers;

use App\Support\StaticBanks;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReportController extends Controller
{
    private const TYPES = [
        'clients'     => 'قائمة العملاء والحالات',
        'payments'    => 'التحصيلات',
        'ptp'         => 'وعود الدفع (PTP)',
        'dcr'         => 'التقرير اليومي DCR',
        'performance' => 'أداء الموظفين',
        'complaints'  => 'الشكاوى',
    ];

    private const FORMATS  = ['xlsx' => 'Excel (xlsx)', 'pdf' => 'PDF', 'csv' => 'CSV'];
    private const STATUSES = ['completed' => 'جاهز', 'pending' => 'قيد التجهيز', 'failed' => 'فشل'];

    /** Every export of the current user. TODO (DB): ReportExport::where('user_id', auth()->id())->latest() */
    private function allExports(): Collection
    {
        // id, type, format, bank, scope text, rows, status, who, when, error
        $rows = [
            [8, 'clients',     'xlsx', 'Emirates NBD',         'سبتمبر 2026',        6,   'completed', 'Test Account', 'اليوم 10:02',   null],
            [7, 'performance', 'pdf',  'كل البنوك',             'سبتمبر 2026',        3,   'completed', 'Beshoy nople', 'أمس 15:30',     null],
            [6, 'payments',    'xlsx', 'بنك مصر',               '01/09 - 30/09/2026', null, 'pending',  'Beshoy nople', 'أمس 15:41',     null],
            [5, 'dcr',         'pdf',  'Emirates NBD',          '30/09/2026',         null, 'failed',   'Beshoy nople', 'قبل يومين',     'تعذر إنشاء الملف: لا توجد بيانات لهذا اليوم.'],
            [4, 'ptp',         'xlsx', 'Emirates NBD',          'سبتمبر 2026',        7,   'completed', 'Beshoy nople', 'قبل 3 أيام',    null],
            [3, 'complaints',  'xlsx', 'Emirates NBD',          'سبتمبر 2026',        5,   'completed', 'Test Account', 'قبل 4 أيام',    null],
            [2, 'clients',     'csv',  'بنك مصر',               'أغسطس 2026',         48,  'completed', 'Test Account', 'قبل أسبوع',     null],
            [1, 'payments',    'pdf',  'كل البنوك',             'أغسطس 2026',         120, 'completed', 'Beshoy nople', 'قبل أسبوع',     null],
        ];

        return collect($rows)->map(fn (array $r) => (object) array_combine(
            ['id', 'type', 'format', 'bank', 'scope', 'rows', 'status', 'user', 'at', 'error'], $r
        ));
    }

    // GET /reports   : new report form + the latest 4 exports
    public function index(): View
    {
        return view('reports.index', [
            'types'   => self::TYPES,
            'formats' => self::FORMATS,
            'banks'   => StaticBanks::all()->pluck('name', 'id'),
            'exports' => $this->allExports()->take(4),
        ]);
    }

    // POST /reports
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'type'   => ['required', Rule::in(array_keys(self::TYPES))],
            'bank'   => ['nullable', 'integer'],
            'from'   => ['nullable', 'date'],
            'to'     => ['nullable', 'date', 'after_or_equal:from'],
            'format' => ['required', Rule::in(array_keys(self::FORMATS))],
        ]);

        // TODO (DB): create a report_exports row (pending) and dispatch a queued job that builds the file
        return redirect()->route('reports.exports')->with('success', 'تم إرسال طلب التقرير، سيظهر هنا عند جاهزيته (بيانات تجريبية).');
    }

    // GET /reports/exports?type=&status=&format=
    public function exports(Request $request): View
    {
        $all = $this->allExports();

        $filters = [
            'type'   => (string) $request->query('type', ''),
            'status' => (string) $request->query('status', ''),
            'format' => (string) $request->query('format', ''),
        ];

        return view('reports.exports', [
            'exports'  => $all
                ->when($filters['type'] !== '',   fn (Collection $c) => $c->where('type', $filters['type']))
                ->when($filters['status'] !== '', fn (Collection $c) => $c->where('status', $filters['status']))
                ->when($filters['format'] !== '', fn (Collection $c) => $c->where('format', $filters['format']))
                ->values(),
            'filters'  => $filters,
            'types'    => self::TYPES,
            'formats'  => self::FORMATS,
            'statuses' => self::STATUSES,
            'counts'   => $all->countBy('status')->all() + ['all' => $all->count()],
        ]);
    }

    // POST /reports/exports/{export}/retry
    public function retry(int $export): RedirectResponse
    {
        $this->allExports()->firstWhere('id', $export) ?? abort(404);

        // TODO (DB): set status = pending and dispatch the job again
        return back()->with('success', 'تمت إعادة محاولة التقرير (بيانات تجريبية).');
    }

    // DELETE /reports/exports/{export}
    public function destroy(int $export): RedirectResponse
    {
        $this->allExports()->firstWhere('id', $export) ?? abort(404);

        // TODO (DB): delete the stored file and the report_exports row
        return back()->with('success', 'تم حذف التقرير (بيانات تجريبية، لم يتم الحذف).');
    }
}