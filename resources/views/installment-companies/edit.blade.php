@extends('layouts.app')

@section('title', 'تعديل شركة تقسيط')

@section('content')

    @php
        /*
        |--------------------------------------------------------------------------
        | SAFE COMPANY VALUES
        |--------------------------------------------------------------------------
        */

        $companyName = $company->name ?? '';

        $companyCode = $company->code ?? '';

        $companyIsActive = (bool) ($company->is_active ?? false);

        $companyCases = (int) ($company->cases_count ?? 0);

        $companyDebt = (float) ($company->total_debt ?? 0);

        $averageDebtPerCase = $companyCases > 0
            ? $companyDebt / $companyCases
            : 0;

        $createdAt = $company->created_at ?? null;

        $updatedAt = $company->updated_at ?? null;


        /*
        |--------------------------------------------------------------------------
        | STATUS
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
    @endphp


    {{-- ================================================================
        PAGE HEADER
    ================================================================= --}}
    <x-page-header
        title="تعديل شركة تقسيط"
        :subtitle="$company->name"
        icon="fa-building"
    />


    {{-- ================================================================
        FLASH MESSAGES
    ================================================================= --}}
    <x-flash />


    {{-- ================================================================
        VALIDATION ERRORS
    ================================================================= --}}
    @if($errors->any())

        <div class="mb-5 card border-danger/20 bg-danger/[0.03]">

            <div class="flex items-start gap-3">

                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-danger/10 text-danger">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </span>

                <div class="min-w-0">

                    <div class="text-sm font-bold text-fg">
                        تعذر حفظ التعديلات
                    </div>

                    <div class="mt-1 text-[11px] text-dim">
                        يرجى مراجعة البيانات التالية ثم المحاولة مرة أخرى.
                    </div>

                    <ul class="mt-3 space-y-1.5 text-xs text-danger">

                        @foreach($errors->all() as $error)

                            <li class="flex items-start gap-2">

                                <i class="fa-solid fa-circle text-[5px] mt-1.5"></i>

                                <span>
                                    {{ $error }}
                                </span>

                            </li>

                        @endforeach

                    </ul>

                </div>

            </div>

        </div>

    @endif


    {{-- ================================================================
        COMPANY QUICK INFO
    ================================================================= --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">


        {{-- ============================================================
            COMPANY ID
        ============================================================= --}}
        <div class="card">

            <div class="flex items-center justify-between gap-3">

                <div>

                    <div class="text-xs text-dim">
                        معرف الشركة
                    </div>

                    <div class="mt-2 font-mono text-lg font-bold text-info">
                        #{{ $company->id }}
                    </div>

                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-info/10 text-info">
                    <i class="fa-solid fa-fingerprint"></i>
                </span>

            </div>

        </div>


        {{-- ============================================================
            CASES
        ============================================================= --}}
        <div class="card">

            <div class="flex items-center justify-between gap-3">

                <div>

                    <div class="text-xs text-dim">
                        عدد الحالات
                    </div>

                    <div class="mt-2 text-lg font-bold text-info">
                        {{ number_format($companyCases) }}
                    </div>

                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-info/10 text-info">
                    <i class="fa-solid fa-folder-open"></i>
                </span>

            </div>

        </div>


        {{-- ============================================================
            DEBT
        ============================================================= --}}
        <div class="card">

            <div class="flex items-center justify-between gap-3">

                <div>

                    <div class="text-xs text-dim">
                        إجمالي المديونية
                    </div>

                    <div class="mt-2 text-lg font-bold text-accent">
                        EGP {{ number_format($companyDebt, 0) }}
                    </div>

                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-accent/10 text-accent">
                    <i class="fa-solid fa-money-bill-wave"></i>
                </span>

            </div>

        </div>


        {{-- ============================================================
            STATUS
        ============================================================= --}}
        <div class="card">

            <div class="flex items-center justify-between gap-3">

                <div>

                    <div class="text-xs text-dim">
                        الحالة الحالية
                    </div>

                    <div class="mt-2 flex items-center gap-2 {{ $statusClass }}">

                        <span class="h-2 w-2 rounded-full {{ $statusDot }}"></span>

                        <span class="text-sm font-bold">
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

    </div>


    {{-- ================================================================
        MAIN CONTENT
    ================================================================= --}}
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">


        {{-- ============================================================
            MAIN EDIT FORM
        ============================================================= --}}
        <div class="xl:col-span-2">

            <form
                method="POST"
                action="{{ route('installment-companies.update', $company->id) }}"
                class="card max-w-xl space-y-4"
                id="editInstallmentCompanyForm"
            >

                @csrf
                @method('PUT')


                {{-- ====================================================
                    FORM HEADER
                ================================================= --}}
                <div class="mb-5 flex items-center justify-between gap-3 border-b border-white/5 pb-4">

                    <div class="flex items-center gap-3">

                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-info/10 text-info">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </span>

                        <div>

                            <div class="text-sm font-bold text-fg">
                                تعديل بيانات الشركة
                            </div>

                            <div class="mt-1 text-[11px] text-dim">
                                تحديث البيانات الأساسية لشركة التقسيط
                            </div>

                        </div>

                    </div>


                    <span class="badge badge-outline-info">

                        <i class="fa-solid fa-pencil text-[9px]"></i>

                        تعديل

                    </span>

                </div>


                {{-- ====================================================
                    EXISTING FORM
                    Keep the reusable form untouched.
                ===================================================== --}}
                @include('installment-companies._form', [
                    'company' => $company
                ])


                {{-- ====================================================
                    FORM ACTIONS
                ===================================================== --}}
                <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-white/5 pt-5">


                    <div class="text-[10px] text-dim">

                        <i class="fa-solid fa-clock-rotate-left ml-1 text-info"></i>

                        تأكد من البيانات الجديدة قبل حفظ التعديلات.

                    </div>


                    <div class="flex flex-wrap items-center gap-2">


                        <a
                            href="{{ route('installment-companies.index') }}"
                            class="btn btn-secondary"
                            title="العودة إلى قائمة شركات التقسيط"
                            aria-label="العودة إلى قائمة شركات التقسيط"
                        >
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                            رجوع
                        </a>


                        <button
                            type="button"
                            class="btn btn-secondary"
                            id="resetEditCompanyForm"
                            title="استعادة البيانات الأصلية"
                            aria-label="استعادة البيانات الأصلية"
                        >
                            <i class="fa-solid fa-rotate-left text-xs"></i>
                            استعادة
                        </button>


                        <button
                            type="submit"
                            class="btn btn-primary"
                            id="updateCompanyButton"
                            title="حفظ تعديلات الشركة"
                            aria-label="حفظ تعديلات الشركة"
                        >

                            <i
                                class="fa-solid fa-floppy-disk text-xs"
                                id="updateCompanyIcon"
                            ></i>

                            <span id="updateCompanyText">
                                حفظ التعديلات
                            </span>

                        </button>

                    </div>

                </div>

            </form>

        </div>


        {{-- ============================================================
            SIDE PANEL
        ============================================================= --}}
        <div class="space-y-5">


            {{-- ========================================================
                LIVE PREVIEW
            ========================================================= --}}
            <div class="card">

                <div class="flex items-center justify-between mb-4">

                    <div>

                        <div class="text-sm font-bold text-fg">
                            المعاينة الحالية
                        </div>

                        <div class="text-[11px] text-dim mt-1">
                            معاينة بيانات الشركة أثناء التعديل
                        </div>

                    </div>

                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-info/10 text-info">
                        <i class="fa-solid fa-eye"></i>
                    </span>

                </div>


                <div class="rounded-xl border border-white/5 bg-white/[0.02] p-4">

                    <div class="flex items-center gap-3">

                        <span class="relative flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-info/10 bg-info/10 text-info">

                            <i class="fa-solid fa-building"></i>

                            <span
                                id="previewStatusDot"
                                class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-[#14191e] {{ $statusDot }}"
                            ></span>

                        </span>


                        <div class="min-w-0">

                            <div
                                id="editPreviewName"
                                class="truncate text-sm font-bold text-fg"
                            >
                                {{ $companyName ?: 'اسم الشركة' }}
                            </div>


                            <div class="mt-1 flex items-center gap-2">

                                <span
                                    id="editPreviewCode"
                                    class="font-mono text-[10px] text-dim"
                                    dir="ltr"
                                >
                                    {{ $companyCode ?: 'CODE' }}
                                </span>

                                <span class="text-white/10">
                                    •
                                </span>

                                <span class="text-[10px] text-dim">
                                    شركة تقسيط
                                </span>

                            </div>

                        </div>

                    </div>


                    <div class="mt-4 grid grid-cols-2 gap-2">


                        <div class="rounded-lg border border-white/5 bg-white/[0.02] p-3">

                            <div class="text-[10px] text-dim">
                                الحالة
                            </div>

                            <div
                                id="editPreviewStatus"
                                class="mt-1 flex items-center gap-1.5 text-xs font-semibold {{ $statusClass }}"
                            >

                                <span class="h-1.5 w-1.5 rounded-full {{ $statusDot }}"></span>

                                {{ $statusText }}

                            </div>

                        </div>


                        <div class="rounded-lg border border-white/5 bg-white/[0.02] p-3">

                            <div class="text-[10px] text-dim">
                                الحالات
                            </div>

                            <div class="mt-1 text-xs font-semibold text-info">
                                {{ number_format($companyCases) }}
                            </div>

                        </div>


                    </div>

                </div>

            </div>


            {{-- ========================================================
                CHANGE CHECKLIST
            ========================================================= --}}
            <div class="card">

                <div class="flex items-center justify-between mb-4">

                    <div>

                        <div class="text-sm font-bold text-fg">
                            مراجعة التعديل
                        </div>

                        <div class="text-[11px] text-dim mt-1">
                            التحقق من البيانات الحالية
                        </div>

                    </div>

                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand/10 text-brand">
                        <i class="fa-solid fa-list-check"></i>
                    </span>

                </div>


                <div class="space-y-3">


                    {{-- NAME --}}
                    <div class="flex items-center justify-between gap-3">

                        <div class="flex items-center gap-2">

                            <span
                                id="editNameCheckIcon"
                                class="flex h-6 w-6 items-center justify-center rounded-md bg-brand/10 text-brand"
                            >
                                <i class="fa-solid fa-check text-[9px]"></i>
                            </span>

                            <span class="text-xs text-muted">
                                اسم الشركة
                            </span>

                        </div>

                        <span
                            id="editNameCheckText"
                            class="text-[10px] text-brand"
                        >
                            متوفر
                        </span>

                    </div>


                    {{-- CODE --}}
                    <div class="flex items-center justify-between gap-3">

                        <div class="flex items-center gap-2">

                            <span
                                id="editCodeCheckIcon"
                                class="flex h-6 w-6 items-center justify-center rounded-md bg-brand/10 text-brand"
                            >
                                <i class="fa-solid fa-check text-[9px]"></i>
                            </span>

                            <span class="text-xs text-muted">
                                كود الشركة
                            </span>

                        </div>

                        <span
                            id="editCodeCheckText"
                            class="text-[10px] text-brand"
                        >
                            متوفر
                        </span>

                    </div>


                    {{-- STATUS --}}
                    <div class="flex items-center justify-between gap-3">

                        <div class="flex items-center gap-2">

                            <span
                                id="editStatusCheckIcon"
                                class="flex h-6 w-6 items-center justify-center rounded-md {{ $statusBg }} {{ $statusClass }}"
                            >

                                @if($companyIsActive)

                                    <i class="fa-solid fa-check text-[9px]"></i>

                                @else

                                    <i class="fa-solid fa-pause text-[9px]"></i>

                                @endif

                            </span>

                            <span class="text-xs text-muted">
                                حالة الشركة
                            </span>

                        </div>

                        <span
                            id="editStatusCheckText"
                            class="text-[10px] {{ $statusClass }}"
                        >
                            {{ $statusText }}
                        </span>

                    </div>

                </div>

            </div>


            {{-- ========================================================
                CURRENT FINANCIAL SUMMARY
            ========================================================= --}}
            <div class="card">

                <div class="flex items-center justify-between mb-4">

                    <div>

                        <div class="text-sm font-bold text-fg">
                            ملخص مالي
                        </div>

                        <div class="text-[11px] text-dim mt-1">
                            البيانات الحالية للشركة
                        </div>

                    </div>

                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-accent/10 text-accent">
                        <i class="fa-solid fa-chart-column"></i>
                    </span>

                </div>


                <div class="space-y-3">


                    <div class="flex items-center justify-between">

                        <span class="text-xs text-dim">
                            عدد الحالات
                        </span>

                        <span class="font-semibold text-info">
                            {{ number_format($companyCases) }}
                        </span>

                    </div>


                    <div class="flex items-center justify-between">

                        <span class="text-xs text-dim">
                            إجمالي المديونية
                        </span>

                        <span class="font-semibold text-accent">
                            EGP {{ number_format($companyDebt, 0) }}
                        </span>

                    </div>


                    <div class="flex items-center justify-between border-t border-white/5 pt-3">

                        <span class="text-xs text-dim">
                            متوسط المديونية
                        </span>

                        <span class="font-semibold text-fg">
                            EGP {{ number_format($averageDebtPerCase, 0) }}
                        </span>

                    </div>

                </div>

            </div>


            {{-- ========================================================
                RECORD INFORMATION
            ========================================================= --}}
            <div class="card">

                <div class="flex items-center justify-between mb-4">

                    <div>

                        <div class="text-sm font-bold text-fg">
                            معلومات السجل
                        </div>

                        <div class="text-[11px] text-dim mt-1">
                            تواريخ إنشاء وتحديث الشركة
                        </div>

                    </div>

                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-white/5 text-dim">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </span>

                </div>


                <div class="space-y-3">


                    @if($createdAt)

                        <div class="flex items-center justify-between">

                            <span class="text-xs text-dim">
                                <i class="fa-regular fa-calendar ml-1"></i>
                                تاريخ الإنشاء
                            </span>

                            <span class="font-mono text-[10px] text-muted">
                                {{ $createdAt->format('Y-m-d H:i') }}
                            </span>

                        </div>

                    @endif


                    @if($updatedAt)

                        <div class="flex items-center justify-between">

                            <span class="text-xs text-dim">
                                <i class="fa-solid fa-clock-rotate-left ml-1"></i>
                                آخر تحديث
                            </span>

                            <span class="font-mono text-[10px] text-muted">
                                {{ $updatedAt->format('Y-m-d H:i') }}
                            </span>

                        </div>

                    @endif


                    <div class="flex items-center justify-between border-t border-white/5 pt-3">

                        <span class="text-xs text-dim">
                            معرف السجل
                        </span>

                        <span class="font-mono text-[10px] text-info">
                            #{{ $company->id }}
                        </span>

                    </div>

                </div>

            </div>


            {{-- ========================================================
                EDIT GUIDANCE
            ========================================================= --}}
            <div class="card">

                <div class="flex items-start gap-3">

                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-warning/10 text-warning">
                        <i class="fa-solid fa-lightbulb"></i>
                    </span>

                    <div>

                        <div class="text-sm font-semibold text-fg">
                            قبل حفظ التعديل
                        </div>

                        <ul class="mt-3 space-y-2 text-[11px] text-dim">

                            <li class="flex items-start gap-2">

                                <i class="fa-solid fa-check text-brand mt-0.5"></i>

                                <span>
                                    تأكد من اسم الشركة والكود.
                                </span>

                            </li>

                            <li class="flex items-start gap-2">

                                <i class="fa-solid fa-check text-brand mt-0.5"></i>

                                <span>
                                    راجع حالة الشركة قبل تغييرها.
                                </span>

                            </li>

                            <li class="flex items-start gap-2">

                                <i class="fa-solid fa-check text-brand mt-0.5"></i>

                                <span>
                                    لا تغيّر البيانات إلا إذا كانت التعديلات مؤكدة.
                                </span>

                            </li>

                        </ul>

                    </div>

                </div>

            </div>


            {{-- ========================================================
                KEYBOARD SHORTCUTS
            ========================================================= --}}
            <div class="card">

                <div class="flex items-center justify-between mb-3">

                    <div class="text-xs font-semibold text-fg">
                        اختصارات لوحة المفاتيح
                    </div>

                    <i class="fa-solid fa-keyboard text-dim"></i>

                </div>


                <div class="space-y-2">


                    <div class="flex items-center justify-between text-[10px] text-dim">

                        <span>
                            حفظ التعديلات
                        </span>

                        <kbd class="rounded border border-white/10 bg-white/5 px-2 py-1 font-mono text-[9px] text-muted">
                            Ctrl + Enter
                        </kbd>

                    </div>


                    <div class="flex items-center justify-between text-[10px] text-dim">

                        <span>
                            التركيز على أول حقل
                        </span>

                        <kbd class="rounded border border-white/10 bg-white/5 px-2 py-1 font-mono text-[9px] text-muted">
                            Ctrl + K
                        </kbd>

                    </div>


                    <div class="flex items-center justify-between text-[10px] text-dim">

                        <span>
                            استعادة البيانات
                        </span>

                        <kbd class="rounded border border-white/10 bg-white/5 px-2 py-1 font-mono text-[9px] text-muted">
                            Esc
                        </kbd>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ================================================================
        JAVASCRIPT
    ================================================================= --}}
    <script>

        document.addEventListener(
            'DOMContentLoaded',
            function () {

                const form =
                    document.getElementById(
                        'editInstallmentCompanyForm'
                    );

                if (!form) {
                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | FIND EXISTING FORM FIELDS
                |--------------------------------------------------------------------------
                */

                const nameInput =
                    form.querySelector(
                        '[name="name"]'
                    );

                const codeInput =
                    form.querySelector(
                        '[name="code"]'
                    );

                const statusInput =
                    form.querySelector(
                        '[name="is_active"]'
                    );


                /*
                |--------------------------------------------------------------------------
                | ORIGINAL VALUES
                |--------------------------------------------------------------------------
                */

                const originalValues = {};

                if (nameInput) {

                    originalValues.name =
                        nameInput.value;

                }

                if (codeInput) {

                    originalValues.code =
                        codeInput.value;

                }

                if (statusInput) {

                    if (
                        statusInput.type ===
                        'checkbox'
                    ) {

                        originalValues.is_active =
                            statusInput.checked;

                    } else {

                        originalValues.is_active =
                            statusInput.value;

                    }

                }


                /*
                |--------------------------------------------------------------------------
                | PREVIEW ELEMENTS
                |--------------------------------------------------------------------------
                */

                const previewName =
                    document.getElementById(
                        'editPreviewName'
                    );

                const previewCode =
                    document.getElementById(
                        'editPreviewCode'
                    );

                const previewStatus =
                    document.getElementById(
                        'editPreviewStatus'
                    );

                const previewStatusDot =
                    document.getElementById(
                        'previewStatusDot'
                    );


                /*
                |--------------------------------------------------------------------------
                | CHECKLIST
                |--------------------------------------------------------------------------
                */

                const nameCheckIcon =
                    document.getElementById(
                        'editNameCheckIcon'
                    );

                const nameCheckText =
                    document.getElementById(
                        'editNameCheckText'
                    );

                const codeCheckIcon =
                    document.getElementById(
                        'editCodeCheckIcon'
                    );

                const codeCheckText =
                    document.getElementById(
                        'editCodeCheckText'
                    );

                const statusCheckIcon =
                    document.getElementById(
                        'editStatusCheckIcon'
                    );

                const statusCheckText =
                    document.getElementById(
                        'editStatusCheckText'
                    );


                /*
                |--------------------------------------------------------------------------
                | GET STATUS
                |--------------------------------------------------------------------------
                */

                function getStatus() {

                    if (!statusInput) {
                        return true;
                    }


                    if (
                        statusInput.type ===
                        'checkbox'
                    ) {

                        return statusInput.checked;

                    }


                    return (
                        statusInput.value === '1' ||
                        statusInput.value === 'active'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | UPDATE PREVIEW
                |--------------------------------------------------------------------------
                */

                function updatePreview() {

                    /*
                    |--------------------------------------------------------------------------
                    | NAME
                    |--------------------------------------------------------------------------
                    */

                    if (
                        nameInput &&
                        previewName
                    ) {

                        const value =
                            nameInput.value.trim();

                        previewName.textContent =
                            value !== ''
                                ? value
                                : 'اسم الشركة';

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | CODE
                    |--------------------------------------------------------------------------
                    */

                    if (
                        codeInput &&
                        previewCode
                    ) {

                        const value =
                            codeInput.value.trim();

                        previewCode.textContent =
                            value !== ''
                                ? value.toUpperCase()
                                : 'CODE';

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | STATUS
                    |--------------------------------------------------------------------------
                    */

                    const active =
                        getStatus();


                    if (previewStatus) {

                        if (active) {

                            previewStatus.className =
                                'mt-1 flex items-center gap-1.5 text-xs font-semibold text-brand';

                            previewStatus.innerHTML =
                                '<span class="h-1.5 w-1.5 rounded-full bg-brand"></span>' +
                                'نشطة';

                        } else {

                            previewStatus.className =
                                'mt-1 flex items-center gap-1.5 text-xs font-semibold text-danger';

                            previewStatus.innerHTML =
                                '<span class="h-1.5 w-1.5 rounded-full bg-danger"></span>' +
                                'متوقفة';

                        }

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | STATUS DOT
                    |--------------------------------------------------------------------------
                    */

                    if (previewStatusDot) {

                        previewStatusDot.classList.remove(
                            'bg-brand',
                            'bg-danger'
                        );

                        previewStatusDot.classList.add(
                            active
                                ? 'bg-brand'
                                : 'bg-danger'
                        );

                    }


                    updateChecklist();

                }


                /*
                |--------------------------------------------------------------------------
                | UPDATE CHECKLIST
                |--------------------------------------------------------------------------
                */

                function updateChecklist() {


                    /*
                    |--------------------------------------------------------------------------
                    | NAME
                    |--------------------------------------------------------------------------
                    */

                    if (
                        nameInput &&
                        nameCheckIcon &&
                        nameCheckText
                    ) {

                        const valid =
                            nameInput.value.trim().length > 0;


                        if (valid) {

                            nameCheckIcon.className =
                                'flex h-6 w-6 items-center justify-center rounded-md bg-brand/10 text-brand';

                            nameCheckIcon.innerHTML =
                                '<i class="fa-solid fa-check text-[9px]"></i>';

                            nameCheckText.textContent =
                                'متوفر';

                            nameCheckText.className =
                                'text-[10px] text-brand';

                        } else {

                            nameCheckIcon.className =
                                'flex h-6 w-6 items-center justify-center rounded-md bg-danger/10 text-danger';

                            nameCheckIcon.innerHTML =
                                '<i class="fa-solid fa-xmark text-[9px]"></i>';

                            nameCheckText.textContent =
                                'مطلوب';

                            nameCheckText.className =
                                'text-[10px] text-danger';

                        }

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | CODE
                    |--------------------------------------------------------------------------
                    */

                    if (
                        codeInput &&
                        codeCheckIcon &&
                        codeCheckText
                    ) {

                        const valid =
                            codeInput.value.trim().length > 0;


                        if (valid) {

                            codeCheckIcon.className =
                                'flex h-6 w-6 items-center justify-center rounded-md bg-brand/10 text-brand';

                            codeCheckIcon.innerHTML =
                                '<i class="fa-solid fa-check text-[9px]"></i>';

                            codeCheckText.textContent =
                                'متوفر';

                            codeCheckText.className =
                                'text-[10px] text-brand';

                        } else {

                            codeCheckIcon.className =
                                'flex h-6 w-6 items-center justify-center rounded-md bg-danger/10 text-danger';

                            codeCheckIcon.innerHTML =
                                '<i class="fa-solid fa-xmark text-[9px]"></i>';

                            codeCheckText.textContent =
                                'غير محدد';

                            codeCheckText.className =
                                'text-[10px] text-danger';

                        }

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | STATUS
                    |--------------------------------------------------------------------------
                    */

                    if (
                        statusCheckIcon &&
                        statusCheckText
                    ) {

                        const active =
                            getStatus();


                        if (active) {

                            statusCheckIcon.className =
                                'flex h-6 w-6 items-center justify-center rounded-md bg-brand/10 text-brand';

                            statusCheckIcon.innerHTML =
                                '<i class="fa-solid fa-check text-[9px]"></i>';

                            statusCheckText.textContent =
                                'نشطة';

                            statusCheckText.className =
                                'text-[10px] text-brand';

                        } else {

                            statusCheckIcon.className =
                                'flex h-6 w-6 items-center justify-center rounded-md bg-danger/10 text-danger';

                            statusCheckIcon.innerHTML =
                                '<i class="fa-solid fa-pause text-[9px]"></i>';

                            statusCheckText.textContent =
                                'متوقفة';

                            statusCheckText.className =
                                'text-[10px] text-danger';

                        }

                    }

                }


                /*
                |--------------------------------------------------------------------------
                | LIVE NAME
                |--------------------------------------------------------------------------
                */

                if (nameInput) {

                    nameInput.addEventListener(
                        'input',
                        updatePreview
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | LIVE CODE
                |--------------------------------------------------------------------------
                */

                if (codeInput) {

                    codeInput.addEventListener(
                        'input',
                        function () {

                            this.value =
                                this.value
                                    .replace(/\s+/g, '')
                                    .toUpperCase();

                            updatePreview();

                        }
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | LIVE STATUS
                |--------------------------------------------------------------------------
                */

                if (statusInput) {

                    statusInput.addEventListener(
                        'change',
                        updatePreview
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | RESTORE ORIGINAL DATA
                |--------------------------------------------------------------------------
                */

                const resetButton =
                    document.getElementById(
                        'resetEditCompanyForm'
                    );


                if (resetButton) {

                    resetButton.addEventListener(
                        'click',
                        function () {

                            const confirmed =
                                confirm(
                                    'هل تريد استعادة البيانات الأصلية للشركة؟\n\nسيتم إلغاء التعديلات الحالية.'
                                );


                            if (!confirmed) {
                                return;
                            }


                            if (nameInput) {

                                nameInput.value =
                                    originalValues.name ?? '';

                            }


                            if (codeInput) {

                                codeInput.value =
                                    originalValues.code ?? '';

                            }


                            if (statusInput) {

                                if (
                                    statusInput.type ===
                                    'checkbox'
                                ) {

                                    statusInput.checked =
                                        originalValues.is_active;

                                } else {

                                    statusInput.value =
                                        originalValues.is_active;

                                }

                            }


                            updatePreview();

                        }
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | SUBMIT STATE
                |--------------------------------------------------------------------------
                */

                let submitting = false;


                const updateButton =
                    document.getElementById(
                        'updateCompanyButton'
                    );

                const updateIcon =
                    document.getElementById(
                        'updateCompanyIcon'
                    );

                const updateText =
                    document.getElementById(
                        'updateCompanyText'
                    );


                form.addEventListener(
                    'submit',
                    function (event) {

                        if (submitting) {

                            event.preventDefault();

                            return;

                        }


                        submitting = true;


                        if (updateButton) {

                            updateButton.disabled =
                                true;

                            updateButton.classList.add(
                                'opacity-70',
                                'cursor-not-allowed'
                            );

                        }


                        if (updateIcon) {

                            updateIcon.className =
                                'fa-solid fa-spinner fa-spin text-xs';

                        }


                        if (updateText) {

                            updateText.textContent =
                                'جاري حفظ التعديلات...';

                        }

                    }
                );


                /*
                |--------------------------------------------------------------------------
                | CTRL + ENTER
                |--------------------------------------------------------------------------
                */

                document.addEventListener(
                    'keydown',
                    function (event) {

                        if (
                            (event.ctrlKey || event.metaKey) &&
                            event.key === 'Enter'
                        ) {

                            event.preventDefault();


                            if (!submitting) {

                                form.requestSubmit();

                            }

                        }

                    }
                );


                /*
                |--------------------------------------------------------------------------
                | CTRL + K
                |--------------------------------------------------------------------------
                */

                document.addEventListener(
                    'keydown',
                    function (event) {

                        if (
                            (event.ctrlKey || event.metaKey) &&
                            event.key.toLowerCase() === 'k'
                        ) {

                            event.preventDefault();


                            const firstInput =
                                form.querySelector(
                                    'input:not([type="hidden"]):not([type="checkbox"]), textarea, select'
                                );


                            if (firstInput) {

                                firstInput.focus();

                                if (
                                    typeof firstInput.select ===
                                    'function'
                                ) {

                                    firstInput.select();

                                }

                            }

                        }

                    }
                );


                /*
                |--------------------------------------------------------------------------
                | ESC = RESTORE
                |--------------------------------------------------------------------------
                */

                document.addEventListener(
                    'keydown',
                    function (event) {

                        if (event.key !== 'Escape') {
                            return;
                        }


                        const activeElement =
                            document.activeElement;


                        /*
                        |--------------------------------------------------------------------------
                        | Don't interfere with SELECT.
                        |--------------------------------------------------------------------------
                        */

                        if (
                            activeElement &&
                            activeElement.tagName ===
                            'SELECT'
                        ) {

                            return;

                        }


                        const confirmed =
                            confirm(
                                'هل تريد إلغاء التعديلات واستعادة البيانات الأصلية؟'
                            );


                        if (!confirmed) {
                            return;
                        }


                        if (nameInput) {

                            nameInput.value =
                                originalValues.name ?? '';

                        }


                        if (codeInput) {

                            codeInput.value =
                                originalValues.code ?? '';

                        }


                        if (statusInput) {

                            if (
                                statusInput.type ===
                                'checkbox'
                            ) {

                                statusInput.checked =
                                    originalValues.is_active;

                            } else {

                                statusInput.value =
                                    originalValues.is_active;

                            }

                        }


                        updatePreview();

                    }
                );


                /*
                |--------------------------------------------------------------------------
                | INITIAL PREVIEW
                |--------------------------------------------------------------------------
                */

                updatePreview();

            });

    </script>

@endsection