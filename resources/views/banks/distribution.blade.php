{{-- resources/views/banks/distribution.blade.php --}}
@extends('layouts.app')

@section('title', 'توزيع الحالات - ' . $bank->name)

@section('content')

    @php
        /*
        |--------------------------------------------------------------------------
        | Safe page data
        |--------------------------------------------------------------------------
        | Everything here is calculated from the variables already supplied
        | by the controller. No new backend fields or routes are required.
        */

        $stats = is_array($stats ?? null) ? $stats : [];

        $totalCases       = (int) ($stats['total'] ?? 0);
        $assignedCases    = (int) ($stats['assigned'] ?? 0);
        $unassignedCases  = (int) ($stats['unassigned'] ?? 0);
        $employeeCount    = (int) ($stats['employees'] ?? 0);

        $assignmentRate = $totalCases > 0
            ? round(($assignedCases / $totalCases) * 100, 1)
            : 0;

        $distributionRate = $totalCases > 0
            ? round(($unassignedCases / $totalCases) * 100, 1)
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Safe collections / arrays
        |--------------------------------------------------------------------------
        */

        $scopes    = is_array($scopes ?? null) ? $scopes : [];
        $modes     = is_array($modes ?? null) ? $modes : [];
        $buckets   = is_array($buckets ?? null) ? $buckets : [];
        $loanTypes = is_array($loanTypes ?? null) ? $loanTypes : [];

        /*
        |--------------------------------------------------------------------------
        | Select options
        |--------------------------------------------------------------------------
        */

        $bucketOptions = collect($buckets)
            ->mapWithKeys(fn ($value) => [(string) $value => $value])
            ->all();

        $loanTypeOptions = collect($loanTypes)
            ->mapWithKeys(fn ($value) => [(string) $value => $value])
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Employee data
        |--------------------------------------------------------------------------
        */

        $employeeCollection = $employees ?? collect();

        $employeeCollection = $employeeCollection instanceof \Illuminate\Support\Collection
            ? $employeeCollection
            : collect($employeeCollection);

        $allEmployeeIds = $employeeCollection
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->all();

        /*
        |--------------------------------------------------------------------------
        | Employee selection
        |--------------------------------------------------------------------------
        | Fresh page:
        |   -> select all employees.
        |
        | After validation:
        |   -> preserve exactly what the user selected.
        */

        $hasOldEmployeeSelection = old('employee_ids') !== null;

        $oldEmployeeIds = $hasOldEmployeeSelection
            ? old('employee_ids', [])
            : $allEmployeeIds;

        $oldEmployeeIds = is_array($oldEmployeeIds)
            ? array_map('strval', $oldEmployeeIds)
            : [];

        /*
        |--------------------------------------------------------------------------
        | History
        |--------------------------------------------------------------------------
        */

        $history = $history ?? [];

        /*
        |--------------------------------------------------------------------------
        | Validation state
        |--------------------------------------------------------------------------
        */

        $hasErrors = $errors->any();

        $errorMessages = $errors->all();

        /*
        |--------------------------------------------------------------------------
        | Current selections
        |--------------------------------------------------------------------------
        */

        $selectedScope = old('scope', 'unassigned');
        $selectedMode  = old('mode', 'equal');

        /*
        |--------------------------------------------------------------------------
        | UI descriptions
        |--------------------------------------------------------------------------
        */

        $scopeDescriptions = [
            'unassigned' => 'يتم التعامل فقط مع الحالات التي لم يتم توزيعها على موظف.',
            'all'         => 'يمكن إعادة توزيع جميع الحالات المطابقة للفلاتر، بما فيها الحالات الموزعة.',
        ];

        $modeDescriptions = [
            'equal'    => 'تقسيم الحالات بالتساوي تقريباً بين الموظفين المحددين.',
            'capacity' => 'توزيع الحالات بناءً على السعة المتاحة لكل موظف.',
            'manual'   => 'تحديد عدد الحالات المطلوب لكل موظف يدوياً.',
        ];
    @endphp

    <x-bank-header
        :bank="$bank"
        title="توزيع الحالات"
        subtitle="توزيع الحالات على الموظفين"
    />

    <x-flash />

    {{-- ================================================================
         VALIDATION SUMMARY
    ================================================================= --}}
    @if($hasErrors)
        <div class="mb-6 rounded-xl border border-danger/25 bg-danger/[0.05] p-4">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-danger/10 text-danger">
                    <i class="fa-solid fa-circle-exclamation"></i>
                </span>

                <div class="min-w-0">
                    <h2 class="font-bold text-fg">تعذر تنفيذ عملية التوزيع</h2>

                    <p class="mt-1 text-sm text-muted">
                        راجع البيانات التالية ثم حاول مرة أخرى.
                    </p>

                    @if(count($errorMessages))
                        <ul class="mt-3 space-y-1 text-sm text-danger">
                            @foreach($errorMessages as $message)
                                <li class="flex items-start gap-2">
                                    <i class="fa-solid fa-angle-left mt-1 text-[10px]"></i>
                                    <span>{{ $message }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- ================================================================
         OPERATION STATUS
    ================================================================= --}}
    <div class="mb-6 rounded-xl border border-info/20 bg-info/[0.04] px-4 py-3">
        <div class="flex flex-wrap items-center gap-3">

            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-info/10 text-info">
                <i class="fa-solid fa-diagram-project"></i>
            </span>

            <div class="min-w-0">
                <p class="text-sm font-semibold text-fg">
                    مساحة توزيع الحالات
                </p>

                <p class="mt-0.5 text-xs text-muted">
                    حدد نطاق الحالات، طريقة التوزيع، ثم اختر الموظفين قبل تنفيذ العملية.
                </p>
            </div>

            <div class="mr-auto flex flex-wrap items-center gap-2 text-xs">
                <span class="badge badge-neutral">
                    {{ number_format($totalCases) }} إجمالي
                </span>

                <span class="badge badge-outline-info">
                    {{ number_format($unassignedCases) }} غير موزعة
                </span>

                <span class="badge badge-outline-info">
                    {{ number_format($employeeCount) }} موظف
                </span>
            </div>
        </div>
    </div>

    {{-- ================================================================
         KPI CARDS
    ================================================================= --}}
    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <x-metric-card
            label="إجمالي الحالات"
            :value="$totalCases"
            icon="fa-briefcase"
            color="info"
        />

        <x-metric-card
            label="حالات موزعة"
            :value="$assignedCases"
            icon="fa-user-check"
            color="brand"
        />

        <x-metric-card
            label="حالات غير موزعة"
            :value="$unassignedCases"
            icon="fa-user-xmark"
            color="warning"
        />

        <x-metric-card
            label="الموظفون المتاحون"
            :value="$employeeCount"
            icon="fa-users"
            color="cyan"
        />

    </section>

    {{-- ================================================================
         DISTRIBUTION PROGRESS
    ================================================================= --}}
    <section class="mb-6 grid grid-cols-1 gap-4 lg:grid-cols-2">

        <div class="card">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-[11px] text-dim">نسبة الحالات الموزعة</p>
                    <p class="mt-1 text-xl font-bold text-fg">
                        {{ number_format($assignmentRate, 1) }}%
                    </p>
                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand/10 text-brand">
                    <i class="fa-solid fa-chart-pie"></i>
                </span>
            </div>

            <div class="mt-4 progress">
                <div
                    class="h-full rounded-full bg-brand"
                    style="width: {{ min(100, max(0, $assignmentRate)) }}%"
                ></div>
            </div>

            <div class="mt-2 flex justify-between text-[11px] text-muted">
                <span>{{ number_format($assignedCases) }} موزعة</span>
                <span>{{ number_format($totalCases) }} إجمالي</span>
            </div>
        </div>

        <div class="card">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-[11px] text-dim">نسبة الحالات غير الموزعة</p>
                    <p class="mt-1 text-xl font-bold text-fg">
                        {{ number_format($distributionRate, 1) }}%
                    </p>
                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-warning/10 text-warning">
                    <i class="fa-solid fa-user-clock"></i>
                </span>
            </div>

            <div class="mt-4 progress">
                <div
                    class="h-full rounded-full bg-warning"
                    style="width: {{ min(100, max(0, $distributionRate)) }}%"
                ></div>
            </div>

            <div class="mt-2 flex justify-between text-[11px] text-muted">
                <span>{{ number_format($unassignedCases) }} غير موزعة</span>
                <span>{{ number_format($totalCases) }} إجمالي</span>
            </div>
        </div>

    </section>

    {{-- ================================================================
         MAIN DISTRIBUTION FORM
    ================================================================= --}}
    <form
        id="distributionForm"
        method="POST"
        action="{{ route('banks.distribution.store', $bank->id) }}"
        data-total="{{ $totalCases }}"
        data-unassigned="{{ $unassignedCases }}"
        class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-3"
    >
        @csrf

        {{-- ============================================================
             LEFT SIDE SETTINGS
        ============================================================= --}}
        <div class="space-y-4">

            {{-- ========================================================
                 STEP 1 - CASES
            ========================================================= --}}
            <x-section-card
                title="أي الحالات؟"
                icon="fa-filter"
                color="info"
            >
                <div class="mb-4 rounded-lg border border-info/15 bg-info/[0.03] px-3 py-2">
                    <p class="text-[11px] leading-5 text-muted">
                        اختر نطاق الحالات التي تريد توزيعها، ثم استخدم الفلاتر لتضييق النطاق إذا لزم الأمر.
                    </p>
                </div>

                <div class="space-y-2">

                    @forelse($scopes as $key => $scope)

                        <label
                            class="check-row distribution-option"
                            data-option-scope="{{ $key }}"
                        >
                            <input
                                type="radio"
                                name="scope"
                                value="{{ $key }}"
                                class="accent-brand"
                                @checked($selectedScope === $key)
                            >

                            <span class="min-w-0">
                                <b class="text-fg">
                                    {{ $scope['label'] ?? $key }}
                                </b>

                                <span class="block text-[11px] text-dim">
                                    {{ $scope['hint'] ?? ($scopeDescriptions[$key] ?? '') }}
                                </span>
                            </span>

                        </label>

                    @empty

                        <div class="rounded-lg border border-line bg-surface px-3 py-3 text-sm text-muted">
                            لا توجد نطاقات توزيع متاحة حالياً.
                        </div>

                    @endforelse

                </div>

                <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">

                    <x-select-field
                        name="bucket"
                        label="الشريحة"
                        :options="$bucketOptions"
                        placeholder="الكل"
                    />

                    <x-select-field
                        name="loan_type"
                        label="نوع القرض"
                        :options="$loanTypeOptions"
                        placeholder="الكل"
                    />

                </div>

                <div class="mt-4 flex items-start gap-2 rounded-lg border border-line bg-surface px-3 py-2">
                    <i class="fa-solid fa-circle-info mt-0.5 text-xs text-info"></i>

                    <p
                        id="scopeHint"
                        class="text-[11px] leading-5 text-muted"
                    >
                        {{ $scopeDescriptions[$selectedScope] ?? 'سيتم استخدام النطاق المحدد في عملية التوزيع.' }}
                    </p>
                </div>

            </x-section-card>

            {{-- ========================================================
                 STEP 2 - DISTRIBUTION MODE
            ========================================================= --}}
            <x-section-card
                title="طريقة التوزيع"
                icon="fa-sliders"
                color="warning"
            >

                <div class="mb-4 rounded-lg border border-warning/15 bg-warning/[0.03] px-3 py-2">
                    <p class="text-[11px] leading-5 text-muted">
                        طريقة التوزيع تحدد كيف سيتم حساب عدد الحالات لكل موظف.
                    </p>
                </div>

                <div class="space-y-2">

                    @forelse($modes as $key => $mode)

                        <label
                            class="check-row distribution-option"
                            data-option-mode="{{ $key }}"
                        >
                            <input
                                type="radio"
                                name="mode"
                                value="{{ $key }}"
                                class="accent-brand"
                                @checked($selectedMode === $key)
                            >

                            <span class="min-w-0">
                                <b class="text-fg">
                                    {{ $mode['label'] ?? $key }}
                                </b>

                                <span class="block text-[11px] text-dim">
                                    {{ $mode['hint'] ?? ($modeDescriptions[$key] ?? '') }}
                                </span>
                            </span>

                        </label>

                    @empty

                        <div class="rounded-lg border border-line bg-surface px-3 py-3 text-sm text-muted">
                            لا توجد طرق توزيع متاحة حالياً.
                        </div>

                    @endforelse

                </div>

                <div class="mt-4 flex items-start gap-2 rounded-lg border border-line bg-surface px-3 py-2">
                    <i class="fa-solid fa-lightbulb mt-0.5 text-xs text-warning"></i>

                    <p
                        id="modeHint"
                        class="text-[11px] leading-5 text-muted"
                    >
                        {{ $modeDescriptions[$selectedMode] ?? 'اختر طريقة التوزيع المناسبة.' }}
                    </p>
                </div>

            </x-section-card>

            {{-- ========================================================
                 LIVE OPERATION SUMMARY
            ========================================================= --}}
            <div class="card">

                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] text-dim">
                            ملخص التوزيع
                        </p>

                        <p
                            id="preview"
                            class="mt-1 text-sm font-semibold text-fg"
                            aria-live="polite"
                        ></p>
                    </div>

                    <span
                        id="previewIcon"
                        class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-info/10 text-info"
                    >
                        <i class="fa-solid fa-calculator"></i>
                    </span>
                </div>

                {{-- Live calculation details --}}
                <div
                    id="calculationDetails"
                    class="mt-4 hidden grid-cols-2 gap-2 sm:grid-cols-3"
                >
                    <div class="rounded-lg border border-line bg-surface px-3 py-2">
                        <p class="text-[10px] text-dim">الحالات</p>
                        <p id="summaryPool" class="mt-1 font-bold text-fg">0</p>
                    </div>

                    <div class="rounded-lg border border-line bg-surface px-3 py-2">
                        <p class="text-[10px] text-dim">الموظفون</p>
                        <p id="summaryEmployees" class="mt-1 font-bold text-fg">0</p>
                    </div>

                    <div class="rounded-lg border border-line bg-surface px-3 py-2">
                        <p class="text-[10px] text-dim">المتوسط</p>
                        <p id="summaryAverage" class="mt-1 font-bold text-fg">0</p>
                    </div>
                </div>

                {{-- Manual mode details --}}
                <div
                    id="manualDetails"
                    class="mt-3 hidden rounded-lg border border-warning/20 bg-warning/[0.04] px-3 py-2"
                >
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-hand-pointer text-xs text-warning"></i>

                        <span class="text-[11px] text-muted">
                            في الوضع اليدوي يجب تحديد عدد الحالات لكل موظف من جدول الموظفين.
                        </span>
                    </div>
                </div>

                @error('counts')
                    <p class="form-error">{{ $message }}</p>
                @enderror

                @error('employee_ids')
                    <p class="form-error">{{ $message }}</p>
                @enderror

                <div
                    id="clientValidation"
                    class="mt-3 hidden rounded-lg border border-danger/20 bg-danger/[0.04] px-3 py-2 text-xs text-danger"
                    aria-live="polite"
                ></div>

                <div class="mt-4 flex flex-wrap gap-2">

                    <button
                        id="distributionSubmit"
                        type="submit"
                        class="btn btn-primary"
                        data-submit-button
                    >
                        <i class="fa-solid fa-play text-xs"></i>
                        <span data-submit-label>تنفيذ التوزيع</span>
                    </button>

                    <a
                        href="{{ route('banks.panel', $bank->id) }}"
                        class="btn btn-secondary"
                    >
                        إلغاء
                    </a>

                </div>

                <p class="mt-3 text-[10px] leading-5 text-dim">
                    سيتم تنفيذ العملية على الخادم بعد التحقق من البيانات والصلاحيات.
                </p>

            </div>

        </div>

        {{-- ============================================================
             RIGHT SIDE - EMPLOYEES
        ============================================================= --}}
        <div class="xl:col-span-2">

            <x-panel title="الموظفون" icon="fa-users">

                {{-- Employee toolbar --}}
                <div class="mb-4 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

                    <div class="flex flex-wrap items-center gap-2">

                        <span
                            id="selectedEmployeeBadge"
                            class="badge badge-outline-info"
                        >
                            0 موظف محدد
                        </span>

                        <span
                            id="employeeLoadBadge"
                            class="badge badge-neutral"
                        >
                            0 حالة
                        </span>

                    </div>

                    <div class="flex flex-wrap items-center gap-2">

                        <div class="relative w-full sm:w-64">
                            <i class="fa-solid fa-magnifying-glass absolute right-3 top-1/2 -translate-y-1/2 text-xs text-dim"></i>

                            <input
                                id="employeeSearch"
                                type="search"
                                class="form-input pr-9"
                                placeholder="بحث عن موظف..."
                                autocomplete="off"
                                aria-label="البحث عن موظف"
                            >
                        </div>

                        <button
                            type="button"
                            id="selectVisibleEmployees"
                            class="btn btn-secondary btn-sm"
                        >
                            <i class="fa-solid fa-check-double text-xs"></i>
                            تحديد الظاهر
                        </button>

                        <button
                            type="button"
                            id="clearEmployees"
                            class="btn btn-secondary btn-sm"
                        >
                            <i class="fa-solid fa-xmark text-xs"></i>
                            إلغاء التحديد
                        </button>

                    </div>
                </div>

                {{-- Employee information bar --}}
                <div class="mb-4 rounded-xl border border-line bg-surface px-4 py-3">

                    <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-[11px]">

                        <div class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-brand"></span>
                            <span class="text-muted">
                                موظفون محددون:
                            </span>
                            <b id="selectedCountText" class="text-fg">0</b>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-info"></span>
                            <span class="text-muted">
                                إجمالي السعة:
                            </span>
                            <b id="capacityText" class="text-fg">0</b>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-warning"></span>
                            <span class="text-muted">
                                الحمل الحالي:
                            </span>
                            <b id="currentLoadText" class="text-fg">0</b>
                        </div>

                        <div class="mr-auto text-dim">
                            <span id="visibleEmployeeText">
                                {{ $employeeCollection->count() }} موظف
                            </span>
                        </div>

                    </div>
                </div>

                <div class="table-wrap">
                    <table class="data-table whitespace-nowrap">

                        <thead>
                            <tr>

                                <th class="w-10">
                                    <input
                                        id="checkAllEmployees"
                                        type="checkbox"
                                        class="accent-brand"
                                        aria-label="تحديد الكل"
                                        @checked(count($oldEmployeeIds) === count($allEmployeeIds) && count($allEmployeeIds) > 0)
                                    >
                                </th>

                                <th>الموظف</th>

                                <th>الرتبة</th>

                                <th class="w-56">
                                    الحمل الحالي
                                </th>

                                <th class="w-32">
                                    عدد الحالات
                                </th>

                            </tr>
                        </thead>

                        <tbody id="employeeTableBody">

                            @forelse($employeeCollection as $employee)

                                @php
                                    $current = (int) ($employee->current ?? 0);
                                    $capacity = (int) ($employee->capacity ?? 0);

                                    $load = $capacity > 0
                                        ? (int) round(($current / $capacity) * 100)
                                        : 0;

                                    $load = min(100, max(0, $load));

                                    $isOverloaded = $capacity > 0 && $current >= $capacity;
                                    $isHighLoad   = $capacity > 0 && $load >= 80;

                                    $employeeId = (string) $employee->id;
                                @endphp

                                <tr
                                    class="emp-row"
                                    data-employee-id="{{ $employeeId }}"
                                    data-employee-name="{{ strtolower($employee->name ?? '') }}"
                                    data-employee-code="{{ strtolower($employee->code ?? '') }}"
                                    data-current="{{ $current }}"
                                    data-capacity="{{ $capacity }}"
                                >

                                    {{-- Checkbox --}}
                                    <td>

                                        <input
                                            type="checkbox"
                                            name="employee_ids[]"
                                            value="{{ $employee->id }}"
                                            class="emp-check accent-brand"
                                            @checked(in_array($employeeId, $oldEmployeeIds, true))
                                        >

                                    </td>

                                    {{-- Employee --}}
                                    <td>

                                        <div class="flex items-center gap-3">

                                            <x-avatar :name="$employee->name" />

                                            <div class="min-w-0">

                                                <div class="font-semibold text-fg">
                                                    {{ $employee->name }}
                                                </div>

                                                <div class="text-[11px] text-dim">
                                                    {{ $employee->code }}
                                                </div>

                                            </div>

                                        </div>

                                    </td>

                                    {{-- Role --}}
                                    <td>
                                        <span class="badge badge-outline-info">
                                            {{ $employee->role }}
                                        </span>
                                    </td>

                                    {{-- Current load --}}
                                    <td>

                                        <div class="mb-1 flex justify-between text-[11px] text-muted">

                                            <span>
                                                {{ number_format($current) }}
                                                /
                                                {{ number_format($capacity) }}
                                            </span>

                                            <span
                                                class="{{ $isOverloaded ? 'text-danger' : ($isHighLoad ? 'text-warning' : 'text-muted') }}"
                                            >
                                                {{ $capacity > 0 ? $load . '%' : 'بدون سعة' }}
                                            </span>

                                        </div>

                                        <div class="progress">

                                            <div
                                                class="h-full rounded-full {{ $isOverloaded ? 'bg-danger' : ($isHighLoad ? 'bg-warning' : 'bg-brand') }}"
                                                style="width: {{ $capacity > 0 ? $load : 0 }}%"
                                            ></div>

                                        </div>

                                        @if($isOverloaded)

                                            <div class="mt-1 text-[10px] text-danger">
                                                <i class="fa-solid fa-triangle-exclamation"></i>
                                                السعة ممتلئة
                                            </div>

                                        @elseif($capacity <= 0)

                                            <div class="mt-1 text-[10px] text-warning">
                                                <i class="fa-solid fa-circle-exclamation"></i>
                                                لا توجد سعة مسجلة
                                            </div>

                                        @elseif($isHighLoad)

                                            <div class="mt-1 text-[10px] text-warning">
                                                <i class="fa-solid fa-gauge-high"></i>
                                                حمل مرتفع
                                            </div>

                                        @endif

                                    </td>

                                    {{-- Manual count --}}
                                    <td>

                                        <input
                                            type="number"
                                            min="0"
                                            name="counts[{{ $employee->id }}]"
                                            value="{{ old('counts.' . $employee->id) }}"
                                            class="emp-count form-input py-1.5 disabled:opacity-40"
                                            placeholder="0"
                                            inputmode="numeric"
                                            aria-label="عدد الحالات للموظف {{ $employee->name }}"
                                            disabled
                                        >

                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="5">
                                        <x-empty-state text="لا يوجد موظفون متاحون للتوزيع حالياً" />
                                    </td>
                                </tr>

                            @endforelse

                        </tbody>

                    </table>
                </div>

                {{-- No search results --}}
                <div
                    id="noEmployeeSearchResults"
                    class="mt-4 hidden rounded-xl border border-line bg-surface px-4 py-6 text-center"
                >
                    <i class="fa-solid fa-magnifying-glass mb-2 text-xl text-dim"></i>

                    <p class="text-sm font-semibold text-fg">
                        لا توجد نتائج
                    </p>

                    <p class="mt-1 text-xs text-muted">
                        لم يتم العثور على موظف يطابق عبارة البحث.
                    </p>
                </div>

            </x-panel>

        </div>

    </form>

    {{-- ================================================================
         HISTORY
    ================================================================= --}}
    <x-panel title="آخر عمليات التوزيع" icon="fa-clock-rotate-left">

        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">

            <div>
                <p class="text-xs text-muted">
                    سجل مختصر لآخر عمليات توزيع تم تنفيذها على هذا البنك.
                </p>
            </div>

            @if(is_countable($history))
                <span class="badge badge-neutral">
                    {{ count($history) }} عملية
                </span>
            @endif

        </div>

        <div class="table-wrap">

            <table class="data-table whitespace-nowrap">

                <thead>
                    <tr>
                        <th>الوقت</th>
                        <th>المنفذ</th>
                        <th>عدد الحالات</th>
                        <th>عدد الموظفين</th>
                        <th>الطريقة</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($history as $record)

                        @php
                            /*
                            |--------------------------------------------------
                            | Preserve the existing history tuple structure:
                            | [$when, $by, $cases, $people, $mode]
                            |--------------------------------------------------
                            */
                            $when   = $record[0] ?? '—';
                            $by     = $record[1] ?? '—';
                            $cases  = $record[2] ?? 0;
                            $people = $record[3] ?? 0;
                            $mode   = $record[4] ?? '—';
                        @endphp

                        <tr>

                            <td>
                                <span class="text-muted">
                                    {{ $when }}
                                </span>
                            </td>

                            <td class="text-fg">
                                <div class="flex items-center gap-2">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-info/10 text-info">
                                        <i class="fa-solid fa-user text-xs"></i>
                                    </span>

                                    <span>
                                        {{ $by }}
                                    </span>
                                </div>
                            </td>

                            <td>
                                <span class="badge badge-neutral">
                                    {{ number_format((int) $cases) }}
                                </span>
                            </td>

                            <td>
                                {{ number_format((int) $people) }}
                            </td>

                            <td>
                                <span class="badge badge-neutral">
                                    {{ $mode }}
                                </span>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5">
                                <x-empty-state text="لا توجد عمليات توزيع سابقة" />
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        <div class="mt-4 flex items-start gap-2 rounded-lg border border-line bg-surface px-3 py-2">
            <i class="fa-solid fa-clock-rotate-left mt-0.5 text-xs text-dim"></i>

            <p class="text-[10px] leading-5 text-dim">
                يعرض هذا القسم السجل المتاح من الخادم. تفاصيل التدقيق الكاملة تعتمد على سجل النشاط في النظام.
            </p>
        </div>

    </x-panel>

