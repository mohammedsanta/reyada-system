{{-- resources/views/settings/index.blade.php --}}
@extends('layouts.app')

@section('title', 'إعدادات النظام')

@php
    // name => [title, description]
    $toggles = [
        'notify_payment'        => ['إشعار عند تسجيل تحصيل جديد', 'يصل للمشرفين لمراجعته وتأكيده'],
        'notify_complaint'      => ['إشعار عند تسجيل شكوى جديدة', 'يصل للمسؤول عن الشكاوى'],
        'notify_broken_promise' => ['إشعار عند كسر وعد دفع', 'يصل للموظف ومشرفه'],
        'daily_digest'          => ['ملخص يومي بالبريد الإلكتروني', 'تقرير مختصر كل صباح للمدير'],
    ];

    /*
    |--------------------------------------------------------------------------
    | Safe frontend values
    |--------------------------------------------------------------------------
    | We intentionally use only settings that already exist in this page.
    | This prevents the page from depending on new controller/database fields.
    */

    $companyName = old('company_name', $s['company_name'] ?? '');
    $companyPhone = old('company_phone', $s['company_phone'] ?? '');
    $companyEmail = old('company_email', $s['company_email'] ?? '');
    $companyAddress = old('company_address', $s['company_address'] ?? '');

    $defaultPerPage = old('default_per_page', $s['default_per_page'] ?? '');
    $maxCases = old('max_cases_per_employee', $s['max_cases_per_employee'] ?? '');
    $ptpDefaultDays = old('ptp_default_days', $s['ptp_default_days'] ?? '');
    $ptpAlertDays = old('ptp_overdue_alert_days', $s['ptp_overdue_alert_days'] ?? '');

    $loginAttempts = old('login_max_attempts', $s['login_max_attempts'] ?? '');
    $sessionMinutes = old('session_minutes', $s['session_minutes'] ?? '');

    $notificationValues = [];

    foreach ($toggles as $name => [$label, $hint]) {
        $notificationValues[$name] = (bool) old($name, $s[$name] ?? false);
    }

    $enabledNotifications = collect($notificationValues)->filter()->count();
    $notificationCount = count($notificationValues);

    $companyFields = [
        $companyName,
        $companyPhone,
        $companyEmail,
        $companyAddress,
    ];

    $completedCompanyFields = collect($companyFields)
        ->filter(fn ($value) => filled($value))
        ->count();

    $companyCompletion = count($companyFields) > 0
        ? (int) round(($completedCompanyFields / count($companyFields)) * 100)
        : 0;

    $collectionValues = [
        $defaultPerPage,
        $maxCases,
        $ptpDefaultDays,
        $ptpAlertDays,
    ];

    $completedCollectionFields = collect($collectionValues)
        ->filter(fn ($value) => filled($value))
        ->count();

    $collectionCompletion = count($collectionValues) > 0
        ? (int) round(($completedCollectionFields / count($collectionValues)) * 100)
        : 0;

    $securityValues = [
        $loginAttempts,
        $sessionMinutes,
    ];

    $completedSecurityFields = collect($securityValues)
        ->filter(fn ($value) => filled($value))
        ->count();

    $securityCompletion = count($securityValues) > 0
        ? (int) round(($completedSecurityFields / count($securityValues)) * 100)
        : 0;

    $overallCompletion = (int) round(
        collect([
            $companyCompletion,
            $collectionCompletion,
            $securityCompletion,
            $notificationCount > 0 ? 100 : 0,
        ])->avg()
    );
@endphp

