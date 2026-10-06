{{-- resources/views/banks/import.blade.php --}}
@extends('layouts.app')

@section('title', 'استيراد النطاق - ' . $bank->name)

@php
    /*
    |--------------------------------------------------------------------------
    | SAFE IMPORT DATA
    |--------------------------------------------------------------------------
    | Everything here is derived from the variables already provided by
    | the controller. No new database fields or routes are required.
    */

    $historyCollection = collect($history ?? []);

    $requiredColumns = collect($required ?? []);
    $optionalColumns = collect($optional ?? []);

    $totalImports = $historyCollection->count();

    $totalRows = $historyCollection->sum(
        fn ($row) => (int) ($row->total ?? 0)
    );

    $totalSuccess = $historyCollection->sum(
        fn ($row) => (int) ($row->success ?? 0)
    );

    $totalFailed = $historyCollection->sum(
        fn ($row) => (int) ($row->failed ?? 0)
    );

    $successRate = $totalRows > 0
        ? round(($totalSuccess / $totalRows) * 100)
        : 0;

    $failureRate = $totalRows > 0
        ? round(($totalFailed / $totalRows) * 100)
        : 0;

    $latestImport = $historyCollection->first();

    $successfulImports = $historyCollection->filter(
        fn ($row) => (int) ($row->failed ?? 0) === 0
    )->count();

    $failedImports = $historyCollection->filter(
        fn ($row) => (int) ($row->failed ?? 0) > 0
    )->count();

    $defaultMode = old('mode', 'upsert');

    $defaultMonth = $defaults['month'] ?? null;
    $defaultYear = $defaults['year'] ?? null;

    /*
    |--------------------------------------------------------------------------
    | FILE LIMITS
    |--------------------------------------------------------------------------
    */

    $maxFileSizeMb = 10;
    $maxFileSizeBytes = $maxFileSizeMb * 1024 * 1024;

    /*
    |--------------------------------------------------------------------------
    | SUPPORTED EXTENSIONS
    |--------------------------------------------------------------------------
    */

    $supportedExtensions = [
        'xlsx',
        'xls',
        'csv',
    ];
@endphp

