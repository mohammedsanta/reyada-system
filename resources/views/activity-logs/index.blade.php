{{-- resources/views/activity-logs/index.blade.php --}}
@extends('layouts.app')

@section('title', 'سجل النشاط')

@php
    $eventTones = [
        'login'     => 'badge-neutral',
        'created'   => 'badge-success',
        'updated'   => 'badge-info',
        'deleted'   => 'badge-danger',
        'imported'  => 'badge-outline-info',
        'exported'  => 'badge-outline-info',
        'assigned'  => 'badge-warning',
        'confirmed' => 'badge-success',
    ];

    $eventIcons = [
        'login'     => 'fa-right-to-bracket',
        'created'   => 'fa-plus',
        'updated'   => 'fa-pen',
        'deleted'   => 'fa-trash',
        'imported'  => 'fa-file-arrow-up',
        'exported'  => 'fa-file-arrow-down',
        'assigned'  => 'fa-user-plus',
        'confirmed' => 'fa-circle-check',
    ];

    $eventColors = [
        'login'     => 'info',
        'created'   => 'success',
        'updated'   => 'info',
        'deleted'   => 'danger',
        'imported'  => 'cyan',
        'exported'  => 'cyan',
        'assigned'  => 'warning',
        'confirmed' => 'success',
    ];

    /*
    |--------------------------------------------------------------------------
    | Normalize existing data
    |--------------------------------------------------------------------------
    */

    $logCollection = collect($logs ?? []);

    $userOptions = collect($users ?? []);

    $eventOptions = collect($events ?? []);

    /*
    |--------------------------------------------------------------------------
    | IMPORTANT:
    | Normalize $details safely.
    |
    | If $details is already an array/collection keyed by log ID,
    | this keeps it intact.
    |--------------------------------------------------------------------------
    */

    $detailsMap = collect($details ?? []);

    /*
    |--------------------------------------------------------------------------
    | Safe filters
    |--------------------------------------------------------------------------
    */

    $safeFilters = array_merge([
        'search' => '',
        'user'   => '',
        'event'  => '',
        'date'   => '',
    ], $filters ?? []);

    /*
    |--------------------------------------------------------------------------
    | Current-page statistics
    |--------------------------------------------------------------------------
    */

    $pageTotal = $logCollection->count();

    $eventCounts = $logCollection
        ->groupBy('event')
        ->map(fn ($items) => $items->count());

    $uniqueUsers = $logCollection
        ->pluck('user')
        ->filter()
        ->unique()
        ->count();

    $uniqueIps = $logCollection
        ->pluck('ip')
        ->filter()
        ->unique()
        ->count();

    $createdCount = (int) ($eventCounts['created'] ?? 0);
    $updatedCount = (int) ($eventCounts['updated'] ?? 0);
    $deletedCount = (int) ($eventCounts['deleted'] ?? 0);
    $loginCount = (int) ($eventCounts['login'] ?? 0);

    /*
    |--------------------------------------------------------------------------
    | Event summary
    |--------------------------------------------------------------------------
    */

    $eventSummary = $eventOptions
        ->map(function ($label, $key) use ($eventCounts) {
            return [
                'key'   => $key,
                'label' => $label,
                'count' => (int) ($eventCounts[$key] ?? 0),
            ];
        })
        ->filter(fn ($item) => $item['count'] > 0)
        ->sortByDesc('count')
        ->values();

    /*
    |--------------------------------------------------------------------------
    | Prepare details lookup
    |--------------------------------------------------------------------------
    */

    $getLogChanges = function ($log) use ($detailsMap) {

        $logId = $log->id ?? null;

        /*
        | Try the log ID as-is first.
        */
        $detail = $detailsMap->get($logId);

        /*
        | Also try string ID because JSON/array keys can differ.
        */
        if ($detail === null && $logId !== null) {
            $detail = $detailsMap->get((string) $logId);
        }

        /*
        | If no separate detail record exists,
        | use changes directly from the log if available.
        */
        if ($detail === null) {
            return $log->changes ?? [];
        }

        /*
        | Details may be:
        | ['changes' => [...]]
        */
        if (is_array($detail) && array_key_exists('changes', $detail)) {
            return $detail['changes'] ?? [];
        }

        /*
        | Or it may already be the changes array.
        */
        return $detail;
    };

    /*
    |--------------------------------------------------------------------------
    | JavaScript activity data
    |--------------------------------------------------------------------------
    */

    $activityJsData = $logCollection
        ->map(function ($log) use ($eventOptions, $getLogChanges) {

            $eventKey = (string) ($log->event ?? '');

            return [
                'id'          => (string) ($log->id ?? ''),
                'at'          => (string) ($log->at ?? '-'),
                'user'        => (string) ($log->user ?? '-'),
                'event'       => $eventKey,
                'event_label' => (string) (
                    $eventOptions[$eventKey]
                    ?? $eventKey
                    ?: '-'
                ),
                'description' => (string) ($log->description ?? '-'),
                'subject'     => (string) ($log->subject ?? ''),
                'ip'          => (string) ($log->ip ?? '-'),
                'device'      => (string) ($log->device ?? '-'),
                'changes'     => $getLogChanges($log),
            ];
        })
        ->values();

    /*
    |--------------------------------------------------------------------------
    | Active filters
    |--------------------------------------------------------------------------
    */

    $activeFilterCount = collect($safeFilters)
        ->filter(fn ($value) => $value !== null && $value !== '')
        ->count();