@section('content')

    {{-- =========================================================
         PAGE HEADER
    ========================================================== --}}
    <x-page-header
        title="إعدادات النظام"
        subtitle="بيانات الشركة وقواعد التحصيل والأمان والإشعارات"
        icon="fa-gear"
    />

    <x-flash />

    {{-- =========================================================
         SETTINGS OVERVIEW
    ========================================================== --}}
    <section
        id="settingsOverview"
        class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4"
    >

        {{-- Overall configuration --}}
        <div class="card relative overflow-hidden">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs text-muted">اكتمال الإعدادات</p>
                    <p id="overallCompletion" class="mt-2 text-2xl font-bold text-fg">
                        {{ $overallCompletion }}%
                    </p>
                </div>

                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand/10 text-brand">
                    <i class="fa-solid fa-chart-pie"></i>
                </span>
            </div>

            <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-white/5">
                <div
                    id="overallProgress"
                    class="h-full rounded-full bg-brand transition-all duration-300"
                    style="width: {{ $overallCompletion }}%"
                ></div>
            </div>

            <p class="mt-2 text-[11px] text-dim">
                مستوى جاهزية إعدادات النظام
            </p>
        </div>

        {{-- Company --}}
        <div class="card relative overflow-hidden">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs text-muted">بيانات الشركة</p>
                    <p id="companyCompletion" class="mt-2 text-2xl font-bold text-fg">
                        {{ $companyCompletion }}%
                    </p>
                </div>

                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-info/10 text-info">
                    <i class="fa-solid fa-building"></i>
                </span>
            </div>

            <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-white/5">
                <div
                    id="companyProgress"
                    class="h-full rounded-full bg-info transition-all duration-300"
                    style="width: {{ $companyCompletion }}%"
                ></div>
            </div>

            <p class="mt-2 text-[11px] text-dim">
                {{ $completedCompanyFields }} من {{ count($companyFields) }} حقول مكتملة
            </p>
        </div>

        {{-- Collection --}}
        <div class="card relative overflow-hidden">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs text-muted">قواعد التحصيل</p>
                    <p id="collectionCompletion" class="mt-2 text-2xl font-bold text-fg">
                        {{ $collectionCompletion }}%
                    </p>
                </div>

                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-warning/10 text-warning">
                    <i class="fa-solid fa-sliders"></i>
                </span>
            </div>

            <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-white/5">
                <div
                    id="collectionProgress"
                    class="h-full rounded-full bg-warning transition-all duration-300"
                    style="width: {{ $collectionCompletion }}%"
                ></div>
            </div>

            <p class="mt-2 text-[11px] text-dim">
                {{ $completedCollectionFields }} من {{ count($collectionValues) }} حقول مكتملة
            </p>
        </div>

        {{-- Security --}}
        <div class="card relative overflow-hidden">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs text-muted">الأمان</p>
                    <p id="securityCompletion" class="mt-2 text-2xl font-bold text-fg">
                        {{ $securityCompletion }}%
                    </p>
                </div>

                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-danger/10 text-danger">
                    <i class="fa-solid fa-shield-halved"></i>
                </span>
            </div>

            <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-white/5">
                <div
                    id="securityProgress"
                    class="h-full rounded-full bg-danger transition-all duration-300"
                    style="width: {{ $securityCompletion }}%"
                ></div>
            </div>

            <p class="mt-2 text-[11px] text-dim">
                إعدادات الحماية والجلسات
            </p>
        </div>

    </section>

    {{-- =========================================================
         CONTROL BAR
    ========================================================== --}}
    <div
        id="settingsToolbar"
        class="card sticky top-4 z-30 mb-6"
    >
        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

            <div class="flex flex-1 flex-wrap items-center gap-2">

                {{-- Search --}}
                <div class="relative min-w-[220px] flex-1 lg:max-w-md">
                    <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-dim"></i>

                    <input
                        id="settingsSearch"
                        type="search"
                        autocomplete="off"
                        placeholder="ابحث داخل الإعدادات..."
                        class="form-input pl-9"
                        aria-label="البحث داخل الإعدادات"
                    >
                </div>

                {{-- Section navigation --}}
                <button
                    type="button"
                    class="btn btn-secondary btn-sm"
                    data-scroll-section="companySection"
                    title="بيانات الشركة"
                >
                    <i class="fa-solid fa-building text-xs"></i>
                    الشركة
                </button>

                <button
                    type="button"
                    class="btn btn-secondary btn-sm"
                    data-scroll-section="collectionSection"
                    title="قواعد التحصيل"
                >
                    <i class="fa-solid fa-sliders text-xs"></i>
                    التحصيل
                </button>

                <button
                    type="button"
                    class="btn btn-secondary btn-sm"
                    data-scroll-section="securitySection"
                    title="الأمان"
                >
                    <i class="fa-solid fa-shield-halved text-xs"></i>
                    الأمان
                </button>

                <button
                    type="button"
                    class="btn btn-secondary btn-sm"
                    data-scroll-section="notificationsSection"
                    title="الإشعارات"
                >
                    <i class="fa-solid fa-bell text-xs"></i>
                    الإشعارات
                </button>

            </div>

            <div class="flex items-center gap-2">

                {{-- Unsaved indicator --}}
                <span
                    id="unsavedIndicator"
                    class="hidden items-center gap-2 rounded-lg border border-warning/20 bg-warning/[0.05] px-3 py-2 text-[11px] text-warning"
                >
                    <i class="fa-solid fa-circle text-[6px]"></i>
                    تغييرات غير محفوظة
                </span>

                <button
                    type="button"
                    id="resetChanges"
                    class="btn btn-secondary btn-sm"
                    disabled
                    title="إلغاء التغييرات"
                >
                    <i class="fa-solid fa-rotate-left text-xs"></i>
                    تراجع
                </button>

                <button
                    type="submit"
                    form="settingsForm"
                    id="toolbarSave"
                    class="btn btn-primary btn-sm"
                    title="حفظ الإعدادات - Ctrl + S"
                >
                    <i class="fa-solid fa-floppy-disk text-xs"></i>
                    حفظ
                </button>

            </div>
        </div>

        <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-line pt-3">
            <div class="flex flex-wrap items-center gap-3 text-[11px] text-dim">

                <span>
                    <i class="fa-solid fa-circle-check mr-1 text-brand"></i>
                    <span id="enabledNotifications">{{ $enabledNotifications }}</span>
                    إشعارات مفعلة
                </span>

                <span>
                    <i class="fa-solid fa-users mr-1 text-info"></i>
                    <span id="casesCapacity">{{ $maxCases ?: '—' }}</span>
                    حالة / موظف
                </span>

                <span>
                    <i class="fa-solid fa-clock mr-1 text-warning"></i>
                    <span id="sessionSummary">{{ $sessionMinutes ?: '—' }}</span>
                    دقيقة للجلسة
                </span>

            </div>

            <span class="text-[10px] text-dim">
                Ctrl + S للحفظ · Ctrl + K للبحث · Esc لإغلاق البحث
            </span>
        </div>
    </div>

    {{-- =========================================================
         MAIN FORM
    ========================================================== --}}
    <form
        id="settingsForm"
        method="POST"
        action="{{ route('settings.update') }}"
        class="space-y-4"
    >

        @csrf
        @method('PUT')

        {{-- =====================================================
             SETTINGS GRID
        ====================================================== --}}
        <div class="grid grid-cols-1 gap-4 xl:grid-cols-2">

            {{-- =================================================
                 COMPANY
            ================================================== --}}
            <div id="companySection" data-settings-section data-section-name="بيانات الشركة">

                <x-section-card
                    title="بيانات الشركة"
                    icon="fa-building"
                    color="info"
                >

                    <div class="mb-4 flex items-center justify-between gap-3 rounded-xl border border-info/10 bg-info/[0.03] px-3 py-2">
                        <div class="flex items-center gap-2 text-[11px] text-dim">
                            <i class="fa-solid fa-circle-info text-info"></i>
                            <span>تظهر هذه البيانات في أجزاء مختلفة من النظام.</span>
                        </div>

                        <span
                            class="settings-section-status text-[10px] text-brand"
                            data-section-status
                        >
                            <i class="fa-solid fa-check"></i>
                            محفوظ
                        </span>
                    </div>

                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">

                        <div class="md:col-span-2">
                            <x-form-field
                                name="company_name"
                                label="اسم الشركة"
                                :value="$companyName"
                                autocomplete="organization"
                            />
                        </div>

                        <x-form-field
                            name="company_phone"
                            label="الهاتف"
                            :value="$companyPhone"
                            dir="ltr"
                            type="tel"
                            autocomplete="tel"
                        />

                        <x-form-field
                            name="company_email"
                            label="البريد الإلكتروني"
                            type="email"
                            :value="$companyEmail"
                            dir="ltr"
                            autocomplete="email"
                        />

                        <div class="md:col-span-2">
                            <x-form-field
                                name="company_address"
                                label="العنوان"
                                :value="$companyAddress"
                                autocomplete="street-address"
                            />
                        </div>

                    </div>

                    {{-- Company completion --}}
                    <div class="mt-4 rounded-xl border border-line bg-white/[0.02] p-3">

                        <div class="mb-2 flex items-center justify-between gap-3">
                            <span class="text-[11px] text-muted">
                                اكتمال بيانات الشركة
                            </span>

                            <b
                                id="companyCompletionInline"
                                class="text-xs text-info"
                            >
                                {{ $companyCompletion }}%
                            </b>
                        </div>

                        <div class="h-1.5 overflow-hidden rounded-full bg-white/5">
                            <div
                                id="companyProgressInline"
                                class="h-full rounded-full bg-info transition-all duration-300"
                                style="width: {{ $companyCompletion }}%"
                            ></div>
                        </div>

                    </div>

                </x-section-card>

            </div>

            {{-- =================================================
                 COLLECTION RULES
            ================================================== --}}
            <div id="collectionSection" data-settings-section data-section-name="قواعد التحصيل">

                <x-section-card
                    title="قواعد التحصيل"
                    icon="fa-sliders"
                    color="warning"
                >

                    <div class="mb-4 flex items-center justify-between gap-3 rounded-xl border border-warning/10 bg-warning/[0.03] px-3 py-2">

                        <div class="flex items-center gap-2 text-[11px] text-dim">
                            <i class="fa-solid fa-gauge-high text-warning"></i>
                            <span>تتحكم هذه القواعد في سلوك عمليات التحصيل والتوزيع.</span>
                        </div>

                        <span
                            class="settings-section-status text-[10px] text-brand"
                            data-section-status
                        >
                            <i class="fa-solid fa-check"></i>
                            محفوظ
                        </span>

                    </div>

                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">

                        <x-select-field
                            name="default_per_page"
                            label="عدد الصفوف الافتراضي في الجداول"
                            :options="$perPage"
                            :value="$defaultPerPage"
                        />

                        <x-form-field
                            name="max_cases_per_employee"
                            label="أقصى عدد حالات لكل موظف"
                            type="number"
                            min="1"
                            max="500"
                            :value="$maxCases"
                        />

                        <x-form-field
                            name="ptp_default_days"
                            label="موعد الوعد الافتراضي (بعد كم يوم)"
                            type="number"
                            min="0"
                            max="30"
                            :value="$ptpDefaultDays"
                        />

                        <x-form-field
                            name="ptp_overdue_alert_days"
                            label="تنبيه الوعد المتأخر بعد (أيام)"
                            type="number"
                            min="0"
                            max="30"
                            :value="$ptpAlertDays"
                        />

                    </div>

                    <p class="mt-3 text-[11px] leading-5 text-dim">
                        أقصى عدد حالات يُستخدم في صفحة توزيع الحالات كسعة لكل موظف.
                    </p>

                    {{-- Live collection summary --}}
                    <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-3">

                        <div class="rounded-xl border border-line bg-white/[0.02] p-3">
                            <p class="text-[10px] text-dim">سعة الموظف</p>
                            <p id="capacityPreview" class="mt-1 font-bold text-fg">
                                {{ $maxCases ?: '—' }}
                            </p>
                        </div>

                        <div class="rounded-xl border border-line bg-white/[0.02] p-3">
                            <p class="text-[10px] text-dim">الوعد الافتراضي</p>
                            <p id="ptpPreview" class="mt-1 font-bold text-fg">
                                {{ $ptpDefaultDays !== '' ? $ptpDefaultDays . ' يوم' : '—' }}
                            </p>
                        </div>

                        <div class="rounded-xl border border-line bg-white/[0.02] p-3">
                            <p class="text-[10px] text-dim">تنبيه التأخير</p>
                            <p id="alertPreview" class="mt-1 font-bold text-fg">
                                {{ $ptpAlertDays !== '' ? $ptpAlertDays . ' يوم' : '—' }}
                            </p>
                        </div>

                    </div>

                </x-section-card>

            </div>

            {{-- =================================================
                 SECURITY
            ================================================== --}}
            <div id="securitySection" data-settings-section data-section-name="الأمان">

                <x-section-card
                    title="الأمان"
                    icon="fa-shield-halved"
                    color="danger"
                >

                    <div class="mb-4 flex items-center justify-between gap-3 rounded-xl border border-danger/10 bg-danger/[0.03] px-3 py-2">

                        <div class="flex items-center gap-2 text-[11px] text-dim">
                            <i class="fa-solid fa-lock text-danger"></i>
                            <span>إعدادات مهمة للتحكم في جلسات المستخدمين ومحاولات الدخول.</span>
                        </div>

                        <span
                            id="securityLevel"
                            class="text-[10px] text-brand"
                        >
                            جيد
                        </span>

                    </div>

                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">

                        <div class="relative">
                            <x-form-field
                                name="login_max_attempts"
                                label="محاولات الدخول الخاطئة قبل الحظر"
                                type="number"
                                min="3"
                                max="10"
                                :value="$loginAttempts"
                            />

                            <span
                                id="loginAttemptsHint"
                                class="mt-1 block text-[10px] text-dim"
                            >
                                {{ $loginAttempts ? $loginAttempts . ' محاولات مسموحة' : 'لم يتم تحديد القيمة' }}
                            </span>
                        </div>

                        <div>
                            <x-form-field
                                name="session_minutes"
                                label="مدة الجلسة بدون نشاط (دقيقة)"
                                type="number"
                                min="15"
                                max="1440"
                                :value="$sessionMinutes"
                            />

                            <span
                                id="sessionHint"
                                class="mt-1 block text-[10px] text-dim"
                            >
                                {{ $sessionMinutes ? $sessionMinutes . ' دقيقة' : 'لم يتم تحديد القيمة' }}
                            </span>
                        </div>

                    </div>

                    {{-- Security indicators --}}
                    <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-2">

                        <div class="rounded-xl border border-line bg-white/[0.02] p-3">

                            <div class="flex items-center justify-between">
                                <span class="text-[10px] text-dim">
                                    حماية تسجيل الدخول
                                </span>

                                <i class="fa-solid fa-user-shield text-danger"></i>
                            </div>

                            <p
                                id="loginSecurityPreview"
                                class="mt-2 text-sm font-bold text-fg"
                            >
                                {{ $loginAttempts ? 'مضبوطة' : 'تحتاج ضبط' }}
                            </p>

                        </div>

                        <div class="rounded-xl border border-line bg-white/[0.02] p-3">

                            <div class="flex items-center justify-between">
                                <span class="text-[10px] text-dim">
                                    إدارة الجلسة
                                </span>

                                <i class="fa-solid fa-clock text-warning"></i>
                            </div>

                            <p
                                id="sessionSecurityPreview"
                                class="mt-2 text-sm font-bold text-fg"
                            >
                                {{ $sessionMinutes ? 'مضبوطة' : 'تحتاج ضبط' }}
                            </p>

                        </div>

                    </div>

                </x-section-card>

            </div>

            {{-- =================================================
                 NOTIFICATIONS
            ================================================== --}}
            <div id="notificationsSection" data-settings-section data-section-name="الإشعارات">

                <x-section-card
                    title="الإشعارات"
                    icon="fa-bell"
                    color="brand"
                >

                    <div class="mb-4 flex items-center justify-between gap-3 rounded-xl border border-brand/10 bg-brand/[0.03] px-3 py-2">

                        <div class="flex items-center gap-2 text-[11px] text-dim">
                            <i class="fa-solid fa-bell text-brand"></i>
                            <span>تحكم في الأحداث التي تحتاج متابعة داخل النظام.</span>
                        </div>

                        <span
                            id="notificationStatus"
                            class="text-[10px] text-brand"
                        >
                            {{ $enabledNotifications }} / {{ $notificationCount }} مفعلة
                        </span>

                    </div>

                    <div class="space-y-2">

                        @foreach($toggles as $name => [$label, $hint])

                            <label
                                class="check-row settings-toggle-row transition"
                                data-setting-row
                                data-setting-label="{{ $label }}"
                            >

                                <input
                                    type="hidden"
                                    name="{{ $name }}"
                                    value="0"
                                >

                                <input
                                    type="checkbox"
                                    name="{{ $name }}"
                                    value="1"
                                    class="accent-brand"
                                    @checked($notificationValues[$name])
                                >

                                <span class="min-w-0 flex-1">

                                    <b class="text-fg">
                                        {{ $label }}
                                    </b>

                                    <span class="block text-[11px] text-dim">
                                        {{ $hint }}
                                    </span>

                                </span>

                                <span
                                    class="notification-state shrink-0 text-[10px] text-dim"
                                    data-notification-state
                                >
                                    {{ $notificationValues[$name] ? 'مفعل' : 'متوقف' }}
                                </span>

                            </label>

                        @endforeach

                    </div>

                    <div class="mt-4 rounded-xl border border-line bg-white/[0.02] p-3">

                        <div class="flex items-start gap-3">

                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-brand/10 text-brand">
                                <i class="fa-solid fa-lightbulb text-sm"></i>
                            </span>

                            <div>
                                <p class="text-xs font-semibold text-fg">
                                    نصيحة تشغيلية
                                </p>

                                <p class="mt-1 text-[11px] leading-5 text-dim">
                                    فعّل الإشعارات التي يحتاجها فريق التشغيل يومياً، وراجع
                                    الإشعارات غير الضرورية لتقليل الضوضاء داخل النظام.
                                </p>
                            </div>

                        </div>

                    </div>

                </x-section-card>

            </div>

        </div>

        {{-- =====================================================
             CONFIGURATION HEALTH
        ====================================================== --}}
        <section
            id="configurationHealth"
            class="card"
        >

            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                <div class="flex items-center gap-3">

                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-info/10 text-info">
                        <i class="fa-solid fa-gauge-high"></i>
                    </span>

                    <div>
                        <h2 class="font-bold text-fg">
                            حالة إعدادات النظام
                        </h2>

                        <p class="mt-1 text-[11px] text-dim">
                            ملخص سريع يساعدك على اكتشاف الإعدادات الناقصة قبل الحفظ.
                        </p>
                    </div>

                </div>

                <span
                    id="healthBadge"
                    class="badge badge-outline-info"
                >
                    جاري الفحص
                </span>

            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">

                {{-- Company health --}}
                <div
                    class="rounded-xl border border-line bg-white/[0.02] p-4"
                    data-health-card
                >

                    <div class="flex items-center justify-between gap-3">

                        <div>
                            <p class="text-[11px] text-muted">
                                بيانات الشركة
                            </p>

                            <p
                                id="companyHealthText"
                                class="mt-1 text-xs font-semibold text-fg"
                            >
                                {{ $companyCompletion }}% مكتمل
                            </p>
                        </div>

                        <i class="fa-solid fa-building text-info"></i>

                    </div>

                </div>

                {{-- Collection health --}}
                <div
                    class="rounded-xl border border-line bg-white/[0.02] p-4"
                    data-health-card
                >

                    <div class="flex items-center justify-between gap-3">

                        <div>
                            <p class="text-[11px] text-muted">
                                قواعد التحصيل
                            </p>

                            <p
                                id="collectionHealthText"
                                class="mt-1 text-xs font-semibold text-fg"
                            >
                                {{ $collectionCompletion }}% مكتمل
                            </p>
                        </div>

                        <i class="fa-solid fa-sliders text-warning"></i>

                    </div>

                </div>

                {{-- Security health --}}
                <div
                    class="rounded-xl border border-line bg-white/[0.02] p-4"
                    data-health-card
                >

                    <div class="flex items-center justify-between gap-3">

                        <div>
                            <p class="text-[11px] text-muted">
                                الأمان
                            </p>

                            <p
                                id="securityHealthText"
                                class="mt-1 text-xs font-semibold text-fg"
                            >
                                {{ $securityCompletion }}% مكتمل
                            </p>
                        </div>

                        <i class="fa-solid fa-shield-halved text-danger"></i>

                    </div>

                </div>

                {{-- Notifications health --}}
                <div
                    class="rounded-xl border border-line bg-white/[0.02] p-4"
                    data-health-card
                >

                    <div class="flex items-center justify-between gap-3">

                        <div>
                            <p class="text-[11px] text-muted">
                                الإشعارات
                            </p>

                            <p
                                id="notificationHealthText"
                                class="mt-1 text-xs font-semibold text-fg"
                            >
                                {{ $enabledNotifications }} / {{ $notificationCount }} مفعلة
                            </p>
                        </div>

                        <i class="fa-solid fa-bell text-brand"></i>

                    </div>

                </div>

            </div>

            {{-- Health messages --}}
            <div
                id="healthMessages"
                class="mt-4 space-y-2"
            ></div>

        </section>

        {{-- =====================================================
             FORM ACTIONS
        ====================================================== --}}
        <div
            id="formActions"
            class="card sticky bottom-4 z-20"
        >

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                <div class="flex items-center gap-3">

                    <span
                        id="saveStatusIcon"
                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand/10 text-brand"
                    >
                        <i class="fa-solid fa-check"></i>
                    </span>

                    <div>

                        <p
                            id="saveStatusTitle"
                            class="text-xs font-semibold text-fg"
                        >
                            جميع التغييرات محفوظة
                        </p>

                        <p
                            id="saveStatusDescription"
                            class="mt-1 text-[10px] text-dim"
                        >
                            راجع الإعدادات ثم اضغط حفظ عند الانتهاء.
                        </p>

                    </div>

                </div>

                <div class="flex gap-2">

                    <button
                        type="button"
                        id="resetChangesBottom"
                        class="btn btn-secondary"
                        disabled
                    >
                        <i class="fa-solid fa-rotate-left text-xs"></i>
                        إلغاء التغييرات
                    </button>

                    <button
                        type="submit"
                        id="saveSettings"
                        class="btn btn-primary"
                    >
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        حفظ الإعدادات
                    </button>

                    <a
                        href="{{ route('dashboard') }}"
                        class="btn btn-secondary"
                    >
                        إلغاء
                    </a>

                </div>

            </div>

        </div>

    </form>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    const form = document.getElementById('settingsForm');

    if (!form) {
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | Cached elements
    |--------------------------------------------------------------------------
    | Keep DOM queries cached to avoid unnecessary work and page lag.
    */

    const searchInput = document.getElementById('settingsSearch');

    const saveButtons = [
        document.getElementById('saveSettings'),
        document.getElementById('toolbarSave'),
    ].filter(Boolean);

    const resetButtons = [
        document.getElementById('resetChanges'),
        document.getElementById('resetChangesBottom'),
    ].filter(Boolean);

    const unsavedIndicator = document.getElementById('unsavedIndicator');

    const saveStatusIcon = document.getElementById('saveStatusIcon');
    const saveStatusTitle = document.getElementById('saveStatusTitle');
    const saveStatusDescription = document.getElementById('saveStatusDescription');

    const notificationStatus = document.getElementById('notificationStatus');
    const enabledNotifications = document.getElementById('enabledNotifications');

    const capacityPreview = document.getElementById('capacityPreview');
    const ptpPreview = document.getElementById('ptpPreview');
    const alertPreview = document.getElementById('alertPreview');

    const casesCapacity = document.getElementById('casesCapacity');
    const sessionSummary = document.getElementById('sessionSummary');

    const securityLevel = document.getElementById('securityLevel');
    const loginAttemptsHint = document.getElementById('loginAttemptsHint');
    const sessionHint = document.getElementById('sessionHint');

    const loginSecurityPreview = document.getElementById('loginSecurityPreview');
    const sessionSecurityPreview = document.getElementById('sessionSecurityPreview');

    const overallCompletion = document.getElementById('overallCompletion');
    const overallProgress = document.getElementById('overallProgress');

    const companyCompletion = document.getElementById('companyCompletion');
    const companyProgress = document.getElementById('companyProgress');

    const companyCompletionInline = document.getElementById('companyCompletionInline');
    const companyProgressInline = document.getElementById('companyProgressInline');

    const collectionCompletion = document.getElementById('collectionCompletion');
    const collectionProgress = document.getElementById('collectionProgress');

    const securityCompletion = document.getElementById('securityCompletion');
    const securityProgress = document.getElementById('securityProgress');

    const healthBadge = document.getElementById('healthBadge');
    const healthMessages = document.getElementById('healthMessages');

    const companyHealthText = document.getElementById('companyHealthText');
    const collectionHealthText = document.getElementById('collectionHealthText');
    const securityHealthText = document.getElementById('securityHealthText');
    const notificationHealthText = document.getElementById('notificationHealthText');

    const settingSections = [
        ...document.querySelectorAll('[data-settings-section]')
    ];

    const toggleRows = [
        ...document.querySelectorAll('[data-setting-row]')
    ];

    /*
    |--------------------------------------------------------------------------
    | Original state
    |--------------------------------------------------------------------------
    */

    const originalFormState = new FormData(form);

    function serializeForm() {
        const data = new FormData(form);

        return JSON.stringify(
            [...data.entries()]
                .map(([key, value]) => [key, String(value)])
                .sort((a, b) => {
                    if (a[0] === b[0]) {
                        return a[1].localeCompare(b[1]);
                    }

                    return a[0].localeCompare(b[0]);
                })
        );
    }

    const initialState = serializeForm();

    /*
    |--------------------------------------------------------------------------
    | Unsaved changes
    |--------------------------------------------------------------------------
    */

    let isDirty = false;

    function setDirtyState(dirty) {
        isDirty = dirty;

        if (unsavedIndicator) {
            unsavedIndicator.classList.toggle('hidden', !dirty);
            unsavedIndicator.classList.toggle('inline-flex', dirty);
        }

        resetButtons.forEach(button => {
            button.disabled = !dirty;
        });

        if (saveStatusTitle && saveStatusDescription) {
            if (dirty) {
                saveStatusTitle.textContent = 'توجد تغييرات غير محفوظة';
                saveStatusDescription.textContent =
                    'راجع التعديلات ثم اضغط حفظ الإعدادات.';
            } else {
                saveStatusTitle.textContent = 'جميع التغييرات محفوظة';
                saveStatusDescription.textContent =
                    'رافع الإعدادات ثم اضغط حفظ عند الانتهاء.';
            }
        }

        if (saveStatusIcon) {
            saveStatusIcon.classList.toggle('bg-warning/10', dirty);
            saveStatusIcon.classList.toggle('text-warning', dirty);

            saveStatusIcon.classList.toggle('bg-brand/10', !dirty);
            saveStatusIcon.classList.toggle('text-brand', !dirty);

            saveStatusIcon.innerHTML = dirty
                ? '<i class="fa-solid fa-pen-to-square"></i>'
                : '<i class="fa-solid fa-check"></i>';
        }

        settingSections.forEach(section => {
            const status = section.querySelector('[data-section-status]');

            if (!status) {
                return;
            }

            status.innerHTML = dirty
                ? '<i class="fa-solid fa-circle text-[5px]"></i> تمتلك تغييرات'
                : '<i class="fa-solid fa-check"></i> محفوظ';

            status.classList.toggle('text-warning', dirty);
            status.classList.toggle('text-brand', !dirty);
        });
    }

    function refreshDirtyState() {
        setDirtyState(serializeForm() !== initialState);
    }

    /*
    |--------------------------------------------------------------------------
    | Field collection
    |--------------------------------------------------------------------------
    */

    const companyFields = [
        form.querySelector('[name="company_name"]'),
        form.querySelector('[name="company_phone"]'),
        form.querySelector('[name="company_email"]'),
        form.querySelector('[name="company_address"]'),
    ].filter(Boolean);

    const collectionFields = [
        form.querySelector('[name="default_per_page"]'),
        form.querySelector('[name="max_cases_per_employee"]'),
        form.querySelector('[name="ptp_default_days"]'),
        form.querySelector('[name="ptp_overdue_alert_days"]'),
    ].filter(Boolean);

    const securityFields = [
        form.querySelector('[name="login_max_attempts"]'),
        form.querySelector('[name="session_minutes"]'),
    ].filter(Boolean);

    const notificationFields = [
        ...form.querySelectorAll(
            'input[type="checkbox"][name="notify_payment"],' +
            'input[type="checkbox"][name="notify_complaint"],' +
            'input[type="checkbox"][name="notify_broken_promise"],' +
            'input[type="checkbox"][name="daily_digest"]'
        )
    ];

    /*
    |--------------------------------------------------------------------------
    | Completion helpers
    |--------------------------------------------------------------------------
    */

    function calculateCompletion(fields) {
        if (!fields.length) {
            return 0;
        }

        const completed = fields.filter(field => {
            return String(field.value ?? '').trim() !== '';
        }).length;

        return Math.round((completed / fields.length) * 100);
    }

    function setProgress(element, value) {
        if (!element) {
            return;
        }

        element.style.width = Math.max(0, Math.min(100, value)) + '%';
    }

    function refreshCompletion() {
        const company = calculateCompletion(companyFields);
        const collection = calculateCompletion(collectionFields);
        const security = calculateCompletion(securityFields);

        const notifications = notificationFields.filter(
            checkbox => checkbox.checked
        ).length;

        const overall = Math.round(
            (
                company +
                collection +
                security +
                (notificationFields.length ? 100 : 0)
            ) / 4
        );

        if (companyCompletion) {
            companyCompletion.textContent = company + '%';
        }

        if (companyCompletionInline) {
            companyCompletionInline.textContent = company + '%';
        }

        setProgress(companyProgress, company);
        setProgress(companyProgressInline, company);

        if (collectionCompletion) {
            collectionCompletion.textContent = collection + '%';
        }

        setProgress(collectionProgress, collection);

        if (securityCompletion) {
            securityCompletion.textContent = security + '%';
        }

        setProgress(securityProgress, security);

        if (overallCompletion) {
            overallCompletion.textContent = overall + '%';
        }

        setProgress(overallProgress, overall);

        if (companyHealthText) {
            companyHealthText.textContent = company + '% مكتمل';
        }

        if (collectionHealthText) {
            collectionHealthText.textContent = collection + '% مكتمل';
        }

        if (securityHealthText) {
            securityHealthText.textContent = security + '% مكتمل';
        }

        if (notificationHealthText) {
            notificationHealthText.textContent =
                notifications + ' / ' + notificationFields.length + ' مفعلة';
        }

        if (healthBadge) {
            if (overall >= 90) {
                healthBadge.textContent = 'إعدادات ممتازة';
                healthBadge.className = 'badge badge-outline-success';
            } else if (overall >= 70) {
                healthBadge.textContent = 'إعدادات جيدة';
                healthBadge.className = 'badge badge-outline-info';
            } else {
                healthBadge.textContent = 'تحتاج مراجعة';
                healthBadge.className = 'badge badge-outline-warning';
            }
        }

        if (enabledNotifications) {
            enabledNotifications.textContent = notifications;
        }

        if (notificationStatus) {
            notificationStatus.textContent =
                notifications + ' / ' + notificationFields.length + ' مفعلة';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Collection live preview
    |--------------------------------------------------------------------------
    */

    function refreshCollectionPreview() {
        const maxCasesField = form.querySelector(
            '[name="max_cases_per_employee"]'
        );

        const ptpField = form.querySelector(
            '[name="ptp_default_days"]'
        );

        const alertField = form.querySelector(
            '[name="ptp_overdue_alert_days"]'
        );

        const maxCases = maxCasesField?.value || '';
        const ptpDays = ptpField?.value || '';
        const alertDays = alertField?.value || '';

        if (capacityPreview) {
            capacityPreview.textContent = maxCases || '—';
        }

        if (casesCapacity) {
            casesCapacity.textContent = maxCases || '—';
        }

        if (ptpPreview) {
            ptpPreview.textContent = ptpDays
                ? ptpDays + ' يوم'
                : '—';
        }

        if (alertPreview) {
            alertPreview.textContent = alertDays
                ? alertDays + ' يوم'
                : '—';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Security live preview
    |--------------------------------------------------------------------------
    */

    function refreshSecurityPreview() {
        const attemptsField = form.querySelector(
            '[name="login_max_attempts"]'
        );

        const sessionField = form.querySelector(
            '[name="session_minutes"]'
        );

        const attempts = Number(attemptsField?.value || 0);
        const session = Number(sessionField?.value || 0);

        if (loginAttemptsHint) {
            loginAttemptsHint.textContent = attempts
                ? attempts + ' محاولات مسموحة'
                : 'لم يتم تحديد القيمة';
        }

        if (sessionHint) {
            sessionHint.textContent = session
                ? session + ' دقيقة'
                : 'لم يتم تحديد القيمة';
        }

        if (sessionSummary) {
            sessionSummary.textContent = session || '—';
        }

        if (loginSecurityPreview) {
            loginSecurityPreview.textContent =
                attempts >= 3
                    ? 'مضبوطة'
                    : 'تحتاج ضبط';
        }

        if (sessionSecurityPreview) {
            sessionSecurityPreview.textContent =
                session >= 15
                    ? 'مضبوطة'
                    : 'تحتاج ضبط';
        }

        if (securityLevel) {
            if (attempts >= 3 && attempts <= 5 && session >= 15 && session <= 480) {
                securityLevel.textContent = 'مستوى جيد';
                securityLevel.className = 'text-[10px] text-brand';
            } else if (attempts && session) {
                securityLevel.textContent = 'يحتاج مراجعة';
                securityLevel.className = 'text-[10px] text-warning';
            } else {
                securityLevel.textContent = 'غير مكتمل';
                securityLevel.className = 'text-[10px] text-danger';
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Notification state
    |--------------------------------------------------------------------------
    */

    function refreshNotifications() {
        const checked = notificationFields.filter(
            checkbox => checkbox.checked
        ).length;

        toggleRows.forEach(row => {
            const checkbox = row.querySelector(
                'input[type="checkbox"]'
            );

            const state = row.querySelector(
                '[data-notification-state]'
            );

            if (!checkbox || !state) {
                return;
            }

            state.textContent = checkbox.checked
                ? 'مفعل'
                : 'متوقف';

            state.classList.toggle(
                'text-brand',
                checkbox.checked
            );

            state.classList.toggle(
                'text-dim',
                !checkbox.checked
            );
        });

        if (notificationStatus) {
            notificationStatus.textContent =
                checked + ' / ' + notificationFields.length + ' مفعلة';
        }

        if (enabledNotifications) {
            enabledNotifications.textContent = checked;
        }

        if (notificationHealthText) {
            notificationHealthText.textContent =
                checked + ' / ' + notificationFields.length + ' مفعلة';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Configuration health messages
    |--------------------------------------------------------------------------
    */

    function refreshHealthMessages() {
        if (!healthMessages) {
            return;
        }

        const messages = [];

        const company = calculateCompletion(companyFields);
        const collection = calculateCompletion(collectionFields);
        const security = calculateCompletion(securityFields);

        const attemptsField = form.querySelector(
            '[name="login_max_attempts"]'
        );

        const sessionField = form.querySelector(
            '[name="session_minutes"]'
        );

        const attempts = Number(attemptsField?.value || 0);
        const session = Number(sessionField?.value || 0);

        if (company < 100) {
            messages.push({
                type: 'info',
                icon: 'fa-building',
                text: 'بيانات الشركة غير مكتملة بالكامل.'
            });
        }

        if (collection < 100) {
            messages.push({
                type: 'warning',
                icon: 'fa-sliders',
                text: 'هناك قواعد تحصيل لم يتم تحديدها.'
            });
        }

        if (!attempts || !session) {
            messages.push({
                type: 'danger',
                icon: 'fa-shield-halved',
                text: 'راجع إعدادات الأمان قبل تشغيل النظام بشكل كامل.'
            });
        }

        if (
            attempts > 5 ||
            (session && session > 720)
        ) {
            messages.push({
                type: 'warning',
                icon: 'fa-triangle-exclamation',
                text: 'إعدادات الأمان الحالية تسمح بمدة أو عدد محاولات مرتفع نسبياً.'
            });
        }

        if (!messages.length) {
            messages.push({
                type: 'success',
                icon: 'fa-circle-check',
                text: 'لا توجد ملاحظات مهمة على إعدادات النظام الحالية.'
            });
        }

        healthMessages.innerHTML = messages.map(message => {
            const classes = {
                info: 'border-info/20 bg-info/[0.05] text-info',
                warning: 'border-warning/20 bg-warning/[0.05] text-warning',
                danger: 'border-danger/20 bg-danger/[0.05] text-danger',
                success: 'border-brand/20 bg-brand/[0.05] text-brand',
            };

            return `
                <div class="flex items-center gap-3 rounded-xl border px-3 py-2 text-[11px] ${classes[message.type]}">
                    <i class="fa-solid ${message.icon}"></i>
                    <span>${message.text}</span>
                </div>
            `;
        }).join('');
    }

    /*
    |--------------------------------------------------------------------------
    | Refresh everything
    |--------------------------------------------------------------------------
    */

    function refreshUI() {
        refreshCompletion();
        refreshCollectionPreview();
        refreshSecurityPreview();
        refreshNotifications();
        refreshHealthMessages();
        refreshDirtyState();
    }

    /*
    |--------------------------------------------------------------------------
    | Form changes
    |--------------------------------------------------------------------------
    */

    form.addEventListener('input', refreshUI);
    form.addEventListener('change', refreshUI);

    /*
    |--------------------------------------------------------------------------
    | Search inside settings
    |--------------------------------------------------------------------------
    */

    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = this.value.trim().toLowerCase();

            settingSections.forEach(section => {
                if (!query) {
                    section.classList.remove('hidden');
                    return;
                }

                const text = section.textContent.toLowerCase();

                section.classList.toggle(
                    'hidden',
                    !text.includes(query)
                );
            });
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Section navigation
    |--------------------------------------------------------------------------
    */

    document.addEventListener('click', function (event) {

        const navigationButton = event.target.closest(
            '[data-scroll-section]'
        );

        if (!navigationButton) {
            return;
        }

        const sectionId = navigationButton.dataset.scrollSection;
        const section = document.getElementById(sectionId);

        if (!section) {
            return;
        }

        section.classList.remove('hidden');

        section.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Reset changes
    |--------------------------------------------------------------------------
    */

    function resetFormToInitialState() {
        if (!isDirty) {
            return;
        }

        if (!window.confirm('هل تريد إلغاء جميع التغييرات غير المحفوظة؟')) {
            return;
        }

        /*
         * Reset native controls first.
         */
        form.reset();

        /*
         * Some browsers/components may not refresh immediately.
         * Trigger our own UI refresh afterwards.
         */
        window.requestAnimationFrame(() => {
            refreshUI();
        });
    }

    resetButtons.forEach(button => {
        button.addEventListener(
            'click',
            resetFormToInitialState
        );
    });

    /*
    |--------------------------------------------------------------------------
    | Save buttons
    |--------------------------------------------------------------------------
    */

    let submitting = false;

    function setSavingState() {
        saveButtons.forEach(button => {
            button.disabled = true;

            button.dataset.originalHtml =
                button.dataset.originalHtml || button.innerHTML;

            button.innerHTML =
                '<i class="fa-solid fa-spinner fa-spin text-xs"></i> جارٍ الحفظ...';
        });
    }

    form.addEventListener('submit', function (event) {

        if (submitting) {
            event.preventDefault();
            return;
        }

        submitting = true;

        setSavingState();
    });

    /*
    |--------------------------------------------------------------------------
    | Keyboard shortcuts
    |--------------------------------------------------------------------------
    */

    document.addEventListener('keydown', function (event) {

        /*
         * Ctrl + S
         */
        if (
            (event.ctrlKey || event.metaKey) &&
            event.key.toLowerCase() === 's'
        ) {
            event.preventDefault();

            if (!submitting) {
                form.requestSubmit();
            }

            return;
        }

        /*
         * Ctrl + K
         */
        if (
            (event.ctrlKey || event.metaKey) &&
            event.key.toLowerCase() === 'k'
        ) {
            event.preventDefault();

            searchInput?.focus();
            searchInput?.select();

            return;
        }

        /*
         * Escape
         */
        if (
            event.key === 'Escape' &&
            document.activeElement === searchInput
        ) {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
            searchInput.blur();
        }
    });

    /*
    |--------------------------------------------------------------------------
    | Warn before leaving with unsaved changes
    |--------------------------------------------------------------------------
    */

    window.addEventListener('beforeunload', function (event) {

        if (!isDirty || submitting) {
            return;
        }

        event.preventDefault();
        event.returnValue = '';
    });

    /*
    |--------------------------------------------------------------------------
    | Prevent accidental navigation through normal links
    |--------------------------------------------------------------------------
    */

    document.addEventListener('click', function (event) {

        if (!isDirty || submitting) {
            return;
        }

        const link = event.target.closest('a[href]');

        if (!link) {
            return;
        }

        if (
            link.getAttribute('href') === '#' ||
            link.target === '_blank'
        ) {
            return;
        }

        const confirmed = window.confirm(
            'لديك تغييرات غير محفوظة. هل تريد مغادرة الصفحة؟'
        );

        if (!confirmed) {
            event.preventDefault();
        }
    });

    /*
    |--------------------------------------------------------------------------
    | Initial state
    |--------------------------------------------------------------------------
    */

    refreshUI();

})();
</script>
@endpush