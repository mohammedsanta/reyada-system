<?php

// app/Http/Controllers/ActivityLogController.php  (static data)
// "سجل النشاط": who did what, when and from where. Read-only (a log is never edited).

namespace App\Http\Controllers;

use App\Support\StaticData;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    private const EVENTS = [
        'login' => 'تسجيل دخول', 'created' => 'إضافة', 'updated' => 'تعديل', 'deleted' => 'حذف',
        'imported' => 'استيراد', 'exported' => 'تصدير', 'assigned' => 'توزيع', 'confirmed' => 'تأكيد',
    ];

    /** TODO (DB): ActivityLog::with('user')->latest()  ("changes" = the old / new values saved in the properties JSON column) */
    private function logs(): Collection
    {
        $today = Carbon::today();

        // id, user, event, description, subject, when, ip, device, changes [[field, before, after], ...]
        $rows = [
            [10, 'Test Account', 'login',     'تسجيل دخول إلى النظام',                       null,                  $today->copy()->setTime(9, 12),                '197.55.12.4', 'Chrome · Windows', []],
            [9,  'Test Account', 'imported',  'استيراد نطاق Emirates NBD (6 حالات)',         'نطاق سبتمبر 2026',    $today->copy()->setTime(9, 30),                '197.55.12.4', 'Chrome · Windows', [['عدد الحالات', '-', '6'], ['الملف', '-', 'scope_september.xlsx']]],
            [8,  'Beshoy nople', 'assigned',  'توزيع 24 حالة على 3 موظفين',                  'Emirates NBD',        $today->copy()->setTime(10, 5),                '41.33.8.21',  'Edge · Windows',   [['الموظف', 'بدون موظف', 'HOOL (12)'], ['الموظف', 'بدون موظف', 'Ahmed Borlsy (12)']]],
            [7,  'HOOL',         'updated',   'تعديل بيانات العميل حسن زينب الهواري',        'عميل #1000002',       $today->copy()->setTime(10, 22),               '41.33.9.77',  'Chrome · Android', [['الهاتف البديل', '01054355111', '01054355112'], ['الشريحة', '1', '2']]],
            [6,  'Beshoy nople', 'confirmed', 'تأكيد التحصيل #4089 بمبلغ 1,500 EGP',        'إيصال #4089',         $today->copy()->setTime(11, 40),               '41.33.8.21',  'Edge · Windows',   [['الحالة', 'قيد المراجعة', 'مؤكد'], ['المحصل في الحالة', '0', '1,500 EGP']]],
            [5,  'Ahmed Borlsy', 'created',   'تسجيل وعد دفع جديد للعميل منى هبة صالح',      'وعد #3',              $today->copy()->subDay()->setTime(15, 12),     '41.33.9.12',  'Safari · iPhone',  [['المبلغ', '-', '1,500 EGP'], ['الموعد', '-', 'غداً']]],
            [4,  'Beshoy nople', 'exported',  'تصدير تقرير العملاء (xlsx)',                  'تقرير #8',            $today->copy()->subDay()->setTime(14, 2),      '41.33.8.21',  'Edge · Windows',   []],
            [3,  'Beshoy nople', 'updated',   'تعديل جماعي: تغيير الموظف المسؤول لـ 2 حالة', 'Emirates NBD',        $today->copy()->subDays(2)->setTime(16, 40),   '41.33.8.21',  'Edge · Windows',   [['الموظف', 'بدون موظف', 'Ahmed Borlsy']]],
            [2,  'Test Account', 'deleted',   'حذف العميل التجريبي #999999',                 'عميل #999999',        $today->copy()->subDays(3)->setTime(12, 45),   '197.55.12.4', 'Chrome · Windows', [['الاسم', 'عميل تجريبي', '-']]],
            [1,  'HOOL',         'login',     'تسجيل دخول إلى النظام',                       null,                  $today->copy()->subDays(3)->setTime(8, 50),    '41.33.9.77',  'Chrome · Android', []],
        ];

        return collect($rows)->map(function (array $r) {
            $log = (object) array_combine(['id', 'user', 'event', 'description', 'subject', 'when', 'ip', 'device', 'changes'], $r);
            $log->at   = $log->when->format('d/m/Y H:i');
            $log->date = $log->when->toDateString();

            return $log;
        });
    }

    // GET /activity-logs?search=&user=&event=&date=
    public function index(Request $request): View
    {
        $all = $this->logs();

        $filters = [
            'search' => trim((string) $request->query('search', '')),
            'user'   => (string) $request->query('user', ''),
            'event'  => (string) $request->query('event', ''),
            'date'   => (string) $request->query('date', ''),
        ];

        $logs = $all
            ->when($filters['search'] !== '', fn (Collection $c) => $c->filter(fn ($l) => str_contains(mb_strtolower($l->description . ' ' . $l->subject), mb_strtolower($filters['search']))))
            ->when($filters['user'] !== '',   fn (Collection $c) => $c->where('user', $filters['user']))
            ->when($filters['event'] !== '',  fn (Collection $c) => $c->where('event', $filters['event']))
            ->when($filters['date'] !== '',   fn (Collection $c) => $c->where('date', $filters['date']))
            ->values();

        $today = Carbon::today()->toDateString();

        return view('activity-logs.index', [
            'logs'    => $logs,
            'filters' => $filters,
            'events'  => self::EVENTS,
            'users'   => StaticData::users()->pluck('name'),
            'stats'   => [
                ['label' => 'أنشطة اليوم',     'value' => $all->where('date', $today)->count(),                          'icon' => 'fa-bolt',             'color' => 'brand'],
                ['label' => 'تسجيلات الدخول',  'value' => $all->where('event', 'login')->count(),                        'icon' => 'fa-right-to-bracket', 'color' => 'info'],
                ['label' => 'إضافة وتعديل',    'value' => $all->whereIn('event', ['created', 'updated'])->count(),       'icon' => 'fa-pen-to-square',    'color' => 'warning'],
                ['label' => 'عمليات حذف',      'value' => $all->where('event', 'deleted')->count(),                      'icon' => 'fa-trash',            'color' => 'danger'],
            ],
            // data for the "details" dialog (opened by JavaScript)
            'details' => $logs->mapWithKeys(fn ($l) => [$l->id => [
                'user' => $l->user, 'event' => self::EVENTS[$l->event], 'at' => $l->at, 'description' => $l->description,
                'subject' => $l->subject, 'ip' => $l->ip, 'device' => $l->device, 'changes' => $l->changes,
            ]])->all(),
        ]);
    }
}