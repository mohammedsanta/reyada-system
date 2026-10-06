{{-- resources/views/banks/scope-edit.blade.php --}}
@extends('layouts.app')

@section('title', 'تعديل النطاق - ' . $bank->name)

@php
    /*
    |--------------------------------------------------------------------------
    | SAFE / PAGE HELPERS
    |--------------------------------------------------------------------------
    */

    $bankName = $bank->name ?? 'البنك';

    $currentBucket   = $filters['bucket'] ?? '';
    $currentLoan    = $filters['loan_type'] ?? '';
    $currentStatus  = $filters['status'] ?? '';
    $currentEmployee = $filters['employee'] ?? '';

    $matchedCount = $matched->count();
    $totalCount = (int) ($total ?? 0);

    $matchRate = $totalCount > 0
        ? round(($matchedCount / $totalCount) * 100, 1)
        : 0;

    $activeFilterCount = collect([
        $currentBucket !== '',
        $currentLoan !== '',
        $currentStatus !== '',
        $currentEmployee !== '',
    ])->filter()->count();

    /*
    |--------------------------------------------------------------------------
    | PREVIEW STATISTICS
    |--------------------------------------------------------------------------
    */

    $matchedCollection = collect($matched);

    $employeeAssignedCount = $matchedCollection
        ->filter(fn ($case) => filled($case->employee_name ?? null))
        ->count();

    $unassignedCount = $matchedCount - $employeeAssignedCount;

    $statusSummary = $matchedCollection
        ->groupBy(fn ($case) => $case->status ?? 'غير محدد')
        ->map(fn ($items) => $items->count());

    $bucketSummary = $matchedCollection
        ->groupBy(fn ($case) => $case->bucket ?? 'غير محدد')
        ->map(fn ($items) => $items->count());

    $loanSummary = $matchedCollection
        ->groupBy(fn ($case) => $case->loan_type ?? 'غير محدد')
        ->map(fn ($items) => $items->count());

    /*
    |--------------------------------------------------------------------------
    | SAFE FILTER LABELS
    |--------------------------------------------------------------------------
    */

    $filterSummary = [];

    if ($currentBucket !== '') {
        $filterSummary[] = 'الشريحة: ' . $currentBucket;
    }

    if ($currentLoan !== '') {
        $filterSummary[] = 'نوع القرض: ' . $currentLoan;
    }

    if ($currentStatus !== '') {
        $filterSummary[] = 'الحالة: ' . $currentStatus;
    }

    if ($currentEmployee !== '') {

        if ($currentEmployee === '0') {
            $filterSummary[] = 'الموظف: بدون موظف';
        } else {
            $employeeLabel = $employees[$currentEmployee] ?? $currentEmployee;
            $filterSummary[] = 'الموظف: ' . $employeeLabel;
        }
    }
@endphp

