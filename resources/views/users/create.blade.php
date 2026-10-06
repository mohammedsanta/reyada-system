{{-- resources/views/users/create.blade.php --}}
@extends('layouts.app')

@section('title', 'إضافة موظف جديد')

@section('content')

    @php
        /*
        |--------------------------------------------------------------------------
        | SAFE DEFAULTS
        |--------------------------------------------------------------------------
        */

        $formUser = null;

        $oldName = old('name', '');

        $oldEmail = old('email', '');

        $oldRole = old('role', '');

        $oldStatus = old('is_active', 1);
    @endphp


    {{-- ================================================================
        PAGE HEADER
    ================================================================= --}}
    <x-page-header
        title="إضافة موظف جديد"
        subtitle="إنشاء حساب موظف وتحديد رتبته"
        icon="fa-user-plus"
    >

        <x-slot:actions>

            <a
                href="{{ route('users.index') }}"
                class="btn btn-secondary btn-sm"
                title="العودة إلى قائمة الموظفين"
                aria-label="العودة إلى قائمة الموظفين"
            >
                <i class="fa-solid fa-arrow-right text-xs"></i>
                رجوع
            </a>

        </x-slot:actions>

    </x-page-header>


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
                        يرجى مراجعة بيانات الموظف
                    </div>

                    <div class="mt-1 text-[11px] text-dim">
                        يوجد {{ $errors->count() }} خطأ يحتاج إلى المراجعة قبل إنشاء الحساب.
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
        ACCOUNT CREATION OVERVIEW
    ================================================================= --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">


        {{-- ============================================================
            ACCOUNT TYPE
        ============================================================= --}}
        <div class="card">

            <div class="flex items-center justify-between gap-3">

                <div>

                    <div class="text-xs text-dim">
                        نوع السجل
                    </div>

                    <div class="mt-2 text-sm font-bold text-info">
                        موظف
                    </div>

                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-info/10 text-info">
                    <i class="fa-solid fa-user"></i>
                </span>

            </div>

        </div>


        {{-- ============================================================
            ROLE
        ============================================================= --}}
        <div class="card">

            <div class="flex items-center justify-between gap-3">

                <div>

                    <div class="text-xs text-dim">
                        الرتبة
                    </div>

                    <div
                        id="overviewRole"
                        class="mt-2 text-sm font-bold text-brand"
                    >
                        لم تحدد بعد
                    </div>

                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand/10 text-brand">
                    <i class="fa-solid fa-user-shield"></i>
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
                        حالة الحساب
                    </div>

                    <div
                        id="overviewStatus"
                        class="mt-2 flex items-center gap-2 text-sm font-bold text-brand"
                    >
                        <span class="h-2 w-2 rounded-full bg-brand"></span>
                        نشط
                    </div>

                </div>

                <span
                    id="overviewStatusIcon"
                    class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand/10 text-brand"
                >
                    <i class="fa-solid fa-circle-check"></i>
                </span>

            </div>

        </div>


        {{-- ============================================================
            SECURITY
        ============================================================= --}}
        <div class="card">

            <div class="flex items-center justify-between gap-3">

                <div>

                    <div class="text-xs text-dim">
                        مستوى الأمان
                    </div>

                    <div
                        id="securityLevel"
                        class="mt-2 text-sm font-bold text-warning"
                    >
                        يحتاج بيانات
                    </div>

                </div>

                <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-warning/10 text-warning">
                    <i class="fa-solid fa-shield-halved"></i>
                </span>

            </div>

        </div>

    </div>


    {{-- ================================================================
        MAIN CONTENT
    ================================================================= --}}
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-3">


        {{-- ============================================================
            MAIN FORM
        ============================================================= --}}
        <div class="xl:col-span-2">

            <form
                method="POST"
                action="{{ route('users.store') }}"
                class="max-w-4xl space-y-4"
                id="createUserForm"
            >

                @csrf


                {{-- ====================================================
                    FORM HEADER
                ================================================= --}}
                <div class="card mb-4">

                    <div class="flex items-center justify-between gap-3">

                        <div class="flex items-center gap-3">

                            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-info/10 text-info">
                                <i class="fa-solid fa-user-plus"></i>
                            </span>

                            <div>

                                <div class="text-sm font-bold text-fg">
                                    بيانات الموظف
                                </div>

                                <div class="mt-1 text-[11px] text-dim">
                                    أدخل بيانات الحساب وحدد صلاحيات الموظف
                                </div>

                            </div>

                        </div>

                        <span class="badge badge-outline-info">
                            <i class="fa-solid fa-pen text-[9px]"></i>
                            إنشاء جديد
                        </span>

                    </div>

                </div>


                {{-- ====================================================
                    EXISTING USER FORM
                    Keep this component untouched.
                ===================================================== --}}
                @include('users._form', [
                    'user' => $formUser
                ])


                {{-- ====================================================
                    FORM ACTIONS
                ================================================= --}}
                <div class="card mt-4">

                    <div class="flex flex-wrap items-center justify-between gap-3">

                        <div class="text-[10px] text-dim">

                            <i class="fa-solid fa-shield-halved ml-1 text-brand"></i>

                            راجع بيانات الموظف وصلاحياته قبل إنشاء الحساب.

                        </div>


                        <div class="flex flex-wrap items-center gap-2">

                            <a
                                href="{{ route('users.index') }}"
                                class="btn btn-secondary"
                                title="إلغاء والعودة إلى قائمة الموظفين"
                                aria-label="إلغاء والعودة إلى قائمة الموظفين"
                            >
                                <i class="fa-solid fa-arrow-right text-xs"></i>
                                إلغاء
                            </a>


                            <button
                                type="button"
                                class="btn btn-secondary"
                                id="resetUserForm"
                                title="إعادة ضبط البيانات"
                                aria-label="إعادة ضبط البيانات"
                            >
                                <i class="fa-solid fa-rotate-right text-xs"></i>
                                إعادة ضبط
                            </button>


                            <button
                                type="submit"
                                class="btn btn-primary"
                                id="createUserButton"
                                title="إنشاء حساب الموظف"
                                aria-label="إنشاء حساب الموظف"
                            >

                                <i
                                    class="fa-solid fa-user-plus text-xs"
                                    id="createUserIcon"
                                ></i>

                                <span id="createUserText">
                                    إنشاء الموظف
                                </span>

                            </button>

                        </div>

                    </div>

                </div>

            </form>

        </div>


        {{-- ============================================================
            SIDE PANEL
        ============================================================= --}}
        <div class="space-y-5">


            {{-- ========================================================
                LIVE USER PREVIEW
            ========================================================= --}}
            <div class="card">

                <div class="flex items-center justify-between mb-4">

                    <div>

                        <div class="text-sm font-bold text-fg">
                            معاينة الحساب
                        </div>

                        <div class="text-[11px] text-dim mt-1">
                            شكل بيانات الموظف أثناء الإدخال
                        </div>

                    </div>

                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-info/10 text-info">
                        <i class="fa-solid fa-eye"></i>
                    </span>

                </div>


                <div class="rounded-xl border border-white/5 bg-white/[0.02] p-4">

                    <div class="flex items-center gap-3">

                        <span class="relative flex h-12 w-12 shrink-0 items-center justify-center rounded-xl border border-info/10 bg-info/10 text-info">

                            <span
                                id="userPreviewInitial"
                                class="text-lg font-bold"
                            >
                                ?
                            </span>

                            <span
                                id="previewStatusDot"
                                class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-[#14191e] bg-brand"
                            ></span>

                        </span>


                        <div class="min-w-0">

                            <div
                                id="userPreviewName"
                                class="truncate text-sm font-bold text-fg"
                            >
                                اسم الموظف
                            </div>

                            <div
                                id="userPreviewEmail"
                                class="mt-1 truncate text-[10px] text-dim"
                                dir="ltr"
                            >
                                email@example.com
                            </div>

                        </div>

                    </div>


                    <div class="mt-4 grid grid-cols-2 gap-2">


                        <div class="rounded-lg border border-white/5 bg-white/[0.02] p-3">

                            <div class="text-[10px] text-dim">
                                الرتبة
                            </div>

                            <div
                                id="previewRole"
                                class="mt-1 truncate text-xs font-semibold text-brand"
                            >
                                غير محددة
                            </div>

                        </div>


                        <div class="rounded-lg border border-white/5 bg-white/[0.02] p-3">

                            <div class="text-[10px] text-dim">
                                الحالة
                            </div>

                            <div
                                id="previewStatus"
                                class="mt-1 flex items-center gap-1.5 text-xs font-semibold text-brand"
                            >
                                <span class="h-1.5 w-1.5 rounded-full bg-brand"></span>
                                نشط
                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- ========================================================
                ACCOUNT CHECKLIST
            ========================================================= --}}
            <div class="card">

                <div class="flex items-center justify-between mb-4">

                    <div>

                        <div class="text-sm font-bold text-fg">
                            مراجعة الحساب
                        </div>

                        <div class="text-[11px] text-dim mt-1">
                            حالة البيانات الأساسية
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
                                id="nameCheckIcon"
                                class="flex h-6 w-6 items-center justify-center rounded-md bg-white/5 text-dim"
                            >
                                <i class="fa-solid fa-minus text-[9px]"></i>
                            </span>

                            <span class="text-xs text-muted">
                                اسم الموظف
                            </span>

                        </div>

                        <span
                            id="nameCheckText"
                            class="text-[10px] text-dim"
                        >
                            مطلوب
                        </span>

                    </div>


                    {{-- EMAIL --}}
                    <div class="flex items-center justify-between gap-3">

                        <div class="flex items-center gap-2">

                            <span
                                id="emailCheckIcon"
                                class="flex h-6 w-6 items-center justify-center rounded-md bg-white/5 text-dim"
                            >
                                <i class="fa-solid fa-minus text-[9px]"></i>
                            </span>

                            <span class="text-xs text-muted">
                                البريد الإلكتروني
                            </span>

                        </div>

                        <span
                            id="emailCheckText"
                            class="text-[10px] text-dim"
                        >
                            مطلوب
                        </span>

                    </div>


                    {{-- ROLE --}}
                    <div class="flex items-center justify-between gap-3">

                        <div class="flex items-center gap-2">

                            <span
                                id="roleCheckIcon"
                                class="flex h-6 w-6 items-center justify-center rounded-md bg-white/5 text-dim"
                            >
                                <i class="fa-solid fa-minus text-[9px]"></i>
                            </span>

                            <span class="text-xs text-muted">
                                الرتبة
                            </span>

                        </div>

                        <span
                            id="roleCheckText"
                            class="text-[10px] text-dim"
                        >
                            مطلوب
                        </span>

                    </div>


                    {{-- PASSWORD --}}
                    <div class="flex items-center justify-between gap-3">

                        <div class="flex items-center gap-2">

                            <span
                                id="passwordCheckIcon"
                                class="flex h-6 w-6 items-center justify-center rounded-md bg-white/5 text-dim"
                            >
                                <i class="fa-solid fa-minus text-[9px]"></i>
                            </span>

                            <span class="text-xs text-muted">
                                كلمة المرور
                            </span>

                        </div>

                        <span
                            id="passwordCheckText"
                            class="text-[10px] text-dim"
                        >
                            مطلوب
                        </span>

                    </div>


                </div>

            </div>


            {{-- ========================================================
                SECURITY CARD
            ========================================================= --}}
            <div class="card">

                <div class="flex items-center justify-between mb-4">

                    <div>

                        <div class="text-sm font-bold text-fg">
                            أمان الحساب
                        </div>

                        <div class="text-[11px] text-dim mt-1">
                            مؤشرات بيانات الحساب
                        </div>

                    </div>

                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-warning/10 text-warning">
                        <i class="fa-solid fa-shield-halved"></i>
                    </span>

                </div>


                <div class="space-y-3">


                    <div class="flex items-center justify-between">

                        <span class="text-xs text-dim">
                            البريد الإلكتروني
                        </span>

                        <span
                            id="securityEmail"
                            class="text-[10px] text-dim"
                        >
                            غير مكتمل
                        </span>

                    </div>


                    <div class="flex items-center justify-between">

                        <span class="text-xs text-dim">
                            كلمة المرور
                        </span>

                        <span
                            id="securityPassword"
                            class="text-[10px] text-dim"
                        >
                            غير مكتملة
                        </span>

                    </div>


                    <div class="flex items-center justify-between border-t border-white/5 pt-3">

                        <span class="text-xs text-dim">
                            حالة الحساب
                        </span>

                        <span
                            id="securityStatus"
                            class="text-[10px] text-brand"
                        >
                            نشط
                        </span>

                    </div>

                </div>

            </div>


            {{-- ========================================================
                INFORMATION
            ========================================================= --}}
            <div class="card">

                <div class="flex items-start gap-3">

                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-info/10 text-info">
                        <i class="fa-solid fa-circle-info"></i>
                    </span>

                    <div>

                        <div class="text-sm font-semibold text-fg">
                            ملاحظات مهمة
                        </div>

                        <ul class="mt-3 space-y-2 text-[11px] text-dim">

                            <li class="flex items-start gap-2">

                                <i class="fa-solid fa-check text-brand mt-0.5"></i>

                                <span>
                                    استخدم بريدًا إلكترونيًا خاصًا بالموظف.
                                </span>

                            </li>

                            <li class="flex items-start gap-2">

                                <i class="fa-solid fa-check text-brand mt-0.5"></i>

                                <span>
                                    اختر الرتبة المناسبة لمهام الموظف.
                                </span>

                            </li>

                            <li class="flex items-start gap-2">

                                <i class="fa-solid fa-check text-brand mt-0.5"></i>

                                <span>
                                    تجنب مشاركة كلمة المرور مع أي شخص.
                                </span>

                            </li>

                            <li class="flex items-start gap-2">

                                <i class="fa-solid fa-check text-brand mt-0.5"></i>

                                <span>
                                    راجع البيانات قبل إنشاء الحساب.
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
                            إنشاء الحساب
                        </span>

                        <kbd class="rounded border border-white/10 bg-white/5 px-2 py-1 font-mono text-[9px] text-muted">
                            Ctrl + Enter
                        </kbd>

                    </div>


                    <div class="flex items-center justify-between text-[10px] text-dim">

                        <span>
                            الانتقال لأول حقل
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

        document.addEventListener(
            'DOMContentLoaded',
            function () {

                const form =
                    document.getElementById(
                        'createUserForm'
                    );

                if (!form) {
                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | FIND EXISTING FIELDS
                |--------------------------------------------------------------------------
                | We don't modify users/_form.blade.php.
                | We only react to its existing fields.
                |--------------------------------------------------------------------------
                */

                const nameInput =
                    form.querySelector(
                        '[name="name"]'
                    );

                const emailInput =
                    form.querySelector(
                        '[name="email"]'
                    );

                const roleInput =
                    form.querySelector(
                        '[name="role"]'
                    );

                const passwordInput =
                    form.querySelector(
                        '[name="password"]'
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
                        'userPreviewName'
                    );

                const previewEmail =
                    document.getElementById(
                        'userPreviewEmail'
                    );

                const previewInitial =
                    document.getElementById(
                        'userPreviewInitial'
                    );

                const previewRole =
                    document.getElementById(
                        'previewRole'
                    );

                const previewStatus =
                    document.getElementById(
                        'previewStatus'
                    );

                const previewStatusDot =
                    document.getElementById(
                        'previewStatusDot'
                    );


                /*
                |--------------------------------------------------------------------------
                | OVERVIEW
                |--------------------------------------------------------------------------
                */

                const overviewRole =
                    document.getElementById(
                        'overviewRole'
                    );

                const overviewStatus =
                    document.getElementById(
                        'overviewStatus'
                    );

                const overviewStatusIcon =
                    document.getElementById(
                        'overviewStatusIcon'
                    );


                /*
                |--------------------------------------------------------------------------
                | SECURITY
                |--------------------------------------------------------------------------
                */

                const securityLevel =
                    document.getElementById(
                        'securityLevel'
                    );

                const securityEmail =
                    document.getElementById(
                        'securityEmail'
                    );

                const securityPassword =
                    document.getElementById(
                        'securityPassword'
                    );

                const securityStatus =
                    document.getElementById(
                        'securityStatus'
                    );


                /*
                |--------------------------------------------------------------------------
                | CHECKLIST
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

                const emailCheckIcon =
                    document.getElementById(
                        'emailCheckIcon'
                    );

                const emailCheckText =
                    document.getElementById(
                        'emailCheckText'
                    );

                const roleCheckIcon =
                    document.getElementById(
                        'roleCheckIcon'
                    );

                const roleCheckText =
                    document.getElementById(
                        'roleCheckText'
                    );

                const passwordCheckIcon =
                    document.getElementById(
                        'passwordCheckIcon'
                    );

                const passwordCheckText =
                    document.getElementById(
                        'passwordCheckText'
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
                | GET ROLE LABEL
                |--------------------------------------------------------------------------
                */

                function getRoleLabel() {

                    if (!roleInput) {
                        return '';
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | SELECT
                    |--------------------------------------------------------------------------
                    */

                    if (
                        roleInput.tagName ===
                        'SELECT'
                    ) {

                        const option =
                            roleInput.options[
                                roleInput.selectedIndex
                            ];


                        if (
                            option &&
                            option.value !== ''
                        ) {

                            return option.textContent.trim();

                        }


                        return '';

                    }


                    return roleInput.value.trim();

                }


                /*
                |--------------------------------------------------------------------------
                | SET CHECK STATE
                |--------------------------------------------------------------------------
                */

                function setCheckState(
                    icon,
                    text,
                    valid,
                    validText = 'متوفر',
                    invalidText = 'مطلوب'
                ) {

                    if (!icon || !text) {
                        return;
                    }


                    if (valid) {

                        icon.className =
                            'flex h-6 w-6 items-center justify-center rounded-md bg-brand/10 text-brand';

                        icon.innerHTML =
                            '<i class="fa-solid fa-check text-[9px]"></i>';

                        text.textContent =
                            validText;

                        text.className =
                            'text-[10px] text-brand';

                    } else {

                        icon.className =
                            'flex h-6 w-6 items-center justify-center rounded-md bg-white/5 text-dim';

                        icon.innerHTML =
                            '<i class="fa-solid fa-minus text-[9px]"></i>';

                        text.textContent =
                            invalidText;

                        text.className =
                            'text-[10px] text-dim';

                    }

                }


                /*
                |--------------------------------------------------------------------------
                | UPDATE PREVIEW
                |--------------------------------------------------------------------------
                */

                function updatePreview() {

                    const name =
                        nameInput
                            ? nameInput.value.trim()
                            : '';

                    const email =
                        emailInput
                            ? emailInput.value.trim()
                            : '';

                    const role =
                        getRoleLabel();

                    const active =
                        getStatus();


                    /*
                    |--------------------------------------------------------------------------
                    | NAME
                    |--------------------------------------------------------------------------
                    */

                    if (previewName) {

                        previewName.textContent =
                            name !== ''
                                ? name
                                : 'اسم الموظف';

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | INITIAL
                    |--------------------------------------------------------------------------
                    */

                    if (previewInitial) {

                        previewInitial.textContent =
                            name !== ''
                                ? name.charAt(0).toUpperCase()
                                : '?';

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | EMAIL
                    |--------------------------------------------------------------------------
                    */

                    if (previewEmail) {

                        previewEmail.textContent =
                            email !== ''
                                ? email
                                : 'email@example.com';

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | ROLE
                    |--------------------------------------------------------------------------
                    */

                    if (previewRole) {

                        previewRole.textContent =
                            role !== ''
                                ? role
                                : 'غير محددة';

                    }


                    if (overviewRole) {

                        overviewRole.textContent =
                            role !== ''
                                ? role
                                : 'لم تحدد بعد';

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | STATUS
                    |--------------------------------------------------------------------------
                    */

                    if (previewStatus) {

                        if (active) {

                            previewStatus.className =
                                'mt-1 flex items-center gap-1.5 text-xs font-semibold text-brand';

                            previewStatus.innerHTML =
                                '<span class="h-1.5 w-1.5 rounded-full bg-brand"></span>' +
                                'نشط';

                        } else {

                            previewStatus.className =
                                'mt-1 flex items-center gap-1.5 text-xs font-semibold text-danger';

                            previewStatus.innerHTML =
                                '<span class="h-1.5 w-1.5 rounded-full bg-danger"></span>' +
                                'متوقف';

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


                    /*
                    |--------------------------------------------------------------------------
                    | OVERVIEW STATUS
                    |--------------------------------------------------------------------------
                    */

                    if (overviewStatus) {

                        if (active) {

                            overviewStatus.className =
                                'mt-2 flex items-center gap-2 text-sm font-bold text-brand';

                            overviewStatus.innerHTML =
                                '<span class="h-2 w-2 rounded-full bg-brand"></span>' +
                                'نشط';

                        } else {

                            overviewStatus.className =
                                'mt-2 flex items-center gap-2 text-sm font-bold text-danger';

                            overviewStatus.innerHTML =
                                '<span class="h-2 w-2 rounded-full bg-danger"></span>' +
                                'متوقف';

                        }

                    }


                    if (overviewStatusIcon) {

                        if (active) {

                            overviewStatusIcon.className =
                                'flex h-10 w-10 items-center justify-center rounded-xl bg-brand/10 text-brand';

                            overviewStatusIcon.innerHTML =
                                '<i class="fa-solid fa-circle-check"></i>';

                        } else {

                            overviewStatusIcon.className =
                                'flex h-10 w-10 items-center justify-center rounded-xl bg-danger/10 text-danger';

                            overviewStatusIcon.innerHTML =
                                '<i class="fa-solid fa-circle-pause"></i>';

                        }

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | SECURITY STATUS
                    |--------------------------------------------------------------------------
                    */

                    if (securityStatus) {

                        securityStatus.textContent =
                            active
                                ? 'نشط'
                                : 'متوقف';

                        securityStatus.className =
                            active
                                ? 'text-[10px] text-brand'
                                : 'text-[10px] text-danger';

                    }


                    updateChecklist();

                }


                /*
                |--------------------------------------------------------------------------
                | UPDATE CHECKLIST
                |--------------------------------------------------------------------------
                */

                function updateChecklist() {

                    const name =
                        nameInput
                            ? nameInput.value.trim()
                            : '';

                    const email =
                        emailInput
                            ? emailInput.value.trim()
                            : '';

                    const role =
                        getRoleLabel();

                    const password =
                        passwordInput
                            ? passwordInput.value
                            : '';


                    /*
                    |--------------------------------------------------------------------------
                    | NAME
                    |--------------------------------------------------------------------------
                    */

                    setCheckState(
                        nameCheckIcon,
                        nameCheckText,
                        name.length > 0
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | EMAIL
                    |--------------------------------------------------------------------------
                    */

                    const emailValid =
                        email.length > 0 &&
                        /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);


                    setCheckState(
                        emailCheckIcon,
                        emailCheckText,
                        emailValid
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | ROLE
                    |--------------------------------------------------------------------------
                    */

                    setCheckState(
                        roleCheckIcon,
                        roleCheckText,
                        role.length > 0
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | PASSWORD
                    |--------------------------------------------------------------------------
                    */

                    const passwordValid =
                        password.length >= 8;


                    setCheckState(
                        passwordCheckIcon,
                        passwordCheckText,
                        passwordValid
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | SECURITY EMAIL
                    |--------------------------------------------------------------------------
                    */

                    if (securityEmail) {

                        if (emailValid) {

                            securityEmail.textContent =
                                'مكتمل';

                            securityEmail.className =
                                'text-[10px] text-brand';

                        } else {

                            securityEmail.textContent =
                                'غير مكتمل';

                            securityEmail.className =
                                'text-[10px] text-dim';

                        }

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | SECURITY PASSWORD
                    |--------------------------------------------------------------------------
                    */

                    if (securityPassword) {

                        if (passwordValid) {

                            securityPassword.textContent =
                                'جيد';

                            securityPassword.className =
                                'text-[10px] text-brand';

                        } else if (password.length > 0) {

                            securityPassword.textContent =
                                'قصيرة';

                            securityPassword.className =
                                'text-[10px] text-warning';

                        } else {

                            securityPassword.textContent =
                                'غير مكتملة';

                            securityPassword.className =
                                'text-[10px] text-dim';

                        }

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | SECURITY LEVEL
                    |--------------------------------------------------------------------------
                    */

                    let score = 0;

                    if (name.length > 0) {
                        score++;
                    }

                    if (emailValid) {
                        score++;
                    }

                    if (role.length > 0) {
                        score++;
                    }

                    if (passwordValid) {
                        score++;
                    }


                    if (!securityLevel) {
                        return;
                    }


                    if (score >= 4) {

                        securityLevel.textContent =
                            'مكتمل';

                        securityLevel.className =
                            'mt-2 text-sm font-bold text-brand';

                    } else if (score >= 2) {

                        securityLevel.textContent =
                            'جيد';

                        securityLevel.className =
                            'mt-2 text-sm font-bold text-info';

                    } else {

                        securityLevel.textContent =
                            'يحتاج بيانات';

                        securityLevel.className =
                            'mt-2 text-sm font-bold text-warning';

                    }

                }


                /*
                |--------------------------------------------------------------------------
                | FIELD EVENTS
                |--------------------------------------------------------------------------
                */

                [
                    nameInput,
                    emailInput,
                    roleInput,
                    passwordInput,
                    statusInput
                ]
                    .filter(Boolean)
                    .forEach(function (field) {

                        field.addEventListener(
                            'input',
                            updatePreview
                        );

                        field.addEventListener(
                            'change',
                            updatePreview
                        );

                    });


                /*
                |--------------------------------------------------------------------------
                | EMAIL NORMALIZATION
                |--------------------------------------------------------------------------
                */

                if (emailInput) {

                    emailInput.addEventListener(
                        'blur',
                        function () {

                            this.value =
                                this.value.trim().toLowerCase();

                            updatePreview();

                        }
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | RESET
                |--------------------------------------------------------------------------
                */

                const resetButton =
                    document.getElementById(
                        'resetUserForm'
                    );


                if (resetButton) {

                    resetButton.addEventListener(
                        'click',
                        function () {

                            const confirmed =
                                confirm(
                                    'هل تريد إعادة ضبط جميع بيانات الموظف؟'
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

                let submitting = false;


                const createButton =
                    document.getElementById(
                        'createUserButton'
                    );

                const createIcon =
                    document.getElementById(
                        'createUserIcon'
                    );

                const createText =
                    document.getElementById(
                        'createUserText'
                    );


                form.addEventListener(
                    'submit',
                    function (event) {

                        if (submitting) {

                            event.preventDefault();

                            return;

                        }


                        submitting = true;


                        if (createButton) {

                            createButton.disabled =
                                true;

                            createButton.classList.add(
                                'opacity-70',
                                'cursor-not-allowed'
                            );

                        }


                        if (createIcon) {

                            createIcon.className =
                                'fa-solid fa-spinner fa-spin text-xs';

                        }


                        if (createText) {

                            createText.textContent =
                                'جاري إنشاء الموظف...';

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
                | ESC = RESET
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


                        if (
                            activeElement &&
                            activeElement.tagName ===
                            'SELECT'
                        ) {

                            return;

                        }


                        const confirmed =
                            confirm(
                                'هل تريد إعادة ضبط بيانات الموظف؟'
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
                | INITIAL STATE
                |--------------------------------------------------------------------------
                */

                updatePreview();

            });

    </script>

@endsection