@endphp

@section('content')

    <x-page-header title="سجل النشاط" subtitle="من فعل ماذا ومتى ومن أين" icon="fa-clock-rotate-left">
        <x-slot:actions>
            <button type="button" id="refreshLogs" class="btn btn-secondary btn-sm" title="تحديث الصفحة">
                <i class="fa-solid fa-arrows-rotate text-xs"></i>
                تحديث
            </button>

            <button type="button" id="printLogs" class="btn btn-outline-info btn-sm" title="طباعة سجل النشاط">
                <i class="fa-solid fa-print text-xs"></i>
                طباعة
            </button>
        </x-slot:actions>
    </x-page-header>

    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($stats as $stat)
            <x-metric-card
                :label="$stat['label']"
                :value="$stat['value']"
                :icon="$stat['icon']"
                :color="$stat['color']"
            />
        @endforeach
    </section>

    {{-- QUICK OVERVIEW --}}
    <section class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-3">

        <x-panel title="ملخص السجل" icon="fa-chart-simple">
            <div class="grid grid-cols-2 gap-3">

                <div class="rounded-xl border border-white/5 bg-white/5 p-3">
                    <p class="text-[11px] text-dim">نتائج الصفحة</p>
                    <p class="mt-1 text-lg font-bold text-fg" id="page-result-count">{{ $pageTotal }}</p>
                </div>

                <div class="rounded-xl border border-white/5 bg-white/5 p-3">
                    <p class="text-[11px] text-dim">مستخدمون</p>
                    <p class="mt-1 text-lg font-bold text-fg">{{ $uniqueUsers }}</p>
                </div>

                <div class="rounded-xl border border-white/5 bg-white/5 p-3">
                    <p class="text-[11px] text-dim">عناوين IP</p>
                    <p class="mt-1 text-lg font-bold text-fg">{{ $uniqueIps }}</p>
                </div>

                <div class="rounded-xl border border-white/5 bg-white/5 p-3">
                    <p class="text-[11px] text-dim">فلاتر نشطة</p>
                    <p class="mt-1 text-lg font-bold text-fg">{{ $activeFilterCount }}</p>
                </div>

            </div>
        </x-panel>

        <x-panel title="أكثر الأحداث" icon="fa-chart-column">
            <div class="space-y-3">

                @forelse($eventSummary->take(5) as $item)
                    @php
                        $eventPercentage = $pageTotal > 0
                            ? min(100, round(($item['count'] / $pageTotal) * 100))
                            : 0;

                        $eventIcon = $eventIcons[$item['key']] ?? 'fa-bolt';
                    @endphp

                    <div>
                        <div class="mb-1 flex items-center justify-between gap-3 text-xs">
                            <span class="flex items-center gap-2 text-fg">
                                <i class="fa-solid {{ $eventIcon }} text-dim"></i>
                                {{ $item['label'] }}
                            </span>

                            <span class="font-bold text-fg">
                                {{ $item['count'] }}
                            </span>
                        </div>

                        <div class="h-1.5 overflow-hidden rounded-full bg-white/5">
                            <div
                                class="h-full rounded-full bg-brand transition-all duration-500"
                                style="width: {{ $eventPercentage }}%"
                            ></div>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-dim">لا توجد بيانات كافية لعرض الملخص.</p>
                @endforelse

            </div>
        </x-panel>

        <x-panel title="مؤشرات سريعة" icon="fa-shield-halved">
            <div class="space-y-3">

                <div class="flex items-center justify-between rounded-xl border border-white/5 bg-white/5 px-3 py-2">
                    <span class="text-xs text-dim">
                        <i class="fa-solid fa-plus mr-1"></i>
                        إنشاء
                    </span>
                    <b class="text-sm text-success">{{ $createdCount }}</b>
                </div>

                <div class="flex items-center justify-between rounded-xl border border-white/5 bg-white/5 px-3 py-2">
                    <span class="text-xs text-dim">
                        <i class="fa-solid fa-pen mr-1"></i>
                        تعديل
                    </span>
                    <b class="text-sm text-info">{{ $updatedCount }}</b>
                </div>

                <div class="flex items-center justify-between rounded-xl border border-white/5 bg-white/5 px-3 py-2">
                    <span class="text-xs text-dim">
                        <i class="fa-solid fa-trash mr-1"></i>
                        حذف
                    </span>
                    <b class="text-sm text-danger">{{ $deletedCount }}</b>
                </div>

                <div class="flex items-center justify-between rounded-xl border border-white/5 bg-white/5 px-3 py-2">
                    <span class="text-xs text-dim">
                        <i class="fa-solid fa-right-to-bracket mr-1"></i>
                        دخول
                    </span>
                    <b class="text-sm text-fg">{{ $loginCount }}</b>
                </div>

            </div>
        </x-panel>

    </section>

    {{-- FILTERS --}}
    <form method="GET" action="{{ route('activity-logs.index') }}" class="card filter-bar">

        <div class="min-w-[220px] flex-1">
            <label for="search" class="sr-only">بحث</label>

            <div class="relative">
                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-dim"></i>

                <input
                    id="search"
                    name="search"
                    type="search"
                    value="{{ $safeFilters['search'] }}"
                    placeholder="ابحث في الوصف..."
                    class="form-input pl-9"
                    autocomplete="off"
                >

                <kbd class="pointer-events-none absolute right-3 top-1/2 hidden -translate-y-1/2 rounded border border-white/10 bg-white/5 px-2 py-0.5 text-[10px] text-dim sm:block">
                    Ctrl K
                </kbd>
            </div>
        </div>

        <div class="w-44">
            <label for="user" class="form-label">المستخدم</label>

            <select id="user" name="user" class="form-input" onchange="this.form.submit()">
                <option value="">الكل</option>

                @foreach($userOptions as $name)
                    <option
                        value="{{ $name }}"
                        @selected($safeFilters['user'] === $name)
                    >
                        {{ $name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="w-40">
            <label for="event" class="form-label">الحدث</label>

            <select id="event" name="event" class="form-input" onchange="this.form.submit()">
                <option value="">الكل</option>

                @foreach($eventOptions as $key => $label)
                    <option
                        value="{{ $key }}"
                        @selected($safeFilters['event'] === $key)
                    >
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="w-44">
            <label for="date" class="form-label">التاريخ</label>

            <input
                id="date"
                name="date"
                type="date"
                value="{{ $safeFilters['date'] }}"
                class="form-input"
                onchange="this.form.submit()"
            >
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-filter text-xs"></i>
            تصفية
        </button>

        <a
            href="{{ route('activity-logs.index') }}"
            class="btn btn-secondary"
            title="إعادة ضبط"
        >
            <i class="fa-solid fa-rotate-right"></i>
        </a>

    </form>

    {{-- ACTIVE FILTERS --}}
    @if($activeFilterCount > 0)
        <div class="mb-4 flex flex-wrap items-center gap-2">

            <span class="text-xs text-dim">
                الفلاتر النشطة:
            </span>

            @if($safeFilters['search'] !== '')
                <span class="badge badge-info">
                    البحث: {{ $safeFilters['search'] }}
                </span>
            @endif

            @if($safeFilters['user'] !== '')
                <span class="badge badge-neutral">
                    المستخدم: {{ $safeFilters['user'] }}
                </span>
            @endif

            @if($safeFilters['event'] !== '')
                <span class="badge badge-warning">
                    الحدث: {{ $eventOptions[$safeFilters['event']] ?? $safeFilters['event'] }}
                </span>
            @endif

            @if($safeFilters['date'] !== '')
                <span class="badge badge-neutral">
                    التاريخ: {{ $safeFilters['date'] }}
                </span>
            @endif

            <a
                href="{{ route('activity-logs.index') }}"
                class="text-xs text-brand hover:underline"
            >
                مسح الكل
            </a>

        </div>
    @endif

    {{-- TABLE TOOLBAR --}}
    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">

        <div class="flex flex-wrap items-center gap-2">

            <button
                type="button"
                id="selectAllLogs"
                class="btn btn-secondary btn-sm"
                title="تحديد الأنشطة الظاهرة"
            >
                <i class="fa-solid fa-check-double text-xs"></i>
                تحديد الكل
            </button>

            <button
                type="button"
                id="clearLogSelection"
                class="btn btn-secondary btn-sm"
                title="إلغاء التحديد"
            >
                <i class="fa-solid fa-xmark text-xs"></i>
                إلغاء التحديد
            </button>

            <button
                type="button"
                id="copySelectedLogs"
                class="btn btn-outline-info btn-sm"
                disabled
            >
                <i class="fa-solid fa-copy text-xs"></i>
                نسخ المحدد
            </button>

            <button
                type="button"
                id="exportLogs"
                class="btn btn-outline-success btn-sm"
            >
                <i class="fa-solid fa-file-excel text-xs"></i>
                تصدير CSV
            </button>

        </div>

        <div class="flex items-center gap-2 text-xs text-dim">
            <span>
                النتائج:
                <b id="visible-count" class="text-fg">{{ $pageTotal }}</b>
            </span>

            <span>·</span>

            <span>
                المحدد:
                <b id="selected-count" class="text-fg">0</b>
            </span>
        </div>

    </div>

    {{-- TABLE --}}
    <div class="table-wrap">
        <table class="data-table whitespace-nowrap" id="activityTable">

            <thead>
                <tr>
                    <th class="w-10">
                        <input
                            type="checkbox"
                            id="checkAllLogs"
                            class="accent-brand"
                            aria-label="تحديد كل الأنشطة"
                        >
                    </th>

                    <th>الوقت</th>
                    <th>المستخدم</th>
                    <th>الحدث</th>
                    <th>الوصف</th>
                    <th>الجهاز</th>
                    <th></th>
                </tr>
            </thead>

            <tbody id="activityTableBody">

                @forelse($logCollection as $log)

                    @php
                        $eventKey = (string) ($log->event ?? '');
                        $eventLabel = $eventOptions[$eventKey] ?? $eventKey ?: '-';
                        $eventTone = $eventTones[$eventKey] ?? 'badge-neutral';
                        $eventIcon = $eventIcons[$eventKey] ?? 'fa-bolt';
                        $eventColor = $eventColors[$eventKey] ?? 'info';
                        $logId = (string) ($log->id ?? '');
                    @endphp

                    <tr
                        data-activity-row
                        data-search="{{ strtolower(trim(($log->user ?? '') . ' ' . ($log->description ?? '') . ' ' . ($log->subject ?? '') . ' ' . ($log->ip ?? '') . ' ' . ($log->device ?? '') . ' ' . $eventLabel)) }}"
                    >

                        <td>
                            <input
                                type="checkbox"
                                class="activity-check accent-brand"
                                value="{{ $logId }}"
                                aria-label="تحديد النشاط {{ $logId }}"
                            >
                        </td>

                        <td>
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/5 text-dim">
                                    <i class="fa-solid fa-clock text-xs"></i>
                                </span>

                                <div>
                                    <div class="font-medium text-fg">
                                        {{ $log->at ?? '-' }}
                                    </div>

                                    @if($logId !== '')
                                        <div class="mt-0.5 text-[10px] text-dim">
                                            #{{ $logId }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="flex items-center gap-2">
                                <x-avatar
                                    :name="$log->user ?? '-'"
                                    size="h-8 w-8"
                                />

                                <div>
                                    <span class="font-semibold text-fg">
                                        {{ $log->user ?? '-' }}
                                    </span>

                                    @if($logId !== '')
                                        <button
                                            type="button"
                                            class="mr-1 text-[10px] text-dim hover:text-brand"
                                            data-copy-value="{{ $log->user ?? '' }}"
                                            title="نسخ اسم المستخدم"
                                            aria-label="نسخ اسم المستخدم"
                                        >
                                            <i class="fa-solid fa-copy"></i>
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <td>
                            <span class="badge {{ $eventTone }}">
                                <i class="fa-solid {{ $eventIcon }} ml-1 text-[9px]"></i>
                                {{ $eventLabel }}
                            </span>
                        </td>

                        <td class="whitespace-normal min-w-[280px] max-w-[520px]">

                            <div class="flex items-start gap-2">

                                <span class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-{{ $eventColor }}/10 text-{{ $eventColor }}">
                                    <i class="fa-solid {{ $eventIcon }} text-[10px]"></i>
                                </span>

                                <div>
                                    <span class="text-fg">
                                        {{ $log->description ?? '-' }}
                                    </span>

                                    @if($log->subject)
                                        <span class="mr-1 text-[11px] text-dim">
                                            · {{ $log->subject }}
                                        </span>
                                    @endif
                                </div>

                            </div>

                        </td>

                        <td>

                            <button
                                type="button"
                                class="text-right"
                                data-copy-ip="{{ $log->ip ?? '' }}"
                                title="نسخ عنوان IP"
                            >
                                <span dir="ltr" class="text-fg hover:text-brand">
                                    {{ $log->ip ?? '-' }}
                                </span>
                            </button>

                            <br>

                            <span class="text-[11px] text-dim">
                                {{ $log->device ?? '-' }}
                            </span>

                        </td>

                        <td>
                            <div class="flex items-center gap-2">

                                <button
                                    type="button"
                                    class="btn btn-secondary btn-sm"
                                    data-log="{{ $logId }}"
                                >
                                    <i class="fa-solid fa-eye text-xs"></i>
                                    تفاصيل
                                </button>

                                <button
                                    type="button"
                                    class="icon-btn icon-btn-info"
                                    data-copy-description="{{ $log->description ?? '' }}"
                                    title="نسخ الوصف"
                                    aria-label="نسخ الوصف"
                                >
                                    <i class="fa-solid fa-copy"></i>
                                </button>

                            </div>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="7">
                            <x-empty-state
                                icon="fa-clock-rotate-left"
                                text="لا توجد أنشطة مطابقة"
                            />
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>
    </div>

    {{-- NO LOCAL SEARCH RESULTS --}}
    <div id="noLocalResults" class="mt-4 hidden">
        <x-empty-state
            icon="fa-filter-circle-xmark"
            text="لا توجد نتائج مطابقة للبحث الحالي"
        />
    </div>

    {{-- DETAILS DIALOG --}}
    <x-modal id="logModal" title="تفاصيل النشاط" width="40rem">

        <div class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-white/5 bg-white/5 p-3">

            <div class="flex items-center gap-3">

                <span
                    id="log-event-icon"
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-info/10 text-info"
                >
                    <i class="fa-solid fa-bolt"></i>
                </span>

                <div>
                    <p class="text-[11px] text-dim">النشاط</p>
                    <p id="log-event" class="font-bold text-fg"></p>
                </div>

            </div>

            <button
                type="button"
                id="copyLogDetails"
                class="btn btn-secondary btn-sm"
            >
                <i class="fa-solid fa-copy text-xs"></i>
                نسخ التفاصيل
            </button>

        </div>

        <dl class="grid grid-cols-2 gap-4 text-sm">

            <div>
                <dt class="text-[11px] text-dim">المستخدم</dt>

                <dd class="mt-1 flex items-center gap-2 font-semibold text-fg">
                    <span id="log-user"></span>

                    <button
                        type="button"
                        id="copyLogUser"
                        class="text-dim hover:text-brand"
                        title="نسخ المستخدم"
                    >
                        <i class="fa-solid fa-copy text-[10px]"></i>
                    </button>
                </dd>
            </div>

            <div>
                <dt class="text-[11px] text-dim">رقم النشاط</dt>

                <dd class="mt-1 flex items-center gap-2 text-fg">
                    <span id="log-id"></span>

                    <button
                        type="button"
                        id="copyLogId"
                        class="text-dim hover:text-brand"
                        title="نسخ رقم النشاط"
                    >
                        <i class="fa-solid fa-copy text-[10px]"></i>
                    </button>
                </dd>
            </div>

            <div>
                <dt class="text-[11px] text-dim">الوقت</dt>
                <dd id="log-at" class="mt-1 text-fg"></dd>
            </div>

            <div>
                <dt class="text-[11px] text-dim">الجهاز</dt>

                <dd class="mt-1 text-fg">
                    <span id="log-device"></span>
                </dd>
            </div>

            <div>
                <dt class="text-[11px] text-dim">عنوان IP</dt>

                <dd class="mt-1 flex items-center gap-2 text-fg">

                    <span id="log-ip" dir="ltr"></span>

                    <button
                        type="button"
                        id="copyLogIp"
                        class="text-dim hover:text-brand"
                        title="نسخ IP"
                    >
                        <i class="fa-solid fa-copy text-[10px]"></i>
                    </button>

                </dd>
            </div>

            <div>
                <dt class="text-[11px] text-dim">الحدث</dt>
                <dd id="log-event-key" class="mt-1 text-fg"></dd>
            </div>

            <div class="col-span-2">
                <dt class="text-[11px] text-dim">الوصف</dt>

                <dd
                    id="log-description"
                    class="mt-1 whitespace-pre-wrap rounded-xl border border-white/5 bg-white/5 p-3 text-fg"
                ></dd>
            </div>

        </dl>

        <div id="log-changes-wrap" class="mt-5">

            <div class="mb-2 flex items-center justify-between gap-3">
                <p class="text-xs font-bold text-fg">التغييرات</p>

                <span
                    id="log-changes-count"
                    class="badge badge-neutral"
                >
                    0
                </span>
            </div>

            <div class="table-wrap">

                <table class="data-table">

                    <thead>
                        <tr>
                            <th>الحقل</th>
                            <th>قبل</th>
                            <th>بعد</th>
                        </tr>
                    </thead>

                    <tbody id="log-changes"></tbody>

                </table>

            </div>

        </div>

        <p
            id="log-no-changes"
            class="mt-5 hidden text-xs text-dim"
        >
            لا توجد قيم محفوظة لهذا النشاط.
        </p>

    </x-modal>

@endsection

@push('scripts')

<script>
document.addEventListener('DOMContentLoaded', () => {

    /*
    |--------------------------------------------------------------------------
    | Data
    |--------------------------------------------------------------------------
    */

    const details = {{ Js::from($detailsMap->all()) }};
    const activityData = {{ Js::from($activityJsData->all()) }};
    const eventLabels = {{ Js::from($eventOptions->all()) }};

    /*
    |--------------------------------------------------------------------------
    | Cached DOM
    |--------------------------------------------------------------------------
    | Cache frequently used nodes.
    | This avoids repeatedly searching the entire document.
    */

    const searchInput = document.getElementById('search');
    const tableBody = document.getElementById('activityTableBody');
    const table = document.getElementById('activityTable');

    const checkAll = document.getElementById('checkAllLogs');
    const selectAllButton = document.getElementById('selectAllLogs');
    const clearSelectionButton = document.getElementById('clearLogSelection');
    const copySelectedButton = document.getElementById('copySelectedLogs');

    const selectedCount = document.getElementById('selected-count');
    const visibleCount = document.getElementById('visible-count');
    const pageResultCount = document.getElementById('page-result-count');

    const noLocalResults = document.getElementById('noLocalResults');

    const refreshButton = document.getElementById('refreshLogs');
    const printButton = document.getElementById('printLogs');
    const exportButton = document.getElementById('exportLogs');

    const activityRows = Array.from(
        document.querySelectorAll('[data-activity-row]')
    );

    const checkboxes = Array.from(
        document.querySelectorAll('.activity-check')
    );

    /*
    |--------------------------------------------------------------------------
    | Modal fields
    |--------------------------------------------------------------------------
    */

    const modal = document.getElementById('logModal');

    const logUser = document.getElementById('log-user');
    const logId = document.getElementById('log-id');
    const logEvent = document.getElementById('log-event');
    const logEventKey = document.getElementById('log-event-key');
    const logAt = document.getElementById('log-at');
    const logIp = document.getElementById('log-ip');
    const logDevice = document.getElementById('log-device');
    const logDescription = document.getElementById('log-description');

    const logEventIcon = document.getElementById('log-event-icon');

    const changesWrap = document.getElementById('log-changes-wrap');
    const changesBody = document.getElementById('log-changes');
    const changesCount = document.getElementById('log-changes-count');
    const noChanges = document.getElementById('log-no-changes');

    const copyLogDetails = document.getElementById('copyLogDetails');
    const copyLogUser = document.getElementById('copyLogUser');
    const copyLogId = document.getElementById('copyLogId');
    const copyLogIp = document.getElementById('copyLogIp');

    let currentLog = null;

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    const safeText = (value) => {
        if (value === null || value === undefined || value === '') {
            return '-';
        }

        return String(value);
    };

    const getVisibleRows = () => {
        return activityRows.filter(row => !row.hidden);
    };

    const getVisibleCheckboxes = () => {
        return getVisibleRows()
            .map(row => row.querySelector('.activity-check'))
            .filter(Boolean);
    };

    const getSelectedCheckboxes = () => {
        return checkboxes.filter(checkbox => checkbox.checked);
    };

    /*
    |--------------------------------------------------------------------------
    | Clipboard
    |--------------------------------------------------------------------------
    */

    const copyText = async (value) => {

        const textValue = safeText(value);

        if (!textValue || textValue === '-') {
            return false;
        }

        try {

            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(textValue);
                return true;
            }

            const textarea = document.createElement('textarea');

            textarea.value = textValue;
            textarea.setAttribute('readonly', '');
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';

            document.body.appendChild(textarea);

            textarea.select();
            document.execCommand('copy');

            textarea.remove();

            return true;

        } catch (error) {

            console.warn('Clipboard copy failed:', error);

            return false;
        }
    };

    const showCopiedState = (button) => {

        if (!button) return;

        const originalHtml = button.innerHTML;

        button.innerHTML = '<i class="fa-solid fa-check"></i>';

        window.setTimeout(() => {
            button.innerHTML = originalHtml;
        }, 1200);
    };

    /*
    |--------------------------------------------------------------------------
    | Selection
    |--------------------------------------------------------------------------
    */

    const refreshSelectionState = () => {

        const selected = getSelectedCheckboxes();
        const visible = getVisibleCheckboxes();

        selectedCount.textContent = selected.length;

        copySelectedButton.disabled = selected.length === 0;

        if (!visible.length) {

            checkAll.checked = false;
            checkAll.indeterminate = false;

        } else {

            const checkedVisible = visible.filter(
                checkbox => checkbox.checked
            ).length;

            if (checkedVisible === visible.length) {

                checkAll.checked = true;
                checkAll.indeterminate = false;

            } else if (checkedVisible === 0) {

                checkAll.checked = false;
                checkAll.indeterminate = false;

            } else {

                checkAll.checked = false;
                checkAll.indeterminate = true;
            }
        }
    };

    checkAll?.addEventListener('change', () => {

        const visibleCheckboxes = getVisibleCheckboxes();

        visibleCheckboxes.forEach(checkbox => {
            checkbox.checked = checkAll.checked;
        });

        refreshSelectionState();
    });

    selectAllButton?.addEventListener('click', () => {

        getVisibleCheckboxes().forEach(checkbox => {
            checkbox.checked = true;
        });

        refreshSelectionState();
    });

    clearSelectionButton?.addEventListener('click', () => {

        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });

        refreshSelectionState();
    });

    checkboxes.forEach(checkbox => {

        checkbox.addEventListener('change', refreshSelectionState);

    });

    /*
    |--------------------------------------------------------------------------
    | Local search
    |--------------------------------------------------------------------------
    | This only filters the already loaded records.
    | Server-side filtering remains untouched.
    */

    let searchTimer = null;

    searchInput?.addEventListener('input', () => {

        window.clearTimeout(searchTimer);

        searchTimer = window.setTimeout(() => {

            const query = searchInput.value
                .trim()
                .toLowerCase();

            let visible = 0;

            activityRows.forEach(row => {

                const searchable = row.dataset.search || '';

                const matches = !query || searchable.includes(query);

                row.hidden = !matches;

                if (matches) {
                    visible++;
                }
            });

            visibleCount.textContent = visible;

            noLocalResults.classList.toggle(
                'hidden',
                visible !== 0 || activityRows.length === 0
            );

            refreshSelectionState();

        }, 80);
    });

    /*
    |--------------------------------------------------------------------------
    | Details modal
    |--------------------------------------------------------------------------
    */

    const normalizeChanges = (changes) => {

        if (!Array.isArray(changes)) {
            return [];
        }

        return changes.filter(change => Array.isArray(change));
    };

    const openDetails = (id) => {

        const log = activityData.find(
            item => String(item.id) === String(id)
        );

        if (!log) {
            return;
        }

        currentLog = log;

        logUser.textContent = safeText(log.user);
        logId.textContent = safeText(log.id);
        logEvent.textContent = safeText(log.event_label);
        logEventKey.textContent = safeText(log.event);
        logAt.textContent = safeText(log.at);
        logIp.textContent = safeText(log.ip);
        logDevice.textContent = safeText(log.device);

        const description = log.subject
            ? `${safeText(log.description)} (${safeText(log.subject)})`
            : safeText(log.description);

        logDescription.textContent = description;

        /*
        |--------------------------------------------------------------------------
        | Event icon
        |--------------------------------------------------------------------------
        */

        const iconMap = {
            login: 'fa-right-to-bracket',
            created: 'fa-plus',
            updated: 'fa-pen',
            deleted: 'fa-trash',
            imported: 'fa-file-arrow-up',
            exported: 'fa-file-arrow-down',
            assigned: 'fa-user-plus',
            confirmed: 'fa-circle-check',
        };

        const icon = iconMap[log.event] || 'fa-bolt';

        logEventIcon.innerHTML =
            `<i class="fa-solid ${icon}"></i>`;

        /*
        |--------------------------------------------------------------------------
        | Changes
        |--------------------------------------------------------------------------
        */

        const changes = normalizeChanges(log.changes);

        changesBody.replaceChildren();

        changes.forEach((change) => {

            const row = changesBody.insertRow();

            const fieldCell = row.insertCell();
            const beforeCell = row.insertCell();
            const afterCell = row.insertCell();

            fieldCell.textContent = safeText(change[0]);
            beforeCell.textContent = safeText(change[1]);
            afterCell.textContent = safeText(change[2]);

            beforeCell.className = 'text-danger';
            afterCell.className = 'text-brand';
        });

        changesCount.textContent = changes.length;

        changesWrap.classList.toggle(
            'hidden',
            changes.length === 0
        );

        noChanges.classList.toggle(
            'hidden',
            changes.length !== 0
        );

        if (modal?.showModal) {
            modal.showModal();
        } else {
            modal?.classList.remove('hidden');
        }
    };

    /*
    |--------------------------------------------------------------------------
    | Event delegation
    |--------------------------------------------------------------------------
    */

    document.addEventListener('click', async (event) => {

        const detailsButton = event.target.closest('[data-log]');

        if (detailsButton) {

            openDetails(detailsButton.dataset.log);

            return;
        }

        const copyValueButton = event.target.closest('[data-copy-value]');

        if (copyValueButton) {

            const copied = await copyText(
                copyValueButton.dataset.copyValue
            );

            if (copied) {
                showCopiedState(copyValueButton);
            }

            return;
        }

        const copyIpButton = event.target.closest('[data-copy-ip]');

        if (copyIpButton) {

            const copied = await copyText(
                copyIpButton.dataset.copyIp
            );

            if (copied) {
                showCopiedState(copyIpButton);
            }

            return;
        }

        const copyDescriptionButton =
            event.target.closest('[data-copy-description]');

        if (copyDescriptionButton) {

            const copied = await copyText(
                copyDescriptionButton.dataset.copyDescription
            );

            if (copied) {
                showCopiedState(copyDescriptionButton);
            }

            return;
        }
    });

    /*
    |--------------------------------------------------------------------------
    | Copy details modal values
    |--------------------------------------------------------------------------
    */

    copyLogUser?.addEventListener('click', async () => {

        if (!currentLog) return;

        if (await copyText(currentLog.user)) {
            showCopiedState(copyLogUser);
        }
    });

    copyLogId?.addEventListener('click', async () => {

        if (!currentLog) return;

        if (await copyText(currentLog.id)) {
            showCopiedState(copyLogId);
        }
    });

    copyLogIp?.addEventListener('click', async () => {

        if (!currentLog) return;

        if (await copyText(currentLog.ip)) {
            showCopiedState(copyLogIp);
        }
    });

    copyLogDetails?.addEventListener('click', async () => {

        if (!currentLog) return;

        const changes = normalizeChanges(currentLog.changes);

        const lines = [
            `رقم النشاط: ${safeText(currentLog.id)}`,
            `المستخدم: ${safeText(currentLog.user)}`,
            `الحدث: ${safeText(currentLog.event_label)}`,
            `الوقت: ${safeText(currentLog.at)}`,
            `IP: ${safeText(currentLog.ip)}`,
            `الجهاز: ${safeText(currentLog.device)}`,
            `الوصف: ${safeText(currentLog.description)}`,
        ];

        if (currentLog.subject) {
            lines.push(`العنصر: ${safeText(currentLog.subject)}`);
        }

        if (changes.length) {

            lines.push('');
            lines.push('التغييرات:');

            changes.forEach(change => {

                lines.push(
                    `${safeText(change[0])}: ${safeText(change[1])} → ${safeText(change[2])}`
                );
            });
        }

        if (await copyText(lines.join('\n'))) {
            showCopiedState(copyLogDetails);
        }
    });

    /*
    |--------------------------------------------------------------------------
    | Copy selected activities
    |--------------------------------------------------------------------------
    */

    copySelectedButton?.addEventListener('click', async () => {

        const selectedIds = new Set(
            getSelectedCheckboxes().map(
                checkbox => String(checkbox.value)
            )
        );

        const selectedLogs = activityData.filter(
            item => selectedIds.has(String(item.id))
        );

        if (!selectedLogs.length) {
            return;
        }

        const output = selectedLogs.map((log) => {

            const subject = log.subject
                ? ` | ${log.subject}`
                : '';

            return [
                `#${safeText(log.id)}`,
                safeText(log.at),
                safeText(log.user),
                safeText(log.event_label),
                `${safeText(log.description)}${subject}`,
                `IP: ${safeText(log.ip)}`,
                `Device: ${safeText(log.device)}`,
            ].join(' | ');

        }).join('\n');

        if (await copyText(output)) {
            const original = copySelectedButton.innerHTML;

            copySelectedButton.innerHTML =
                '<i class="fa-solid fa-check text-xs"></i> تم النسخ';

            window.setTimeout(() => {
                copySelectedButton.innerHTML = original;
            }, 1400);
        }
    });

    /*
    |--------------------------------------------------------------------------
    | CSV export
    |--------------------------------------------------------------------------
    */

    const csvEscape = (value) => {

        const stringValue = safeText(value)
            .replace(/\r?\n/g, ' ');

        return `"${stringValue.replace(/"/g, '""')}"`;
    };

    exportButton?.addEventListener('click', () => {

        const visibleIds = new Set(
            getVisibleRows()
                .map(row => row.querySelector('.activity-check')?.value)
                .filter(Boolean)
                .map(String)
        );

        const records = activityData.filter(
            item => visibleIds.has(String(item.id))
        );

        if (!records.length) {
            return;
        }

        const header = [
            'ID',
            'الوقت',
            'المستخدم',
            'الحدث',
            'الوصف',
            'العنصر',
            'IP',
            'الجهاز',
        ];

        const rows = records.map(log => [
            log.id,
            log.at,
            log.user,
            log.event_label,
            log.description,
            log.subject,
            log.ip,
            log.device,
        ]);

        const csv = [
            header,
            ...rows,
        ]
            .map(row => row.map(csvEscape).join(','))
            .join('\r\n');

        /*
        | UTF-8 BOM
        | Helps Excel correctly recognize Arabic text.
        */

        const blob = new Blob(
            ['\uFEFF' + csv],
            {
                type: 'text/csv;charset=utf-8;'
            }
        );

        const url = URL.createObjectURL(blob);

        const anchor = document.createElement('a');

        anchor.href = url;
        anchor.download =
            `activity-logs-${new Date().toISOString().slice(0, 10)}.csv`;

        document.body.appendChild(anchor);

        anchor.click();

        anchor.remove();

        URL.revokeObjectURL(url);
    });

    /*
    |--------------------------------------------------------------------------
    | Print
    |--------------------------------------------------------------------------
    */

    printButton?.addEventListener('click', () => {

        const visibleIds = new Set(
            getVisibleRows()
                .map(row => row.querySelector('.activity-check')?.value)
                .filter(Boolean)
                .map(String)
        );

        const records = activityData.filter(
            item => visibleIds.has(String(item.id))
        );

        if (!records.length) {
            return;
        }

        const printWindow = window.open(
            '',
            '_blank',
            'width=1200,height=800'
        );

        if (!printWindow) {
            return;
        }

        const rowsHtml = records.map(log => {

            const description = log.subject
                ? `${safeText(log.description)} · ${safeText(log.subject)}`
                : safeText(log.description);

            return `
                <tr>
                    <td>${safeText(log.id)}</td>
                    <td>${safeText(log.at)}</td>
                    <td>${safeText(log.user)}</td>
                    <td>${safeText(log.event_label)}</td>
                    <td>${description}</td>
                    <td dir="ltr">${safeText(log.ip)}</td>
                    <td>${safeText(log.device)}</td>
                </tr>
            `;
        }).join('');

        printWindow.document.write(`
            <!doctype html>
            <html lang="ar" dir="rtl">
            <head>
                <meta charset="utf-8">

                <title>سجل النشاط</title>

                <style>
                    body {
                        font-family: Arial, sans-serif;
                        padding: 30px;
                        color: #111;
                    }

                    h1 {
                        margin-bottom: 5px;
                    }

                    p {
                        color: #666;
                        margin-top: 0;
                    }

                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 25px;
                    }

                    th,
                    td {
                        border: 1px solid #ddd;
                        padding: 8px;
                        text-align: right;
                        vertical-align: top;
                        font-size: 12px;
                    }

                    th {
                        background: #f3f3f3;
                    }

                    @media print {
                        body {
                            padding: 10px;
                        }
                    }
                </style>
            </head>

            <body>

                <h1>سجل النشاط</h1>

                <p>
                    تم إنشاء التقرير بتاريخ:
                    ${new Date().toLocaleString('ar-EG')}
                </p>

                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الوقت</th>
                            <th>المستخدم</th>
                            <th>الحدث</th>
                            <th>الوصف</th>
                            <th>IP</th>
                            <th>الجهاز</th>
                        </tr>
                    </thead>

                    <tbody>
                        ${rowsHtml}
                    </tbody>
                </table>

            </body>
            </html>
        `);

        printWindow.document.close();

        printWindow.focus();

        window.setTimeout(() => {
            printWindow.print();
        }, 250);
    });

    /*
    |--------------------------------------------------------------------------
    | Refresh
    |--------------------------------------------------------------------------
    */

    refreshButton?.addEventListener('click', () => {

        refreshButton.disabled = true;

        const originalHtml = refreshButton.innerHTML;

        refreshButton.innerHTML =
            '<i class="fa-solid fa-spinner fa-spin text-xs"></i> تحديث...';

        window.location.reload();

        window.setTimeout(() => {
            refreshButton.disabled = false;
            refreshButton.innerHTML = originalHtml;
        }, 2000);
    });

    /*
    |--------------------------------------------------------------------------
    | Keyboard shortcuts
    |--------------------------------------------------------------------------
    */

    document.addEventListener('keydown', (event) => {

        /*
        | Ctrl + K
        | Focus activity search.
        */

        if (
            (event.ctrlKey || event.metaKey) &&
            event.key.toLowerCase() === 'k'
        ) {
            event.preventDefault();

            searchInput?.focus();
            searchInput?.select();

            return;
        }

        /*
        | Escape
        | Clear local search first.
        */

        if (event.key === 'Escape') {

            if (
                document.activeElement === searchInput &&
                searchInput.value !== ''
            ) {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input'));

                return;
            }
        }
    });

    /*
    |--------------------------------------------------------------------------
    | Initial state
    |--------------------------------------------------------------------------
    */

    refreshSelectionState();

    if (pageResultCount) {
        pageResultCount.textContent = activityRows.length;
    }

});
</script>

@endpush