@section('content')

    {{-- ============================================================
         HEADER
    ============================================================= --}}

    <x-bank-header
        :bank="$bank"
        title="تعديل النطاق"
        subtitle="تعديل جماعي لحالات النطاق الحالي"
    />


    <x-flash />


    {{-- ============================================================
         VALIDATION ERRORS
    ============================================================= --}}

    @if($errors->any())

        <div class="mb-6 rounded-xl border border-danger/30 bg-danger/[0.05] p-4">

            <div class="flex items-start gap-3">

                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-danger/10 text-danger">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </span>

                <div class="min-w-0">

                    <p class="font-semibold text-fg">
                        تعذر تنفيذ العملية
                    </p>

                    <p class="mt-1 text-xs text-muted">
                        راجع البيانات التالية قبل محاولة التنفيذ مرة أخرى.
                    </p>

                    <ul class="mt-3 space-y-1 text-xs text-muted">

                        @foreach($errors->all() as $error)

                            <li class="flex items-start gap-2">

                                <i class="fa-solid fa-circle mt-1.5 text-[5px] text-danger"></i>

                                <span>{{ $error }}</span>

                            </li>

                        @endforeach

                    </ul>

                </div>

            </div>

        </div>

    @endif


    {{-- ============================================================
         WARNING / OPERATION SAFETY
    ============================================================= --}}

    <div class="mb-6 rounded-xl border border-warning/20 bg-warning/[0.05] px-4 py-3 text-sm text-warning">

        <div class="flex items-start gap-3">

            <i class="fa-solid fa-triangle-exclamation mt-0.5"></i>

            <div class="min-w-0">

                <p class="font-semibold">
                    تعديل جماعي
                </p>

                <p class="mt-1 text-xs leading-5 text-warning/80">
                    التعديل الجماعي يغيّر حقلاً واحداً في كل الحالات المطابقة للفلاتر،
                    ويُسجَّل في سجل النشاط. راجع عدد الحالات والمعاينة قبل التنفيذ.
                </p>

            </div>

        </div>

    </div>


    {{-- ============================================================
         OPERATION OVERVIEW
    ============================================================= --}}

    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        {{-- TOTAL --}}
        <div class="card">

            <div class="flex items-start justify-between gap-4">

                <div>

                    <p class="text-xs text-muted">
                        إجمالي الحالات
                    </p>

                    <p class="mt-2 text-2xl font-bold text-fg">
                        {{ number_format($totalCount) }}
                    </p>

                    <p class="mt-1 text-[10px] text-dim">
                        إجمالي نطاق البنك
                    </p>

                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-info/10 text-info">
                    <i class="fa-solid fa-database"></i>
                </span>

            </div>

        </div>


        {{-- MATCHED --}}
        <div class="card">

            <div class="flex items-start justify-between gap-4">

                <div>

                    <p class="text-xs text-muted">
                        الحالات المطابقة
                    </p>

                    <p
                        id="matched-count"
                        class="mt-2 text-2xl font-bold text-brand"
                    >
                        {{ number_format($matchedCount) }}
                    </p>

                    <p class="mt-1 text-[10px] text-dim">
                        سيتم تطبيق التعديل عليها
                    </p>

                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand/10 text-brand">
                    <i class="fa-solid fa-filter-circle-check"></i>
                </span>

            </div>

        </div>


        {{-- MATCH RATE --}}
        <div class="card">

            <div class="flex items-start justify-between gap-4">

                <div>

                    <p class="text-xs text-muted">
                        نسبة المطابقة
                    </p>

                    <p class="mt-2 text-2xl font-bold text-info">
                        {{ $matchRate }}%
                    </p>

                    <p class="mt-1 text-[10px] text-dim">
                        من إجمالي الحالات
                    </p>

                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-info/10 text-info">
                    <i class="fa-solid fa-chart-pie"></i>
                </span>

            </div>

            <div class="mt-3 progress">
                <div
                    class="h-full rounded-full bg-info"
                    style="width: {{ min(100, max(0, $matchRate)) }}%"
                ></div>
            </div>

        </div>


        {{-- EMPLOYEE --}}
        <div class="card">

            <div class="flex items-start justify-between gap-4">

                <div>

                    <p class="text-xs text-muted">
                        توزيع الموظفين
                    </p>

                    <p class="mt-2 text-2xl font-bold text-fg">
                        {{ number_format($employeeAssignedCount) }}
                    </p>

                    <p class="mt-1 text-[10px] text-dim">
                        معين ·
                        <span class="text-warning">
                            {{ number_format($unassignedCount) }}
                        </span>
                        بدون موظف
                    </p>

                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-warning/10 text-warning">
                    <i class="fa-solid fa-users"></i>
                </span>

            </div>

        </div>

    </section>


    {{-- ============================================================
         STEP 1: CHOOSE CASES
    ============================================================= --}}

    <form
        method="GET"
        action="{{ route('banks.scope.edit', $bank->id) }}"
        class="card filter-bar"
        id="scope-filters"
    >

        <span
            class="badge badge-outline-info self-center"
            title="الخطوة الأولى"
        >
            1
        </span>


        {{-- BUCKET --}}
        <div class="w-36">

            <label
                for="bucket"
                class="form-label"
            >
                الشريحة
            </label>

            <select
                id="bucket"
                name="bucket"
                class="form-input"
                onchange="this.form.submit()"
            >

                <option value="">
                    الكل
                </option>

                @foreach($buckets as $bucket)

                    <option
                        value="{{ $bucket }}"
                        @selected($currentBucket === (string) $bucket)
                    >
                        {{ $bucket }}
                    </option>

                @endforeach

            </select>

        </div>


        {{-- LOAN TYPE --}}
        <div class="w-44">

            <label
                for="loan_type"
                class="form-label"
            >
                نوع القرض
            </label>

            <select
                id="loan_type"
                name="loan_type"
                class="form-input"
                onchange="this.form.submit()"
            >

                <option value="">
                    الكل
                </option>

                @foreach($loanTypes as $type)

                    <option
                        value="{{ $type }}"
                        @selected($currentLoan === $type)
                    >
                        {{ $type }}
                    </option>

                @endforeach

            </select>

        </div>


        {{-- STATUS --}}
        <div class="w-36">

            <label
                for="status"
                class="form-label"
            >
                الحالة
            </label>

            <select
                id="status"
                name="status"
                class="form-input"
                onchange="this.form.submit()"
            >

                <option value="">
                    الكل
                </option>

                @foreach($statuses as $status)

                    <option
                        value="{{ $status }}"
                        @selected($currentStatus === $status)
                    >
                        {{ $status }}
                    </option>

                @endforeach

            </select>

        </div>


        {{-- EMPLOYEE --}}
        <div class="w-48">

            <label
                for="employee"
                class="form-label"
            >
                الموظف
            </label>

            <select
                id="employee"
                name="employee"
                class="form-input"
                onchange="this.form.submit()"
            >

                <option value="">
                    الكل
                </option>

                <option
                    value="0"
                    @selected($currentEmployee === '0')
                >
                    بدون موظف
                </option>

                @foreach($employees as $id => $name)

                    <option
                        value="{{ $id }}"
                        @selected($currentEmployee === (string) $id)
                    >
                        {{ $name }}
                    </option>

                @endforeach

            </select>

        </div>


        {{-- RESET --}}
        <a
            href="{{ route('banks.scope.edit', $bank->id) }}"
            class="btn btn-secondary"
            title="إعادة ضبط"
        >
            <i class="fa-solid fa-rotate-right"></i>
        </a>


        {{-- MATCH COUNT --}}
        <span class="mr-auto self-center text-sm text-muted">

            المطابقة:

            <b class="text-brand">
                {{ number_format($matchedCount) }}
            </b>

            من

            <b class="text-fg">
                {{ number_format($totalCount) }}
            </b>

        </span>

    </form>


    {{-- ============================================================
         ACTIVE FILTERS
    ============================================================= --}}

    @if($activeFilterCount > 0)

        <div class="mt-3 flex flex-wrap items-center gap-2">

            <span class="text-[11px] font-semibold text-muted">
                الفلاتر الحالية:
            </span>

            @foreach($filterSummary as $filter)

                <span class="tag">
                    <i class="fa-solid fa-filter text-[9px]"></i>
                    {{ $filter }}
                </span>

            @endforeach

            <a
                href="{{ route('banks.scope.edit', $bank->id) }}"
                class="text-[11px] font-semibold text-danger transition hover:text-fg"
            >
                مسح الكل
            </a>

        </div>

    @endif


    {{-- ============================================================
         FILTER RESULT INFORMATION
    ============================================================= --}}

    <div class="mt-4 flex flex-wrap items-center justify-between gap-3">

        <div class="flex items-center gap-2 text-xs text-muted">

            <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-info/10 text-info">
                <i class="fa-solid fa-list-check"></i>
            </span>

            <span>
                ستتم معالجة
                <b class="text-fg">
                    {{ number_format($matchedCount) }}
                </b>
                حالة بناءً على الفلاتر الحالية.
            </span>

        </div>


        @if($matchedCount > 0)

            <span class="badge badge-outline-info">
                <i class="fa-solid fa-circle-check"></i>
                جاهز للمعاينة
            </span>

        @else

            <span class="badge badge-outline-warning">
                <i class="fa-solid fa-circle-exclamation"></i>
                لا توجد حالات
            </span>

        @endif

    </div>


    {{-- ============================================================
         PREVIEW SUMMARY
    ============================================================= --}}

    @if($matchedCount > 0)

        <section class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-3">

            {{-- STATUS DISTRIBUTION --}}
            <div class="card">

                <div class="mb-3 flex items-center justify-between">

                    <p class="text-xs font-semibold text-fg">
                        توزيع الحالات
                    </p>

                    <i class="fa-solid fa-chart-simple text-info"></i>

                </div>

                <div class="space-y-2">

                    @forelse($statusSummary as $statusName => $count)

                        @php
                            $statusPercentage = $matchedCount > 0
                                ? round(($count / $matchedCount) * 100, 1)
                                : 0;
                        @endphp

                        <div>

                            <div class="mb-1 flex items-center justify-between text-[10px]">

                                <span class="text-muted">
                                    {{ $statusName }}
                                </span>

                                <span class="font-semibold text-fg">
                                    {{ $count }}
                                </span>

                            </div>

                            <div class="progress">

                                <div
                                    class="h-full rounded-full bg-info"
                                    style="width: {{ $statusPercentage }}%"
                                ></div>

                            </div>

                        </div>

                    @empty

                        <span class="text-xs text-dim">
                            لا توجد بيانات.
                        </span>

                    @endforelse

                </div>

            </div>


            {{-- BUCKET DISTRIBUTION --}}
            <div class="card">

                <div class="mb-3 flex items-center justify-between">

                    <p class="text-xs font-semibold text-fg">
                        توزيع الشرائح
                    </p>

                    <i class="fa-solid fa-layer-group text-brand"></i>

                </div>

                <div class="flex flex-wrap gap-2">

                    @forelse($bucketSummary as $bucketName => $count)

                        <span class="tag">
                            Bucket {{ $bucketName }}:
                            <b class="text-fg">{{ $count }}</b>
                        </span>

                    @empty

                        <span class="text-xs text-dim">
                            لا توجد بيانات.
                        </span>

                    @endforelse

                </div>

            </div>


            {{-- LOAN DISTRIBUTION --}}
            <div class="card">

                <div class="mb-3 flex items-center justify-between">

                    <p class="text-xs font-semibold text-fg">
                        أنواع القروض
                    </p>

                    <i class="fa-solid fa-file-invoice-dollar text-warning"></i>

                </div>

                <div class="space-y-2">

                    @forelse($loanSummary as $loanName => $count)

                        <div class="flex items-center justify-between rounded-lg border border-line bg-white/[0.02] px-3 py-2">

                            <span class="text-xs text-muted">
                                {{ $loanName }}
                            </span>

                            <span class="badge badge-neutral">
                                {{ $count }}
                            </span>

                        </div>

                    @empty

                        <span class="text-xs text-dim">
                            لا توجد بيانات.
                        </span>

                    @endforelse

                </div>

            </div>

        </section>

    @endif


    {{-- ============================================================
         PREVIEW TABLE
    ============================================================= --}}

    <x-panel
        title="معاينة الحالات التي سيتم تعديلها"
        icon="fa-eye"
        class="mb-6 mt-6"
    >

        {{-- TABLE HEADER --}}
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">

            <div>

                <p class="text-xs text-muted">
                    الحالات المطابقة للفلاتر الحالية
                </p>

                <p class="mt-1 text-[11px] text-dim">
                    راجع هذه البيانات قبل الانتقال إلى خطوة التنفيذ.
                </p>

            </div>


            <div class="flex items-center gap-2">

                <span class="badge badge-outline-info">
                    {{ number_format($matchedCount) }} حالة
                </span>

                @if($unassignedCount > 0)

                    <span class="badge badge-outline-warning">
                        {{ number_format($unassignedCount) }} بدون موظف
                    </span>

                @endif

            </div>

        </div>


        <div class="table-wrap">

            <table class="data-table whitespace-nowrap">

                <thead>

                    <tr>

                        <th>#</th>
                        <th>الكود</th>
                        <th>العميل</th>
                        <th>نوع القرض</th>
                        <th>الشريحة</th>
                        <th>الحالة</th>
                        <th>الموظف</th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($matched as $index => $case)

                        @php
                            $caseStatus = $case->status ?? 'غير محدد';
                            $caseEmployee = $case->employee_name ?? null;
                        @endphp

                        <tr>

                            <td class="text-dim">
                                {{ $index + 1 }}
                            </td>


                            <td>

                                <span class="badge badge-info font-mono">
                                    {{ $case->code ?? '-' }}
                                </span>

                            </td>


                            <td class="font-semibold text-fg">

                                <div class="flex items-center gap-2">

                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand/10 text-brand">
                                        <i class="fa-solid fa-user text-[9px]"></i>
                                    </span>

                                    {{ $case->name ?? '-' }}

                                </div>

                            </td>


                            <td>
                                {{ $case->loan_type ?? '-' }}
                            </td>


                            <td>

                                <span class="badge badge-neutral">
                                    {{ $case->bucket ?? '-' }}
                                </span>

                            </td>


                            <td>

                                <span class="badge badge-success">
                                    <i class="fa-solid fa-circle text-[5px]"></i>
                                    {{ $caseStatus }}
                                </span>

                            </td>


                            <td>

                                @if($caseEmployee)

                                    <span class="inline-flex items-center gap-2 text-fg">

                                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-info/10 text-info">
                                            <i class="fa-solid fa-user text-[9px]"></i>
                                        </span>

                                        {{ $caseEmployee }}

                                    </span>

                                @else

                                    <span class="inline-flex items-center gap-1 text-warning">

                                        <i class="fa-solid fa-user-slash text-[10px]"></i>

                                        بدون موظف

                                    </span>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="7"
                                class="py-12"
                            >

                                <x-empty-state text="لا توجد حالات مطابقة للفلاتر" />

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </x-panel>


    {{-- ============================================================
         STEP 2: WHAT TO CHANGE
    ============================================================= --}}

    <form
        method="POST"
        action="{{ route('banks.scope.update', $bank->id) }}"
        class="max-w-3xl space-y-4"
        id="scope-update-form"
    >

        @csrf
        @method('PUT')


        {{-- ========================================================
             PRESERVE FILTERS
        ========================================================= --}}

        @foreach($filters as $name => $value)

            <input
                type="hidden"
                name="{{ $name }}"
                value="{{ $value }}"
            >

        @endforeach


        {{-- ========================================================
             STEP 2 CARD
        ========================================================= --}}

        <x-section-card
            title="2 · ماذا تريد أن تغيّر؟"
            icon="fa-pen-to-square"
            color="brand"
        >

            {{-- FIELD SELECTION --}}
            <div class="grid grid-cols-1 gap-3 md:grid-cols-2">

                <x-select-field
                    name="field"
                    label="الحقل"
                    :options="$fields"
                    placeholder="اختر الحقل..."
                />


                {{-- EMPLOYEE --}}
                <div
                    data-value-for="assigned_user_id"
                    class="hidden"
                >

                    <x-select-field
                        name="value_employee"
                        label="الموظف الجديد"
                        :options="[0 => 'بدون موظف'] + $employees"
                        placeholder="اختر..."
                    />

                </div>


                {{-- STATUS --}}
                <div
                    data-value-for="status"
                    class="hidden"
                >

                    <x-select-field
                        name="value_status"
                        label="الحالة الجديدة"
                        :options="array_combine($statuses, $statuses)"
                        placeholder="اختر..."
                    />

                </div>


                {{-- BUCKET --}}
                <div
                    data-value-for="bucket"
                    class="hidden"
                >

                    <x-form-field
                        name="value_bucket"
                        label="الشريحة الجديدة"
                        type="number"
                        min="0"
                        max="9"
                    />

                </div>


                {{-- LOAN TYPE --}}
                <div
                    data-value-for="loan_type"
                    class="hidden"
                >

                    <x-select-field
                        name="value_loan_type"
                        label="نوع القرض الجديد"
                        :options="array_combine($loanTypes, $loanTypes)"
                        placeholder="اختر..."
                    />

                </div>


                {{-- DUE DATE --}}
                <div
                    data-value-for="next_due_date"
                    class="hidden"
                >

                    <x-form-field
                        name="value_due_date"
                        label="تاريخ الاستحقاق الجديد"
                        type="date"
                    />

                </div>

            </div>


            {{-- CHANGE SUMMARY --}}
            <div
                id="change-preview"
                class="mt-4 hidden rounded-xl border border-brand/20 bg-brand/[0.04] p-4"
            >

                <div class="flex items-start gap-3">

                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand/10 text-brand">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </span>

                    <div class="min-w-0">

                        <p class="text-xs font-semibold text-fg">
                            ملخص التعديل
                        </p>

                        <p
                            id="summary"
                            class="mt-1 text-sm text-muted"
                        ></p>

                    </div>

                </div>

            </div>


            {{-- EMPTY SUMMARY --}}
            <p
                id="empty-summary"
                class="mt-4 text-sm text-muted"
            >
                اختر الحقل الذي تريد تغييره.
            </p>

        </x-section-card>


        {{-- ========================================================
             IMPACT WARNING
        ========================================================= --}}

        <div
            id="impact-warning"
            class="hidden rounded-xl border border-warning/20 bg-warning/[0.05] px-4 py-3"
        >

            <div class="flex items-start gap-3">

                <i class="fa-solid fa-triangle-exclamation mt-0.5 text-warning"></i>

                <div>

                    <p class="text-xs font-semibold text-warning">
                        راجع تأثير العملية
                    </p>

                    <p class="mt-1 text-[11px] leading-5 text-warning/80">
                        سيتم تطبيق القيمة الجديدة على جميع الحالات المطابقة للفلاتر الحالية.
                        تأكد من صحة الحقل والقيمة قبل التنفيذ.
                    </p>

                </div>

            </div>

        </div>


        {{-- ========================================================
             CONFIRMATION
        ========================================================= --}}

        <label
            class="check-row"
            id="confirm-row"
        >

            <input
                id="confirm"
                type="checkbox"
                name="confirm"
                value="1"
                class="accent-brand"
                @checked(old('confirm'))
                @disabled($matched->isEmpty())
            >

            <span>

                أفهم أن التعديل سيُطبَّق على

                <b
                    id="confirm-count"
                    class="text-fg"
                >
                    {{ number_format($matchedCount) }}
                </b>

                حالة ولا يمكن التراجع عنه من هذه الصفحة.

            </span>

        </label>


        @error('confirm')
            <p class="form-error">
                {{ $message }}
            </p>
        @enderror


        {{-- ========================================================
             FINAL ACTION BAR
        ========================================================= --}}

        <div class="rounded-xl border border-line bg-white/[0.02] p-4">

            <div class="flex flex-wrap items-center justify-between gap-4">

                <div class="flex items-center gap-3">

                    <span
                        id="ready-indicator"
                        class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/5 text-dim"
                    >
                        <i class="fa-solid fa-lock"></i>
                    </span>

                    <div>

                        <p
                            id="ready-title"
                            class="text-xs font-semibold text-fg"
                        >
                            العملية غير جاهزة
                        </p>

                        <p
                            id="ready-description"
                            class="mt-1 text-[10px] text-dim"
                        >
                            اختر الحقل والقيمة ثم أكد العملية.
                        </p>

                    </div>

                </div>


                <div class="flex gap-2">

                    <button
                        type="submit"
                        id="submit-update"
                        class="btn btn-primary"
                        @disabled($matched->isEmpty())
                    >
                        <i class="fa-solid fa-play text-xs"></i>
                        تنفيذ التعديل
                    </button>


                    <a
                        href="{{ route('banks.panel', $bank->id) }}"
                        class="btn btn-secondary"
                    >
                        إلغاء
                    </a>

                </div>

            </div>

        </div>

    </form>


    {{-- ============================================================
         FOOTER INFORMATION
    ============================================================= --}}

    <div class="mt-6 flex flex-wrap items-center justify-between gap-3 text-[11px] text-dim">

        <div class="flex items-center gap-2">

            <i class="fa-solid fa-shield-halved text-brand"></i>

            <span>
                تأكد من مراجعة المعاينة قبل تنفيذ التعديل الجماعي.
            </span>

        </div>


        <div class="flex items-center gap-2">

            <span>
                اختصار البحث:
            </span>

            <kbd class="rounded border border-line bg-white/5 px-1.5 py-0.5 font-mono text-[10px]">
                Ctrl
            </kbd>

            <span>+</span>

            <kbd class="rounded border border-line bg-white/5 px-1.5 py-0.5 font-mono text-[10px]">
                K
            </kbd>

        </div>

    </div>


    {{-- ============================================================
         SCROLL TOP
    ============================================================= --}}

    <button
        id="scope-scroll-top"
        type="button"
        class="fixed bottom-6 left-6 z-30 hidden h-10 w-10 items-center justify-center rounded-xl border border-line bg-surface text-muted shadow-xl shadow-black/30 transition hover:text-brand"
        title="العودة للأعلى"
        aria-label="العودة للأعلى"
    >
        <i class="fa-solid fa-arrow-up text-xs"></i>
    </button>