@endsection


@push('scripts')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('distributionForm');

    if (!form) {
        return;
    }

    /* =========================================================
       ELEMENTS
    ========================================================= */

    const preview = document.getElementById('preview');
    const checkAll = document.getElementById('checkAllEmployees');
    const searchInput = document.getElementById('employeeSearch');
    const clearButton = document.getElementById('clearEmployees');
    const selectVisibleButton = document.getElementById('selectVisibleEmployees');
    const selectedCountElement = document.getElementById('selectedEmployeeCount');
    const capacityElement = document.getElementById('selectedCapacity');
    const loadElement = document.getElementById('selectedLoad');
    const formSubmitButton = form.querySelector('button[type="submit"]');

    const rows = Array.from(form.querySelectorAll('.emp-row'));

    const pools = {
        unassigned: Number(form.dataset.unassigned) || 0,
        all: Number(form.dataset.total) || 0,
    };

    let refreshFrame = null;
    let submitting = false;


    /* =========================================================
       HELPERS
    ========================================================= */

    function getCheckbox(row) {
        return row.querySelector('.emp-check');
    }

    function getCountInput(row) {
        return row.querySelector('.emp-count');
    }

    function getVisibleRows() {
        return rows.filter(row => !row.classList.contains('hidden'));
    }

    function getSelectedRows() {
        return rows.filter(row => {
            const checkbox = getCheckbox(row);
            return checkbox && checkbox.checked;
        });
    }

    function clamp(value, min, max) {
        return Math.min(Math.max(value, min), max);
    }

    function formatNumber(value) {
        return new Intl.NumberFormat('en-US').format(
            Number.isFinite(value) ? value : 0
        );
    }


    /* =========================================================
       MASTER CHECKBOX
    ========================================================= */

    function updateMasterCheckbox() {

        if (!checkAll) {
            return;
        }

        const checkboxes = rows
            .map(getCheckbox)
            .filter(Boolean);

        if (!checkboxes.length) {
            checkAll.checked = false;
            checkAll.indeterminate = false;
            return;
        }

        const checked = checkboxes.filter(
            checkbox => checkbox.checked
        ).length;

        if (checked === 0) {

            checkAll.checked = false;
            checkAll.indeterminate = false;

        } else if (checked === checkboxes.length) {

            checkAll.checked = true;
            checkAll.indeterminate = false;

        } else {

            checkAll.checked = false;
            checkAll.indeterminate = true;
        }
    }


    /* =========================================================
       EMPLOYEE SUMMARY
    ========================================================= */

    function updateEmployeeSummary(selectedRows) {

        if (selectedCountElement) {
            selectedCountElement.textContent = selectedRows.length;
        }

        let totalCapacity = 0;
        let totalCurrent = 0;

        selectedRows.forEach(row => {

            const current = Number(row.dataset.current) || 0;
            const capacity = Number(row.dataset.capacity) || 0;

            totalCurrent += current;
            totalCapacity += capacity;
        });

        if (capacityElement) {
            capacityElement.textContent = formatNumber(totalCapacity);
        }

        if (loadElement) {

            if (totalCapacity > 0) {

                const percentage = Math.round(
                    (totalCurrent / totalCapacity) * 100
                );

                loadElement.textContent =
                    `${formatNumber(totalCurrent)} / ${formatNumber(totalCapacity)} (${percentage}%)`;

            } else {

                loadElement.textContent =
                    `${formatNumber(totalCurrent)} / 0`;
            }
        }
    }


    /* =========================================================
       DISTRIBUTION PREVIEW
    ========================================================= */

    function updatePreview(selectedRows) {

        if (!preview) {
            return;
        }

        const scopeElement =
            form.querySelector('[name="scope"]:checked');

        const modeElement =
            form.querySelector('[name="mode"]:checked');

        if (!scopeElement || !modeElement) {
            return;
        }

        const scope = scopeElement.value;
        const mode = modeElement.value;

        const pool =
            Number.isFinite(pools[scope])
                ? Math.max(0, pools[scope])
                : 0;

        let text = '';
        let warning = false;

        /* -----------------------------------------------------
           No employees
        ----------------------------------------------------- */

        if (!selectedRows.length) {

            text = 'اختر موظفاً واحداً على الأقل';
            warning = true;

        }

        /* -----------------------------------------------------
           Equal distribution
        ----------------------------------------------------- */

        else if (mode === 'equal') {

            const base =
                Math.floor(pool / selectedRows.length);

            const remainder =
                pool % selectedRows.length;

            text =
                `توزيع ${formatNumber(pool)} حالة على ` +
                `${selectedRows.length} موظفين ` +
                `(≈ ${formatNumber(base)} لكل موظف`;

            if (remainder > 0) {
                text += ` + ${remainder} حالات متبقية`;
            }

            text += ')';
        }

        /* -----------------------------------------------------
           Capacity distribution
        ----------------------------------------------------- */

        else if (mode === 'capacity') {

            let availableCapacity = 0;

            selectedRows.forEach(row => {

                const current =
                    Number(row.dataset.current) || 0;

                const capacity =
                    Number(row.dataset.capacity) || 0;

                availableCapacity +=
                    Math.max(0, capacity - current);
            });

            if (availableCapacity <= 0 && pool > 0) {

                text =
                    `لا توجد سعة متاحة لتوزيع ${formatNumber(pool)} حالة`;

                warning = true;

            } else {

                const distributable =
                    Math.min(pool, availableCapacity);

                text =
                    `توزيع ${formatNumber(pool)} حالة حسب السعة المتاحة ` +
                    `(السعة المتاحة: ${formatNumber(availableCapacity)})`;

                if (distributable < pool) {

                    text +=
                        ` — المتاح فعلياً ${formatNumber(distributable)}`;

                    warning = true;
                }
            }
        }

        /* -----------------------------------------------------
           Manual distribution
        ----------------------------------------------------- */

        else if (mode === 'manual') {

            let sum = 0;

            selectedRows.forEach(row => {

                const input = getCountInput(row);

                if (!input) {
                    return;
                }

                const value = Number(input.value);

                if (Number.isFinite(value) && value > 0) {
                    sum += value;
                }
            });

            if (sum > pool) {

                text =
                    `المجموع اليدوي ${formatNumber(sum)} ` +
                    `من ${formatNumber(pool)} حالة — ` +
                    `المجموع أكبر من المتاح`;

                warning = true;

            } else {

                const remaining = pool - sum;

                text =
                    `المجموع اليدوي ${formatNumber(sum)} ` +
                    `من ${formatNumber(pool)} حالة`;

                if (remaining > 0) {

                    text +=
                        ` — المتبقي ${formatNumber(remaining)}`;

                } else {

                    text += ' — التوزيع مكتمل';
                }
            }
        }

        preview.textContent = text;

        preview.classList.toggle(
            'text-danger',
            warning
        );

        preview.classList.toggle(
            'text-fg',
            !warning
        );
    }


    /* =========================================================
       ENABLE / DISABLE MANUAL COUNTS
    ========================================================= */

    function updateCountInputs(selectedRows) {

        const modeElement =
            form.querySelector('[name="mode"]:checked');

        const mode =
            modeElement ? modeElement.value : 'equal';

        const selectedIds = new Set(
            selectedRows.map(row => row.dataset.employeeId)
        );

        rows.forEach(row => {

            const input = getCountInput(row);
            const checkbox = getCheckbox(row);

            if (!input || !checkbox) {
                return;
            }

            const enabled =
                mode === 'manual' &&
                selectedIds.has(row.dataset.employeeId);

            input.disabled = !enabled;

            input.classList.toggle(
                'opacity-50',
                !enabled
            );
        });
    }


    /* =========================================================
       ROW LOAD STATE
    ========================================================= */

    function updateRowStates() {

        rows.forEach(row => {

            const current =
                Number(row.dataset.current) || 0;

            const capacity =
                Number(row.dataset.capacity) || 0;

            const load =
                capacity > 0
                    ? Math.round((current / capacity) * 100)
                    : 0;

            const checkbox = getCheckbox(row);

            row.classList.toggle(
                'opacity-60',
                checkbox ? !checkbox.checked : false
            );

            row.dataset.load = String(load);
        });
    }


    /* =========================================================
       MAIN REFRESH
       ---------------------------------------------------------
       requestAnimationFrame prevents multiple refreshes from
       happening during the same browser frame.
    ========================================================= */

    function refresh() {

        if (refreshFrame !== null) {
            cancelAnimationFrame(refreshFrame);
        }

        refreshFrame = requestAnimationFrame(function () {

            refreshFrame = null;

            const selectedRows = getSelectedRows();

            updateMasterCheckbox();
            updateEmployeeSummary(selectedRows);
            updateCountInputs(selectedRows);
            updatePreview(selectedRows);
            updateRowStates();
        });
    }


    /* =========================================================
       MASTER CHECKBOX EVENT
    ========================================================= */

    if (checkAll) {

        checkAll.addEventListener('change', function () {

            const shouldCheck = checkAll.checked;

            rows.forEach(row => {

                const checkbox = getCheckbox(row);

                if (checkbox) {
                    checkbox.checked = shouldCheck;
                }
            });

            checkAll.indeterminate = false;

            refresh();
        });
    }


    /* =========================================================
       EMPLOYEE CHECKBOX EVENTS
    ========================================================= */

    rows.forEach(row => {

        const checkbox = getCheckbox(row);

        if (!checkbox) {
            return;
        }

        checkbox.addEventListener('change', function () {
            refresh();
        });
    });


    /* =========================================================
       SEARCH
    ========================================================= */

    if (searchInput) {

        let searchTimer = null;

        searchInput.addEventListener('input', function () {

            clearTimeout(searchTimer);

            const query =
                searchInput.value
                    .trim()
                    .toLowerCase();

            searchTimer = setTimeout(function () {

                rows.forEach(row => {

                    const name =
                        String(
                            row.dataset.employeeName || ''
                        ).toLowerCase();

                    const code =
                        String(
                            row.dataset.employeeCode || ''
                        ).toLowerCase();

                    const matches =
                        !query ||
                        name.includes(query) ||
                        code.includes(query);

                    row.classList.toggle(
                        'hidden',
                        !matches
                    );
                });

                refresh();

            }, 80);
        });
    }


    /* =========================================================
       SELECT VISIBLE
    ========================================================= */

    if (selectVisibleButton) {

        selectVisibleButton.addEventListener(
            'click',
            function () {

                getVisibleRows().forEach(row => {

                    const checkbox = getCheckbox(row);

                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });

                refresh();
            }
        );
    }


    /* =========================================================
       CLEAR ALL
    ========================================================= */

    if (clearButton) {

        clearButton.addEventListener(
            'click',
            function () {

                rows.forEach(row => {

                    const checkbox = getCheckbox(row);

                    if (checkbox) {
                        checkbox.checked = false;
                    }
                });

                if (checkAll) {
                    checkAll.checked = false;
                    checkAll.indeterminate = false;
                }

                refresh();
            }
        );
    }


    /* =========================================================
       FORM INPUT EVENTS
    ========================================================= */

    form.addEventListener('input', function (event) {

        if (
            event.target.matches('.emp-count') ||
            event.target.matches('[name="bucket"]') ||
            event.target.matches('[name="loan_type"]')
        ) {
            refresh();
        }
    });


    form.addEventListener('change', function (event) {

        if (
            event.target.matches(
                '[name="scope"], [name="mode"], [name="bucket"], [name="loan_type"]'
            )
        ) {
            refresh();
        }
    });


    /* =========================================================
       FORM SUBMIT VALIDATION
    ========================================================= */

    form.addEventListener('submit', function (event) {

        if (submitting) {
            event.preventDefault();
            return;
        }

        const selectedRows = getSelectedRows();

        if (!selectedRows.length) {

            event.preventDefault();

            if (preview) {

                preview.textContent =
                    'اختر موظفاً واحداً على الأقل قبل تنفيذ التوزيع';

                preview.classList.add('text-danger');
                preview.classList.remove('text-fg');
            }

            return;
        }

        const modeElement =
            form.querySelector('[name="mode"]:checked');

        const scopeElement =
            form.querySelector('[name="scope"]:checked');

        const mode =
            modeElement ? modeElement.value : null;

        const scope =
            scopeElement ? scopeElement.value : null;

        const pool =
            scope && Number.isFinite(pools[scope])
                ? Math.max(0, pools[scope])
                : 0;

        /* -----------------------------------------------------
           Validate manual distribution
        ----------------------------------------------------- */

        if (mode === 'manual') {

            let sum = 0;

            selectedRows.forEach(row => {

                const input = getCountInput(row);

                if (!input) {
                    return;
                }

                const value = Number(input.value);

                if (Number.isFinite(value) && value > 0) {
                    sum += value;
                }
            });

            if (sum > pool) {

                event.preventDefault();

                if (preview) {

                    preview.textContent =
                        `المجموع اليدوي ${formatNumber(sum)} ` +
                        `أكبر من المتاح ${formatNumber(pool)}`;

                    preview.classList.add('text-danger');
                    preview.classList.remove('text-fg');
                }

                return;
            }
        }


        /* -----------------------------------------------------
           Final confirmation
        ----------------------------------------------------- */

        const confirmed = window.confirm(
            'هل أنت متأكد من تنفيذ عملية توزيع الحالات؟'
        );

        if (!confirmed) {

            event.preventDefault();
            return;
        }


        /* -----------------------------------------------------
           Prevent double submission
        ----------------------------------------------------- */

        submitting = true;

        if (formSubmitButton) {

            formSubmitButton.disabled = true;

            formSubmitButton.classList.add(
                'opacity-60',
                'cursor-not-allowed'
            );

            formSubmitButton.innerHTML =
                '<i class="fa-solid fa-spinner fa-spin text-xs"></i> جاري التنفيذ...';
        }
    });


    /* =========================================================
       KEYBOARD SHORTCUTS
    ========================================================= */

    document.addEventListener('keydown', function (event) {

        /* Ctrl + K → employee search */

        if (
            event.ctrlKey &&
            event.key.toLowerCase() === 'k'
        ) {

            event.preventDefault();

            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }


        /* Escape → clear search */

        if (event.key === 'Escape') {

            if (
                searchInput &&
                document.activeElement === searchInput
            ) {

                searchInput.value = '';

                rows.forEach(row => {
                    row.classList.remove('hidden');
                });

                refresh();
            }
        }
    });


    /* =========================================================
       INITIAL STATE
    ========================================================= */

    updateRowStates();
    updateMasterCheckbox();
    refresh();

});
</script>
@endpush