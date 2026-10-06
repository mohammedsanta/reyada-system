{{-- resources/views/banks/visits.blade.php --}}
@extends('layouts.app')

@section('title', 'إدارة الزيارات - ' . $bank->name)

@section('content')

    @php
        /*
        |--------------------------------------------------------------------------
        | Safe frontend calculations
        |--------------------------------------------------------------------------
        | لا نغير أي بيانات backend موجودة.
        | كل الحسابات التالية مبنية فقط على البيانات الموجودة بالفعل.
        */

        $allVisits = collect($visits ?? []);

        $scheduledCount = (int) ($counts['scheduled'] ?? 0);
        $completedCount = (int) ($counts['completed'] ?? 0);
        $missedCount = (int) ($counts['missed'] ?? 0);
        $cancelledCount = (int) ($counts['cancelled'] ?? 0);
        $allCount = (int) ($counts['all'] ?? $allVisits->count());

        $todayCount = (int) ($today ?? 0);

        $statusLabels = $statuses ?? [];
        $outcomeLabels = $outcomes ?? [];

        $activeStatusLabel = $status !== ''
            ? ($statusLabels[$status] ?? $status)
            : 'كل الحالات';

        $activeCollectorLabel = 'كل المحصلين';

        if (!empty($collector) && isset($employees[$collector])) {
            $activeCollectorLabel = $employees[$collector];
        }

        /*
        |--------------------------------------------------------------------------
        | Completion percentage
        |--------------------------------------------------------------------------
        */
        $completionRate = $allCount > 0
            ? round(($completedCount / $allCount) * 100)
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Safe bounds
        |--------------------------------------------------------------------------
        */
        $completionRate = min(100, max(0, $completionRate));

        /*
        |--------------------------------------------------------------------------
        | Current date/time for frontend helpers
        |--------------------------------------------------------------------------
        */
        $todayDate = now()->toDateString();
    @endphp


    {{-- ================================================================
        PAGE HEADER
    ================================================================= --}}
    <x-bank-header
        :bank="$bank"
        title="إدارة الزيارات"
        subtitle="جدولة الزيارات الميدانية ومتابعة نتائجها"
    >

        <x-slot:actions>

            <button
                type="button"
                class="btn btn-primary btn-sm"
                data-open-modal="scheduleModal"
            >
                <i class="fa-solid fa-calendar-plus text-xs"></i>
                جدولة زيارة
            </button>

        </x-slot:actions>

    </x-bank-header>


    {{-- ================================================================
        FLASH
    ================================================================= --}}
    <x-flash />


    @error('action')

        <div class="alert alert-danger mb-6">
            {{ $message }}
        </div>

    @enderror


    {{-- ================================================================
        QUICK OVERVIEW
    ================================================================= --}}
    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <x-metric-card
            label="زيارات اليوم"
            :value="$today"
            icon="fa-calendar-day"
            color="cyan"
        />

        <x-metric-card
            label="مجدولة"
            :value="$counts['scheduled'] ?? 0"
            icon="fa-calendar-check"
            color="info"
        />

        <x-metric-card
            label="تمت"
            :value="$counts['completed'] ?? 0"
            icon="fa-circle-check"
            color="brand"
        />

        <x-metric-card
            label="فائتة"
            :value="$counts['missed'] ?? 0"
            icon="fa-circle-xmark"
            color="danger"
        />

    </section>


    {{-- ================================================================
        SECONDARY OVERVIEW
    ================================================================= --}}
    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        {{-- Total --}}
        <div class="card">

            <div class="flex items-center justify-between gap-3">

                <div>

                    <p class="text-xs text-dim">
                        إجمالي الزيارات
                    </p>

                    <p
                        class="mt-1 text-xl font-bold text-fg"
                        data-total-visits
                    >
                        {{ $allCount }}
                    </p>

                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-info/10 text-info">

                    <i class="fa-solid fa-list-check"></i>

                </div>

            </div>

        </div>


        {{-- Completion --}}
        <div class="card">

            <div class="flex items-center justify-between gap-3">

                <div>

                    <p class="text-xs text-dim">
                        نسبة التنفيذ
                    </p>

                    <p class="mt-1 text-xl font-bold text-brand">
                        {{ $completionRate }}%
                    </p>

                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand/10 text-brand">

                    <i class="fa-solid fa-chart-pie"></i>

                </div>

            </div>

            <div class="mt-3 h-1.5 overflow-hidden rounded-full bg-black/20">

                <div
                    class="h-full rounded-full bg-brand transition-all"
                    style="width: {{ $completionRate }}%"
                ></div>

            </div>

        </div>


        {{-- Cancelled --}}
        <div class="card">

            <div class="flex items-center justify-between gap-3">

                <div>

                    <p class="text-xs text-dim">
                        ملغاة
                    </p>

                    <p class="mt-1 text-xl font-bold text-fg">
                        {{ $cancelledCount }}
                    </p>

                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-danger/10 text-danger">

                    <i class="fa-solid fa-ban"></i>

                </div>

            </div>

        </div>


        {{-- Current filters --}}
        <div class="card">

            <div class="flex items-center justify-between gap-3">

                <div>

                    <p class="text-xs text-dim">
                        الفلاتر الحالية
                    </p>

                    <p class="mt-1 text-sm font-bold text-fg">
                        {{ $activeStatusLabel }}
                    </p>

                    <p class="mt-1 text-[11px] text-dim">
                        {{ $activeCollectorLabel }}
                    </p>

                </div>

                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-cyan/10 text-cyan">

                    <i class="fa-solid fa-filter"></i>

                </div>

            </div>

        </div>

    </section>


    {{-- ================================================================
        TABS + COLLECTOR FILTER + QUICK TOOLS
    ================================================================= --}}
    <div class="mb-4 flex flex-wrap items-center justify-between gap-4">

        <div class="segmented">

            <a
                href="{{ route('banks.visits.index', [
                    'bank' => $bank->id,
                    'collector' => $collector ?: null
                ]) }}"
                class="segmented-item {{ $status === '' ? 'segmented-item-active' : '' }}"
            >
                الكل

                <span class="text-[10px] opacity-70">
                    ({{ $counts['all'] }})
                </span>

            </a>


            @foreach($statuses as $key => $label)

                <a
                    href="{{ route('banks.visits.index', [
                        'bank' => $bank->id,
                        'status' => $key,
                        'collector' => $collector ?: null
                    ]) }}"
                    class="segmented-item {{ $status === $key ? 'segmented-item-active' : '' }}"
                >

                    {{ $label }}

                    <span class="text-[10px] opacity-70">
                        ({{ $counts[$key] ?? 0 }})
                    </span>

                </a>

            @endforeach

        </div>


        <div class="flex flex-wrap items-center gap-2">

            {{-- Collector filter --}}
            <form
                method="GET"
                action="{{ route('banks.visits.index', $bank->id) }}"
                class="w-56"
                id="collectorFilterForm"
            >

                <input
                    type="hidden"
                    name="status"
                    value="{{ $status }}"
                >

                <label
                    for="collector"
                    class="sr-only"
                >
                    المحصل
                </label>

                <select
                    id="collector"
                    name="collector"
                    class="form-input"
                    onchange="this.form.submit()"
                >

                    <option value="0">
                        كل المحصلين
                    </option>

                    @foreach($employees as $id => $name)

                        <option
                            value="{{ $id }}"
                            @selected($collector === $id)
                        >
                            {{ $name }}
                        </option>

                    @endforeach

                </select>

            </form>


            {{-- Print --}}
            <button
                type="button"
                class="btn btn-secondary btn-sm"
                id="printVisits"
                title="طباعة الزيارات"
            >

                <i class="fa-solid fa-print text-xs"></i>

            </button>


            {{-- Export --}}
            <button
                type="button"
                class="btn btn-secondary btn-sm"
                id="exportVisits"
                title="تصدير CSV"
            >

                <i class="fa-solid fa-file-csv text-xs"></i>

            </button>

        </div>

    </div>


    {{-- ================================================================
        TABLE SEARCH / SUMMARY BAR
    ================================================================= --}}
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">

        <div class="relative w-full md:w-80">

            <i class="fa-solid fa-magnifying-glass absolute right-3 top-1/2 -translate-y-1/2 text-xs text-dim"></i>

            <input
                id="visitSearch"
                type="search"
                class="form-input pr-9"
                placeholder="بحث بالعميل، ID، العنوان، المحصل..."
                autocomplete="off"
                aria-label="بحث في الزيارات"
            >

        </div>


        <div class="flex items-center gap-2 text-xs text-dim">

            <span>
                المعروض:
            </span>

            <span
                id="visibleVisitsCount"
                class="font-bold text-fg"
            >
                {{ $allVisits->count() }}
            </span>

            <span>
                من {{ $allVisits->count() }}
            </span>

        </div>

    </div>


    {{-- ================================================================
        ACTIVE FILTER SUMMARY
    ================================================================= --}}
    @if($status !== '' || !empty($collector))

        <div class="mb-4 flex flex-wrap items-center gap-2">

            <span class="text-xs text-dim">
                الفلاتر الحالية:
            </span>


            @if($status !== '')

                <span class="badge badge-neutral">

                    <i class="fa-solid fa-circle-dot ml-1"></i>

                    {{ $activeStatusLabel }}

                </span>

            @endif


            @if(!empty($collector) && isset($employees[$collector]))

                <span class="badge badge-neutral">

                    <i class="fa-solid fa-user ml-1"></i>

                    {{ $employees[$collector] }}

                </span>

            @endif


            <a
                href="{{ route('banks.visits.index', ['bank' => $bank->id]) }}"
                class="text-xs text-info hover:underline"
            >
                مسح الفلاتر
            </a>

        </div>

    @endif


    {{-- ================================================================
        VISITS TABLE
    ================================================================= --}}
    <div
        class="table-wrap"
        id="visitsTableWrap"
    >

        <table
            class="data-table whitespace-nowrap"
            id="visitsTable"
        >

            <thead>

                <tr>

                    <th>الموعد</th>

                    <th>العميل</th>

                    <th>العنوان</th>

                    <th>المحصل</th>

                    <th>الحالة</th>

                    <th>النتيجة / ملاحظات</th>

                    <th>الإجراءات</th>

                </tr>

            </thead>


            <tbody id="visitsTableBody">

                @forelse($visits as $visit)

                    <tr
                        class="visit-row"

                        data-search="{{ strtolower(
                            ($visit->client_name ?? '') . ' ' .
                            ($visit->client_code ?? '') . ' ' .
                            ($visit->address ?? '') . ' ' .
                            ($visit->collector->name ?? '') . ' ' .
                            ($visit->notes ?? '') . ' ' .
                            ($visit->outcome ?? '')
                        ) }}"
                    >

                        {{-- ====================================================
                            موعد
                        ===================================================== --}}
                        <td
                            @class([
                                'font-semibold',
                                'text-brand' =>
                                    $visit->when->isToday() &&
                                    $visit->status === 'scheduled',
                                'text-fg' =>
                                    ! (
                                        $visit->when->isToday() &&
                                        $visit->status === 'scheduled'
                                    ),
                            ])
                        >

                            <div class="flex items-center gap-2">

                                @if(
                                    $visit->when->isToday() &&
                                    $visit->status === 'scheduled'
                                )

                                    <span
                                        class="h-1.5 w-1.5 rounded-full bg-brand"
                                        title="اليوم"
                                    ></span>

                                @endif

                                <span>
                                    {{ $visit->when_label }}
                                </span>

                            </div>

                        </td>


                        {{-- ====================================================
                            العميل
                        ===================================================== --}}
                        <td>

                            <button
                                type="button"

                                class="group text-right"

                                data-view-visit="{{ $visit->id }}"

                                data-client="{{ $visit->client_name }}"

                                data-client-code="{{ $visit->client_code }}"

                                data-address="{{ $visit->address }}"

                                data-collector="{{ $visit->collector->name }}"

                                data-when="{{ $visit->when_label }}"

                                data-status="{{ $visit->status }}"

                                data-status-label="{{ $statusLabels[$visit->status] ?? $visit->status }}"

                                data-outcome="{{ $visit->outcome ? ($outcomes[$visit->outcome] ?? $visit->outcome) : '' }}"

                                data-notes="{{ $visit->notes ?? '' }}"
                            >

                                <span class="block font-semibold text-fg transition group-hover:text-brand">

                                    {{ $visit->client_name }}

                                </span>


                                <span class="mt-0.5 block font-mono text-[10px] text-dim transition group-hover:text-brand">

                                    {{ $visit->client_code }}

                                </span>

                            </button>

                        </td>


                        {{-- ====================================================
                            العنوان
                        ===================================================== --}}
                        <td class="whitespace-normal min-w-[240px]">

                            <div class="flex items-start gap-2">

                                <i class="fa-solid fa-location-dot mt-1 text-xs text-dim"></i>

                                <span>
                                    {{ $visit->address }}
                                </span>

                            </div>

                        </td>


                        {{-- ====================================================
                            المحصل
                        ===================================================== --}}
                        <td>

                            <div class="flex items-center gap-2">

                                <x-avatar
                                    :name="$visit->collector->name"
                                    size="h-8 w-8"
                                />

                                <span>
                                    {{ $visit->collector->name }}
                                </span>

                            </div>

                        </td>


                        {{-- ====================================================
                            الحالة
                        ===================================================== --}}
                        <td>

                            <x-status-badge
                                type="visit"
                                :status="$visit->status"
                            />

                        </td>


                        {{-- ====================================================
                            النتيجة
                        ===================================================== --}}
                        <td class="whitespace-normal min-w-[220px]">

                            @if($visit->outcome)

                                <b class="text-fg">

                                    {{ $outcomes[$visit->outcome] ?? $visit->outcome }}

                                </b>

                                <br>

                            @endif


                            <span class="text-[11px] text-dim">

                                {{ $visit->notes ?? '-' }}

                            </span>

                        </td>


                        {{-- ====================================================
                            الإجراءات
                        ===================================================== --}}
                        <td>

                            <div class="flex items-center gap-2">

                                {{-- ==================================================
                                    VIEW VISIT
                                =================================================== --}}
                                <button
                                    type="button"
                                    class="btn btn-secondary btn-sm"

                                    data-view-visit="{{ $visit->id }}"

                                    data-client="{{ $visit->client_name }}"

                                    data-client-code="{{ $visit->client_code }}"

                                    data-address="{{ $visit->address }}"

                                    data-collector="{{ $visit->collector->name }}"

                                    data-when="{{ $visit->when_label }}"

                                    data-status="{{ $visit->status }}"

                                    data-status-label="{{ $statusLabels[$visit->status] ?? $visit->status }}"

                                    data-outcome="{{ $visit->outcome ? ($outcomes[$visit->outcome] ?? $visit->outcome) : '' }}"

                                    data-notes="{{ $visit->notes ?? '' }}"
                                >

                                    <i class="fa-solid fa-eye text-xs"></i>

                                    عرض

                                </button>


                                @if($visit->status === 'scheduled')

                                    {{-- ==================================================
                                        RECORD RESULT
                                    =================================================== --}}
                                    <button
                                        type="button"

                                        class="btn btn-outline-success btn-sm"

                                        data-result="{{ $visit->id }}"

                                        data-client="{{ $visit->client_name }}"

                                        data-client-code="{{ $visit->client_code }}"

                                        data-collector="{{ $visit->collector->name }}"

                                        data-when="{{ $visit->when_label }}"

                                        data-address="{{ $visit->address }}"

                                        data-status="{{ $visit->status }}"
                                    >

                                        <i class="fa-solid fa-clipboard-check text-xs"></i>

                                        تسجيل النتيجة

                                    </button>


                                    {{-- ==================================================
                                        CANCEL
                                    =================================================== --}}
                                    <form
                                        method="POST"

                                        action="{{ route('banks.visits.update', [$bank->id, $visit->id]) }}"

                                        onsubmit="return confirm('هل تريد إلغاء هذه الزيارة؟')"
                                    >

                                        @csrf

                                        @method('PUT')


                                        <input
                                            type="hidden"
                                            name="action"
                                            value="cancel"
                                        >


                                        <button
                                            type="submit"
                                            class="btn btn-danger btn-sm"
                                        >

                                            إلغاء

                                        </button>

                                    </form>

                                @endif

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="7">

                            <x-empty-state
                                icon="fa-person-walking"
                                text="لا توجد زيارات مطابقة"
                            />

                        </td>

                    </tr>

                @endforelse


                {{-- Frontend empty state --}}
                <tr
                    id="clientSearchEmpty"
                    class="hidden"
                >

                    <td colspan="7">

                        <div class="py-10 text-center">

                            <i class="fa-solid fa-magnifying-glass mb-3 text-2xl text-dim"></i>

                            <p class="text-sm font-semibold text-fg">
                                لا توجد نتائج مطابقة للبحث
                            </p>

                            <p class="mt-1 text-xs text-dim">
                                جرّب البحث باسم العميل أو الكود أو العنوان أو المحصل
                            </p>

                        </div>

                    </td>

                </tr>

            </tbody>

        </table>

    </div>


    {{-- ================================================================
        SCHEDULE A VISIT
    ================================================================= --}}
    @include('banks.partials._schedule-visit-form', [
        'bank' => $bank,
        'clients' => $clients,
        'employees' => $employees,
    ])


    {{-- ================================================================
        RECORD THE RESULT
    ================================================================= --}}
    @include('banks.partials._visit-result-form', [
        'bank' => $bank,
        'outcomes' => $outcomes,
    ])


    {{-- ================================================================
        VISIT DISPLAY
    ================================================================= --}}
    @include('banks.partials._visit-display', [
        'bank' => $bank,
    ])


    {{-- ================================================================
        BACK TO TOP
    ================================================================= --}}
    <button
        type="button"
        id="visitsBackToTop"
        class="fixed bottom-6 left-6 z-40 hidden h-10 w-10 rounded-full border border-border bg-panel text-fg shadow-lg"
        title="العودة للأعلى"
        aria-label="العودة للأعلى"
    >

        <i class="fa-solid fa-arrow-up text-xs"></i>

    </button>


