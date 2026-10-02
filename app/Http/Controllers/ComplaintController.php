<?php

// app/Http/Controllers/ComplaintController.php  (static data)
// "إدارة الشكاوى": log client complaints, assign them, and close them with a resolution.

namespace App\Http\Controllers;

use App\Support\StaticBanks;
use App\Support\StaticData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ComplaintController extends Controller
{
    private const STATUSES   = ['open' => 'مفتوحة', 'in_review' => 'قيد المراجعة', 'resolved' => 'تم الحل', 'rejected' => 'مرفوضة', 'closed' => 'مغلقة'];
    private const PRIORITIES = ['low' => 'منخفضة', 'medium' => 'متوسطة', 'high' => 'عالية', 'urgent' => 'عاجلة'];
    private const SOURCES    = ['phone' => 'مكالمة هاتفية', 'whatsapp' => 'واتساب', 'email' => 'بريد إلكتروني', 'bank' => 'من البنك', 'visit' => 'زيارة'];

    /** Only bank #1 has sample complaints. TODO (DB): Complaint::where('bank_id', ...)->with(...) */
    private function all(int $bankId): Collection
    {
        if ($bankId !== 1) {
            return collect();
        }

        $clients = StaticData::clients()->keyBy('code');

        // id, reference, client code, subject, description, priority, status, source, assigned user id, created, due, resolution
        $rows = [
            [5, 'CMP-000104', '1000003', 'اعتراض على مبلغ المديونية', 'العميل يؤكد أنه سدد قسطين في الشهر الماضي ولم يتم خصمهما من المديونية، ويطلب مراجعة الحساب وإرسال كشف تفصيلي.', 'high',   'open',      'phone',    3, 'اليوم 08:40', 'بعد يومين', null],
            [4, 'CMP-000103', '1000002', 'طلب تأجيل القسط',            'العميل يمر بظرف مالي طارئ ويطلب تأجيل القسط القادم لمدة شهر.',                                                                         'medium', 'in_review', 'whatsapp', 4, 'أمس 14:10',   'غداً',      null],
            [3, 'CMP-000102', '1000001', 'اتصالات متكررة خارج المواعيد', 'العميل يشتكي من تلقي اتصالات بعد الساعة التاسعة مساءً أكثر من مرة في اليوم.',                                                     'urgent', 'open',      'bank',     2, 'أمس 09:00',   'اليوم',     null],
            [2, 'CMP-000101', '1000004', 'خطأ في بيانات الاتصال',      'رقم الهاتف المسجل لا يخص العميل وتصل إليه اتصالات تخص شخصاً آخر.',                                                                      'low',    'resolved',  'email',    3, 'قبل 3 أيام',  '-',         'تم تصحيح رقم الهاتف وإيقاف الاتصال بالرقم القديم.'],
            [1, 'CMP-000100', '1000000', 'شكوى من أسلوب المحصل',       'العميل يقول إن المحصل تحدث بأسلوب غير لائق خلال الزيارة الميدانية.',                                                                    'high',   'rejected',  'visit',    4, 'قبل 5 أيام',  '-',         'تمت مراجعة تسجيل الزيارة ولم يثبت وجود مخالفة.'],
        ];

        return collect($rows)->map(function (array $r) use ($clients) {
            [$id, $ref, $code, $subject, $description, $priority, $status, $source, $assigned, $created, $due, $resolution] = $r;

            return (object) [
                'id' => $id, 'reference' => $ref,
                'client_code' => $code, 'client_name' => $clients[$code]->name, 'client_phone' => $clients[$code]->phone,
                'subject' => $subject, 'description' => $description,
                'priority' => $priority, 'status' => $status, 'source' => $source,
                'assigned_id' => $assigned, 'assigned_name' => StaticData::user($assigned)->name,
                'created' => $created, 'due' => $due, 'resolution' => $resolution,
            ];
        });
    }

    private function findOrFail(int $bankId, int $id): object
    {
        return $this->all($bankId)->firstWhere('id', $id) ?? abort(404);
    }

    // GET /banks/{bank}/complaints?status=&search=
    public function index(Request $request, int $bank): View
    {
        $bank = StaticBanks::find($bank);
        $all  = $this->all($bank->id);

        $status = array_key_exists((string) $request->query('status'), self::STATUSES) ? $request->query('status') : '';
        $search = trim((string) $request->query('search', ''));

        $complaints = $all
            ->when($status !== '', fn (Collection $c) => $c->where('status', $status))
            ->when($search !== '', function (Collection $c) use ($search) {
                $needle = mb_strtolower($search);

                return $c->filter(fn ($x) => str_contains(mb_strtolower($x->reference . ' ' . $x->client_name . ' ' . $x->subject), $needle));
            })
            ->values();

        return view('banks.complaints', [
            'bank'       => $bank,
            'complaints' => $complaints,
            'status'     => $status,
            'search'     => $search,
            'counts'     => $all->countBy('status')->all() + ['all' => $all->count()],
            'urgent'     => $all->where('priority', 'urgent')->whereIn('status', ['open', 'in_review'])->count(),
            'statuses'   => self::STATUSES,
            'priorities' => self::PRIORITIES,
            'sources'    => self::SOURCES,
            'clients'    => StaticData::clients()->mapWithKeys(fn ($c) => [$c->code => $c->name . ' (' . $c->code . ')'])->all(),
            'employees'  => StaticData::employees()->pluck('name', 'id')->all(),
        ]);
    }

    // POST /banks/{bank}/complaints
    public function store(Request $request, int $bank): RedirectResponse
    {
        StaticBanks::find($bank);

        $request->validate([
            'client_code' => ['required', Rule::in(StaticData::clients()->pluck('code')->all())],
            'subject'     => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'priority'    => ['required', Rule::in(array_keys(self::PRIORITIES))],
            'source'      => ['required', Rule::in(array_keys(self::SOURCES))],
            'assigned_to' => ['nullable', Rule::in(StaticData::employees()->pluck('id')->all())],
        ]);

        // TODO (DB): Complaint::create([... 'reference_number' => next CMP number, 'logged_by' => auth()->id(), 'status' => 'open'])
        return redirect()->route('banks.complaints.index', $bank)->with('success', 'تم تسجيل الشكوى (بيانات تجريبية، لم يتم الحفظ).');
    }

    // GET /banks/{bank}/complaints/{complaint}
    public function show(int $bank, int $complaint): View
    {
        return view('banks.complaint', [
            'bank'       => StaticBanks::find($bank),
            'complaint'  => $this->findOrFail($bank, $complaint),
            'statuses'   => self::STATUSES,
            'priorities' => self::PRIORITIES,
            'sources'    => self::SOURCES,
            'employees'  => StaticData::employees()->pluck('name', 'id')->all(),
        ]);
    }

    // PUT /banks/{bank}/complaints/{complaint}
    public function update(Request $request, int $bank, int $complaint): RedirectResponse
    {
        $this->findOrFail($bank, $complaint);

        $request->validate([
            'status'      => ['required', Rule::in(array_keys(self::STATUSES))],
            'priority'    => ['required', Rule::in(array_keys(self::PRIORITIES))],
            'assigned_to' => ['nullable', Rule::in(StaticData::employees()->pluck('id')->all())],
            'resolution'  => ['required_if:status,resolved,rejected', 'nullable', 'string', 'max:2000'],
        ], ['resolution.required_if' => 'اكتب نتيجة الشكوى قبل إغلاقها.']);

        // TODO (DB): $complaint->update([...]); set resolved_by / resolved_at when status becomes resolved or rejected
        return redirect()->route('banks.complaints.show', [$bank, $complaint])->with('success', 'تم تحديث الشكوى (بيانات تجريبية، لم يتم الحفظ).');
    }
}