@section('content')

    {{-- ================================================================
         PAGE HEADER
         ================================================================ --}}
    <x-bank-header
        :bank="$bank"
        title="استيراد النطاق"
        subtitle="رفع ملف Excel وتحديث الحالات"
    >
        <x-slot:actions>

            {{-- Template --}}
            <a
                href="#"
                class="btn btn-outline-success btn-sm"
                title="تحميل قالب الاستيراد"
                onclick="event.preventDefault(); showTemplateNotice();"
            >
                <i class="fa-solid fa-file-excel"></i>
                تحميل القالب
            </a>

            {{-- Back --}}
            <a
                href="{{ route('banks.panel', $bank->id) }}"
                class="btn btn-secondary btn-sm"
            >
                <i class="fa-solid fa-arrow-right text-xs"></i>
                رجوع
            </a>

        </x-slot:actions>
    </x-bank-header>


    <x-flash />


    {{-- ================================================================
         VALIDATION ERRORS
         ================================================================ --}}
    @if($errors->any())

        <div class="card mb-6 border-danger/20 bg-danger/5">

            <div class="flex items-start gap-3">

                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-danger/10 text-danger">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </span>

                <div class="min-w-0">

                    <h3 class="text-sm font-bold text-fg">
                        تعذر تجهيز عملية الاستيراد
                    </h3>

                    <p class="mt-1 text-[11px] text-muted">
                        يرجى مراجعة الأخطاء التالية قبل المحاولة مرة أخرى.
                    </p>

                    <ul class="mt-3 space-y-1.5 text-xs text-danger">

                        @foreach($errors->all() as $error)

                            <li class="flex items-start gap-2">
                                <i class="fa-solid fa-circle text-[5px] mt-1.5"></i>
                                <span>{{ $error }}</span>
                            </li>

                        @endforeach

                    </ul>

                </div>

            </div>

        </div>

    @endif


    {{-- ================================================================
         IMPORT OVERVIEW
         ================================================================ --}}
    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        {{-- Required columns --}}
        <x-metric-card
            label="أعمدة مطلوبة"
            :value="$requiredColumns->count()"
            unit="عمود"
            icon="fa-table-columns"
            color="warning"
        />

        {{-- Optional columns --}}
        <x-metric-card
            label="أعمدة اختيارية"
            :value="$optionalColumns->count()"
            unit="عمود"
            icon="fa-table-list"
            color="info"
        />

        {{-- Previous imports --}}
        <x-metric-card
            label="عمليات سابقة"
            :value="$totalImports"
            unit="عملية"
            icon="fa-clock-rotate-left"
            color="cyan"
        />

        {{-- Overall success --}}
        <x-metric-card
            label="معدل نجاح البيانات"
            :value="$successRate . '%'"
            unit="من السجل"
            icon="fa-circle-check"
            color="brand"
        />

    </section>


    {{-- ================================================================
         MAIN IMPORT AREA
         ================================================================ --}}
    <section class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-3">

        {{-- ============================================================
             UPLOAD FORM
             ============================================================ --}}
        <form
            id="importForm"
            method="POST"
            action="{{ route('banks.scope.import.store', $bank->id) }}"
            enctype="multipart/form-data"
            class="space-y-4 xl:col-span-2"
        >

            @csrf


            {{-- ========================================================
                 IMPORT DATA
                 ======================================================== --}}
            <x-section-card
                title="بيانات الاستيراد"
                icon="fa-file-arrow-up"
                color="cyan"
            >

                {{-- Period --}}
                <div class="grid grid-cols-2 gap-3">

                    <x-select-field
                        name="month"
                        label="الشهر"
                        :options="$months"
                        :value="$defaultMonth"
                    />

                    <x-select-field
                        name="year"
                        label="السنة"
                        :options="$years"
                        :value="$defaultYear"
                    />

                </div>


                {{-- File uploader --}}
                <div class="mt-4">

                    <input
                        id="file"
                        name="file"
                        type="file"
                        accept=".xlsx,.xls,.csv"
                        class="sr-only"
                    >


                    <label
                        for="file"
                        id="dropzone"
                        class="dropzone cursor-pointer transition"
                    >

                        <i
                            id="uploadIcon"
                            class="fa-solid fa-cloud-arrow-up text-3xl text-cyan transition"
                        ></i>


                        <span
                            id="file-name"
                            class="font-semibold text-fg"
                        >
                            اضغط لاختيار الملف
                        </span>


                        <span
                            id="file-meta"
                            class="text-[11px] text-dim"
                        >
                            xlsx, xls, csv · حتى {{ $maxFileSizeMb }} ميجابايت
                        </span>


                        <span
                            id="file-status"
                            class="hidden text-[11px] text-brand"
                        ></span>

                    </label>


                    {{-- File validation message --}}
                    <div
                        id="file-error"
                        class="mt-2 hidden rounded-lg border border-danger/20 bg-danger/5 px-3 py-2 text-[11px] text-danger"
                    ></div>


                    @error('file')
                        <p class="form-error">{{ $message }}</p>
                    @enderror

                </div>


                {{-- Selected file information --}}
                <div
                    id="fileInfo"
                    class="mt-4 hidden rounded-xl border border-white/[0.06] bg-white/[0.02] p-4"
                >

                    <div class="flex flex-wrap items-center justify-between gap-4">

                        <div class="flex min-w-0 items-center gap-3">

                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-success/10 text-success">
                                <i class="fa-solid fa-file-excel"></i>
                            </span>

                            <div class="min-w-0">

                                <p
                                    id="selectedFileName"
                                    class="truncate text-sm font-semibold text-fg"
                                ></p>

                                <p
                                    id="selectedFileSize"
                                    class="mt-0.5 text-[10px] text-dim"
                                ></p>

                            </div>

                        </div>


                        <button
                            type="button"
                            id="removeFile"
                            class="text-xs text-danger transition hover:text-fg"
                        >
                            <i class="fa-solid fa-xmark ml-1"></i>
                            إزالة الملف
                        </button>

                    </div>


                    {{-- File validation --}}
                    <div class="mt-3 flex flex-wrap gap-2">

                        <span
                            id="extensionCheck"
                            class="badge badge-outline-info"
                        >
                            <i class="fa-solid fa-circle-check ml-1"></i>
                            الامتداد
                        </span>

                        <span
                            id="sizeCheck"
                            class="badge badge-outline-info"
                        >
                            <i class="fa-solid fa-circle-check ml-1"></i>
                            الحجم
                        </span>

                    </div>

                </div>

            </x-section-card>


            {{-- ========================================================
                 EXISTING DATA MODE
                 ======================================================== --}}
            <x-section-card
                title="التعامل مع البيانات الموجودة"
                icon="fa-sliders"
                color="warning"
            >

                <div class="mb-4 rounded-xl border border-warning/10 bg-warning/5 px-4 py-3">

                    <div class="flex items-start gap-3">

                        <i class="fa-solid fa-circle-info mt-0.5 text-warning"></i>

                        <div>

                            <p class="text-xs font-semibold text-fg">
                                اختر طريقة معالجة البيانات المتكررة
                            </p>

                            <p class="mt-1 text-[11px] leading-5 text-dim">
                                هذا الاختيار يحدد كيف يتعامل النظام مع السجلات
                                الموجودة بالفعل أثناء عملية الاستيراد.
                            </p>

                        </div>

                    </div>

                </div>


                <div class="space-y-2">

                    @foreach($modes as $key => $mode)

                        <label
                            class="check-row cursor-pointer transition"
                            data-import-mode="{{ $key }}"
                        >

                            <input
                                type="radio"
                                name="mode"
                                value="{{ $key }}"
                                class="accent-brand import-mode-radio"
                                @checked($defaultMode === $key)
                            >

                            <span class="flex-1">

                                <span class="flex flex-wrap items-center gap-2">

                                    <b class="text-fg">
                                        {{ $mode['label'] }}
                                    </b>

                                    @if($key === 'upsert')
                                        <span class="badge badge-outline-info">
                                            الافتراضي
                                        </span>
                                    @endif

                                </span>

                                <span class="block text-[11px] text-dim">
                                    {{ $mode['hint'] }}
                                </span>

                            </span>

                        </label>

                    @endforeach

                </div>


                @error('mode')
                    <p class="form-error">{{ $message }}</p>
                @enderror


                {{-- Selected mode indicator --}}
                <div class="mt-4 rounded-lg border border-white/[0.06] bg-white/[0.02] px-3 py-2">

                    <div class="flex items-center gap-2 text-[11px]">

                        <i class="fa-solid fa-circle-check text-brand"></i>

                        <span class="text-muted">
                            الوضع المحدد:
                        </span>

                        <strong
                            id="selectedModeLabel"
                            class="text-fg"
                        >
                            @if(isset($modes[$defaultMode]))
                                {{ $modes[$defaultMode]['label'] }}
                            @else
                                -
                            @endif
                        </strong>

                    </div>

                </div>

            </x-section-card>


            {{-- ========================================================
                 SUBMIT ACTIONS
                 ======================================================== --}}
            <div class="flex flex-wrap items-center justify-between gap-3">

                <div class="flex flex-wrap gap-2">

                    <button
                        id="importSubmit"
                        type="submit"
                        class="btn btn-primary"
                    >
                        <i class="fa-solid fa-play text-xs"></i>
                        بدء الاستيراد
                    </button>

                    <button
                        type="reset"
                        id="resetImport"
                        class="btn btn-secondary"
                    >
                        <i class="fa-solid fa-rotate-left text-xs"></i>
                        إعادة تعيين
                    </button>

                    <a
                        href="{{ route('banks.panel', $bank->id) }}"
                        class="btn btn-secondary"
                    >
                        إلغاء
                    </a>

                </div>


                <div class="text-[10px] text-dim">

                    <i class="fa-solid fa-keyboard ml-1"></i>

                    <span>
                        Ctrl + Enter للبدء
                    </span>

                </div>

            </div>

        </form>


        {{-- ============================================================
             REQUIRED COLUMNS
             ============================================================ --}}
        <x-section-card
            title="أعمدة الملف"
            icon="fa-table-columns"
            color="info"
        >

            {{-- Required --}}
            <div>

                <div class="mb-2 flex items-center justify-between gap-2">

                    <p class="text-xs font-semibold text-fg">
                        أعمدة مطلوبة
                    </p>

                    <span class="badge badge-outline-warning">
                        {{ $requiredColumns->count() }}
                    </span>

                </div>

                <div class="mb-4 flex flex-wrap gap-1.5">

                    @forelse($requiredColumns as $column)

                        <span
                            class="badge badge-warning font-mono"
                            dir="ltr"
                        >
                            {{ $column }} *
                        </span>

                    @empty

                        <span class="text-[11px] text-dim">
                            لا توجد أعمدة محددة.
                        </span>

                    @endforelse

                </div>

            </div>


            {{-- Optional --}}
            <div>

                <div class="mb-2 flex items-center justify-between gap-2">

                    <p class="text-xs font-semibold text-fg">
                        أعمدة اختيارية
                    </p>

                    <span class="badge badge-outline-info">
                        {{ $optionalColumns->count() }}
                    </span>

                </div>

                <div class="flex flex-wrap gap-1.5">

                    @forelse($optionalColumns as $column)

                        <span
                            class="tag font-mono"
                            dir="ltr"
                        >
                            {{ $column }}
                        </span>

                    @empty

                        <span class="text-[11px] text-dim">
                            لا توجد أعمدة اختيارية.
                        </span>

                    @endforelse

                </div>

            </div>


            {{-- File rules --}}
            <div class="mt-5 space-y-2 border-t border-white/[0.06] pt-4">

                <div class="flex items-start gap-2 text-[11px] text-dim">

                    <i class="fa-solid fa-check mt-0.5 text-brand"></i>

                    <span>
                        الصف الأول يجب أن يحتوي على أسماء الأعمدة.
                    </span>

                </div>

                <div class="flex items-start gap-2 text-[11px] text-dim">

                    <i class="fa-solid fa-check mt-0.5 text-brand"></i>

                    <span>
                        أسماء الأعمدة باللغة الإنجليزية كما هي محددة.
                    </span>

                </div>

                <div class="flex items-start gap-2 text-[11px] text-dim">

                    <i class="fa-solid fa-check mt-0.5 text-brand"></i>

                    <span dir="ltr">
                        YYYY-MM-DD
                    </span>

                </div>

                <div class="flex items-start gap-2 text-[11px] text-dim">

                    <i class="fa-solid fa-check mt-0.5 text-brand"></i>

                    <span>
                        الحد الأقصى لحجم الملف {{ $maxFileSizeMb }} MB.
                    </span>

                </div>

            </div>


            {{-- Supported formats --}}
            <div class="mt-5 border-t border-white/[0.06] pt-4">

                <p class="mb-2 text-[11px] font-semibold text-fg">
                    الصيغ المدعومة
                </p>

                <div class="flex flex-wrap gap-2">

                    @foreach($supportedExtensions as $extension)

                        <span class="tag font-mono" dir="ltr">
                            .{{ $extension }}
                        </span>

                    @endforeach

                </div>

            </div>

        </x-section-card>

    </section>


    {{-- ================================================================
         IMPORT HISTORY SUMMARY
         ================================================================ --}}
    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        <x-metric-card
            label="إجمالي الصفوف"
            :value="number_format($totalRows)"
            unit="صف"
            icon="fa-table-list"
            color="info"
        />

        <x-metric-card
            label="صفوف ناجحة"
            :value="number_format($totalSuccess)"
            unit="صف"
            icon="fa-circle-check"
            color="brand"
        />

        <x-metric-card
            label="صفوف فاشلة"
            :value="number_format($totalFailed)"
            unit="صف"
            icon="fa-circle-xmark"
            color="danger"
        />

        <x-metric-card
            label="عمليات ناجحة"
            :value="number_format($successfulImports)"
            unit="عملية"
            icon="fa-check-double"
            color="brand"
        />

    </section>


    {{-- ================================================================
         IMPORT HEALTH
         ================================================================ --}}
    <section class="card mb-6">

        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">

            <div class="flex items-center gap-3">

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand/10 text-brand">
                    <i class="fa-solid fa-chart-pie"></i>
                </span>

                <div>

                    <h2 class="text-sm font-bold text-fg">
                        حالة عمليات الاستيراد
                    </h2>

                    <p class="mt-0.5 text-[11px] text-muted">
                        ملخص العمليات المسجلة لهذا البنك.
                    </p>

                </div>

            </div>

            @if($latestImport)

                <span class="badge badge-outline-info">
                    آخر عملية: {{ $latestImport->at ?? '-' }}
                </span>

            @endif

        </div>


        <div class="grid grid-cols-1 gap-5 md:grid-cols-3">

            {{-- Success --}}
            <div>

                <div class="mb-2 flex items-center justify-between text-[11px]">

                    <span class="text-muted">
                        معدل نجاح الصفوف
                    </span>

                    <strong class="text-brand">
                        {{ $successRate }}%
                    </strong>

                </div>

                <div class="progress">

                    <div
                        class="h-full rounded-full bg-brand"
                        style="width: {{ min(100, $successRate) }}%"
                    ></div>

                </div>

            </div>


            {{-- Failure --}}
            <div>

                <div class="mb-2 flex items-center justify-between text-[11px]">

                    <span class="text-muted">
                        معدل فشل الصفوف
                    </span>

                    <strong class="{{ $failureRate > 0 ? 'text-danger' : 'text-brand' }}">
                        {{ $failureRate }}%
                    </strong>

                </div>

                <div class="progress">

                    <div
                        class="h-full rounded-full {{ $failureRate > 0 ? 'bg-danger' : 'bg-brand' }}"
                        style="width: {{ min(100, $failureRate) }}%"
                    ></div>

                </div>

            </div>


            {{-- Import success --}}
            <div>

                <div class="mb-2 flex items-center justify-between text-[11px]">

                    <span class="text-muted">
                        عمليات بدون فشل
                    </span>

                    <strong class="text-fg">
                        {{ $successfulImports }}/{{ $totalImports }}
                    </strong>

                </div>

                <div class="progress">

                    <div
                        class="h-full rounded-full bg-info"
                        style="width: {{ $totalImports > 0 ? min(100, round(($successfulImports / $totalImports) * 100)) : 0 }}%"
                    ></div>

                </div>

            </div>

        </div>

    </section>


    {{-- ================================================================
         HISTORY
         ================================================================ --}}
    <x-panel
        title="سجل عمليات الاستيراد"
        icon="fa-clock-rotate-left"
    >

        {{-- History header --}}
        <div class="mb-5 flex flex-wrap items-center justify-between gap-3">

            <div class="flex flex-wrap items-center gap-2">

                <span class="badge badge-outline-info">
                    {{ number_format($totalImports) }} عملية
                </span>

                @if($failedImports > 0)

                    <span class="badge badge-outline-danger">
                        {{ number_format($failedImports) }} بها أخطاء
                    </span>

                @endif

            </div>


            <div class="text-[11px] text-dim">

                <i class="fa-solid fa-database ml-1 text-info"></i>

                {{ number_format($totalRows) }}
                صف تمت معالجته

            </div>

        </div>


        <div class="table-wrap">

            <table class="data-table whitespace-nowrap">

                <thead>

                    <tr>

                        <th>الملف</th>
                        <th>الفترة</th>
                        <th>المنفذ</th>
                        <th>الصفوف</th>
                        <th>نجح</th>
                        <th>فشل</th>
                        <th>الحالة</th>
                        <th>الوقت</th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($history as $row)

                        @php
                            $rowTotal = (int) ($row->total ?? 0);
                            $rowSuccess = (int) ($row->success ?? 0);
                            $rowFailed = (int) ($row->failed ?? 0);

                            $rowSuccessRate = $rowTotal > 0
                                ? round(($rowSuccess / $rowTotal) * 100)
                                : 0;
                        @endphp

                        <tr>

                            {{-- File --}}
                            <td>

                                <div class="flex items-center gap-3">

                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-success/10 text-success">

                                        <i class="fa-solid fa-file-excel text-xs"></i>

                                    </span>

                                    <div class="min-w-0">

                                        <div class="max-w-[220px] truncate font-semibold text-fg">
                                            <span dir="ltr">
                                                {{ $row->file ?? '-' }}
                                            </span>
                                        </div>

                                        <div class="mt-0.5 text-[10px] text-dim">
                                            {{ $rowSuccessRate }}% نجاح
                                        </div>

                                    </div>

                                </div>

                            </td>


                            {{-- Period --}}
                            <td>

                                <span class="text-xs text-muted">
                                    <i class="fa-regular fa-calendar ml-1 text-info"></i>
                                    {{ $row->period ?? '-' }}
                                </span>

                            </td>


                            {{-- User --}}
                            <td>

                                <span class="flex items-center gap-2">

                                    <span class="flex h-7 w-7 items-center justify-center rounded-full bg-info/10 text-info">
                                        <i class="fa-solid fa-user text-[9px]"></i>
                                    </span>

                                    <span class="text-xs text-fg">
                                        {{ $row->by ?? '-' }}
                                    </span>

                                </span>

                            </td>


                            {{-- Total --}}
                            <td>

                                <span class="font-semibold text-fg">
                                    {{ number_format($rowTotal) }}
                                </span>

                            </td>


                            {{-- Success --}}
                            <td>

                                <span class="font-semibold text-brand">
                                    {{ number_format($rowSuccess) }}
                                </span>

                            </td>


                            {{-- Failed --}}
                            <td>

                                <span class="{{ $rowFailed > 0 ? 'font-semibold text-danger' : 'text-muted' }}">
                                    {{ number_format($rowFailed) }}
                                </span>

                            </td>


                            {{-- Status --}}
                            <td>

                                <x-status-badge
                                    type="import"
                                    :status="$row->status ?? 'pending'"
                                />

                            </td>


                            {{-- Time --}}
                            <td>

                                <span class="text-xs text-muted">
                                    <i class="fa-regular fa-clock ml-1 text-info"></i>
                                    {{ $row->at ?? '-' }}
                                </span>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="8">

                                <x-empty-state
                                    text="لا توجد عمليات استيراد سابقة"
                                />

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- History footer --}}
        @if($totalImports > 0)

            <div class="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-white/[0.06] pt-4 text-[11px] text-dim">

                <div class="flex flex-wrap gap-4">

                    <span>
                        <i class="fa-solid fa-check text-brand ml-1"></i>
                        {{ number_format($successfulImports) }} عملية بدون فشل
                    </span>

                    <span>
                        <i class="fa-solid fa-triangle-exclamation text-danger ml-1"></i>
                        {{ number_format($failedImports) }} عملية بها فشل
                    </span>

                </div>

                <span>
                    إجمالي الصفوف:
                    <strong class="text-fg">
                        {{ number_format($totalRows) }}
                    </strong>
                </span>

            </div>

        @endif

    </x-panel>


    {{-- ================================================================
         PAGE FOOTER
         ================================================================ --}}
    <div class="mt-6 flex flex-wrap items-center justify-between gap-3 text-[11px] text-dim">

        <div class="flex flex-wrap items-center gap-3">

            <span>
                <i class="fa-solid fa-building-columns ml-1 text-info"></i>
                {{ $bank->name }}
            </span>

            <span>•</span>

            <span>
                استيراد النطاق
            </span>

        </div>

        <button
            type="button"
            onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
            class="transition hover:text-fg"
            title="العودة للأعلى"
        >
            <i class="fa-solid fa-arrow-up ml-1"></i>
            للأعلى
        </button>

    </div>