@endsection


@push('scripts')

<script>

document.addEventListener('DOMContentLoaded', () => {

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    */

    const updateUrl = @json(
        route(
            'banks.visits.update',
            [$bank->id, '__ID__']
        )
    );


    /*
    |--------------------------------------------------------------------------
    | Elements
    |--------------------------------------------------------------------------
    */

    const searchInput =
        document.getElementById(
            'visitSearch'
        );


    const tableBody =
        document.getElementById(
            'visitsTableBody'
        );


    const rows =
        Array.from(
            document.querySelectorAll(
                '.visit-row'
            )
        );


    const visibleCount =
        document.getElementById(
            'visibleVisitsCount'
        );


    const searchEmpty =
        document.getElementById(
            'clientSearchEmpty'
        );


    /*
    |--------------------------------------------------------------------------
    | RESULT FORM ELEMENTS
    |--------------------------------------------------------------------------
    */

    const resultForm =
        document.getElementById(
            'resultForm'
        );


    const resultVisitId =
        document.getElementById(
            'result-visit-id'
        );


    const resultVisitIdDisplay =
        document.getElementById(
            'result-visit-id-display'
        );


    const resultClient =
        document.getElementById(
            'result-client'
        );


    const resultClientCode =
        document.getElementById(
            'result-client-code'
        );


    const resultVisitDate =
        document.getElementById(
            'result-visit-date'
        );


    const resultCollector =
        document.getElementById(
            'result-collector'
        );


    const resultVisitAddress =
        document.getElementById(
            'result-visit-address'
        );


    const resultModal =
        document.getElementById(
            'resultModal'
        );


    const resultNotes =
        resultForm
            ? resultForm.querySelector(
                'textarea[name="notes"]'
            )
            : null;


    const resultNotesCounter =
        document.getElementById(
            'resultNotesCounter'
        );


    const saveResultButton =
        document.getElementById(
            'saveResultButton'
        );


    /*
    |--------------------------------------------------------------------------
    | OPEN RESULT MODAL
    |--------------------------------------------------------------------------
    |
    | هذه هي النقطة الأساسية.
    |
    | زر "تسجيل النتيجة" يحتوي على كل بيانات الزيارة.
    | نقرأ البيانات منه ونضعها داخل الـ isolated modal.
    |
    */

    window.openResult = function (id) {

        const button =
            document.querySelector(
                `[data-result="${id}"]`
            );


        if (!button) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Form action
        |--------------------------------------------------------------------------
        */

        if (resultForm) {

            resultForm.action =
                updateUrl.replace(
                    '__ID__',
                    id
                );

        }


        /*
        |--------------------------------------------------------------------------
        | Visit ID
        |--------------------------------------------------------------------------
        */

        if (resultVisitId) {

            resultVisitId.value =
                id;

        }


        if (resultVisitIdDisplay) {

            resultVisitIdDisplay.textContent =
                id;

        }


        /*
        |--------------------------------------------------------------------------
        | Client
        |--------------------------------------------------------------------------
        */

        if (resultClient) {

            resultClient.textContent =
                button.dataset.client ||
                '-';

        }


        /*
        |--------------------------------------------------------------------------
        | Client Code
        |--------------------------------------------------------------------------
        */

        if (resultClientCode) {

            resultClientCode.textContent =
                button.dataset.clientCode ||
                '-';

        }


        /*
        |--------------------------------------------------------------------------
        | Visit date
        |--------------------------------------------------------------------------
        */

        if (resultVisitDate) {

            resultVisitDate.textContent =
                button.dataset.when ||
                '-';

        }


        /*
        |--------------------------------------------------------------------------
        | Collector
        |--------------------------------------------------------------------------
        */

        if (resultCollector) {

            resultCollector.textContent =
                button.dataset.collector ||
                '-';

        }


        /*
        |--------------------------------------------------------------------------
        | Address
        |--------------------------------------------------------------------------
        */

        if (resultVisitAddress) {

            resultVisitAddress.textContent =
                button.dataset.address ||
                '-';

        }


        /*
        |--------------------------------------------------------------------------
        | Reset outcome
        |--------------------------------------------------------------------------
        */

        const outcome =
            resultForm
                ? resultForm.querySelector(
                    '[name="outcome"]'
                )
                : null;


        if (outcome) {

            outcome.value =
                '';

            outcome.dispatchEvent(
                new Event('change')
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Reset notes
        |--------------------------------------------------------------------------
        */

        if (resultNotes) {

            resultNotes.value =
                '';

        }


        /*
        |--------------------------------------------------------------------------
        | Reset notes counter
        |--------------------------------------------------------------------------
        */

        if (resultNotesCounter) {

            resultNotesCounter.textContent =
                '0 حرف';

        }


        /*
        |--------------------------------------------------------------------------
        | Reset validation
        |--------------------------------------------------------------------------
        */

        const resultValidation =
            document.getElementById(
                'resultValidation'
            );


        if (resultValidation) {

            resultValidation.classList.add(
                'hidden'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Reset save button
        |--------------------------------------------------------------------------
        */

        if (saveResultButton) {

            saveResultButton.disabled =
                false;


            saveResultButton.dataset.submitted =
                '0';


            saveResultButton.innerHTML = `
                <i class="fa-solid fa-check text-xs"></i>
                حفظ النتيجة
            `;

        }


        /*
        |--------------------------------------------------------------------------
        | Open modal
        |--------------------------------------------------------------------------
        */

        if (resultModal) {

            resultModal.showModal();

        }

    };


    /*
    |--------------------------------------------------------------------------
    | RESULT BUTTON CLICK
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        (event) => {

            const button =
                event.target.closest(
                    '[data-result]'
                );


            if (!button) {
                return;
            }


            openResult(
                button.dataset.result
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | SEARCH VISITS
    |--------------------------------------------------------------------------
    */

    function filterVisits() {

        if (!searchInput) {
            return;
        }


        const query =
            searchInput.value
                .trim()
                .toLowerCase();


        let visible = 0;


        rows.forEach(
            (row) => {

                const searchable =
                    (
                        row.dataset.search ||
                        ''
                    ).toLowerCase();


                const matched =
                    query === '' ||
                    searchable.includes(
                        query
                    );


                row.classList.toggle(
                    'hidden',
                    !matched
                );


                if (matched) {

                    visible++;

                }

            }
        );


        if (visibleCount) {

            visibleCount.textContent =
                visible;

        }


        if (searchEmpty) {

            searchEmpty.classList.toggle(
                'hidden',
                visible !== 0 ||
                query === ''
            );

        }

    }


    if (searchInput) {

        searchInput.addEventListener(
            'input',
            filterVisits
        );


        /*
        |--------------------------------------------------------------------------
        | Ctrl + K
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'keydown',
            (event) => {

                if (
                    (event.ctrlKey ||
                        event.metaKey) &&
                    event.key.toLowerCase() === 'k'
                ) {

                    event.preventDefault();

                    searchInput.focus();

                    searchInput.select();

                }


                /*
                |--------------------------------------------------------------------------
                | Escape clears search
                |--------------------------------------------------------------------------
                */

                if (
                    event.key === 'Escape' &&
                    document.activeElement ===
                        searchInput
                ) {

                    searchInput.value =
                        '';

                    filterVisits();

                    searchInput.blur();

                }

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | RESULT NOTES COUNTER
    |--------------------------------------------------------------------------
    */

    function updateResultNotesCounter() {

        if (
            !resultNotes ||
            !resultNotesCounter
        ) {

            return;

        }


        resultNotesCounter.textContent =
            `${resultNotes.value.length} حرف`;

    }


    if (resultNotes) {

        resultNotes.addEventListener(
            'input',
            updateResultNotesCounter
        );


        updateResultNotesCounter();

    }


    /*
    |--------------------------------------------------------------------------
    | PREVENT DOUBLE SUBMIT - RESULT
    |--------------------------------------------------------------------------
    */

    if (resultForm) {

        resultForm.addEventListener(
            'submit',
            (event) => {

                if (
                    saveResultButton &&
                    saveResultButton.dataset.submitted === '1'
                ) {

                    event.preventDefault();

                    return;

                }


                if (!saveResultButton) {
                    return;
                }


                saveResultButton.dataset.submitted =
                    '1';


                saveResultButton.disabled =
                    true;


                saveResultButton.innerHTML = `
                    <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                    جاري الحفظ...
                `;

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | PREVENT DOUBLE SUBMIT - CANCEL FORMS
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll(
            'form[action*="visits"][method="POST"]'
        )
        .forEach(
            (form) => {

                /*
                | لا نطبق هذا على resultForm
                | لأنه يتم التعامل معه بالأعلى.
                */

                if (
                    form.id === 'resultForm'
                ) {

                    return;

                }


                form.addEventListener(
                    'submit',
                    (event) => {

                        const button =
                            form.querySelector(
                                'button[type="submit"]'
                            );


                        if (!button) {
                            return;
                        }


                        if (
                            button.dataset.submitted === '1'
                        ) {

                            event.preventDefault();

                            return;

                        }


                        button.dataset.submitted =
                            '1';


                        button.disabled =
                            true;

                    }
                );

            }
        );


    /*
    |--------------------------------------------------------------------------
    | PRINT
    |--------------------------------------------------------------------------
    */

    const printButton =
        document.getElementById(
            'printVisits'
        );


    if (printButton) {

        printButton.addEventListener(
            'click',
            () => {

                window.print();

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | CSV EXPORT
    |--------------------------------------------------------------------------
    */

    const exportButton =
        document.getElementById(
            'exportVisits'
        );


    if (exportButton) {

        exportButton.addEventListener(
            'click',
            () => {

                const visibleRows =
                    rows.filter(
                        (row) =>
                            !row.classList.contains(
                                'hidden'
                            )
                    );


                const data = [];


                data.push([
                    'الموعد',
                    'العميل',
                    'العنوان',
                    'المحصل',
                    'الحالة',
                    'النتيجة / الملاحظات'
                ]);


                visibleRows.forEach(
                    (row) => {

                        const cells =
                            Array.from(
                                row.querySelectorAll(
                                    'td'
                                )
                            );


                        if (cells.length < 6) {
                            return;
                        }


                        const values =
                            cells
                                .slice(0, 6)
                                .map(
                                    (cell) =>
                                        cell.innerText
                                            .replace(
                                                /\s+/g,
                                                ' '
                                            )
                                            .trim()
                                );


                        data.push(
                            values
                        );

                    }
                );


                const csv =
                    data
                        .map(
                            (row) =>
                                row
                                    .map(
                                        (value) => {

                                            const escaped =
                                                String(value)
                                                    .replace(
                                                        /"/g,
                                                        '""'
                                                    );

                                            return `"${escaped}"`;

                                        }
                                    )
                                    .join(',')
                        )
                        .join('\n');


                /*
                | BOM for Arabic / Excel
                */

                const blob =
                    new Blob(
                        [
                            "\uFEFF" +
                            csv
                        ],
                        {
                            type:
                                'text/csv;charset=utf-8;'
                        }
                    );


                const url =
                    URL.createObjectURL(
                        blob
                    );


                const link =
                    document.createElement(
                        'a'
                    );


                link.href =
                    url;


                link.download =
                    'visits-' +
                    new Date()
                        .toISOString()
                        .slice(0, 10) +
                    '.csv';


                document.body.appendChild(
                    link
                );


                link.click();


                link.remove();


                URL.revokeObjectURL(
                    url
                );

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | BACK TO TOP
    |--------------------------------------------------------------------------
    */

    const backToTop =
        document.getElementById(
            'visitsBackToTop'
        );


    if (backToTop) {

        window.addEventListener(
            'scroll',
            () => {

                backToTop.classList.toggle(
                    'hidden',
                    window.scrollY < 400
                );

            },
            {
                passive: true
            }
        );


        backToTop.addEventListener(
            'click',
            () => {

                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATION ERROR
    |--------------------------------------------------------------------------
    */

    @if($errors->any() && old('_form') === 'schedule')

        const scheduleModal =
            document.getElementById(
                'scheduleModal'
            );


        if (scheduleModal) {

            scheduleModal.showModal();

        }

    @elseif($errors->any() && old('_form') === 'result' && old('_visit_id'))

        openResult(
            @json(old('_visit_id'))
        );

    @endif

});
</script>

@endpush