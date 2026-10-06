{{-- resources/views/installment-companies/panel.blade.php : control panel of one company --}}
@extends('layouts.app')

@section('title', $company->name . ' - لوحة التحكم')

@section('content')

    @php
        /*
        |--------------------------------------------------------------------------
        | SAFE COMPANY VALUES
        |--------------------------------------------------------------------------
        */

        $companyCases = (int) ($company->cases_count ?? 0);

        $companyDebt = (float) ($company->total_debt ?? 0);

        $companyIsActive = (bool) ($company->is_active ?? false);

        $companyCode = $company->code ?? null;


        /*
        |--------------------------------------------------------------------------
        | SAFE METRICS
        |--------------------------------------------------------------------------
        */

        $averageDebtPerCase = $companyCases > 0
            ? $companyDebt / $companyCases
            : 0;


        /*
        |--------------------------------------------------------------------------
        | COMPANY STATUS
        |--------------------------------------------------------------------------
        */

        $statusText = $companyIsActive
            ? 'نشطة'
            : 'متوقفة';

        $statusClass = $companyIsActive
            ? 'text-brand'
            : 'text-danger';

        $statusBg = $companyIsActive
            ? 'bg-brand/10'
            : 'bg-danger/10';

        $statusDot = $companyIsActive
            ? 'bg-brand'
            : 'bg-danger';


        /*
        |--------------------------------------------------------------------------
        | COMPANY CREATION DATE
        |--------------------------------------------------------------------------
        */

        $createdAt = $company->created_at ?? null;

    @endphp


    {{-- ================================================================
        COMPANY HEADER
    ================================================================= --}}
    <header class="card mb-8 flex flex-wrap items-center justify-center gap-4 py-8 sm:relative">

        {{-- ============================================================
            BACK BUTTON
        ============================================================= --}}
        <a
            href="{{ route('installment-companies.index') }}"
            class="btn btn-secondary btn-sm sm:absolute sm:right-6 sm:top-6"
            title="العودة إلى قائمة شركات التقسيط"
            aria-label="العودة إلى قائمة شركات التقسيط"
        >
            <i class="fa-solid fa-arrow-right text-xs"></i>
            رجوع
        </a>


        {{-- ============================================================
            COMPANY IDENTITY
        ============================================================= --}}
        <div class="flex items-center gap-4">

            <span
                class="relative flex h-14 w-14 items-center justify-center rounded-xl bg-info/15 text-info"
                title="شركة تقسيط"
            >

                <i class="fa-solid fa-building text-xl"></i>


                {{-- STATUS DOT --}}
                <span
                    class="absolute bottom-0 right-0 h-3.5 w-3.5 rounded-full border-2 border-[#14191e] {{ $statusDot }}"
                    title="{{ $statusText }}"
                ></span>

            </span>


            <div>

                <div class="flex flex-wrap items-center gap-2">

                    <h1 class="text-2xl font-bold text-fg">
                        {{ $company->name }}
                    </h1>


                    <span
                        class="badge {{ $companyIsActive ? 'badge-success' : 'badge-danger' }}"
                        title="حالة الشركة"
                    >
                        <i class="fa-solid fa-circle text-[7px]"></i>
                        {{ $statusText }}
                    </span>

                </div>


                <p class="mt-1 text-xs text-muted">
                    لوحة تحكم شركة التقسيط
                </p>


                <div class="mt-2 flex flex-wrap items-center gap-3 text-[10px] text-dim">

                    <span>
                        <i class="fa-solid fa-hashtag ml-1 text-info"></i>
                        ID {{ $company->id }}
                    </span>


                    @if($companyCode)

                        <span class="text-white/10">•</span>

                        <span
                            dir="ltr"
                            class="font-mono"
                        >
                            <i class="fa-solid fa-code ml-1 text-info"></i>
                            {{ $companyCode }}
                        </span>

                    @endif


                    @if($createdAt)

                        <span class="text-white/10">•</span>

                        <span>
                            <i class="fa-regular fa-calendar ml-1"></i>
                            {{ $createdAt->format('Y-m-d') }}
                        </span>

                    @endif

                </div>

            </div>

        </div>

    </header>


    {{-- ================================================================
        COMPANY STATUS / QUICK INFORMATION
    ================================================================= --}}
    <section class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">

        {{-- ============================================================
            STATUS
        ============================================================= --}}
        <div class="card">

            <div class="flex items-center justify-between gap-3">

                <div>

                    <div class="text-xs text-dim">
                        حالة الشركة
                    </div>

                    <div class="mt-2 flex items-center gap-2">

                        <span
                            class="h-2 w-2 rounded-full {{ $statusDot }}"
                        ></span>

                        <span class="text-sm font-bold {{ $statusClass }}">
                            {{ $statusText }}
                        </span>

                    </div>

                </div>


                <span class="flex h-10 w-10 items-center justify-center rounded-xl {{ $statusBg }} {{ $statusClass }}">

                    @if($companyIsActive)

                        <i class="fa-solid fa-circle-check"></i>

                    @else

                        <i class="fa-solid fa-circle-pause"></i>

                    @endif

                </span>

            </div>

        </div>


        {{-- ============================================================
            COMPANY CODE
        ============================================================= --}}
        <div class="card">

            <div class="flex items-center justify-between gap-3">

                <div class="min-w-0">

                    <div class="text-xs text-dim">
                        كود الشركة
                    </div>

                    <div
                        id="companyCodeValue"
                        class="mt-2 truncate font-mono text-sm font-bold text-info"
                        dir="ltr"
                    >
                        {{ $companyCode ?: 'غير محدد' }}
                    </div>

                </div>


                @if($companyCode)

                    <button
                        type="button"
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-info/10 text-info transition hover:bg-info/20"
                        onclick="copyCompanyPanelCode(@js($companyCode), this)"
                        title="نسخ كود الشركة"
                        aria-label="نسخ كود الشركة"
                    >
                        <i class="fa-regular fa-copy text-xs"></i>
                    </button>

                @else

                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/5 text-dim">
                        <i class="fa-solid fa-minus"></i>
                    </span>

                @endif

            </div>

        </div>


        {{-- ============================================================
            AVERAGE DEBT
        ============================================================= --}}
        <div class="card">

            <div class="flex items-center justify-between gap-3">

                <div>

                    <div class="text-xs text-dim">
                        متوسط المديونية / حالة
                    </div>

                    <div class="mt-2 text-sm font-bold text-accent">
                        EGP {{ number_format($averageDebtPerCase, 0) }}
                    </div>

                </div>


                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-accent/10 text-accent">
                    <i class="fa-solid fa-calculator"></i>
                </span>

            </div>

        </div>

    </section>


    {{-- ================================================================
        MAIN KPI CARDS
    ================================================================= --}}
    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">

        <x-stat-card
            label="عدد الحالات"
            :value="$companyCases"
            unit="حالة"
        />

        <x-stat-card
            label="إجمالي المديونية"
            :value="number_format($companyDebt)"
            unit="EGP"
        />

    </section>


    {{-- ================================================================
        COMPANY OVERVIEW
    ================================================================= --}}
    <section class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-3">


        {{-- ============================================================
            COLLECTION OVERVIEW
        ============================================================= --}}
        <div class="card">

            <div class="flex items-center justify-between mb-5">

                <div>

                    <div class="text-sm font-bold text-fg">
                        نظرة عامة
                    </div>

                    <div class="mt-1 text-[11px] text-dim">
                        ملخص سريع لبيانات الشركة
                    </div>

                </div>

                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-info/10 text-info">
                    <i class="fa-solid fa-chart-simple"></i>
                </span>

            </div>


            <div class="space-y-4">


                {{-- CASES --}}
                <div>

                    <div class="mb-2 flex items-center justify-between">

                        <span class="text-xs text-dim">
                            الحالات
                        </span>

                        <span class="font-semibold text-info">
                            {{ number_format($companyCases) }}
                        </span>

                    </div>


                    <div class="progress">

                        <div
                            class="h-full rounded-full bg-info"
                            style="width: {{ $companyCases > 0 ? '100' : '0' }}%"
                        ></div>

                    </div>

                </div>


                {{-- DEBT --}}
                <div>

                    <div class="mb-2 flex items-center justify-between">

                        <span class="text-xs text-dim">
                            إجمالي المديونية
                        </span>

                        <span class="font-semibold text-accent">
                            EGP {{ number_format($companyDebt, 0) }}
                        </span>

                    </div>


                    <div class="progress">

                        <div
                            class="h-full rounded-full bg-accent"
                            style="width: {{ $companyDebt > 0 ? '100' : '0' }}%"
                        ></div>

                    </div>

                </div>


                {{-- AVERAGE --}}
                <div class="flex items-center justify-between border-t border-white/5 pt-4">

                    <span class="text-xs text-dim">
                        متوسط المديونية
                    </span>

                    <span class="font-semibold text-fg">
                        EGP {{ number_format($averageDebtPerCase, 0) }}
                    </span>

                </div>

            </div>

        </div>


        {{-- ============================================================
            SYSTEM READINESS
        ============================================================= --}}
        <div class="card">

            <div class="flex items-center justify-between mb-5">

                <div>

                    <div class="text-sm font-bold text-fg">
                        جاهزية الشركة
                    </div>

                    <div class="mt-1 text-[11px] text-dim">
                        حالة البيانات الأساسية
                    </div>

                </div>

                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand/10 text-brand">
                    <i class="fa-solid fa-shield-check"></i>
                </span>

            </div>


            <div class="space-y-3">


                {{-- COMPANY NAME --}}
                <div class="flex items-center justify-between">

                    <div class="flex items-center gap-2">

                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand/10 text-brand">
                            <i class="fa-solid fa-building text-[10px]"></i>
                        </span>

                        <span class="text-xs text-muted">
                            اسم الشركة
                        </span>

                    </div>

                    <span class="text-[10px] text-brand">
                        <i class="fa-solid fa-check ml-1"></i>
                        متوفر
                    </span>

                </div>


                {{-- COMPANY CODE --}}
                <div class="flex items-center justify-between">

                    <div class="flex items-center gap-2">

                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-info/10 text-info">
                            <i class="fa-solid fa-code text-[10px]"></i>
                        </span>

                        <span class="text-xs text-muted">
                            كود الشركة
                        </span>

                    </div>

                    @if($companyCode)

                        <span class="text-[10px] text-brand">
                            <i class="fa-solid fa-check ml-1"></i>
                            متوفر
                        </span>

                    @else

                        <span class="text-[10px] text-danger">
                            <i class="fa-solid fa-xmark ml-1"></i>
                            غير محدد
                        </span>

                    @endif

                </div>


                {{-- STATUS --}}
                <div class="flex items-center justify-between">

                    <div class="flex items-center gap-2">

                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand/10 text-brand">
                            <i class="fa-solid fa-circle-check text-[10px]"></i>
                        </span>

                        <span class="text-xs text-muted">
                            حالة التشغيل
                        </span>

                    </div>

                    <span class="text-[10px] {{ $statusClass }}">
                        <i class="fa-solid fa-circle ml-1 text-[6px]"></i>
                        {{ $statusText }}
                    </span>

                </div>


            </div>

        </div>


        {{-- ============================================================
            QUICK SUMMARY
        ============================================================= --}}
        <div class="card">

            <div class="flex items-center justify-between mb-5">

                <div>

                    <div class="text-sm font-bold text-fg">
                        ملخص سريع
                    </div>

                    <div class="mt-1 text-[11px] text-dim">
                        أهم الأرقام الحالية
                    </div>

                </div>

                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-accent/10 text-accent">
                    <i class="fa-solid fa-bolt"></i>
                </span>

            </div>


            <div class="grid grid-cols-2 gap-3">


                <div class="rounded-xl border border-white/5 bg-white/[0.02] p-3">

                    <div class="text-[10px] text-dim">
                        الحالات
                    </div>

                    <div class="mt-1 text-lg font-bold text-info">
                        {{ number_format($companyCases) }}
                    </div>

                </div>


                <div class="rounded-xl border border-white/5 bg-white/[0.02] p-3">

                    <div class="text-[10px] text-dim">
                        المديونية
                    </div>

                    <div class="mt-1 text-lg font-bold text-accent">
                        {{ number_format($companyDebt, 0) }}
                    </div>

                </div>


                <div class="col-span-2 rounded-xl border border-white/5 bg-white/[0.02] p-3">

                    <div class="flex items-center justify-between">

                        <span class="text-[10px] text-dim">
                            متوسط المديونية / حالة
                        </span>

                        <span class="font-semibold text-fg">
                            EGP {{ number_format($averageDebtPerCase, 0) }}
                        </span>

                    </div>

                </div>

            </div>

        </div>

    </section>


    {{-- ================================================================
        COMPANY MODULES
    ================================================================= --}}
    <section>

        <div class="mb-4 flex items-center justify-between">

            <div>

                <div class="flex items-center gap-2 text-sm font-bold text-fg">

                    <i class="fa-solid fa-layer-group text-info"></i>

                    وحدات الشركة

                </div>

                <div class="mt-1 text-[11px] text-dim">
                    الأدوات والعمليات الخاصة بشركة التقسيط
                </div>

            </div>


            <span class="badge badge-outline-info">
                3 وحدات
            </span>

        </div>


        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">

            {{-- ========================================================
                VIEW CASES
            ========================================================= --}}
            <x-hub-tile
                title="عرض النطاق"
                description="استعراض عملاء الشركة وبياناتهم"
                icon="fa-eye"
                color="brand"
            />


            {{-- ========================================================
                IMPORT
            ========================================================= --}}
            <x-hub-tile
                title="استيراد النطاق"
                description="رفع ملفات Excel وتحديث البيانات"
                icon="fa-file-arrow-up"
                color="cyan"
            />


            {{-- ========================================================
                DISTRIBUTION
            ========================================================= --}}
            <x-hub-tile
                title="توزيع الحالات"
                description="توزيع الحالات على الموظفين"
                icon="fa-sitemap"
                color="accent"
            />

        </div>

    </section>


    {{-- ================================================================
        UPCOMING OPERATIONS
        These are informational only — no fake routes.
    ================================================================= --}}
    <section class="mt-6">

        <div class="card">

            <div class="flex flex-wrap items-center justify-between gap-3">

                <div class="flex items-center gap-3">

                    <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/5 text-dim">
                        <i class="fa-solid fa-toolbox"></i>
                    </span>

                    <div>

                        <div class="text-sm font-bold text-fg">
                            العمليات القادمة
                        </div>

                        <div class="mt-1 text-[11px] text-dim">
                            وحدات يمكن ربطها لاحقًا ببيانات التحصيل الفعلية
                        </div>

                    </div>

                </div>


                <span class="badge badge-outline-info">
                    قريبًا
                </span>

            </div>


            <div class="mt-5 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">


                <div class="rounded-xl border border-white/5 bg-white/[0.02] p-4">

                    <div class="flex items-center gap-2">

                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-info/10 text-info">
                            <i class="fa-solid fa-users text-xs"></i>
                        </span>

                        <span class="text-xs font-semibold text-fg">
                            العملاء
                        </span>

                    </div>

                    <div class="mt-2 text-[10px] text-dim">
                        إدارة عملاء الشركة
                    </div>

                </div>


                <div class="rounded-xl border border-white/5 bg-white/[0.02] p-4">

                    <div class="flex items-center gap-2">

                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-accent/10 text-accent">
                            <i class="fa-solid fa-money-bill-transfer text-xs"></i>
                        </span>

                        <span class="text-xs font-semibold text-fg">
                            التحصيل
                        </span>

                    </div>

                    <div class="mt-2 text-[10px] text-dim">
                        متابعة عمليات التحصيل
                    </div>

                </div>


                <div class="rounded-xl border border-white/5 bg-white/[0.02] p-4">

                    <div class="flex items-center gap-2">

                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand/10 text-brand">
                            <i class="fa-solid fa-chart-line text-xs"></i>
                        </span>

                        <span class="text-xs font-semibold text-fg">
                            التقارير
                        </span>

                    </div>

                    <div class="mt-2 text-[10px] text-dim">
                        تقارير أداء الشركة
                    </div>

                </div>


                <div class="rounded-xl border border-white/5 bg-white/[0.02] p-4">

                    <div class="flex items-center gap-2">

                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-warning/10 text-warning">
                            <i class="fa-solid fa-clock-rotate-left text-xs"></i>
                        </span>

                        <span class="text-xs font-semibold text-fg">
                            سجل النشاط
                        </span>

                    </div>

                    <div class="mt-2 text-[10px] text-dim">
                        تتبع عمليات المستخدمين
                    </div>

                </div>

            </div>

        </div>

    </section>


    {{-- ================================================================
        COMPANY INFORMATION
    ================================================================= --}}
    <section class="mt-6">

        <div class="card">

            <div class="flex items-center justify-between mb-5">

                <div>

                    <div class="text-sm font-bold text-fg">
                        معلومات الشركة
                    </div>

                    <div class="mt-1 text-[11px] text-dim">
                        البيانات الأساسية المسجلة
                    </div>

                </div>

                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-info/10 text-info">
                    <i class="fa-solid fa-circle-info"></i>
                </span>

            </div>


            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">


                {{-- NAME --}}
                <div class="rounded-xl border border-white/5 bg-white/[0.02] p-4">

                    <div class="text-[10px] text-dim">
                        اسم الشركة
                    </div>

                    <div class="mt-2 truncate text-sm font-semibold text-fg">
                        {{ $company->name }}
                    </div>

                </div>


                {{-- CODE --}}
                <div class="rounded-xl border border-white/5 bg-white/[0.02] p-4">

                    <div class="text-[10px] text-dim">
                        كود الشركة
                    </div>

                    <div
                        class="mt-2 truncate font-mono text-sm font-semibold text-info"
                        dir="ltr"
                    >
                        {{ $companyCode ?: '-' }}
                    </div>

                </div>


                {{-- ID --}}
                <div class="rounded-xl border border-white/5 bg-white/[0.02] p-4">

                    <div class="text-[10px] text-dim">
                        معرف الشركة
                    </div>

                    <div class="mt-2 font-mono text-sm font-semibold text-fg">
                        #{{ $company->id }}
                    </div>

                </div>


                {{-- STATUS --}}
                <div class="rounded-xl border border-white/5 bg-white/[0.02] p-4">

                    <div class="text-[10px] text-dim">
                        الحالة
                    </div>

                    <div class="mt-2 flex items-center gap-2 text-sm font-semibold {{ $statusClass }}">

                        <span class="h-1.5 w-1.5 rounded-full {{ $statusDot }}"></span>

                        {{ $statusText }}

                    </div>

                </div>


            </div>

        </div>

    </section>


    {{-- ================================================================
        JAVASCRIPT
    ================================================================= --}}
    <script>

        /*
        |--------------------------------------------------------------------------
        | COPY COMPANY CODE
        |--------------------------------------------------------------------------
        */

        function copyCompanyPanelCode(value, button) {

            if (!navigator.clipboard) {
                return;
            }


            navigator.clipboard.writeText(value)
                .then(function () {

                    const icon =
                        button.querySelector('i');

                    if (!icon) {
                        return;
                    }


                    const originalClass =
                        icon.className;


                    icon.className =
                        'fa-solid fa-check text-brand';


                    button.classList.add(
                        'bg-brand/10',
                        'text-brand'
                    );


                    setTimeout(function () {

                        icon.className =
                            originalClass;

                        button.classList.remove(
                            'bg-brand/10',
                            'text-brand'
                        );

                    }, 1200);

                });

        }


        /*
        |--------------------------------------------------------------------------
        | KEYBOARD SHORTCUT
        | Alt + B = Back to companies
        |--------------------------------------------------------------------------
        */

        document.addEventListener(
            'keydown',
            function (event) {

                if (
                    event.altKey &&
                    event.key.toLowerCase() === 'b'
                ) {

                    event.preventDefault();

                    window.location.href =
                        @js(route('installment-companies.index'));

                }

            }
        );

    </script>

@endsection