@endsection


@push('scripts')

<script>

    /* ============================================================
       ELEMENTS
    ============================================================ */

    const fieldSelect =
        document.getElementById('field');

    const summary =
        document.getElementById('summary');

    const emptySummary =
        document.getElementById('empty-summary');

    const changePreview =
        document.getElementById('change-preview');

    const impactWarning =
        document.getElementById('impact-warning');

    const confirmInput =
        document.getElementById('confirm');

    const confirmCount =
        document.getElementById('confirm-count');

    const submitButton =
        document.getElementById('submit-update');

    const updateForm =
        document.getElementById('scope-update-form');

    const filterForm =
        document.getElementById('scope-filters');

    const readyIndicator =
        document.getElementById('ready-indicator');

    const readyTitle =
        document.getElementById('ready-title');

    const readyDescription =
        document.getElementById('ready-description');

    const matchedCount =
        {{ $matchedCount }};

    const fieldLabels =
        @json($fields);


    /* ============================================================
       FIELD / VALUE HELPERS
    ============================================================ */

    function getVisibleValue(field) {

        const selectors = {

            assigned_user_id:
                '[name="value_employee"]',

            status:
                '[name="value_status"]',

            bucket:
                '[name="value_bucket"]',

            loan_type:
                '[name="value_loan_type"]',

            next_due_date:
                '[name="value_due_date"]'

        };

        const selector = selectors[field];

        if (!selector) {
            return '';
        }

        const element =
            document.querySelector(selector);

        return element?.value ?? '';

    }


    function getVisibleValueLabel(field) {

        const selectors = {

            assigned_user_id:
                '[name="value_employee"]',

            status:
                '[name="value_status"]',

            bucket:
                '[name="value_bucket"]',

            loan_type:
                '[name="value_loan_type"]',

            next_due_date:
                '[name="value_due_date"]'

        };

        const selector = selectors[field];

        if (!selector) {
            return '';
        }

        const element =
            document.querySelector(selector);

        if (!element) {
            return '';
        }


        if (element.tagName === 'SELECT') {

            const selected =
                element.options[element.selectedIndex];

            return selected?.textContent?.trim() ?? '';

        }


        return element.value ?? '';
    }


    /* ============================================================
       REFRESH FIELD UI
    ============================================================ */

    function refresh() {

        const field =
            fieldSelect?.value ?? '';


        /*
        |--------------------------------------------------------------------------
        | Show only selected value block
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll('[data-value-for]')
            .forEach(block => {

                block.classList.toggle(
                    'hidden',
                    block.dataset.valueFor !== field
                );

            });


        /*
        |--------------------------------------------------------------------------
        | Empty state
        |--------------------------------------------------------------------------
        */

        if (!field) {

            changePreview?.classList.add('hidden');
            impactWarning?.classList.add('hidden');
            emptySummary?.classList.remove('hidden');

            updateReadyState();

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Selected field
        |--------------------------------------------------------------------------
        */

        emptySummary?.classList.add('hidden');
        changePreview?.classList.remove('hidden');
        impactWarning?.classList.remove('hidden');


        const fieldLabel =
            fieldLabels[field] ?? field;


        const valueLabel =
            getVisibleValueLabel(field);


        if (valueLabel) {

            summary.textContent =
                `سيتم تغيير «${fieldLabel}» إلى «${valueLabel}» في ${matchedCount} حالة.`;

        } else {

            summary.textContent =
                `سيتم تغيير «${fieldLabel}» في ${matchedCount} حالة. اختر القيمة الجديدة.`;

        }


        updateReadyState();

    }


    /* ============================================================
       READY STATE
    ============================================================ */

    function updateReadyState() {

        const field =
            fieldSelect?.value ?? '';

        const value =
            field ? getVisibleValue(field) : '';

        const confirmed =
            confirmInput?.checked ?? false;

        const ready =
            matchedCount > 0 &&
            field !== '' &&
            value !== '' &&
            confirmed;


        if (!readyIndicator) {
            return;
        }


        if (ready) {

            readyIndicator.className =
                'flex h-9 w-9 items-center justify-center rounded-lg bg-brand/10 text-brand';

            readyIndicator.innerHTML =
                '<i class="fa-solid fa-circle-check"></i>';

            readyTitle.textContent =
                'العملية جاهزة للتنفيذ';

            readyTitle.className =
                'text-xs font-semibold text-brand';

            readyDescription.textContent =
                'راجع الملخص ثم اضغط تنفيذ التعديل.';

            if (submitButton) {
                submitButton.disabled = false;
            }

        } else {

            readyIndicator.className =
                'flex h-9 w-9 items-center justify-center rounded-lg bg-white/5 text-dim';

            readyIndicator.innerHTML =
                '<i class="fa-solid fa-lock"></i>';

            readyTitle.textContent =
                'العملية غير جاهزة';

            readyTitle.className =
                'text-xs font-semibold text-fg';


            if (matchedCount === 0) {

                readyDescription.textContent =
                    'لا توجد حالات مطابقة للفلاتر الحالية.';

            } else if (!field) {

                readyDescription.textContent =
                    'اختر الحقل الذي تريد تغييره.';

            } else if (!value) {

                readyDescription.textContent =
                    'اختر القيمة الجديدة.';

            } else if (!confirmed) {

                readyDescription.textContent =
                    'أكد أنك تريد تطبيق التعديل على جميع الحالات المطابقة.';

            }


            if (submitButton) {

                submitButton.disabled =
                    matchedCount === 0 ||
                    !field ||
                    !value ||
                    !confirmed;

            }

        }

    }


    /* ============================================================
       EVENTS
    ============================================================ */

    fieldSelect?.addEventListener(
        'change',
        refresh
    );


    document.addEventListener(
        'change',
        event => {

            if (
                event.target.matches(
                    '[name="value_employee"], [name="value_status"], [name="value_bucket"], [name="value_loan_type"], [name="value_due_date"]'
                )
            ) {

                refresh();

            }


            if (
                event.target === confirmInput
            ) {

                updateReadyState();

            }

        }
    );


    /* ============================================================
       CONFIRMATION COUNT
    ============================================================ */

    if (confirmCount) {

        confirmCount.textContent =
            new Intl.NumberFormat('en-US')
                .format(matchedCount);

    }


    /* ============================================================
       FORM SUBMIT SAFETY
    ============================================================ */

    let submitting = false;


    updateForm?.addEventListener(
        'submit',
        event => {

            const field =
                fieldSelect?.value ?? '';

            const value =
                field ? getVisibleValue(field) : '';

            const confirmed =
                confirmInput?.checked ?? false;


            /*
            |--------------------------------------------------------------------------
            | Client-side safety check
            |--------------------------------------------------------------------------
            */

            if (
                matchedCount === 0 ||
                !field ||
                !value ||
                !confirmed
            ) {

                event.preventDefault();

                if (!field) {

                    showScopeToast(
                        'اختر الحقل الذي تريد تغييره.'
                    );

                } else if (!value) {

                    showScopeToast(
                        'اختر القيمة الجديدة.'
                    );

                } else if (!confirmed) {

                    showScopeToast(
                        'يجب تأكيد العملية قبل التنفيذ.'
                    );

                }

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Double-submit protection
            |--------------------------------------------------------------------------
            */

            if (submitting) {

                event.preventDefault();

                return;

            }


            /*
            |--------------------------------------------------------------------------
            | Final confirmation
            |--------------------------------------------------------------------------
            */

            const fieldLabel =
                fieldLabels[field] ?? field;

            const valueLabel =
                getVisibleValueLabel(field);


            const message =
                `سيتم تغيير «${fieldLabel}» إلى «${valueLabel}» في ${matchedCount} حالة.\n\nهل أنت متأكد من تنفيذ التعديل؟`;


            if (!confirm(message)) {

                event.preventDefault();

                return;

            }


            submitting = true;


            if (submitButton) {

                submitButton.disabled = true;

                submitButton.innerHTML =
                    '<i class="fa-solid fa-spinner fa-spin text-xs"></i> جارٍ التنفيذ...';

            }

        }
    );


    /* ============================================================
       FILTER SUBMIT LOADING
    ============================================================ */

    filterForm?.addEventListener(
        'submit',
        () => {

            const selects =
                filterForm.querySelectorAll('select');

            selects.forEach(select => {

                select.disabled = true;

            });

        }
    );


    /* ============================================================
       KEYBOARD SHORTCUTS
    ============================================================ */

    document.addEventListener(
        'keydown',
        event => {

            /*
            |--------------------------------------------------------------------------
            | Ctrl + K
            |--------------------------------------------------------------------------
            */

            if (
                (event.ctrlKey || event.metaKey) &&
                event.key.toLowerCase() === 'k'
            ) {

                event.preventDefault();

                document
                    .getElementById('bucket')
                    ?.focus();

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Ctrl + Enter => execute
            |--------------------------------------------------------------------------
            */

            if (
                (event.ctrlKey || event.metaKey) &&
                event.key === 'Enter'
            ) {

                event.preventDefault();

                if (
                    updateForm &&
                    !submitButton?.disabled
                ) {

                    updateForm.requestSubmit();

                }

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Escape => reset selected field
            |--------------------------------------------------------------------------
            */

            if (event.key === 'Escape') {

                if (
                    document.activeElement &&
                    (
                        document.activeElement.tagName === 'SELECT' ||
                        document.activeElement.tagName === 'INPUT'
                    )
                ) {

                    document.activeElement.blur();

                }

            }

        }
    );


    /* ============================================================
       TOAST
    ============================================================ */

    function showScopeToast(message) {

        let toast =
            document.getElementById(
                'scope-edit-toast'
            );


        if (!toast) {

            toast =
                document.createElement('div');

            toast.id =
                'scope-edit-toast';

            toast.className =
                'fixed bottom-6 right-6 z-[100] rounded-xl border border-line bg-surface px-4 py-3 text-xs font-semibold text-fg shadow-2xl shadow-black/40 transition';

            document.body.appendChild(toast);

        }


        toast.textContent =
            message;

        toast.style.opacity =
            '1';

        toast.style.transform =
            'translateY(0)';


        clearTimeout(
            window.__scopeToastTimer
        );


        window.__scopeToastTimer =
            setTimeout(() => {

                toast.style.opacity =
                    '0';

                toast.style.transform =
                    'translateY(8px)';

            }, 2200);

    }


    /* ============================================================
       SCROLL TOP
    ============================================================ */

    const scrollTop =
        document.getElementById(
            'scope-scroll-top'
        );


    function refreshScrollButton() {

        if (!scrollTop) {
            return;
        }


        if (window.scrollY > 500) {

            scrollTop.classList.remove(
                'hidden'
            );

            scrollTop.classList.add(
                'flex'
            );

        } else {

            scrollTop.classList.add(
                'hidden'
            );

            scrollTop.classList.remove(
                'flex'
            );

        }

    }


    window.addEventListener(
        'scroll',
        refreshScrollButton,
        { passive: true }
    );


    scrollTop?.addEventListener(
        'click',
        () => {

            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });

        }
    );


    /* ============================================================
       INITIALIZE
    ============================================================ */

    refresh();

    refreshScrollButton();

</script>

@endpush