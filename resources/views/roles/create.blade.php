{{-- resources/views/roles/create.blade.php --}}
@extends('layouts.app')

@section('title', 'إضافة رتبة نظامية')

@section('content')

    @php
        /*
        |--------------------------------------------------------------------------
        | Safe defaults
        |--------------------------------------------------------------------------
        | We keep this page compatible with the existing roles._form.
        | No new database fields or routes are required here.
        */

        $roleName = old('name', '');
        $roleDescription = old('description', '');

        $hasValidationErrors = $errors->any();
    @endphp

    {{-- ================================================================
         PAGE HEADER
         ================================================================ --}}
    <x-page-header
        title="إضافة رتبة نظامية"
        subtitle="حدد اسم الرتبة وصلاحياتها"
        icon="fa-user-shield"
    >
        <x-slot:actions>
            <a
                href="{{ route('roles.index') }}"
                class="btn btn-secondary btn-sm"
                id="backButton"
            >
                <i class="fa-solid fa-arrow-right text-xs"></i>
                رجوع
            </a>
        </x-slot:actions>
    </x-page-header>


    {{-- ================================================================
         FLASH MESSAGE
         ================================================================ --}}
    @if(session('success'))
        <div class="mb-4">
            <x-flash type="success" :message="session('success')" />
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4">
            <x-flash type="error" :message="session('error')" />
        </div>
    @endif


    {{-- ================================================================
         VALIDATION ERRORS
         ================================================================ --}}
    @if($hasValidationErrors)
        <div class="card mb-4 border border-red-500/20 bg-red-500/5">

            <div class="flex items-start gap-3">

                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-red-500/10 text-red-400">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </span>

                <div class="min-w-0 flex-1">

                    <h3 class="font-bold text-fg">
                        تعذر إنشاء الرتبة
                    </h3>

                    <p class="mt-1 text-sm text-muted">
                        يرجى مراجعة البيانات التالية ثم المحاولة مرة أخرى.
                    </p>

                    <ul class="mt-3 space-y-1 text-sm text-red-300">
                        @foreach($errors->all() as $error)
                            <li class="flex items-start gap-2">
                                <i class="fa-solid fa-circle text-[6px] mt-2"></i>
                                <span>{{ $error }}</span>
                            </li>
                        @endforeach
                    </ul>

                </div>

            </div>

        </div>
    @endif


    {{-- ================================================================
         MAIN FORM
         ================================================================ --}}
    <form
        method="POST"
        action="{{ route('roles.store') }}"
        class="max-w-6xl space-y-4"
        id="roleCreateForm"
    >

        @csrf


        {{-- ============================================================
             TOP INFORMATION / STATUS
             ============================================================ --}}
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">

            {{-- Role Type --}}
            <div class="card flex items-center gap-3">

                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-info/10 text-info">
                    <i class="fa-solid fa-shield-halved"></i>
                </span>

                <div class="min-w-0">
                    <p class="text-xs text-muted">
                        نوع السجل
                    </p>

                    <p class="mt-1 font-semibold text-fg">
                        رتبة نظامية
                    </p>
                </div>

            </div>


            {{-- Initial Status --}}
            <div class="card flex items-center gap-3">

                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand/10 text-brand">
                    <i class="fa-solid fa-circle-check"></i>
                </span>

                <div class="min-w-0">
                    <p class="text-xs text-muted">
                        الحالة
                    </p>

                    <p class="mt-1 font-semibold text-fg">
                        سيتم تحديدها من إعدادات الرتبة
                    </p>
                </div>

            </div>


            {{-- Security --}}
            <div class="card flex items-center gap-3">

                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-warning/10 text-warning">
                    <i class="fa-solid fa-lock"></i>
                </span>

                <div class="min-w-0">
                    <p class="text-xs text-muted">
                        مستوى التحكم
                    </p>

                    <p class="mt-1 font-semibold text-fg">
                        صلاحيات حسب الرتبة
                    </p>
                </div>

            </div>

        </div>


        {{-- ============================================================
             FORM + SIDE INFORMATION
             ============================================================ --}}
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">

            {{-- ========================================================
                 EXISTING FORM
                 ======================================================== --}}
            <div class="xl:col-span-2">

                <div class="card space-y-5">

                    {{-- Form Header --}}
                    <div class="flex items-start justify-between gap-4 border-b border-border pb-4">

                        <div class="flex items-start gap-3">

                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-info/10 text-info">
                                <i class="fa-solid fa-user-shield"></i>
                            </span>

                            <div>
                                <h2 class="font-bold text-fg">
                                    بيانات الرتبة
                                </h2>

                                <p class="mt-1 text-xs text-muted">
                                    أدخل البيانات الأساسية وحدد الصلاحيات المناسبة.
                                </p>
                            </div>

                        </div>

                        <span
                            class="hidden rounded-full border border-info/20 bg-info/5 px-3 py-1 text-[11px] text-info sm:inline-flex"
                        >
                            إعداد نظامي
                        </span>

                    </div>


                    {{-- =================================================
                         EXISTING ROLE FORM FIELDS
                         ================================================= --}}
                    @include('roles._form', ['role' => null])


                    {{-- =================================================
                         HELPFUL INFORMATION
                         ================================================= --}}
                    <div class="rounded-xl border border-border bg-black/10 p-4">

                        <div class="flex items-start gap-3">

                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-info/10 text-info">
                                <i class="fa-solid fa-circle-info text-sm"></i>
                            </span>

                            <div class="min-w-0">

                                <h3 class="text-sm font-semibold text-fg">
                                    ملاحظة مهمة
                                </h3>

                                <p class="mt-1 text-xs leading-6 text-muted">
                                    استخدم اسمًا واضحًا ومميزًا للرتبة، ثم امنحها فقط الصلاحيات
                                    التي يحتاجها المستخدمون التابعون لها.
                                </p>

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                         SECURITY WARNING
                         ================================================= --}}
                    <div class="rounded-xl border border-warning/20 bg-warning/5 p-4">

                        <div class="flex items-start gap-3">

                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-warning/10 text-warning">
                                <i class="fa-solid fa-shield-halved text-sm"></i>
                            </span>

                            <div class="min-w-0">

                                <h3 class="text-sm font-semibold text-fg">
                                    مبدأ أقل صلاحية
                                </h3>

                                <p class="mt-1 text-xs leading-6 text-muted">
                                    لا تمنح الرتبة صلاحيات إضافية غير مطلوبة. كل صلاحية يجب أن
                                    تكون مرتبطة بالمهام الفعلية للمستخدم.
                                </p>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- ========================================================
                 RIGHT SIDEBAR
                 ======================================================== --}}
            <div class="space-y-4">


                {{-- ====================================================
                     LIVE PREVIEW
                     ==================================================== --}}
                <div class="card">

                    <div class="mb-4 flex items-center gap-3">

                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand/10 text-brand">
                            <i class="fa-solid fa-eye"></i>
                        </span>

                        <div>
                            <h3 class="font-bold text-fg">
                                معاينة الرتبة
                            </h3>

                            <p class="text-xs text-muted">
                                معاينة أثناء إدخال البيانات
                            </p>
                        </div>

                    </div>


                    <div class="rounded-xl border border-border bg-black/10 p-4">

                        <div class="flex items-center gap-3">

                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-info/10 text-info">
                                <i class="fa-solid fa-user-shield"></i>
                            </span>

                            <div class="min-w-0">

                                <p
                                    id="rolePreviewName"
                                    class="truncate font-bold text-fg"
                                >
                                    {{ $roleName ?: 'اسم الرتبة الجديدة' }}
                                </p>

                                <p class="mt-1 text-xs text-muted">
                                    رتبة نظامية
                                </p>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- ====================================================
                     CREATION CHECKLIST
                     ==================================================== --}}
                <div class="card">

                    <div class="mb-4 flex items-center gap-3">

                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand/10 text-brand">
                            <i class="fa-solid fa-list-check"></i>
                        </span>

                        <div>
                            <h3 class="font-bold text-fg">
                                قائمة المراجعة
                            </h3>

                            <p class="text-xs text-muted">
                                تأكد من البيانات قبل الحفظ
                            </p>
                        </div>

                    </div>


                    <div class="space-y-3">

                        {{-- Name --}}
                        <div
                            id="checkName"
                            class="flex items-center gap-3 text-xs text-muted"
                        >
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white/5">
                                <i class="fa-solid fa-circle text-[7px]"></i>
                            </span>

                            <span>
                                تحديد اسم الرتبة
                            </span>
                        </div>


                        {{-- Permissions --}}
                        <div
                            id="checkPermissions"
                            class="flex items-center gap-3 text-xs text-muted"
                        >
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white/5">
                                <i class="fa-solid fa-circle text-[7px]"></i>
                            </span>

                            <span>
                                مراجعة الصلاحيات
                            </span>
                        </div>


                        {{-- Security --}}
                        <div
                            id="checkSecurity"
                            class="flex items-center gap-3 text-xs text-muted"
                        >
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white/5">
                                <i class="fa-solid fa-circle text-[7px]"></i>
                            </span>

                            <span>
                                التأكد من مبدأ أقل صلاحية
                            </span>
                        </div>


                        {{-- Ready --}}
                        <div
                            id="checkReady"
                            class="flex items-center gap-3 text-xs text-muted"
                        >
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-white/5">
                                <i class="fa-solid fa-circle text-[7px]"></i>
                            </span>

                            <span>
                                الرتبة جاهزة للحفظ
                            </span>
                        </div>

                    </div>

                </div>


                {{-- ====================================================
                     ROLE GUIDELINES
                     ==================================================== --}}
                <div class="card">

                    <div class="mb-4 flex items-center gap-3">

                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-accent/10 text-accent">
                            <i class="fa-solid fa-lightbulb"></i>
                        </span>

                        <div>
                            <h3 class="font-bold text-fg">
                                إرشادات
                            </h3>

                            <p class="text-xs text-muted">
                                قبل إنشاء الرتبة
                            </p>
                        </div>

                    </div>


                    <div class="space-y-3 text-xs leading-6 text-muted">

                        <div class="flex items-start gap-2">
                            <i class="fa-solid fa-check mt-1 text-brand"></i>
                            <span>
                                اجعل اسم الرتبة واضحًا وسهل التعرف عليه.
                            </span>
                        </div>

                        <div class="flex items-start gap-2">
                            <i class="fa-solid fa-check mt-1 text-brand"></i>
                            <span>
                                اجمع الصلاحيات المرتبطة بنفس المسؤوليات داخل رتبة واحدة.
                            </span>
                        </div>

                        <div class="flex items-start gap-2">
                            <i class="fa-solid fa-check mt-1 text-brand"></i>
                            <span>
                                تجنب إعطاء صلاحيات الإدارة للرتب التشغيلية.
                            </span>
                        </div>

                        <div class="flex items-start gap-2">
                            <i class="fa-solid fa-check mt-1 text-brand"></i>
                            <span>
                                يمكن تعديل الصلاحيات لاحقًا من إعدادات الرتبة.
                            </span>
                        </div>

                    </div>

                </div>


                {{-- ====================================================
                     KEYBOARD SHORTCUTS
                     ==================================================== --}}
                <div class="card">

                    <div class="mb-4 flex items-center gap-3">

                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/5 text-muted">
                            <i class="fa-solid fa-keyboard"></i>
                        </span>

                        <div>
                            <h3 class="font-bold text-fg">
                                اختصارات لوحة المفاتيح
                            </h3>

                            <p class="text-xs text-muted">
                                لتسريع العمل
                            </p>
                        </div>

                    </div>


                    <div class="space-y-3">

                        <div class="flex items-center justify-between gap-3 text-xs">
                            <span class="text-muted">
                                التركيز على اسم الرتبة
                            </span>

                            <kbd class="rounded-lg border border-border bg-black/20 px-2 py-1 text-[10px] text-fg">
                                Ctrl + K
                            </kbd>
                        </div>

                        <div class="flex items-center justify-between gap-3 text-xs">
                            <span class="text-muted">
                                حفظ الرتبة
                            </span>

                            <kbd class="rounded-lg border border-border bg-black/20 px-2 py-1 text-[10px] text-fg">
                                Ctrl + Enter
                            </kbd>
                        </div>

                        <div class="flex items-center justify-between gap-3 text-xs">
                            <span class="text-muted">
                                الرجوع
                            </span>

                            <kbd class="rounded-lg border border-border bg-black/20 px-2 py-1 text-[10px] text-fg">
                                Esc
                            </kbd>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- ============================================================
             BOTTOM ACTION BAR
             ============================================================ --}}
        <div class="card flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

            <div class="flex items-start gap-3">

                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand/10 text-brand">
                    <i class="fa-solid fa-floppy-disk"></i>
                </span>

                <div>

                    <p class="text-sm font-semibold text-fg">
                        جاهز لإنشاء الرتبة؟
                    </p>

                    <p class="mt-1 text-xs text-muted">
                        راجع الاسم والصلاحيات قبل حفظ التغييرات.
                    </p>

                </div>

            </div>


            <div class="flex flex-wrap items-center gap-2">

                <a
                    href="{{ route('roles.index') }}"
                    class="btn btn-secondary btn-sm"
                    id="bottomBackButton"
                >
                    <i class="fa-solid fa-xmark text-xs"></i>
                    إلغاء
                </a>

                <button
                    type="button"
                    class="btn btn-secondary btn-sm"
                    id="resetRoleForm"
                >
                    <i class="fa-solid fa-rotate-left text-xs"></i>
                    إعادة تعيين
                </button>

                <button
                    type="submit"
                    class="btn btn-primary btn-sm"
                    id="submitRoleButton"
                >
                    <i class="fa-solid fa-plus text-xs"></i>
                    إنشاء الرتبة
                </button>

            </div>

        </div>

    </form>


    {{-- ================================================================
         JAVASCRIPT ENHANCEMENTS
         ================================================================ --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            const form = document.getElementById('roleCreateForm');
            const submitButton = document.getElementById('submitRoleButton');
            const resetButton = document.getElementById('resetRoleForm');

            const previewName = document.getElementById('rolePreviewName');

            const checkName = document.getElementById('checkName');
            const checkPermissions = document.getElementById('checkPermissions');
            const checkSecurity = document.getElementById('checkSecurity');
            const checkReady = document.getElementById('checkReady');

            if (!form) {
                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Find common role fields safely
            |--------------------------------------------------------------------------
            | We don't assume the exact structure of roles._form.
            */

            const nameInput =
                form.querySelector('[name="name"]') ||
                form.querySelector('input[name*="name"]');

            const descriptionInput =
                form.querySelector('[name="description"]') ||
                form.querySelector('textarea[name*="description"]');

            const permissionInputs =
                form.querySelectorAll(
                    'input[name="permissions[]"],' +
                    'input[name="permission[]"],' +
                    'input[name*="permission"],' +
                    'select[name="permissions[]"],' +
                    'select[name="permission[]"]'
                );


            /*
            |--------------------------------------------------------------------------
            | Live role name preview
            |--------------------------------------------------------------------------
            */

            function updatePreview() {

                if (!previewName) {
                    return;
                }

                const value = nameInput
                    ? nameInput.value.trim()
                    : '';

                previewName.textContent =
                    value || 'اسم الرتبة الجديدة';
            }


            /*
            |--------------------------------------------------------------------------
            | Checklist helpers
            |--------------------------------------------------------------------------
            */

            function markCheck(element, valid) {

                if (!element) {
                    return;
                }

                const icon = element.querySelector('i');

                if (valid) {

                    element.classList.remove('text-muted');
                    element.classList.add('text-brand');

                    const box = element.querySelector('span');

                    if (box) {
                        box.classList.remove('bg-white/5');
                        box.classList.add('bg-brand/10');
                    }

                    if (icon) {
                        icon.className = 'fa-solid fa-circle-check text-brand';
                    }

                } else {

                    element.classList.remove('text-brand');
                    element.classList.add('text-muted');

                    const box = element.querySelector('span');

                    if (box) {
                        box.classList.remove('bg-brand/10');
                        box.classList.add('bg-white/5');
                    }

                    if (icon) {
                        icon.className = 'fa-solid fa-circle text-[7px]';
                    }
                }
            }


            function updateChecklist() {

                const nameValid = nameInput
                    ? nameInput.value.trim().length > 0
                    : false;

                let permissionsValid = true;

                if (permissionInputs.length > 0) {

                    permissionsValid = Array.from(permissionInputs).some(input => {

                        if (input.type === 'checkbox' || input.type === 'radio') {
                            return input.checked;
                        }

                        if (input.tagName === 'SELECT') {
                            return Array.from(input.selectedOptions).length > 0;
                        }

                        return input.value;
                    });
                }

                /*
                |--------------------------------------------------------------------------
                | Security checklist
                |--------------------------------------------------------------------------
                | This is informational only. We don't block submission.
                */

                const securityValid = nameValid;

                const ready =
                    nameValid &&
                    permissionsValid &&
                    securityValid;

                markCheck(checkName, nameValid);
                markCheck(checkPermissions, permissionsValid);
                markCheck(checkSecurity, securityValid);
                markCheck(checkReady, ready);
            }


            /*
            |--------------------------------------------------------------------------
            | Live updates
            |--------------------------------------------------------------------------
            */

            if (nameInput) {

                nameInput.addEventListener('input', function () {
                    updatePreview();
                    updateChecklist();
                });

                nameInput.addEventListener('change', function () {
                    updatePreview();
                    updateChecklist();
                });

            }


            if (descriptionInput) {

                descriptionInput.addEventListener('input', function () {
                    updateChecklist();
                });

            }


            permissionInputs.forEach(function (input) {

                input.addEventListener('change', function () {
                    updateChecklist();
                });

            });


            /*
            |--------------------------------------------------------------------------
            | Reset
            |--------------------------------------------------------------------------
            */

            if (resetButton) {

                resetButton.addEventListener('click', function () {

                    const confirmed = window.confirm(
                        'هل تريد إعادة تعيين بيانات الرتبة؟'
                    );

                    if (!confirmed) {
                        return;
                    }

                    form.reset();

                    updatePreview();
                    updateChecklist();

                });

            }


            /*
            |--------------------------------------------------------------------------
            | Prevent double submission
            |--------------------------------------------------------------------------
            */

            let submitting = false;

            form.addEventListener('submit', function () {

                if (submitting) {
                    return;
                }

                submitting = true;

                if (submitButton) {

                    submitButton.disabled = true;

                    submitButton.classList.add('opacity-70', 'cursor-not-allowed');

                    submitButton.innerHTML =
                        '<i class="fa-solid fa-spinner fa-spin text-xs"></i> جاري إنشاء الرتبة...';
                }

            });


            /*
            |--------------------------------------------------------------------------
            | Keyboard shortcuts
            |--------------------------------------------------------------------------
            */

            document.addEventListener('keydown', function (event) {

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

                    if (nameInput) {
                        nameInput.focus();
                        nameInput.select();
                    }

                }


                /*
                |--------------------------------------------------------------------------
                | Ctrl + Enter
                |--------------------------------------------------------------------------
                */

                if (
                    (event.ctrlKey || event.metaKey) &&
                    event.key === 'Enter'
                ) {

                    event.preventDefault();

                    if (submitButton && !submitButton.disabled) {
                        form.requestSubmit();
                    }

                }


                /*
                |--------------------------------------------------------------------------
                | Escape
                |--------------------------------------------------------------------------
                */

                if (event.key === 'Escape') {

                    const activeElement = document.activeElement;

                    /*
                    | Don't immediately leave if user is typing in a field.
                    */

                    if (
                        activeElement &&
                        (
                            activeElement.tagName === 'INPUT' ||
                            activeElement.tagName === 'TEXTAREA' ||
                            activeElement.tagName === 'SELECT'
                        )
                    ) {

                        activeElement.blur();
                        return;
                    }

                    window.location.href =
                        "{{ route('roles.index') }}";
                }

            });


            /*
            |--------------------------------------------------------------------------
            | Initial state
            |--------------------------------------------------------------------------
            */

            updatePreview();
            updateChecklist();

        });
    </script>

@endsection