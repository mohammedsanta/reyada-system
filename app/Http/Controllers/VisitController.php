<?php

// app/Http/Controllers/VisitController.php  (static data)
// "إدارة الزيارات": schedule field visits for collectors and record their result.

namespace App\Http\Controllers;

use App\Support\StaticBanks;
use App\Support\StaticData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class VisitController extends Controller
{
    private const STATUSES = ['scheduled' => 'مجدولة', 'completed' => 'تمت', 'missed' => 'فائتة', 'cancelled' => 'ملغاة'];

    // visit result => label. "not_home" makes the visit "missed", every other result makes it "completed".
    private const OUTCOMES = [
        'client_found' => 'تمت: تم لقاء العميل',
        'promised'     => 'تمت: العميل وعد بالدفع',
        'paid'         => 'تمت: تم السداد',
        'refused'      => 'تمت: العميل رفض السداد',
        'not_home'     => 'فائتة: العميل غير موجود',
    ];

    private function dayLabel(Carbon $date): string
    {
        return match (true) {
            $date->isToday()     => 'اليوم',
            $date->isTomorrow()  => 'غداً',
            $date->isYesterday() => 'أمس',
            default              => $date->format('d/m'),
        } . ' ' . $date->format('H:i');
    }

    /** Only bank #1 has sample visits. TODO (DB): Visit::with(['debtCase.client', 'user']) */
    private function all(int $bankId): Collection
    {
        if ($bankId !== 1) {
            return collect();
        }

        $clients = StaticData::clients()->keyBy('code');
        $now     = Carbon::now();

        // id, client code, collector id, scheduled at, status, outcome, notes
        $rows = [
            [1, '1000001', 3, $now->copy()->setTime(11, 0),                'scheduled', null,           null],
            [2, '1000003', 4, $now->copy()->setTime(14, 30),               'scheduled', null,           null],
            [3, '1000004', 4, $now->copy()->addDay()->setTime(10, 0),      'scheduled', null,           'يفضل الزيارة صباحاً'],
            [4, '1000002', 3, $now->copy()->subDay()->setTime(13, 0),      'completed', 'promised',     'وعد بسداد 2,000 EGP نهاية الأسبوع'],
            [5, '1000005', 4, $now->copy()->subDay()->setTime(16, 0),      'missed',    'not_home',     'لا أحد في المنزل، الجيران أكدوا سفره'],
            [6, '1000000', 3, $now->copy()->subDays(3)->setTime(12, 0),    'cancelled', null,           'تم إلغاء الزيارة بعد سداد جزئي'],
        ];

        return collect($rows)->map(function (array $r) use ($clients) {
            [$id, $code, $collector, $when, $status, $outcome, $notes] = $r;

            return (object) [
                'id' => $id, 'client_code' => $code, 'client_name' => $clients[$code]->name, 'address' => $clients[$code]->address,
                'collector' => StaticData::user($collector),
                'when' => $when, 'when_label' => $this->dayLabel($when),
                'status' => $status, 'outcome' => $outcome, 'notes' => $notes,
            ];
        });
    }

    // GET /banks/{bank}/visits?status=&collector=
    public function index(Request $request, int $bank): View
    {
        $bank = StaticBanks::find($bank);
        $all  = $this->all($bank->id);

        $status    = array_key_exists((string) $request->query('status'), self::STATUSES) ? $request->query('status') : '';
        $collector = (int) $request->query('collector', 0);

        $visits = $all
            ->when($status !== '', fn (Collection $c) => $c->where('status', $status))
            ->when($collector > 0, fn (Collection $c) => $c->filter(fn ($v) => $v->collector->id === $collector))
            ->sortBy('when')
            ->values();

        return view('banks.visits', [
            'bank'      => $bank,
            'visits'    => $visits,
            'status'    => $status,
            'collector' => $collector,
            'counts'    => $all->countBy('status')->all() + ['all' => $all->count()],
            'today'     => $all->filter(fn ($v) => $v->when->isToday())->count(),
            'statuses'  => self::STATUSES,
            'outcomes'  => self::OUTCOMES,
            'clients'   => StaticData::clients()->mapWithKeys(fn ($c) => [$c->code => $c->name . ' (' . $c->code . ')'])->all(),
            'employees' => StaticData::employees()->pluck('name', 'id')->all(),
        ]);
    }

    // POST /banks/{bank}/visits
    public function store(Request $request, int $bank): RedirectResponse
    {
        StaticBanks::find($bank);

        $request->validate([
            'client_code'  => ['required', Rule::in(StaticData::clients()->pluck('code')->all())],
            'user_id'      => ['required', Rule::in(StaticData::employees()->pluck('id')->all())],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'address'      => ['nullable', 'string', 'max:500'],
            'notes'        => ['nullable', 'string', 'max:1000'],
        ], ['scheduled_at.after' => 'موعد الزيارة يجب أن يكون في المستقبل.']);

        // TODO (DB): Visit::create([... 'status' => 'scheduled', 'assigned_by' => auth()->id()])
        return redirect()->route('banks.visits.index', $bank)->with('success', 'تمت جدولة الزيارة (بيانات تجريبية، لم يتم الحفظ).');
    }

    // PUT /banks/{bank}/visits/{visit}   (action = result | cancel)
    public function update(Request $request, int $bank, int $visit): RedirectResponse
    {
        $found = $this->all($bank)->firstWhere('id', $visit) ?? abort(404);

        $data = $request->validate([
            'action'  => ['required', Rule::in(['result', 'cancel'])],
            'outcome' => ['required_if:action,result', 'nullable', Rule::in(array_keys(self::OUTCOMES))],
            'notes'   => ['nullable', 'string', 'max:1000'],
        ], ['outcome.required_if' => 'اختر نتيجة الزيارة.']);

        if ($found->status !== 'scheduled') {
            return back()->withErrors(['action' => 'لا يمكن تعديل زيارة غير مجدولة.']);
        }

        // TODO (DB): result => status completed|missed (+ visited_at, outcome, notes) | cancel => status cancelled
        $message = $data['action'] === 'cancel'
            ? 'تم إلغاء الزيارة'
            : ($data['outcome'] === 'not_home' ? 'تم تسجيل الزيارة كفائتة' : 'تم تسجيل نتيجة الزيارة');

        return redirect()->route('banks.visits.index', $bank)->with('success', $message . ' (بيانات تجريبية، لم يتم الحفظ).');
    }
}