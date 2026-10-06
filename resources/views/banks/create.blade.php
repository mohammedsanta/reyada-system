{{-- resources/views/banks/create.blade.php --}}
@extends('layouts.app')

@section('title', 'إضافة بنك')

@section('content')

    {{-- ================================================================
        PAGE HEADER
    ================================================================= --}}
    <x-page-header
        title="إضافة بنك"
        subtitle="إضافة بنك جديد إلى النظام"
        icon="fa-building-columns"
    />

    <x-flash />


    {{-- ================================================================
        VALIDATION ERROR SUMMARY
    ================================================================= --}}
    @if($errors->any())

        <div class="card mb-6 border-danger/20 bg-danger/[0.03]">

            <div class="flex items-start gap-3">

                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-danger/10 text-danger">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </span>

                <div class="min-w-0">

                    <div class="text-sm font-bold text-fg">
                        تعذر حفظ بيانات البنك
                    </div>

                    <div class="mt-1 text-[11px] text-dim">
                        يرجى مراجعة البيانات التالية ثم المحاولة مرة أخرى.
                    </div>

                    <ul class="mt-3 space-y-1">

                        @foreach($errors->all() as $error)

                            <li class="flex items-start gap-2 text-xs text-danger">

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
        MAIN FORM LAYOUT
    ================================================================= --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">


        {{-- ============================================================
            FORM
        ============================================================= --}}
        <div class="xl:col-span-2">

            <form
                method="POST"
                action="{{ route('banks.store') }}"
                class="card space-y-6"
                id="bankCreateForm"
            >

                @csrf


                {{-- ====================================================
                    BASIC INFORMATION
                ===================================================== --}}
                <div>

                    <div class="mb-4 flex items-center justify-between gap-3">

                        <div>

                            <div class="flex items-center gap-2 text-sm font-bold text-fg">

                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-info/10 text-info">
                                    <i class="fa-solid fa-building-columns text-xs"></i>
                                </span>

                                بيانات البنك

                            </div>

                            <div class="mt-1 text-[11px] text-dim">
                                أدخل البيانات الأساسية الخاصة بالبنك.
                            </div>

                        </div>

                        <span class="badge badge-outline-info">
                            بيانات أساسية
                        </span>

                    </div>


                    {{-- =================================================
                        BANK NAME
                    ================================================== --}}
                    <div class="relative">

                        <x-form-field
                            name="name"
                            label="اسم البنك"
                            placeholder="مثال: بنك مصر"
                        />

                        <div class="mt-1 flex items-center justify-between gap-2">

                            <span class="text-[10px] text-dim">
                                <i class="fa-solid fa-circle-info ml-1 text-info"></i>
                                استخدم الاسم الرسمي للبنك.
                            </span>

                            <span
                                id="bankNameCounter"
                                class="text-[10px] text-dim"
                            >
                                0 حرف
                            </span>

                        </div>

                    </div>


                    {{-- =================================================
                        BANK CODE
                    ================================================== --}}
                    <div class="mt-4">

                        <x-form-field
                            name="code"
                            label="الكود"
                            placeholder="مثال: BM"
                        />

                        <div class="mt-1 flex flex-wrap items-center justify-between gap-2">

                            <span class="text-[10px] text-dim">

                                <i class="fa-solid fa-key ml-1 text-info"></i>

                                استخدم كودًا مختصرًا وفريدًا للبنك.

                            </span>

                            <span
                                id="bankCodeCounter"
                                class="text-[10px] text-dim"
                            >
                                0 حرف
                            </span>

                        </div>

                    </div>


                    {{-- =================================================
                        LOGO
                    ================================================== --}}
                    <div class="mt-4">

                        <label
                            for="bankLogo"
                            class="form-label"
                        >

                            <i class="fa-solid fa-image ml-1 text-info"></i>

                            شعار البنك

                            <span class="text-[10px] text-dim">
                                اختياري
                            </span>

                        </label>

                        <input
                            id="bankLogo"
                            name="logo"
                            type="url"
                            value="{{ old('logo') }}"
                            placeholder="https://example.com/logo.png"
                            class="form-input"
                            autocomplete="url"
                            dir="ltr"
                        >

                        <div class="mt-1 flex items-center justify-between gap-2">

                            <span class="text-[10px] text-dim">
                                أدخل رابط مباشر لصورة شعار البنك.
                            </span>

                            <button
                                type="button"
                                id="clearLogoButton"
                                class="hidden text-[10px] text-dim transition hover:text-danger"
                            >
                                <i class="fa-solid fa-xmark ml-1"></i>
                                مسح
                            </button>

                        </div>

                    </div>


                    {{-- =================================================
                        STATUS
                    ================================================== --}}
                    <div class="mt-5 rounded-xl border border-white/5 bg-white/[0.02] p-4">

                        <div class="flex items-center justify-between gap-3">

                            <div>

                                <div class="text-xs font-semibold text-fg">
                                    حالة البنك
                                </div>

                                <div class="mt-1 text-[10px] text-dim">
                                    هل البنك متاح للعمل داخل النظام؟
                                </div>

                            </div>

                            <label
                                class="inline-flex cursor-pointer items-center gap-3"
                                for="bankIsActive"
                            >

                                <span
                                    id="statusLabel"
                                    class="text-xs font-semibold text-brand"
                                >
                                    نشط
                                </span>

                                <input
                                    type="checkbox"
                                    id="bankIsActive"
                                    name="is_active"
                                    value="1"
                                    class="peer sr-only"
                                    @checked(old('is_active', true))
                                >

                                <span
                                    class="relative h-6 w-11 rounded-full bg-white/10 transition peer-checked:bg-brand/30"
                                >

                                    <span
                                        class="absolute right-1 top-1 h-4 w-4 rounded-full bg-dim transition peer-checked:translate-x-[-20px] peer-checked:bg-brand"
                                    ></span>

                                </span>

                            </label>

                        </div>

                    </div>

                </div>


                {{-- ====================================================
                    FORM DIVIDER
                ===================================================== --}}
                <div class="border-t border-white/5"></div>


                {{-- ====================================================
                    FORM ACTIONS
                ===================================================== --}}
                <div class="flex flex-wrap items-center justify-between gap-3 pt-1">

                    <div class="flex items-center gap-2">

                        <span class="h-2 w-2 rounded-full bg-danger"></span>

                        <span class="text-[10px] text-dim">
                            الحقول الأساسية مطلوبة
                        </span>

                    </div>


                    <div class="flex gap-2">

                        <a
                            href="{{ route('banks.index') }}"
                            class="btn btn-secondary"
                            id="cancelBankButton"
                        >
                            <i class="fa-solid fa-arrow-right"></i>
                            إلغاء
                        </a>

                        <button
                            type="reset"
                            class="btn btn-secondary"
                            id="resetBankButton"
                            title="مسح البيانات المدخلة"
                        >
                            <i class="fa-solid fa-rotate-left"></i>
                            إعادة ضبط
                        </button>

                        <button
                            type="submit"
                            class="btn btn-primary"
                            id="saveBankButton"
                        >
                            <i class="fa-solid fa-floppy-disk"></i>
                            <span id="saveBankText">
                                حفظ البنك
                            </span>
                        </button>

                    </div>

                </div>

            </form>

        </div>


        {{-- ============================================================
            PREVIEW / INFORMATION
        ============================================================= --}}
        <div class="space-y-6">


            {{-- ========================================================
                LIVE PREVIEW
            ========================================================= --}}
            <div class="card">

                <div class="flex items-center justify-between mb-4">

                    <div>

                        <div class="text-sm font-bold text-fg">
                            معاينة البنك
                        </div>

                        <div class="text-[11px] text-dim mt-1">
                            ستتغير المعاينة أثناء إدخال البيانات.
                        </div>

                    </div>

                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-info/15 text-info">
                        <i class="fa-solid fa-eye"></i>
                    </span>

                </div>


                {{-- Preview --}}
                <div class="rounded-xl border border-white/5 bg-white/[0.02] p-4">

                    <div class="flex items-center gap-3">

                        <span
                            id="previewLogo"
                            class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-white text-black shadow-sm"
                        >
                            <i
                                id="previewLogoIcon"
                                class="fa-solid fa-building-columns"
                            ></i>

                            <img
                                id="previewLogoImage"
                                src=""
                                alt=""
                                class="hidden h-full w-full object-contain p-1.5"
                            >
                        </span>


                        <div class="min-w-0">

                            <div
                                id="previewName"
                                class="truncate text-sm font-bold text-fg"
                            >
                                اسم البنك
                            </div>

                            <div class="mt-1 flex items-center gap-2">

                                <span
                                    id="previewCode"
                                    class="font-mono text-[10px] text-dim"
                                    dir="ltr"
                                >
                                    CODE
                                </span>

                                <span class="text-white/10">
                                    •
                                </span>

                                <span
                                    id="previewStatus"
                                    class="text-[10px] font-semibold text-brand"
                                >
                                    نشط
                                </span>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- Preview details --}}
                <div class="mt-4 space-y-2">

                    <div class="flex items-center justify-between">

                        <span class="text-[11px] text-dim">
                            الاسم
                        </span>

                        <span
                            id="previewNameDetail"
                            class="max-w-[180px] truncate text-[11px] text-muted"
                        >
                            -
                        </span>

                    </div>

                    <div class="flex items-center justify-between">

                        <span class="text-[11px] text-dim">
                            الكود
                        </span>

                        <span
                            id="previewCodeDetail"
                            class="font-mono text-[11px] text-muted"
                            dir="ltr"
                        >
                            -
                        </span>

                    </div>

                    <div class="flex items-center justify-between">

                        <span class="text-[11px] text-dim">
                            الحالة
                        </span>

                        <span
                            id="previewStatusDetail"
                            class="text-[11px] font-semibold text-brand"
                        >
                            نشط
                        </span>

                    </div>

                </div>

            </div>


            {{-- ========================================================
                QUICK INFORMATION
            ========================================================= --}}
            <div class="card">

                <div class="flex items-center justify-between mb-4">

                    <div>

                        <div class="text-sm font-bold text-fg">
                            إرشادات الإدخال
                        </div>

                        <div class="text-[11px] text-dim mt-1">
                            نصائح قبل حفظ البنك.
                        </div>

                    </div>

                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand/15 text-brand">
                        <i class="fa-solid fa-lightbulb"></i>
                    </span>

                </div>


                <div class="space-y-3">

                    <div class="flex items-start gap-3">

                        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-info/10 text-info">
                            <i class="fa-solid fa-signature text-[10px]"></i>
                        </span>

                        <div>

                            <div class="text-[11px] font-semibold text-fg">
                                اسم واضح
                            </div>

                            <div class="mt-0.5 text-[10px] leading-5 text-dim">
                                استخدم الاسم الرسمي للبنك لتسهيل البحث والإدارة.
                            </div>

                        </div>

                    </div>


                    <div class="flex items-start gap-3">

                        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-warning/10 text-warning">
                            <i class="fa-solid fa-key text-[10px]"></i>
                        </span>

                        <div>

                            <div class="text-[11px] font-semibold text-fg">
                                كود مختصر
                            </div>

                            <div class="mt-0.5 text-[10px] leading-5 text-dim">
                                يفضل استخدام كود قصير وسهل التمييز.
                            </div>

                        </div>

                    </div>


                    <div class="flex items-start gap-3">

                        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-brand/10 text-brand">
                            <i class="fa-solid fa-circle-check text-[10px]"></i>
                        </span>

                        <div>

                            <div class="text-[11px] font-semibold text-fg">
                                حالة البنك
                            </div>

                            <div class="mt-0.5 text-[10px] leading-5 text-dim">
                                يمكنك إنشاء البنك كمتوقف ثم تفعيله لاحقًا.
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- ========================================================
                SHORTCUTS
            ========================================================= --}}
            <div class="card">

                <div class="flex items-center gap-2 text-sm font-bold text-fg">

                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-accent/10 text-accent">
                        <i class="fa-solid fa-keyboard text-xs"></i>
                    </span>

                    اختصارات لوحة المفاتيح

                </div>

                <div class="mt-4 space-y-2">

                    <div class="flex items-center justify-between">

                        <span class="text-[11px] text-dim">
                            التركيز على اسم البنك
                        </span>

                        <kbd class="rounded-md border border-white/10 bg-white/[0.03] px-2 py-1 font-mono text-[9px] text-muted">
                            Ctrl + K
                        </kbd>

                    </div>

                    <div class="flex items-center justify-between">

                        <span class="text-[11px] text-dim">
                            حفظ النموذج
                        </span>

                        <kbd class="rounded-md border border-white/10 bg-white/[0.03] px-2 py-1 font-mono text-[9px] text-muted">
                            Ctrl + Enter
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

            /*
            |--------------------------------------------------------------------------
            | ELEMENTS
            |--------------------------------------------------------------------------
            */

            const form =
                document.getElementById('bankCreateForm');

            const nameInput =
                document.getElementById('name');

            const codeInput =
                document.getElementById('code');

            const logoInput =
                document.getElementById('bankLogo');

            const activeInput =
                document.getElementById('bankIsActive');

            const nameCounter =
                document.getElementById('bankNameCounter');

            const codeCounter =
                document.getElementById('bankCodeCounter');

            const statusLabel =
                document.getElementById('statusLabel');

            const previewName =
                document.getElementById('previewName');

            const previewCode =
                document.getElementById('previewCode');

            const previewStatus =
                document.getElementById('previewStatus');

            const previewNameDetail =
                document.getElementById('previewNameDetail');

            const previewCodeDetail =
                document.getElementById('previewCodeDetail');

            const previewStatusDetail =
                document.getElementById('previewStatusDetail');

            const previewLogoIcon =
                document.getElementById('previewLogoIcon');

            const previewLogoImage =
                document.getElementById('previewLogoImage');

            const clearLogoButton =
                document.getElementById('clearLogoButton');

            const resetButton =
                document.getElementById('resetBankButton');

            const saveButton =
                document.getElementById('saveBankButton');

            const saveText =
                document.getElementById('saveBankText');


            /*
            |--------------------------------------------------------------------------
            | UPDATE NAME PREVIEW
            |--------------------------------------------------------------------------
            */
            function updateNamePreview() {

                if (!nameInput) {
                    return;
                }

                const value =
                    nameInput.value.trim();

                if (nameCounter) {

                    nameCounter.textContent =
                        value.length + ' حرف';

                }

                if (previewName) {

                    previewName.textContent =
                        value || 'اسم البنك';

                }

                if (previewNameDetail) {

                    previewNameDetail.textContent =
                        value || '-';

                }

            }


            /*
            |--------------------------------------------------------------------------
            | UPDATE CODE PREVIEW
            |--------------------------------------------------------------------------
            */
            function updateCodePreview() {

                if (!codeInput) {
                    return;
                }

                const value =
                    codeInput.value.trim();

                if (codeCounter) {

                    codeCounter.textContent =
                        value.length + ' حرف';

                }

                if (previewCode) {

                    previewCode.textContent =
                        value || 'CODE';

                }

                if (previewCodeDetail) {

                    previewCodeDetail.textContent =
                        value || '-';

                }

            }


            /*
            |--------------------------------------------------------------------------
            | UPDATE STATUS
            |--------------------------------------------------------------------------
            */
            function updateStatusPreview() {

                if (!activeInput) {
                    return;
                }

                const active =
                    activeInput.checked;

                if (statusLabel) {

                    statusLabel.textContent =
                        active ? 'نشط' : 'متوقف';

                    statusLabel.className =
                        active
                            ? 'text-xs font-semibold text-brand'
                            : 'text-xs font-semibold text-danger';

                }

                if (previewStatus) {

                    previewStatus.textContent =
                        active ? 'نشط' : 'متوقف';

                    previewStatus.className =
                        active
                            ? 'text-[10px] font-semibold text-brand'
                            : 'text-[10px] font-semibold text-danger';

                }

                if (previewStatusDetail) {

                    previewStatusDetail.textContent =
                        active ? 'نشط' : 'متوقف';

                    previewStatusDetail.className =
                        active
                            ? 'text-[11px] font-semibold text-brand'
                            : 'text-[11px] font-semibold text-danger';

                }

            }


            /*
            |--------------------------------------------------------------------------
            | UPDATE LOGO PREVIEW
            |--------------------------------------------------------------------------
            */
            function updateLogoPreview() {

                if (!logoInput) {
                    return;
                }

                const value =
                    logoInput.value.trim();

                if (!value) {

                    previewLogoImage.classList.add('hidden');

                    previewLogoIcon.classList.remove('hidden');

                    clearLogoButton.classList.add('hidden');

                    return;

                }

                previewLogoImage.src = value;

                previewLogoImage.classList.remove('hidden');

                previewLogoIcon.classList.add('hidden');

                clearLogoButton.classList.remove('hidden');

            }


            /*
            |--------------------------------------------------------------------------
            | LOGO ERROR FALLBACK
            |--------------------------------------------------------------------------
            */
            if (previewLogoImage) {

                previewLogoImage.addEventListener(
                    'error',
                    function () {

                        previewLogoImage.classList.add('hidden');

                        previewLogoIcon.classList.remove('hidden');

                    }
                );

            }


            /*
            |--------------------------------------------------------------------------
            | INPUT EVENTS
            |--------------------------------------------------------------------------
            */
            if (nameInput) {

                nameInput.addEventListener(
                    'input',
                    updateNamePreview
                );

            }


            if (codeInput) {

                codeInput.addEventListener(
                    'input',
                    function () {

                        /*
                        |--------------------------------------------------------------------------
                        | Keep code clean
                        |--------------------------------------------------------------------------
                        */
                        const cursorPosition =
                            codeInput.selectionStart;

                        const original =
                            codeInput.value;

                        const cleaned =
                            original
                                .replace(/\s+/g, '')
                                .toUpperCase();

                        if (original !== cleaned) {

                            codeInput.value =
                                cleaned;

                            try {

                                codeInput.setSelectionRange(
                                    cursorPosition,
                                    cursorPosition
                                );

                            } catch (error) {}

                        }

                        updateCodePreview();

                    }
                );

            }


            if (logoInput) {

                logoInput.addEventListener(
                    'input',
                    updateLogoPreview
                );

            }


            if (activeInput) {

                activeInput.addEventListener(
                    'change',
                    updateStatusPreview
                );

            }


            /*
            |--------------------------------------------------------------------------
            | CLEAR LOGO
            |--------------------------------------------------------------------------
            */
            if (clearLogoButton) {

                clearLogoButton.addEventListener(
                    'click',
                    function () {

                        logoInput.value = '';

                        updateLogoPreview();

                        logoInput.focus();

                    }
                );

            }


            /*
            |--------------------------------------------------------------------------
            | RESET
            |--------------------------------------------------------------------------
            */
            if (resetButton) {

                resetButton.addEventListener(
                    'click',
                    function () {

                        setTimeout(
                            function () {

                                updateNamePreview();

                                updateCodePreview();

                                updateStatusPreview();

                                updateLogoPreview();

                            },
                            10
                        );

                    }
                );

            }


            /*
            |--------------------------------------------------------------------------
            | FORM SUBMIT PROTECTION
            |--------------------------------------------------------------------------
            */
            if (form) {

                form.addEventListener(
                    'submit',
                    function (event) {

                        /*
                        |--------------------------------------------------------------------------
                        | Prevent accidental double submit
                        |--------------------------------------------------------------------------
                        */
                        if (form.dataset.submitting === 'true') {

                            event.preventDefault();

                            return;

                        }

                        form.dataset.submitting = 'true';


                        /*
                        |--------------------------------------------------------------------------
                        | Loading state
                        |--------------------------------------------------------------------------
                        */
                        if (saveButton) {

                            saveButton.disabled = true;

                            saveButton.classList.add(
                                'opacity-70',
                                'cursor-not-allowed'
                            );

                        }

                        if (saveText) {

                            saveText.textContent =
                                'جاري الحفظ...';

                        }

                        if (saveButton) {

                            const icon =
                                saveButton.querySelector('i');

                            if (icon) {

                                icon.className =
                                    'fa-solid fa-spinner fa-spin';

                            }

                        }

                    }
                );

            }


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

                        if (nameInput) {

                            nameInput.focus();

                            nameInput.select();

                        }

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

                        if (form) {

                            form.requestSubmit();

                        }

                    }

                }
            );


            /*
            |--------------------------------------------------------------------------
            | INITIAL STATE
            |--------------------------------------------------------------------------
            */
            updateNamePreview();

            updateCodePreview();

            updateStatusPreview();

            updateLogoPreview();

        });

    </script>

@endsection