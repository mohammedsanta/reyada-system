@extends('layouts.app')

@section('title', 'إضافة شركة تقسيط')

@section('content')

    {{-- ================================================================
        PAGE HEADER
    ================================================================= --}}
    <x-page-header
        title="إضافة شركة تقسيط"
        subtitle="إضافة شركة تقسيط جديدة إلى النظام"
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
                        يرجى مراجعة البيانات المدخلة
                    </div>

                    <div class="mt-1 text-[11px] text-dim">
                        يوجد {{ $errors->count() }} خطأ يحتاج إلى المراجعة قبل حفظ الشركة.
                    </div>

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
        MAIN CONTENT
        Keep the original card structure/style.
    ================================================================= --}}
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">

        {{-- ============================================================
            MAIN FORM
        ============================================================= --}}
        <div class="xl:col-span-2">

            <form
                method="POST"
                action="{{ route('installment-companies.store') }}"
                class="card max-w-xl space-y-4"
                id="installmentCompanyForm"
            >

                @csrf


                {{-- ====================================================
                    FORM HEADER
                ================================================= --}}
                <div class="mb-5 flex items-center justify-between gap-3 border-b border-white/5 pb-4">

                    <div class="flex items-center gap-3">

                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-info/10 text-info">
                            <i class="fa-solid fa-building"></i>
                        </span>

                        <div>

                            <div class="text-sm font-bold text-fg">
                                بيانات الشركة
                            </div>

                            <div class="mt-1 text-[11px] text-dim">
                                أدخل البيانات الأساسية لشركة التقسيط
                            </div>

                        </div>

                    </div>

                    <span
                        class="badge badge-outline-info"
                        title="جميع البيانات المطلوبة يجب إدخالها قبل الحفظ"
                    >
                        <i class="fa-solid fa-circle-info text-[9px]"></i>
                        بيانات أساسية
                    </span>

                </div>


                {{-- ====================================================
                    EXISTING FORM
                    The original reusable form remains untouched.
                ===================================================== --}}
                @include('installment-companies._form')


                {{-- ====================================================
                    FORM ACTIONS
                ===================================================== --}}
                <div class="mt-6 flex flex-wrap items-center justify-between gap-3 border-t border-white/5 pt-5">

                    <div class="text-[10px] text-dim">

                        <i class="fa-solid fa-shield-halved ml-1 text-brand"></i>

                        تأكد من صحة بيانات الشركة قبل الحفظ.

                    </div>


                    <div class="flex items-center gap-2">

                        <a
                            href="{{ route('installment-companies.index') }}"
                            class="btn btn-secondary"
                            title="العودة إلى قائمة شركات التقسيط"
                            aria-label="العودة إلى قائمة شركات التقسيط"
                        >
                            <i class="fa-solid fa-arrow-right text-xs"></i>
                            إلغاء
                        </a>


                        <button
                            type="button"
                            class="btn btn-secondary"
                            id="resetCompanyForm"
                            title="إعادة ضبط الحقول"
                            aria-label="إعادة ضبط الحقول"
                        >
                            <i class="fa-solid fa-rotate-right text-xs"></i>
                            إعادة ضبط
                        </button>


                        <button
                            type="submit"
                            class="btn btn-primary"
                            id="saveCompanyButton"
                            title="حفظ شركة التقسيط"
                            aria-label="حفظ شركة التقسيط"
                        >
                            <i
                                class="fa-solid fa-floppy-disk text-xs"
                                id="saveCompanyIcon"
                            ></i>

                            <span id="saveCompanyText">
                                حفظ الشركة
                            </span>
                        </button>

                    </div>

                </div>

            </form>

        </div>


        {{-- ============================================================
            SIDE INFORMATION / LIVE PREVIEW
        ============================================================= --}}
        <div class="space-y-5">


            {{-- ========================================================
                LIVE PREVIEW
            ========================================================= --}}
            <div class="card">

                <div class="flex items-center justify-between mb-4">

                    <div>

                        <div class="text-sm font-bold text-fg">
                            معاينة الشركة
                        </div>

                        <div class="text-[11px] text-dim mt-1">
                            شكل بيانات الشركة داخل النظام
                        </div>

                    </div>

                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-info/10 text-info">
                        <i class="fa-solid fa-eye"></i>
                    </span>

                </div>


                <div class="rounded-xl border border-white/5 bg-white/[0.02] p-4">

                    <div class="flex items-center gap-3">

                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-info/10 bg-info/10 text-info">

                            <i class="fa-solid fa-building"></i>

                        </span>


                        <div class="min-w-0">

                            <div
                                id="companyPreviewName"
                                class="truncate text-sm font-bold text-fg"
                            >
                                اسم الشركة
                            </div>

                            <div class="mt-1 flex items-center gap-2">

                                <span
                                    id="companyPreviewCode"
                                    class="font-mono text-[10px] text-dim"
                                    dir="ltr"
                                >
                                    CODE
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
                                id="companyPreviewStatus"
                                class="mt-1 flex items-center gap-1.5 text-xs font-semibold text-brand"
                            >
                                <span class="h-1.5 w-1.5 rounded-full bg-brand"></span>
                                نشطة
                            </div>

                        </div>


                        <div class="rounded-lg border border-white/5 bg-white/[0.02] p-3">

                            <div class="text-[10px] text-dim">
                                النوع
                            </div>

                            <div class="mt-1 text-xs font-semibold text-info">
                                تقسيط
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- ========================================================
                FORM CHECKLIST
            ========================================================= --}}
            <div class="card">

                <div class="flex items-center justify-between mb-4">

                    <div>

                        <div class="text-sm font-bold text-fg">
                            مراجعة البيانات
                        </div>

                        <div class="text-[11px] text-dim mt-1">
                            تأكد من البيانات قبل الحفظ
                        </div>

                    </div>

                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand/10 text-brand">
                        <i class="fa-solid fa-list-check"></i>
                    </span>

                </div>


                <div class="space-y-3">

                    <div class="flex items-center justify-between gap-3">

                        <div class="flex items-center gap-2">

                            <span
                                id="nameCheckIcon"
                                class="flex h-6 w-6 items-center justify-center rounded-md bg-white/5 text-dim"
                            >
                                <i class="fa-solid fa-minus text-[9px]"></i>
                            </span>

                            <span class="text-xs text-muted">
                                اسم الشركة
                            </span>

                        </div>

                        <span
                            id="nameCheckText"
                            class="text-[10px] text-dim"
                        >
                            لم يتم الإدخال
                        </span>

                    </div>


                    <div class="flex items-center justify-between gap-3">

                        <div class="flex items-center gap-2">

                            <span
                                id="codeCheckIcon"
                                class="flex h-6 w-6 items-center justify-center rounded-md bg-white/5 text-dim"
                            >
                                <i class="fa-solid fa-minus text-[9px]"></i>
                            </span>

                            <span class="text-xs text-muted">
                                كود الشركة
                            </span>

                        </div>

                        <span
                            id="codeCheckText"
                            class="text-[10px] text-dim"
                        >
                            لم يتم الإدخال
                        </span>

                    </div>


                    <div class="flex items-center justify-between gap-3">

                        <div class="flex items-center gap-2">

                            <span
                                id="statusCheckIcon"
                                class="flex h-6 w-6 items-center justify-center rounded-md bg-brand/10 text-brand"
                            >
                                <i class="fa-solid fa-check text-[9px]"></i>
                            </span>

                            <span class="text-xs text-muted">
                                حالة الشركة
                            </span>

                        </div>

                        <span class="text-[10px] text-brand">
                            نشطة
                        </span>

                    </div>

                </div>

            </div>


            {{-- ========================================================
                INFORMATION CARD
            ========================================================= --}}
            <div class="card">

                <div class="flex items-start gap-3">

                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-info/10 text-info">
                        <i class="fa-solid fa-circle-info"></i>
                    </span>

                    <div>

                        <div class="text-sm font-semibold text-fg">
                            نصائح الإدخال
                        </div>

                        <ul class="mt-3 space-y-2 text-[11px] text-dim">

                            <li class="flex items-start gap-2">
                                <i class="fa-solid fa-check text-brand mt-0.5"></i>
                                <span>
                                    استخدم اسم الشركة الرسمي والواضح.
                                </span>
                            </li>

                            <li class="flex items-start gap-2">
                                <i class="fa-solid fa-check text-brand mt-0.5"></i>
                                <span>
                                    اجعل كود الشركة مختصرًا وسهل التمييز.
                                </span>
                            </li>

                            <li class="flex items-start gap-2">
                                <i class="fa-solid fa-check text-brand mt-0.5"></i>
                                <span>
                                    تأكد من عدم وجود شركة بنفس الكود.
                                </span>
                            </li>

                            <li class="flex items-start gap-2">
                                <i class="fa-solid fa-check text-brand mt-0.5"></i>
                                <span>
                                    راجع البيانات قبل الضغط على حفظ.
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
                            حفظ الشركة
                        </span>

                        <kbd class="rounded border border-white/10 bg-white/5 px-2 py-1 font-mono text-[9px] text-muted">
                            Ctrl + Enter
                        </kbd>

                    </div>

                    <div class="flex items-center justify-between text-[10px] text-dim">

                        <span>
                            البحث داخل الحقول
                        </span>

                        <kbd class="rounded border border-white/10 bg-white/5 px-2 py-1 font-mono text-[9px] text-muted">
                            Ctrl + K
                        </kbd>

                    </div>

                    <div class="flex items-center justify-between text-[10px] text-dim">

                        <span>
                            إعادة ضبط
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

        document.addEventListener('DOMContentLoaded', function () {

            const form =
                document.getElementById(
                    'installmentCompanyForm'
                );

            if (!form) {
                return;
            }


            /*
            |--------------------------------------------------------------------------
            | TRY TO FIND EXISTING FORM FIELDS
            |--------------------------------------------------------------------------
            | We intentionally don't change _form.blade.php.
            | These selectors support common Laravel field names.
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
            | PREVIEW ELEMENTS
            |--------------------------------------------------------------------------
            */

            const previewName =
                document.getElementById(
                    'companyPreviewName'
                );

            const previewCode =
                document.getElementById(
                    'companyPreviewCode'
                );

            const previewStatus =
                document.getElementById(
                    'companyPreviewStatus'
                );


            /*
            |--------------------------------------------------------------------------
            | CHECKLIST ELEMENTS
            |--------------------------------------------------------------------------
            */

            const nameCheckIcon =
                document.getElementById(
                    'nameCheckIcon'
                );

            const nameCheckText =
                document.getElementById(
                    'nameCheckText'
                );

            const codeCheckIcon =
                document.getElementById(
                    'codeCheckIcon'
                );

            const codeCheckText =
                document.getElementById(
                    'codeCheckText'
                );


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

                if (nameInput && previewName) {

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

                if (codeInput && previewCode) {

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

                if (previewStatus) {

                    let active = true;

                    if (statusInput) {

                        if (
                            statusInput.type === 'checkbox'
                        ) {

                            active =
                                statusInput.checked;

                        } else {

                            active =
                                statusInput.value === '1' ||
                                statusInput.value === 'active';

                        }

                    }

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


                updateChecklist();

            }


            /*
            |--------------------------------------------------------------------------
            | CHECKLIST
            |--------------------------------------------------------------------------
            */

            function updateChecklist() {

                /*
                |--------------------------------------------------------------------------
                | NAME CHECK
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
                            'تم الإدخال';

                        nameCheckText.className =
                            'text-[10px] text-brand';

                    } else {

                        nameCheckIcon.className =
                            'flex h-6 w-6 items-center justify-center rounded-md bg-white/5 text-dim';

                        nameCheckIcon.innerHTML =
                            '<i class="fa-solid fa-minus text-[9px]"></i>';

                        nameCheckText.textContent =
                            'لم يتم الإدخال';

                        nameCheckText.className =
                            'text-[10px] text-dim';

                    }

                }


                /*
                |--------------------------------------------------------------------------
                | CODE CHECK
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
                            'تم الإدخال';

                        codeCheckText.className =
                            'text-[10px] text-brand';

                    } else {

                        codeCheckIcon.className =
                            'flex h-6 w-6 items-center justify-center rounded-md bg-white/5 text-dim';

                        codeCheckIcon.innerHTML =
                            '<i class="fa-solid fa-minus text-[9px]"></i>';

                        codeCheckText.textContent =
                            'لم يتم الإدخال';

                        codeCheckText.className =
                            'text-[10px] text-dim';

                    }

                }

            }


            /*
            |--------------------------------------------------------------------------
            | LIVE FIELD EVENTS
            |--------------------------------------------------------------------------
            */

            if (nameInput) {

                nameInput.addEventListener(
                    'input',
                    updatePreview
                );

            }


            if (codeInput) {

                codeInput.addEventListener(
                    'input',
                    function () {

                        /*
                        |--------------------------------------------------------------------------
                        | Normalize company code
                        |--------------------------------------------------------------------------
                        */

                        this.value =
                            this.value
                                .replace(/\s+/g, '')
                                .toUpperCase();

                        updatePreview();

                    }
                );

            }


            if (statusInput) {

                statusInput.addEventListener(
                    'change',
                    updatePreview
                );

            }


            /*
            |--------------------------------------------------------------------------
            | RESET FORM
            |--------------------------------------------------------------------------
            */

            const resetButton =
                document.getElementById(
                    'resetCompanyForm'
                );

            if (resetButton) {

                resetButton.addEventListener(
                    'click',
                    function () {

                        const confirmed =
                            confirm(
                                'هل تريد إعادة ضبط جميع بيانات الشركة؟'
                            );

                        if (!confirmed) {
                            return;
                        }

                        form.reset();

                        updatePreview();

                    }
                );

            }


            /*
            |--------------------------------------------------------------------------
            | SUBMIT LOADING STATE
            |--------------------------------------------------------------------------
            */

            let formSubmitting = false;

            const saveButton =
                document.getElementById(
                    'saveCompanyButton'
                );

            const saveIcon =
                document.getElementById(
                    'saveCompanyIcon'
                );

            const saveText =
                document.getElementById(
                    'saveCompanyText'
                );


            form.addEventListener(
                'submit',
                function (event) {

                    /*
                    |--------------------------------------------------------------------------
                    | Prevent accidental double submit
                    |--------------------------------------------------------------------------
                    */

                    if (formSubmitting) {

                        event.preventDefault();

                        return;

                    }


                    formSubmitting = true;


                    if (saveButton) {

                        saveButton.disabled = true;

                        saveButton.classList.add(
                            'opacity-70',
                            'cursor-not-allowed'
                        );

                    }


                    if (saveIcon) {

                        saveIcon.className =
                            'fa-solid fa-spinner fa-spin text-xs';

                    }


                    if (saveText) {

                        saveText.textContent =
                            'جاري الحفظ...';

                    }

                }
            );


            /*
            |--------------------------------------------------------------------------
            | CTRL + ENTER = SUBMIT
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

                        if (!formSubmitting) {

                            form.requestSubmit();

                        }

                    }

                }
            );


            /*
            |--------------------------------------------------------------------------
            | ESC = RESET
            |--------------------------------------------------------------------------
            */

            document.addEventListener(
                'keydown',
                function (event) {

                    if (event.key !== 'Escape') {
                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Don't reset if user is inside a select/modal-like control.
                    |--------------------------------------------------------------------------
                    */

                    const activeElement =
                        document.activeElement;

                    if (
                        activeElement &&
                        (
                            activeElement.tagName === 'SELECT'
                        )
                    ) {

                        return;

                    }


                    const confirmed =
                        confirm(
                            'هل تريد إعادة ضبط بيانات الشركة؟'
                        );

                    if (!confirmed) {
                        return;
                    }

                    form.reset();

                    updatePreview();

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


                        /*
                        |--------------------------------------------------------------------------
                        | Focus first useful field
                        |--------------------------------------------------------------------------
                        */

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
            | INITIAL STATE
            |--------------------------------------------------------------------------
            */

            updatePreview();

        });

    </script>

@endsection