@endsection


@push('scripts')

<script>

    /*
    |--------------------------------------------------------------------------
    | ELEMENTS
    |--------------------------------------------------------------------------
    */

    const importForm = document.getElementById('importForm');
    const fileInput = document.getElementById('file');
    const dropzone = document.getElementById('dropzone');

    const fileName = document.getElementById('file-name');
    const fileMeta = document.getElementById('file-meta');

    const fileInfo = document.getElementById('fileInfo');
    const selectedFileName = document.getElementById('selectedFileName');
    const selectedFileSize = document.getElementById('selectedFileSize');

    const fileError = document.getElementById('file-error');

    const removeFile = document.getElementById('removeFile');

    const uploadIcon = document.getElementById('uploadIcon');

    const extensionCheck = document.getElementById('extensionCheck');
    const sizeCheck = document.getElementById('sizeCheck');

    const submitButton = document.getElementById('importSubmit');

    const resetButton = document.getElementById('resetImport');

    const selectedModeLabel =
        document.getElementById('selectedModeLabel');


    /*
    |--------------------------------------------------------------------------
    | CONFIG
    |--------------------------------------------------------------------------
    */

    const maxFileSize =
        {{ $maxFileSizeBytes }};

    const supportedExtensions =
        @json($supportedExtensions);


    /*
    |--------------------------------------------------------------------------
    | FORMAT FILE SIZE
    |--------------------------------------------------------------------------
    */

    function formatFileSize(bytes) {

        if (!bytes) {
            return '0 Bytes';
        }

        const units = [
            'Bytes',
            'KB',
            'MB',
            'GB'
        ];

        const index =
            Math.floor(
                Math.log(bytes) / Math.log(1024)
            );

        return (
            parseFloat(
                (bytes / Math.pow(1024, index)).toFixed(2)
            )
            + ' '
            + units[index]
        );

    }


    /*
    |--------------------------------------------------------------------------
    | FILE EXTENSION
    |--------------------------------------------------------------------------
    */

    function getFileExtension(name) {

        if (!name || !name.includes('.')) {
            return '';
        }

        return name
            .split('.')
            .pop()
            .toLowerCase();

    }


    /*
    |--------------------------------------------------------------------------
    | RESET FILE UI
    |--------------------------------------------------------------------------
    */

    function resetFileUI() {

        if (fileInput) {
            fileInput.value = '';
        }

        if (fileName) {
            fileName.textContent =
                'اضغط لاختيار الملف';
        }

        if (fileMeta) {
            fileMeta.textContent =
                'xlsx, xls, csv · حتى {{ $maxFileSizeMb }} ميجابايت';
        }

        if (fileInfo) {
            fileInfo.classList.add('hidden');
        }

        if (fileError) {
            fileError.classList.add('hidden');
            fileError.textContent = '';
        }

        if (uploadIcon) {
            uploadIcon.className =
                'fa-solid fa-cloud-arrow-up text-3xl text-cyan transition';
        }

    }


    /*
    |--------------------------------------------------------------------------
    | DISPLAY FILE
    |--------------------------------------------------------------------------
    */

    function handleSelectedFile(file) {

        if (!file) {
            resetFileUI();
            return;
        }


        const extension =
            getFileExtension(file.name);

        const extensionValid =
            supportedExtensions.includes(extension);

        const sizeValid =
            file.size <= maxFileSize;


        /*
        |--------------------------------------------------------------
        | Reset errors
        |--------------------------------------------------------------
        */

        if (fileError) {

            fileError.classList.add('hidden');

            fileError.textContent = '';

        }


        /*
        |--------------------------------------------------------------
        | Extension validation
        |--------------------------------------------------------------
        */

        if (!extensionValid) {

            if (fileError) {

                fileError.textContent =
                    'نوع الملف غير مدعوم. يرجى اختيار ملف xlsx أو xls أو csv.';

                fileError.classList.remove('hidden');

            }

        }


        /*
        |--------------------------------------------------------------
        | Size validation
        |--------------------------------------------------------------
        */

        if (!sizeValid) {

            if (fileError) {

                fileError.textContent =
                    'حجم الملف أكبر من الحد المسموح به وهو {{ $maxFileSizeMb }} ميجابايت.';

                fileError.classList.remove('hidden');

            }

        }


        /*
        |--------------------------------------------------------------
        | File information
        |--------------------------------------------------------------
        */

        if (fileName) {
            fileName.textContent = file.name;
        }

        if (fileMeta) {

            fileMeta.textContent =
                formatFileSize(file.size)
                + ' · '
                + extension.toUpperCase();

        }


        if (fileInfo) {
            fileInfo.classList.remove('hidden');
        }

        if (selectedFileName) {
            selectedFileName.textContent = file.name;
        }

        if (selectedFileSize) {
            selectedFileSize.textContent =
                formatFileSize(file.size);
        }


        /*
        |--------------------------------------------------------------
        | Extension badge
        |--------------------------------------------------------------
        */

        if (extensionCheck) {

            extensionCheck.className =
                extensionValid
                    ? 'badge badge-outline-info'
                    : 'badge badge-outline-danger';

            extensionCheck.innerHTML =
                extensionValid
                    ? '<i class="fa-solid fa-circle-check ml-1"></i> الامتداد صحيح'
                    : '<i class="fa-solid fa-circle-xmark ml-1"></i> امتداد غير مدعوم';

        }


        /*
        |--------------------------------------------------------------
        | Size badge
        |--------------------------------------------------------------
        */

        if (sizeCheck) {

            sizeCheck.className =
                sizeValid
                    ? 'badge badge-outline-info'
                    : 'badge badge-outline-danger';

            sizeCheck.innerHTML =
                sizeValid
                    ? '<i class="fa-solid fa-circle-check ml-1"></i> الحجم مناسب'
                    : '<i class="fa-solid fa-circle-xmark ml-1"></i> الحجم كبير';

        }


        /*
        |--------------------------------------------------------------
        | Icon
        |--------------------------------------------------------------
        */

        if (uploadIcon) {

            if (extensionValid && sizeValid) {

                uploadIcon.className =
                    'fa-solid fa-circle-check text-3xl text-brand transition';

            } else {

                uploadIcon.className =
                    'fa-solid fa-triangle-exclamation text-3xl text-danger transition';

            }

        }

    }


    /*
    |--------------------------------------------------------------------------
    | FILE INPUT CHANGE
    |--------------------------------------------------------------------------
    */

    if (fileInput) {

        fileInput.addEventListener(
            'change',
            function(event) {

                const file =
                    event.target.files
                        ? event.target.files[0]
                        : null;

                handleSelectedFile(file);

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | REMOVE FILE
    |--------------------------------------------------------------------------
    */

    if (removeFile) {

        removeFile.addEventListener(
            'click',
            function(event) {

                event.preventDefault();

                resetFileUI();

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | DRAG & DROP
    |--------------------------------------------------------------------------
    */

    if (dropzone) {

        [
            'dragenter',
            'dragover'
        ].forEach(eventName => {

            dropzone.addEventListener(
                eventName,
                function(event) {

                    event.preventDefault();
                    event.stopPropagation();

                    dropzone.classList.add(
                        'border-cyan',
                        'bg-cyan/5'
                    );

                }
            );

        });


        [
            'dragleave',
            'drop'
        ].forEach(eventName => {

            dropzone.addEventListener(
                eventName,
                function(event) {

                    event.preventDefault();
                    event.stopPropagation();

                    dropzone.classList.remove(
                        'border-cyan',
                        'bg-cyan/5'
                    );

                }
            );

        });


        dropzone.addEventListener(
            'drop',
            function(event) {

                const files =
                    event.dataTransfer
                        ? event.dataTransfer.files
                        : null;

                if (!files || !files.length) {
                    return;
                }

                const file = files[0];

                /*
                |----------------------------------------------------------
                | Assign dropped file to input when browser permits it.
                |----------------------------------------------------------
                */

                try {

                    const dataTransfer =
                        new DataTransfer();

                    dataTransfer.items.add(file);

                    fileInput.files =
                        dataTransfer.files;

                } catch (error) {

                    /*
                    | Browser may not allow assigning FileList.
                    | The selected file is still displayed and the user
                    | can use the normal picker if necessary.
                    */

                }

                handleSelectedFile(file);

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | IMPORT MODE CHANGE
    |--------------------------------------------------------------------------
    */

    const modeRadios =
        document.querySelectorAll('.import-mode-radio');

    const modeData =
        @json($modes);

    modeRadios.forEach(function(radio) {

        radio.addEventListener(
            'change',
            function() {

                const selected =
                    modeData[this.value];

                if (
                    selectedModeLabel &&
                    selected
                ) {

                    selectedModeLabel.textContent =
                        selected.label;

                }

            }
        );

    });


    /*
    |--------------------------------------------------------------------------
    | FORM SUBMIT PROTECTION
    |--------------------------------------------------------------------------
    */

    let importSubmitting = false;

    if (importForm) {

        importForm.addEventListener(
            'submit',
            function(event) {

                /*
                |----------------------------------------------------------
                | Validate file before submitting.
                |----------------------------------------------------------
                */

                const file =
                    fileInput &&
                    fileInput.files &&
                    fileInput.files.length
                        ? fileInput.files[0]
                        : null;


                if (!file) {

                    event.preventDefault();

                    if (fileError) {

                        fileError.textContent =
                            'يرجى اختيار ملف قبل بدء عملية الاستيراد.';

                        fileError.classList.remove('hidden');

                    }

                    if (dropzone) {
                        dropzone.focus();
                    }

                    return;

                }


                const extension =
                    getFileExtension(file.name);

                const extensionValid =
                    supportedExtensions.includes(extension);

                const sizeValid =
                    file.size <= maxFileSize;


                if (!extensionValid || !sizeValid) {

                    event.preventDefault();

                    if (fileError) {

                        if (!extensionValid) {

                            fileError.textContent =
                                'نوع الملف غير مدعوم.';

                        } else {

                            fileError.textContent =
                                'حجم الملف أكبر من الحد المسموح به.';

                        }

                        fileError.classList.remove('hidden');

                    }

                    return;

                }


                /*
                |----------------------------------------------------------
                | Prevent accidental double submission.
                |----------------------------------------------------------
                */

                if (importSubmitting) {

                    event.preventDefault();

                    return;

                }

                importSubmitting = true;


                /*
                |----------------------------------------------------------
                | Loading state
                |----------------------------------------------------------
                */

                if (submitButton) {

                    submitButton.disabled = true;

                    submitButton.innerHTML =
                        '<i class="fa-solid fa-spinner fa-spin text-xs"></i> جاري الاستيراد...';

                }

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | RESET BUTTON
    |--------------------------------------------------------------------------
    */

    if (resetButton) {

        resetButton.addEventListener(
            'click',
            function() {

                setTimeout(function() {

                    resetFileUI();

                    const defaultRadio =
                        document.querySelector(
                            '.import-mode-radio[value="{{ $defaultMode }}"]'
                        );

                    if (
                        defaultRadio &&
                        selectedModeLabel &&
                        modeData[defaultRadio.value]
                    ) {

                        selectedModeLabel.textContent =
                            modeData[defaultRadio.value].label;

                    }

                }, 0);

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | TEMPLATE NOTICE
    |--------------------------------------------------------------------------
    */

    function showTemplateNotice() {

        /*
        | No fake download route is created here.
        | The existing # link is intentionally preserved until
        | a real template route/file exists.
        */

        const message =
            document.createElement('div');

        message.className =
            'fixed bottom-5 left-5 z-[9999] max-w-sm rounded-xl border border-info/20 bg-[#111418] px-4 py-3 text-xs text-fg shadow-2xl';

        message.innerHTML =
            '<div class="flex items-start gap-3">' +
                '<i class="fa-solid fa-circle-info mt-0.5 text-info"></i>' +
                '<div>' +
                    '<p class="font-semibold">قالب الاستيراد</p>' +
                    '<p class="mt-1 text-[11px] text-muted">سيتم ربط زر تحميل القالب بملف Excel فعلي عند تجهيز قالب البنك.</p>' +
                '</div>' +
            '</div>';

        document.body.appendChild(message);

        setTimeout(function() {

            message.remove();

        }, 3500);

    }


    /*
    |--------------------------------------------------------------------------
    | KEYBOARD SHORTCUTS
    |--------------------------------------------------------------------------
    |
    | Ctrl + Enter -> Start import
    | Escape       -> Blur current field
    |
    */

    document.addEventListener(
        'keydown',
        function(event) {

            if (
                (event.ctrlKey || event.metaKey) &&
                event.key === 'Enter'
            ) {

                if (
                    importForm &&
                    document.activeElement &&
                    (
                        document.activeElement.tagName === 'INPUT' ||
                        document.activeElement.tagName === 'SELECT'
                    )
                ) {

                    event.preventDefault();

                    importForm.requestSubmit();

                }

            }


            if (event.key === 'Escape') {

                const active =
                    document.activeElement;

                if (
                    active &&
                    (
                        active.tagName === 'INPUT' ||
                        active.tagName === 'SELECT'
                    )
                ) {

                    active.blur();

                }

            }

        }
    );

</script>

@endpush