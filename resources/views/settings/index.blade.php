{{-- resources/views/settings/index.blade.php --}}
@extends('layouts.app')

@section('title', 'إعدادات النظام')

@php
    /*
    |--------------------------------------------------------------------------
    | Existing settings
    |--------------------------------------------------------------------------
    | These are the settings currently supported by the existing controller.
    | Do not add unknown input names here until the backend architecture is
    | updated.
    */

    $toggles = [
        'notify_payment'        => ['إشعار عند تسجيل تحصيل جديد', 'يصل للمشرفين لمراجعته وتأكيده'],
        'notify_complaint'      => ['إشعار عند تسجيل شكوى جديدة', 'يصل للمسؤول عن الشكاوى'],
        'notify_broken_promise' => ['إشعار عند كسر وعد دفع', 'يصل للموظف ومشرفه'],
        'daily_digest'          => ['ملخص يومي بالبريد الإلكتروني', 'تقرير مختصر كل صباح للمدير'],
    ];

    /*
    |--------------------------------------------------------------------------
    | Safe values
    |--------------------------------------------------------------------------
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

    /*
    |--------------------------------------------------------------------------
    | Dashboard calculations
    |--------------------------------------------------------------------------
    */

    $companyFields = [
        $companyName,
        $companyPhone,
        $companyEmail,
        $companyAddress,
    ];

    $completedCompanyFields = collect($companyFields)
        ->filter(fn ($value) => filled($value))
        ->count();

    $companyCompletion = count($companyFields)
        ? (int) round(($completedCompanyFields / count($companyFields)) * 100)
        : 0;

    $collectionFields = [
        $defaultPerPage,
        $maxCases,
        $ptpDefaultDays,
        $ptpAlertDays,
    ];

    $completedCollectionFields = collect($collectionFields)
        ->filter(fn ($value) => filled($value))
        ->count();

    $collectionCompletion = count($collectionFields)
        ? (int) round(($completedCollectionFields / count($collectionFields)) * 100)
        : 0;

    $securityFields = [
        $loginAttempts,
        $sessionMinutes,
    ];

    $completedSecurityFields = collect($securityFields)
        ->filter(fn ($value) => filled($value))
        ->count();

    $securityCompletion = count($securityFields)
        ? (int) round(($completedSecurityFields / count($securityFields)) * 100)
        : 0;

    $enabledNotifications = collect($notificationValues)
        ->filter()
        ->count();

    $notificationCount = count($notificationValues);

    $overallCompletion = (int) round(
        collect([
            $companyCompletion,
            $collectionCompletion,
            $securityCompletion,
            $notificationCount > 0 ? 100 : 0,
        ])->avg()
    );

    /*
    |--------------------------------------------------------------------------
    | Frontend-only control center definitions
    |--------------------------------------------------------------------------
    | These intentionally do NOT become form fields.
    | They are navigation/status controls until the backend architecture is
    | implemented.
    */

    $controlGroups = [
        [
            'id' => 'accessControl',
            'title' => 'الوصول والصلاحيات',
            'description' => 'إدارة الأدوار والصلاحيات والجلسات والحماية المتقدمة.',
            'icon' => 'fa-user-shield',
            'color' => 'info',
            'status' => 'إدارة',
        ],
        [
            'id' => 'auditControl',
            'title' => 'السجل والمراجعة',
            'description' => 'تتبع تغييرات الإعدادات والعمليات الحساسة.',
            'icon' => 'fa-clock-rotate-left',
            'color' => 'warning',
            'status' => 'مراقبة',
        ],
        [
            'id' => 'backupControl',
            'title' => 'النسخ الاحتياطي',
            'description' => 'متابعة حالة النسخ الاحتياطية والبيانات والأرشيف.',
            'icon' => 'fa-database',
            'color' => 'brand',
            'status' => 'حماية',
        ],
        [
            'id' => 'systemControl',
            'title' => 'صحة النظام',
            'description' => 'قاعدة البيانات والكاش والـ Queue والـ Scheduler والتخزين.',
            'icon' => 'fa-server',
            'color' => 'cyan',
            'status' => 'تشخيص',
        ],
    ];
@endphp

@section('content')

    {{-- =========================================================
         HEADER
    ========================================================== --}}
    <x-page-header
        title="إعدادات النظام"
        subtitle="مركز التحكم الرئيسي في قواعد التشغيل والأمان والإشعارات والنظام"
        icon="fa-gear"
    />

    <x-flash />

    {{-- =========================================================
         TOP SYSTEM CONTROL SUMMARY
    ========================================================== --}}
    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        {{-- Overall --}}
        <div class="card relative overflow-hidden">
            <div class="flex items-start justify-between gap-3">

                <div>
                    <p class="text-xs text-muted">جاهزية الإعدادات</p>

                    <p
                        id="overallCompletion"
                        class="mt-2 text-2xl font-bold text-fg"
                    >
                        {{ $overallCompletion }}%
                    </p>

                    <p class="mt-1 text-[10px] text-dim">
                        مستوى اكتمال إعدادات النظام الحالية
                    </p>
                </div>

                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand/10 text-brand">
                    <i class="fa-solid fa-gauge-high"></i>
                </span>

            </div>

            <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-white/5">
                <div
                    id="overallProgress"
                    class="h-full rounded-full bg-brand transition-all duration-300"
                    style="width: {{ $overallCompletion }}%"
                ></div>
            </div>
        </div>

        {{-- Security --}}
        <div class="card relative overflow-hidden">
            <div class="flex items-start justify-between gap-3">

                <div>
                    <p class="text-xs text-muted">حالة الأمان</p>

                    <p
                        id="securityCardStatus"
                        class="mt-2 text-2xl font-bold text-fg"
                    >
                        {{ $securityCompletion }}%
                    </p>

                    <p class="mt-1 text-[10px] text-dim">
                        إعدادات الدخول والجلسات
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
        </div>

        {{-- Collection --}}
        <div class="card relative overflow-hidden">
            <div class="flex items-start justify-between gap-3">

                <div>
                    <p class="text-xs text-muted">قواعد التحصيل</p>

                    <p
                        id="collectionCardStatus"
                        class="mt-2 text-2xl font-bold text-fg"
                    >
                        {{ $collectionCompletion }}%
                    </p>

                    <p class="mt-1 text-[10px] text-dim">
                        قواعد التوزيع والوعد والتحصيل
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
        </div>

        {{-- Notifications --}}
        <div class="card relative overflow-hidden">
            <div class="flex items-start justify-between gap-3">

                <div>
                    <p class="text-xs text-muted">الإشعارات</p>

                    <p
                        id="notificationCardStatus"
                        class="mt-2 text-2xl font-bold text-fg"
                    >
                        {{ $enabledNotifications }}/{{ $notificationCount }}
                    </p>

                    <p class="mt-1 text-[10px] text-dim">
                        قواعد الإشعارات المفعلة
                    </p>
                </div>

                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand/10 text-brand">
                    <i class="fa-solid fa-bell"></i>
                </span>

            </div>

            <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-white/5">
                <div
                    id="notificationProgress"
                    class="h-full rounded-full bg-brand transition-all duration-300"
                    style="width: {{ $notificationCount ? ($enabledNotifications / $notificationCount) * 100 : 0 }}%"
                ></div>
            </div>
        </div>

    </section>

    {{-- =========================================================
         QUICK CONTROL CENTER
    ========================================================== --}}
    <section class="card mb-6">

        <div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

            <div class="flex items-center gap-3">

                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-info/10 text-info">
                    <i class="fa-solid fa-sliders"></i>
                </span>

                <div>
                    <h2 class="font-bold text-fg">
                        مركز التحكم
                    </h2>

                    <p class="mt-1 text-[11px] text-dim">
                        الوصول السريع إلى أهم مناطق إدارة النظام.
                    </p>
                </div>

            </div>

            <span class="badge badge-outline-info">
                System Control Center
            </span>

        </div>

        <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">

            @foreach($controlGroups as $group)

                <button
                    type="button"
                    class="group rounded-xl border border-line bg-white/[0.02] p-4 text-right transition hover:border-white/10 hover:bg-white/[0.04]"
                    data-control-group="{{ $group['id'] }}"
                    data-control-title="{{ $group['title'] }}"
                >

                    <div class="flex items-start justify-between gap-3">

                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-{{ $group['color'] }}/10 text-{{ $group['color'] }}">
                            <i class="fa-solid {{ $group['icon'] }}"></i>
                        </span>

                        <span class="text-[9px] text-dim">
                            {{ $group['status'] }}
                        </span>

                    </div>

                    <h3 class="mt-4 text-sm font-bold text-fg">
                        {{ $group['title'] }}
                    </h3>

                    <p class="mt-1 text-[10px] leading-5 text-dim">
                        {{ $group['description'] }}
                    </p>

                    <div class="mt-4 flex items-center justify-between text-[10px]">

                        <span class="text-muted">
                            عرض التفاصيل
                        </span>

                        <i class="fa-solid fa-arrow-left text-dim transition group-hover:-translate-x-1"></i>

                    </div>

                </button>

            @endforeach

        </div>

    </section>

    {{-- =========================================================
         SEARCH + NAVIGATION
    ========================================================== --}}
    <div
        id="settingsToolbar"
        class="card sticky top-4 z-30 mb-6"
    >

        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">

            <div class="relative min-w-[220px] flex-1 lg:max-w-lg">

                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-dim"></i>

                <input
                    id="settingsSearch"
                    type="search"
                    autocomplete="off"
                    placeholder="ابحث عن إعداد أو وظيفة..."
                    class="form-input pl-9"
                    aria-label="البحث داخل إعدادات النظام"
                >

            </div>

            <div class="flex flex-wrap items-center gap-2">

                <button
                    type="button"
                    class="btn btn-secondary btn-sm"
                    data-scroll-section="companySection"
                >
                    <i class="fa-solid fa-building text-xs"></i>
                    الشركة
                </button>

                <button
                    type="button"
                    class="btn btn-secondary btn-sm"
                    data-scroll-section="collectionSection"
                >
                    <i class="fa-solid fa-sliders text-xs"></i>
                    التحصيل
                </button>

                <button
                    type="button"
                    class="btn btn-secondary btn-sm"
                    data-scroll-section="securitySection"
                >
                    <i class="fa-solid fa-shield-halved text-xs"></i>
                    الأمان
                </button>

                <button
                    type="button"
                    class="btn btn-secondary btn-sm"
                    data-scroll-section="notificationsSection"
                >
                    <i class="fa-solid fa-bell text-xs"></i>
                    الإشعارات
                </button>

            </div>

        </div>

        <div class="mt-3 flex flex-wrap items-center justify-between gap-2 border-t border-line pt-3">

            <div class="flex flex-wrap items-center gap-3 text-[10px] text-dim">

                <span>
                    <i class="fa-solid fa-circle-check mr-1 text-brand"></i>
                    <span id="enabledNotifications">
                        {{ $enabledNotifications }}
                    </span>
                    إشعارات مفعلة
                </span>

                <span>
                    <i class="fa-solid fa-users mr-1 text-info"></i>
                    <span id="casesCapacity">
                        {{ $maxCases ?: '—' }}
                    </span>
                    حالة / موظف
                </span>

                <span>
                    <i class="fa-solid fa-clock mr-1 text-warning"></i>
                    <span id="sessionSummary">
                        {{ $sessionMinutes ?: '—' }}
                    </span>
                    دقيقة للجلسة
                </span>

            </div>

            <span class="text-[10px] text-dim">
                Ctrl + K للبحث · Ctrl + S للحفظ
            </span>

        </div>

    </div>

    {{-- =========================================================
         REAL SETTINGS FORM
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
            <div
                id="companySection"
                data-settings-section
                data-section-name="بيانات الشركة"
            >

                <x-section-card
                    title="بيانات الشركة"
                    icon="fa-building"
                    color="info"
                >

                    <div class="mb-4 flex items-center justify-between gap-3 rounded-xl border border-info/10 bg-info/[0.03] px-3 py-2">

                        <div class="flex items-center gap-2 text-[11px] text-dim">
                            <i class="fa-solid fa-circle-info text-info"></i>
                            <span>
                                البيانات الأساسية التي تظهر في أجزاء مختلفة من النظام.
                            </span>
                        </div>

                        <span
                            data-section-status
                            class="text-[10px] text-brand"
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
                            />

                        </div>

                        <x-form-field
                            name="company_phone"
                            label="الهاتف"
                            :value="$companyPhone"
                            dir="ltr"
                            type="tel"
                        />

                        <x-form-field
                            name="company_email"
                            label="البريد الإلكتروني"
                            type="email"
                            :value="$companyEmail"
                            dir="ltr"
                        />

                        <div class="md:col-span-2">

                            <x-form-field
                                name="company_address"
                                label="العنوان"
                                :value="$companyAddress"
                            />

                        </div>

                    </div>

                    <div class="mt-4 rounded-xl border border-line bg-white/[0.02] p-3">

                        <div class="mb-2 flex items-center justify-between">

                            <span class="text-[10px] text-muted">
                                اكتمال بيانات الشركة
                            </span>

                            <b
                                id="companyCompletion"
                                class="text-xs text-info"
                            >
                                {{ $companyCompletion }}%
                            </b>

                        </div>

                        <div class="h-1.5 overflow-hidden rounded-full bg-white/5">

                            <div
                                id="companyProgress"
                                class="h-full rounded-full bg-info transition-all duration-300"
                                style="width: {{ $companyCompletion }}%"
                            ></div>

                        </div>

                    </div>

                </x-section-card>

            </div>

            {{-- =================================================
                 COLLECTION
            ================================================== --}}
            <div
                id="collectionSection"
                data-settings-section
                data-section-name="قواعد التحصيل"
            >

                <x-section-card
                    title="قواعد التحصيل"
                    icon="fa-sliders"
                    color="warning"
                >

                    <div class="mb-4 flex items-center justify-between gap-3 rounded-xl border border-warning/10 bg-warning/[0.03] px-3 py-2">

                        <div class="flex items-center gap-2 text-[11px] text-dim">

                            <i class="fa-solid fa-gauge-high text-warning"></i>

                            <span>
                                القواعد الأساسية لتوزيع الحالات ومواعيد الوعود.
                            </span>

                        </div>

                        <span
                            data-section-status
                            class="text-[10px] text-brand"
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

                    <div class="mt-4 grid grid-cols-1 gap-2 sm:grid-cols-3">

                        <div class="rounded-xl border border-line bg-white/[0.02] p-3">

                            <p class="text-[10px] text-dim">
                                سعة الموظف
                            </p>

                            <p
                                id="capacityPreview"
                                class="mt-1 font-bold text-fg"
                            >
                                {{ $maxCases ?: '—' }}
                            </p>

                        </div>

                        <div class="rounded-xl border border-line bg-white/[0.02] p-3">

                            <p class="text-[10px] text-dim">
                                الوعد الافتراضي
                            </p>

                            <p
                                id="ptpPreview"
                                class="mt-1 font-bold text-fg"
                            >
                                {{ $ptpDefaultDays !== '' ? $ptpDefaultDays . ' يوم' : '—' }}
                            </p>

                        </div>

                        <div class="rounded-xl border border-line bg-white/[0.02] p-3">

                            <p class="text-[10px] text-dim">
                                تنبيه التأخير
                            </p>

                            <p
                                id="alertPreview"
                                class="mt-1 font-bold text-fg"
                            >
                                {{ $ptpAlertDays !== '' ? $ptpAlertDays . ' يوم' : '—' }}
                            </p>

                        </div>

                    </div>

                </x-section-card>

            </div>

            {{-- =================================================
                 SECURITY
            ================================================== --}}
            <div
                id="securitySection"
                data-settings-section
                data-section-name="الأمان"
            >

                <x-section-card
                    title="الأمان"
                    icon="fa-shield-halved"
                    color="danger"
                >

                    <div class="mb-4 flex items-center justify-between gap-3 rounded-xl border border-danger/10 bg-danger/[0.03] px-3 py-2">

                        <div class="flex items-center gap-2 text-[11px] text-dim">

                            <i class="fa-solid fa-lock text-danger"></i>

                            <span>
                                إعدادات الدخول والجلسات الأساسية.
                            </span>

                        </div>

                        <span
                            id="securityLevel"
                            class="text-[10px] text-brand"
                        >
                            جيد
                        </span>

                    </div>

                    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">

                        <div>

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
            <div
                id="notificationsSection"
                data-settings-section
                data-section-name="الإشعارات"
            >

                <x-section-card
                    title="الإشعارات"
                    icon="fa-bell"
                    color="brand"
                >

                    <div class="mb-4 flex items-center justify-between gap-3 rounded-xl border border-brand/10 bg-brand/[0.03] px-3 py-2">

                        <div class="flex items-center gap-2 text-[11px] text-dim">

                            <i class="fa-solid fa-bell text-brand"></i>

                            <span>
                                الأحداث التي تحتاج متابعة من فريق التشغيل.
                            </span>

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
                                class="check-row"
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
                                    data-notification-state
                                    class="shrink-0 text-[10px] text-dim"
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
                                    سياسة الإشعارات
                                </p>

                                <p class="mt-1 text-[11px] leading-5 text-dim">
                                    استخدم الإشعارات للأحداث التي تحتاج إجراءً فعلياً
                                    حتى لا يتحول النظام إلى مصدر ضوضاء للمستخدمين.
                                </p>

                            </div>

                        </div>

                    </div>

                </x-section-card>

            </div>

        </div>

        {{-- =====================================================
             BUSINESS CONTROL CENTER
        ====================================================== --}}
        <section
            id="businessControls"
            data-settings-section
            class="card"
        >

            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                <div class="flex items-center gap-3">

                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-warning/10 text-warning">
                        <i class="fa-solid fa-briefcase"></i>
                    </span>

                    <div>

                        <h2 class="font-bold text-fg">
                            قواعد الأعمال والتحصيل
                        </h2>

                        <p class="mt-1 text-[11px] text-dim">
                            مخطط التحكم في السلوك التشغيلي للتطبيق.
                        </p>

                    </div>

                </div>

                <span class="badge badge-outline-warning">
                    Control Center
                </span>

            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">

                {{-- Assignment --}}
                <div
                    class="rounded-xl border border-line bg-white/[0.02] p-4"
                    data-control-search="توزيع الحالات التعيين الموظفين السعة"
                >

                    <div class="flex items-center justify-between">

                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-info/10 text-info">
                            <i class="fa-solid fa-users-gear"></i>
                        </span>

                        <span class="badge badge-neutral">
                            مستقبلاً
                        </span>

                    </div>

                    <h3 class="mt-4 text-sm font-bold text-fg">
                        التوزيع والتعيين
                    </h3>

                    <p class="mt-1 text-[10px] leading-5 text-dim">
                        التوزيع التلقائي، السعة، إعادة التعيين، وتوزيع الحالات حسب البنك والشريحة.
                    </p>

                    <div class="mt-4 space-y-2 text-[10px] text-dim">

                        <div class="flex justify-between">
                            <span>التعيين التلقائي</span>
                            <span class="text-warning">قيد الإعداد</span>
                        </div>

                        <div class="flex justify-between">
                            <span>إعادة التعيين</span>
                            <span class="text-warning">قيد الإعداد</span>
                        </div>

                    </div>

                </div>

                {{-- Payments --}}
                <div
                    class="rounded-xl border border-line bg-white/[0.02] p-4"
                    data-control-search="التحصيل المدفوعات الدفعات التأكيد الإيصالات"
                >

                    <div class="flex items-center justify-between">

                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand/10 text-brand">
                            <i class="fa-solid fa-money-bill-transfer"></i>
                        </span>

                        <span class="badge badge-neutral">
                            مستقبلاً
                        </span>

                    </div>

                    <h3 class="mt-4 text-sm font-bold text-fg">
                        قواعد المدفوعات
                    </h3>

                    <p class="mt-1 text-[10px] leading-5 text-dim">
                        التأكيد، الدفع الجزئي، التعديل، الإلغاء، حدود المبالغ، والإيصالات.
                    </p>

                    <div class="mt-4 space-y-2 text-[10px] text-dim">

                        <div class="flex justify-between">
                            <span>تأكيد الدفع</span>
                            <span class="text-warning">قيد الإعداد</span>
                        </div>

                        <div class="flex justify-between">
                            <span>منع التكرار</span>
                            <span class="text-warning">قيد الإعداد</span>
                        </div>

                    </div>

                </div>

                {{-- PTP --}}
                <div
                    class="rounded-xl border border-line bg-white/[0.02] p-4"
                    data-control-search="وعد السداد PTP broken promise overdue"
                >

                    <div class="flex items-center justify-between">

                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-warning/10 text-warning">
                            <i class="fa-solid fa-handshake"></i>
                        </span>

                        <span class="badge badge-neutral">
                            مستقبلاً
                        </span>

                    </div>

                    <h3 class="mt-4 text-sm font-bold text-fg">
                        قواعد الوعود بالسداد
                    </h3>

                    <p class="mt-1 text-[10px] leading-5 text-dim">
                        المواعيد، التذكير، كسر الوعد، التصعيد، والموافقة على تعديل الوعد.
                    </p>

                    <div class="mt-4 space-y-2 text-[10px] text-dim">

                        <div class="flex justify-between">
                            <span>تنبيه التأخير</span>
                            <span class="text-brand">متاح</span>
                        </div>

                        <div class="flex justify-between">
                            <span>التصعيد</span>
                            <span class="text-warning">قيد الإعداد</span>
                        </div>

                    </div>

                </div>

                {{-- Complaints --}}
                <div
                    class="rounded-xl border border-line bg-white/[0.02] p-4"
                    data-control-search="الشكاوى complaints SLA escalation"
                >

                    <div class="flex items-center justify-between">

                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-danger/10 text-danger">
                            <i class="fa-solid fa-headset"></i>
                        </span>

                        <span class="badge badge-neutral">
                            مستقبلاً
                        </span>

                    </div>

                    <h3 class="mt-4 text-sm font-bold text-fg">
                        قواعد الشكاوى
                    </h3>

                    <p class="mt-1 text-[10px] leading-5 text-dim">
                        الأولوية، SLA، التصعيد، والمسؤول عن معالجة الشكوى.
                    </p>

                    <div class="mt-4 space-y-2 text-[10px] text-dim">

                        <div class="flex justify-between">
                            <span>الإشعار</span>
                            <span class="text-brand">متاح</span>
                        </div>

                        <div class="flex justify-between">
                            <span>SLA</span>
                            <span class="text-warning">قيد الإعداد</span>
                        </div>

                    </div>

                </div>

            </div>

        </section>

        {{-- =====================================================
             ACCESS + SECURITY CENTER
        ====================================================== --}}
        <section
            id="accessControl"
            data-settings-section
            class="card"
        >

            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                <div class="flex items-center gap-3">

                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-info/10 text-info">
                        <i class="fa-solid fa-user-shield"></i>
                    </span>

                    <div>

                        <h2 class="font-bold text-fg">
                            الوصول والحماية المتقدمة
                        </h2>

                        <p class="mt-1 text-[11px] text-dim">
                            طبقة التحكم المستقبلية في المستخدمين والصلاحيات والجلسات.
                        </p>

                    </div>

                </div>

                <span class="badge badge-outline-info">
                    يحتاج Backend
                </span>

            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">

                <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                    <i class="fa-solid fa-user-lock text-info"></i>

                    <h3 class="mt-3 text-sm font-bold text-fg">
                        سياسة الحسابات
                    </h3>

                    <p class="mt-1 text-[10px] leading-5 text-dim">
                        إيقاف الحسابات، الحسابات غير النشطة، وتغيير كلمات المرور.
                    </p>

                    <span class="mt-3 inline-flex text-[10px] text-warning">
                        قيد التطوير
                    </span>

                </div>

                <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                    <i class="fa-solid fa-key text-warning"></i>

                    <h3 class="mt-3 text-sm font-bold text-fg">
                        المصادقة الثنائية
                    </h3>

                    <p class="mt-1 text-[10px] leading-5 text-dim">
                        فرض 2FA على المديرين والمستخدمين ذوي الصلاحيات الحساسة.
                    </p>

                    <span class="mt-3 inline-flex text-[10px] text-warning">
                        قيد التطوير
                    </span>

                </div>

                <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                    <i class="fa-solid fa-laptop text-brand"></i>

                    <h3 class="mt-3 text-sm font-bold text-fg">
                        الجلسات النشطة
                    </h3>

                    <p class="mt-1 text-[10px] leading-5 text-dim">
                        مشاهدة جلسات المستخدمين وإنهاء الجلسات المشبوهة.
                    </p>

                    <span class="mt-3 inline-flex text-[10px] text-warning">
                        قيد التطوير
                    </span>

                </div>

                <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                    <i class="fa-solid fa-user-shield text-danger"></i>

                    <h3 class="mt-3 text-sm font-bold text-fg">
                        الصلاحيات الحساسة
                    </h3>

                    <p class="mt-1 text-[10px] leading-5 text-dim">
                        حماية الحذف والتصدير وتغيير الصلاحيات والعمليات المالية.
                    </p>

                    <span class="mt-3 inline-flex text-[10px] text-warning">
                        قيد التطوير
                    </span>

                </div>

            </div>

        </section>

        {{-- =====================================================
             AUDIT + DATA GOVERNANCE
        ====================================================== --}}
        <section
            id="auditControl"
            data-settings-section
            class="card"
        >

            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                <div class="flex items-center gap-3">

                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-warning/10 text-warning">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </span>

                    <div>

                        <h2 class="font-bold text-fg">
                            السجل والمراجعة والحوكمة
                        </h2>

                        <p class="mt-1 text-[11px] text-dim">
                            تتبع العمليات الحساسة وتغييرات إعدادات النظام.
                        </p>

                    </div>

                </div>

                <span class="badge badge-outline-warning">
                    Audit Center
                </span>

            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">

                <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                    <div class="flex items-center justify-between">

                        <span class="text-xs font-semibold text-fg">
                            تغييرات الإعدادات
                        </span>

                        <i class="fa-solid fa-gear text-warning"></i>

                    </div>

                    <p class="mt-2 text-[10px] leading-5 text-dim">
                        تسجيل القيمة القديمة والجديدة والمستخدم والتاريخ.
                    </p>

                    <span class="mt-3 inline-flex text-[10px] text-warning">
                        قيد التطوير
                    </span>

                </div>

                <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                    <div class="flex items-center justify-between">

                        <span class="text-xs font-semibold text-fg">
                            عمليات التصدير
                        </span>

                        <i class="fa-solid fa-file-export text-info"></i>

                    </div>

                    <p class="mt-2 text-[10px] leading-5 text-dim">
                        معرفة من قام بتصدير بيانات العملاء ومتى.
                    </p>

                    <span class="mt-3 inline-flex text-[10px] text-warning">
                        قيد التطوير
                    </span>

                </div>

                <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                    <div class="flex items-center justify-between">

                        <span class="text-xs font-semibold text-fg">
                            العمليات الحساسة
                        </span>

                        <i class="fa-solid fa-triangle-exclamation text-danger"></i>

                    </div>

                    <p class="mt-2 text-[10px] leading-5 text-dim">
                        الحذف، التعديل المالي، تغيير الصلاحيات، وإعادة التعيين.
                    </p>

                    <span class="mt-3 inline-flex text-[10px] text-warning">
                        قيد التطوير
                    </span>

                </div>

                <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                    <div class="flex items-center justify-between">

                        <span class="text-xs font-semibold text-fg">
                            تاريخ الإعدادات
                        </span>

                        <i class="fa-solid fa-code-compare text-brand"></i>

                    </div>

                    <p class="mt-2 text-[10px] leading-5 text-dim">
                        مقارنة الإصدارات واسترجاع إعدادات سابقة عند الحاجة.
                    </p>

                    <span class="mt-3 inline-flex text-[10px] text-warning">
                        قيد التطوير
                    </span>

                </div>

            </div>

        </section>

        {{-- =====================================================
             BACKUP + DATA CENTER
        ====================================================== --}}
        <section
            id="backupControl"
            data-settings-section
            class="card"
        >

            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                <div class="flex items-center gap-3">

                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand/10 text-brand">
                        <i class="fa-solid fa-database"></i>
                    </span>

                    <div>

                        <h2 class="font-bold text-fg">
                            البيانات والنسخ الاحتياطي
                        </h2>

                        <p class="mt-1 text-[11px] text-dim">
                            حماية البيانات ومراقبة النسخ والأرشيف.
                        </p>

                    </div>

                </div>

                <span class="badge badge-outline-success">
                    Data Protection
                </span>

            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">

                <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                    <div class="flex items-center justify-between">

                        <span class="text-xs font-semibold text-fg">
                            آخر نسخة احتياطية
                        </span>

                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand/10 text-brand">
                            <i class="fa-solid fa-check"></i>
                        </span>

                    </div>

                    <p class="mt-3 text-sm font-bold text-fg">
                        غير متصل حالياً
                    </p>

                    <p class="mt-1 text-[10px] text-dim">
                        سيتم ربط الحالة الفعلية بالـ backend لاحقاً.
                    </p>

                </div>

                <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                    <div class="flex items-center justify-between">

                        <span class="text-xs font-semibold text-fg">
                            الأرشيف
                        </span>

                        <i class="fa-solid fa-box-archive text-info"></i>

                    </div>

                    <p class="mt-3 text-sm font-bold text-fg">
                        قراءة فقط
                    </p>

                    <p class="mt-1 text-[10px] text-dim">
                        الأرشيف الحالي مصمم للحفاظ على البيانات التاريخية.
                    </p>

                </div>

                <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                    <div class="flex items-center justify-between">

                        <span class="text-xs font-semibold text-fg">
                            الاحتفاظ بالبيانات
                        </span>

                        <i class="fa-solid fa-clock-rotate-left text-warning"></i>

                    </div>

                    <p class="mt-3 text-sm font-bold text-fg">
                        قيد التعريف
                    </p>

                    <p class="mt-1 text-[10px] text-dim">
                        سيتم تحديد سياسة الاحتفاظ قبل إضافة الحذف التلقائي.
                    </p>

                </div>

                <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                    <div class="flex items-center justify-between">

                        <span class="text-xs font-semibold text-fg">
                            سلامة البيانات
                        </span>

                        <i class="fa-solid fa-shield text-brand"></i>

                    </div>

                    <p class="mt-3 text-sm font-bold text-fg">
                        تحتاج فحص
                    </p>

                    <p class="mt-1 text-[10px] text-dim">
                        سيتم ربط فحص التكرارات والقيم غير الصحيحة لاحقاً.
                    </p>

                </div>

            </div>

        </section>

        {{-- =====================================================
             SYSTEM HEALTH
        ====================================================== --}}
        <section
            id="systemControl"
            data-settings-section
            class="card"
        >

            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                <div class="flex items-center gap-3">

                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-cyan/10 text-cyan">
                        <i class="fa-solid fa-server"></i>
                    </span>

                    <div>

                        <h2 class="font-bold text-fg">
                            صحة النظام
                        </h2>

                        <p class="mt-1 text-[11px] text-dim">
                            لوحة تشخيص مستقبلية لمكونات التطبيق الأساسية.
                        </p>

                    </div>

                </div>

                <span class="badge badge-outline-info">
                    System Health
                </span>

            </div>

            <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">

                @php
                    $healthItems = [
                        ['Database', 'fa-database'],
                        ['Cache', 'fa-bolt'],
                        ['Queue', 'fa-layer-group'],
                        ['Scheduler', 'fa-calendar-check'],
                        ['Storage', 'fa-hard-drive'],
                        ['Mail', 'fa-envelope'],
                    ];
                @endphp

                @foreach($healthItems as [$label, $icon])

                    <div class="rounded-xl border border-line bg-white/[0.02] p-3 text-center">

                        <span class="mx-auto flex h-9 w-9 items-center justify-center rounded-lg bg-white/5 text-muted">
                            <i class="fa-solid {{ $icon }}"></i>
                        </span>

                        <p class="mt-2 text-[10px] font-semibold text-fg">
                            {{ $label }}
                        </p>

                        <span class="mt-1 inline-flex items-center gap-1 text-[9px] text-dim">
                            <i class="fa-solid fa-circle text-[5px]"></i>
                            غير متصل
                        </span>

                    </div>

                @endforeach

            </div>

            <div class="mt-4 rounded-xl border border-info/10 bg-info/[0.03] p-3">

                <div class="flex items-start gap-3">

                    <i class="fa-solid fa-circle-info mt-0.5 text-info"></i>

                    <p class="text-[10px] leading-5 text-dim">
                        هذه المنطقة مصممة لتعرض حالة الخدمات الفعلية مستقبلاً.
                        لا يتم اختلاق أي حالة تشغيلية حالياً قبل ربطها بمصادر النظام الحقيقية.
                    </p>

                </div>

            </div>

        </section>

        {{-- =====================================================
             FEATURE FLAGS
        ====================================================== --}}
        <section
            id="featureFlags"
            data-settings-section
            class="card"
        >

            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                <div class="flex items-center gap-3">

                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-info/10 text-info">
                        <i class="fa-solid fa-toggle-on"></i>
                    </span>

                    <div>

                        <h2 class="font-bold text-fg">
                            خصائص النظام
                        </h2>

                        <p class="mt-1 text-[11px] text-dim">
                            Feature Flags للتحكم في إطلاق الوظائف الجديدة تدريجياً.
                        </p>

                    </div>

                </div>

                <span class="badge badge-outline-info">
                    Feature Flags
                </span>

            </div>

            <div class="grid grid-cols-1 gap-2 md:grid-cols-2">

                @php
                    $featureFlags = [
                        ['واجهة Dashboard الجديدة', true],
                        ['صفحة Timeline للعميل', true],
                        ['التوزيع التلقائي للحالات', false],
                        ['التقارير المتقدمة', false],
                        ['فحص جودة البيانات', false],
                        ['مراقبة النظام', false],
                    ];
                @endphp

                @foreach($featureFlags as [$label, $enabled])

                    <div class="flex items-center justify-between gap-3 rounded-xl border border-line bg-white/[0.02] px-4 py-3">

                        <div class="flex items-center gap-3">

                            <span class="flex h-8 w-8 items-center justify-center rounded-lg {{ $enabled ? 'bg-brand/10 text-brand' : 'bg-white/5 text-dim' }}">
                                <i class="fa-solid {{ $enabled ? 'fa-check' : 'fa-minus' }}"></i>
                            </span>

                            <div>

                                <p class="text-xs font-semibold text-fg">
                                    {{ $label }}
                                </p>

                                <p class="mt-1 text-[9px] text-dim">
                                    {{ $enabled ? 'مفعلة في الواجهة الحالية' : 'سيتم تفعيلها بعد ربط النظام' }}
                                </p>

                            </div>

                        </div>

                        <span class="text-[10px] {{ $enabled ? 'text-brand' : 'text-dim' }}">
                            {{ $enabled ? 'ON' : 'OFF' }}
                        </span>

                    </div>

                @endforeach

            </div>

        </section>

        {{-- =====================================================
             CONFIGURATION HEALTH
        ====================================================== --}}
        <section
            id="configurationHealth"
            class="card"
        >

            <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">

                <div class="flex items-center gap-3">

                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand/10 text-brand">
                        <i class="fa-solid fa-heart-pulse"></i>
                    </span>

                    <div>

                        <h2 class="font-bold text-fg">
                            فحص إعدادات النظام
                        </h2>

                        <p class="mt-1 text-[11px] text-dim">
                            مراجعة ذكية للقيم الحالية قبل الحفظ.
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

                <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                    <div class="flex items-center justify-between">

                        <span class="text-[10px] text-dim">
                            الشركة
                        </span>

                        <i class="fa-solid fa-building text-info"></i>

                    </div>

                    <p
                        id="companyHealthText"
                        class="mt-2 text-xs font-bold text-fg"
                    >
                        {{ $companyCompletion }}% مكتمل
                    </p>

                </div>

                <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                    <div class="flex items-center justify-between">

                        <span class="text-[10px] text-dim">
                            التحصيل
                        </span>

                        <i class="fa-solid fa-sliders text-warning"></i>

                    </div>

                    <p
                        id="collectionHealthText"
                        class="mt-2 text-xs font-bold text-fg"
                    >
                        {{ $collectionCompletion }}% مكتمل
                    </p>

                </div>

                <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                    <div class="flex items-center justify-between">

                        <span class="text-[10px] text-dim">
                            الأمان
                        </span>

                        <i class="fa-solid fa-shield-halved text-danger"></i>

                    </div>

                    <p
                        id="securityHealthText"
                        class="mt-2 text-xs font-bold text-fg"
                    >
                        {{ $securityCompletion }}% مكتمل
                    </p>

                </div>

                <div class="rounded-xl border border-line bg-white/[0.02] p-4">

                    <div class="flex items-center justify-between">

                        <span class="text-[10px] text-dim">
                            الإشعارات
                        </span>

                        <i class="fa-solid fa-bell text-brand"></i>

                    </div>

                    <p
                        id="notificationHealthText"
                        class="mt-2 text-xs font-bold text-fg"
                    >
                        {{ $enabledNotifications }}/{{ $notificationCount }} مفعلة
                    </p>

                </div>

            </div>

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

                <div class="flex flex-wrap gap-2">

                    <span
                        id="unsavedIndicator"
                        class="hidden items-center gap-2 rounded-lg border border-warning/20 bg-warning/[0.05] px-3 py-2 text-[10px] text-warning"
                    >
                        <i class="fa-solid fa-circle text-[5px]"></i>
                        تغييرات غير محفوظة
                    </span>

                    <button
                        type="button"
                        id="resetChanges"
                        class="btn btn-secondary"
                        disabled
                    >
                        <i class="fa-solid fa-rotate-left text-xs"></i>
                        تراجع
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
    | Cached DOM references
    |--------------------------------------------------------------------------
    */

    const searchInput = document.getElementById('settingsSearch');

    const saveButton = document.getElementById('saveSettings');
    const resetButton = document.getElementById('resetChanges');

    const unsavedIndicator = document.getElementById('unsavedIndicator');

    const saveStatusIcon = document.getElementById('saveStatusIcon');
    const saveStatusTitle = document.getElementById('saveStatusTitle');
    const saveStatusDescription = document.getElementById('saveStatusDescription');

    const overallCompletion = document.getElementById('overallCompletion');
    const overallProgress = document.getElementById('overallProgress');

    const companyCompletion = document.getElementById('companyCompletion');
    const companyProgress = document.getElementById('companyProgress');

    const collectionProgress = document.getElementById('collectionProgress');
    const collectionCardStatus = document.getElementById('collectionCardStatus');

    const securityProgress = document.getElementById('securityProgress');
    const securityCardStatus = document.getElementById('securityCardStatus');

    const notificationCardStatus = document.getElementById('notificationCardStatus');
    const notificationProgress = document.getElementById('notificationProgress');

    const enabledNotifications = document.getElementById('enabledNotifications');
    const casesCapacity = document.getElementById('casesCapacity');
    const sessionSummary = document.getElementById('sessionSummary');

    const capacityPreview = document.getElementById('capacityPreview');
    const ptpPreview = document.getElementById('ptpPreview');
    const alertPreview = document.getElementById('alertPreview');

    const securityLevel = document.getElementById('securityLevel');
    const loginAttemptsHint = document.getElementById('loginAttemptsHint');
    const sessionHint = document.getElementById('sessionHint');

    const loginSecurityPreview = document.getElementById('loginSecurityPreview');
    const sessionSecurityPreview = document.getElementById('sessionSecurityPreview');

    const companyHealthText = document.getElementById('companyHealthText');
    const collectionHealthText = document.getElementById('collectionHealthText');
    const securityHealthText = document.getElementById('securityHealthText');
    const notificationHealthText = document.getElementById('notificationHealthText');

    const healthBadge = document.getElementById('healthBadge');
    const healthMessages = document.getElementById('healthMessages');

    const settingSections = [
        ...document.querySelectorAll('[data-settings-section]')
    ];

    const notificationFields = [
        ...form.querySelectorAll(
            'input[type="checkbox"][name="notify_payment"],' +
            'input[type="checkbox"][name="notify_complaint"],' +
            'input[type="checkbox"][name="notify_broken_promise"],' +
            'input[type="checkbox"][name="daily_digest"]'
        )
    ];

    const notificationRows = [
        ...document.querySelectorAll('[data-setting-row]')
    ];

    /*
    |--------------------------------------------------------------------------
    | Initial state
    |--------------------------------------------------------------------------
    */

    function serializeForm() {

        const data = new FormData(form);

        return JSON.stringify(
            [...data.entries()]
                .map(([key, value]) => [
                    key,
                    String(value)
                ])
                .sort((a, b) => {

                    if (a[0] === b[0]) {
                        return a[1].localeCompare(b[1]);
                    }

                    return a[0].localeCompare(b[0]);
                })
        );
    }

    const initialState = serializeForm();

    let isDirty = false;
    let isSubmitting = false;

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    function setProgress(element, value) {

        if (!element) {
            return;
        }

        const safeValue = Math.max(
            0,
            Math.min(100, Number(value) || 0)
        );

        element.style.width = safeValue + '%';
    }

    function completion(fields) {

        if (!fields.length) {
            return 0;
        }

        const completed = fields.filter(field => {
            return String(field.value ?? '').trim() !== '';
        }).length;

        return Math.round(
            (completed / fields.length) * 100
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Dirty state
    |--------------------------------------------------------------------------
    */

    function setDirtyState(dirty) {

        isDirty = dirty;

        if (unsavedIndicator) {
            unsavedIndicator.classList.toggle(
                'hidden',
                !dirty
            );

            unsavedIndicator.classList.toggle(
                'inline-flex',
                dirty
            );
        }

        if (resetButton) {
            resetButton.disabled = !dirty;
        }

        if (saveStatusTitle) {
            saveStatusTitle.textContent = dirty
                ? 'توجد تغييرات غير محفوظة'
                : 'جميع التغييرات محفوظة';
        }

        if (saveStatusDescription) {
            saveStatusDescription.textContent = dirty
                ? 'راجع التغييرات ثم اضغط حفظ الإعدادات.'
                : 'راجع الإعدادات ثم اضغط حفظ عند الانتهاء.';
        }

        if (saveStatusIcon) {

            saveStatusIcon.classList.toggle(
                'bg-warning/10',
                dirty
            );

            saveStatusIcon.classList.toggle(
                'text-warning',
                dirty
            );

            saveStatusIcon.classList.toggle(
                'bg-brand/10',
                !dirty
            );

            saveStatusIcon.classList.toggle(
                'text-brand',
                !dirty
            );

            saveStatusIcon.innerHTML = dirty
                ? '<i class="fa-solid fa-pen-to-square"></i>'
                : '<i class="fa-solid fa-check"></i>';
        }

        document.querySelectorAll('[data-section-status]')
            .forEach(status => {

                status.innerHTML = dirty
                    ? '<i class="fa-solid fa-circle text-[5px]"></i> توجد تغييرات'
                    : '<i class="fa-solid fa-check"></i> محفوظ';

                status.classList.toggle(
                    'text-warning',
                    dirty
                );

                status.classList.toggle(
                    'text-brand',
                    !dirty
                );
            });
    }

    function refreshDirtyState() {
        setDirtyState(
            serializeForm() !== initialState
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Completion
    |--------------------------------------------------------------------------
    */

    function refreshCompletion() {

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

        const company = completion(companyFields);
        const collection = completion(collectionFields);
        const security = completion(securityFields);

        const notificationCount = notificationFields.length;

        const enabled = notificationFields.filter(
            checkbox => checkbox.checked
        ).length;

        const notificationCompletion = notificationCount
            ? Math.round((enabled / notificationCount) * 100)
            : 0;

        const overall = Math.round(
            (
                company +
                collection +
                security +
                100
            ) / 4
        );

        if (overallCompletion) {
            overallCompletion.textContent = overall + '%';
        }

        setProgress(
            overallProgress,
            overall
        );

        if (companyCompletion) {
            companyCompletion.textContent = company + '%';
        }

        setProgress(
            companyProgress,
            company
        );

        if (collectionCardStatus) {
            collectionCardStatus.textContent =
                collection + '%';
        }

        setProgress(
            collectionProgress,
            collection
        );

        if (securityCardStatus) {
            securityCardStatus.textContent =
                security + '%';
        }

        setProgress(
            securityProgress,
            security
        );

        if (notificationCardStatus) {
            notificationCardStatus.textContent =
                enabled + '/' + notificationCount;
        }

        setProgress(
            notificationProgress,
            notificationCompletion
        );

        if (enabledNotifications) {
            enabledNotifications.textContent = enabled;
        }

        if (companyHealthText) {
            companyHealthText.textContent =
                company + '% مكتمل';
        }

        if (collectionHealthText) {
            collectionHealthText.textContent =
                collection + '% مكتمل';
        }

        if (securityHealthText) {
            securityHealthText.textContent =
                security + '% مكتمل';
        }

        if (notificationHealthText) {
            notificationHealthText.textContent =
                enabled + '/' + notificationCount + ' مفعلة';
        }

        if (healthBadge) {

            if (overall >= 90) {

                healthBadge.textContent =
                    'إعدادات ممتازة';

                healthBadge.className =
                    'badge badge-outline-success';

            } else if (overall >= 70) {

                healthBadge.textContent =
                    'إعدادات جيدة';

                healthBadge.className =
                    'badge badge-outline-info';

            } else {

                healthBadge.textContent =
                    'تحتاج مراجعة';

                healthBadge.className =
                    'badge badge-outline-warning';
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Collection preview
    |--------------------------------------------------------------------------
    */

    function refreshCollection() {

        const maxCases = form.querySelector(
            '[name="max_cases_per_employee"]'
        )?.value || '';

        const ptpDays = form.querySelector(
            '[name="ptp_default_days"]'
        )?.value || '';

        const alertDays = form.querySelector(
            '[name="ptp_overdue_alert_days"]'
        )?.value || '';

        if (capacityPreview) {
            capacityPreview.textContent =
                maxCases || '—';
        }

        if (casesCapacity) {
            casesCapacity.textContent =
                maxCases || '—';
        }

        if (ptpPreview) {
            ptpPreview.textContent =
                ptpDays
                    ? ptpDays + ' يوم'
                    : '—';
        }

        if (alertPreview) {
            alertPreview.textContent =
                alertDays
                    ? alertDays + ' يوم'
                    : '—';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Security preview
    |--------------------------------------------------------------------------
    */

    function refreshSecurity() {

        const attempts = Number(
            form.querySelector(
                '[name="login_max_attempts"]'
            )?.value || 0
        );

        const session = Number(
            form.querySelector(
                '[name="session_minutes"]'
            )?.value || 0
        );

        if (loginAttemptsHint) {
            loginAttemptsHint.textContent =
                attempts
                    ? attempts + ' محاولات مسموحة'
                    : 'لم يتم تحديد القيمة';
        }

        if (sessionHint) {
            sessionHint.textContent =
                session
                    ? session + ' دقيقة'
                    : 'لم يتم تحديد القيمة';
        }

        if (sessionSummary) {
            sessionSummary.textContent =
                session || '—';
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

            if (
                attempts >= 3 &&
                attempts <= 5 &&
                session >= 15 &&
                session <= 480
            ) {

                securityLevel.textContent =
                    'مستوى جيد';

                securityLevel.className =
                    'text-[10px] text-brand';

            } else if (attempts && session) {

                securityLevel.textContent =
                    'يحتاج مراجعة';

                securityLevel.className =
                    'text-[10px] text-warning';

            } else {

                securityLevel.textContent =
                    'غير مكتمل';

                securityLevel.className =
                    'text-[10px] text-danger';
            }
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    function refreshNotifications() {

        const enabled = notificationFields.filter(
            checkbox => checkbox.checked
        ).length;

        notificationRows.forEach(row => {

            const checkbox = row.querySelector(
                'input[type="checkbox"]'
            );

            const state = row.querySelector(
                '[data-notification-state]'
            );

            if (!checkbox || !state) {
                return;
            }

            state.textContent =
                checkbox.checked
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

        if (enabledNotifications) {
            enabledNotifications.textContent =
                enabled;
        }

        if (notificationCardStatus) {
            notificationCardStatus.textContent =
                enabled + '/' + notificationFields.length;
        }

        if (notificationHealthText) {
            notificationHealthText.textContent =
                enabled +
                '/' +
                notificationFields.length +
                ' مفعلة';
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Health messages
    |--------------------------------------------------------------------------
    */

    function refreshHealth() {

        if (!healthMessages) {
            return;
        }

        const messages = [];

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

        const company = completion(companyFields);
        const collection = completion(collectionFields);
        const security = completion(securityFields);

        const attempts = Number(
            form.querySelector(
                '[name="login_max_attempts"]'
            )?.value || 0
        );

        const session = Number(
            form.querySelector(
                '[name="session_minutes"]'
            )?.value || 0
        );

        const ptpDays = Number(
            form.querySelector(
                '[name="ptp_default_days"]'
            )?.value || 0
        );

        const alertDays = Number(
            form.querySelector(
                '[name="ptp_overdue_alert_days"]'
            )?.value || 0
        );

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
                text: 'هناك إعدادات تحصيل لم يتم تحديدها.'
            });
        }

        if (!attempts || !session) {
            messages.push({
                type: 'danger',
                icon: 'fa-shield-halved',
                text: 'يجب مراجعة إعدادات الأمان قبل تشغيل النظام بشكل كامل.'
            });
        }

        if (
            attempts > 5 ||
            session > 720
        ) {
            messages.push({
                type: 'warning',
                icon: 'fa-triangle-exclamation',
                text: 'إعدادات الأمان الحالية تسمح بقيم مرتفعة نسبياً.'
            });
        }

        if (
            ptpDays > 0 &&
            alertDays > ptpDays
        ) {
            messages.push({
                type: 'warning',
                icon: 'fa-calendar-xmark',
                text: 'تنبيه الوعد المتأخر أكبر من مدة الوعد الافتراضية.'
            });
        }

        if (!messages.length) {
            messages.push({
                type: 'success',
                icon: 'fa-circle-check',
                text: 'لا توجد ملاحظات مهمة على الإعدادات الحالية.'
            });
        }

        const classes = {
            info: 'border-info/20 bg-info/[0.05] text-info',
            warning: 'border-warning/20 bg-warning/[0.05] text-warning',
            danger: 'border-danger/20 bg-danger/[0.05] text-danger',
            success: 'border-brand/20 bg-brand/[0.05] text-brand',
        };

        healthMessages.innerHTML =
            messages.map(message => `
                <div class="flex items-center gap-3 rounded-xl border px-3 py-2 text-[11px] ${classes[message.type]}">
                    <i class="fa-solid ${message.icon}"></i>
                    <span>${message.text}</span>
                </div>
            `).join('');
    }

    /*
    |--------------------------------------------------------------------------
    | Full UI refresh
    |--------------------------------------------------------------------------
    */

    function refreshUI() {

        refreshCompletion();
        refreshCollection();
        refreshSecurity();
        refreshNotifications();
        refreshHealth();
        refreshDirtyState();
    }

    /*
    |--------------------------------------------------------------------------
    | Form events
    |--------------------------------------------------------------------------
    */

    form.addEventListener(
        'input',
        refreshUI
    );

    form.addEventListener(
        'change',
        refreshUI
    );

    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

    if (searchInput) {

        searchInput.addEventListener(
            'input',
            function () {

                const query =
                    this.value
                        .trim()
                        .toLowerCase();

                settingSections.forEach(section => {

                    if (!query) {

                        section.classList.remove(
                            'hidden'
                        );

                        return;
                    }

                    const searchableText =
                        section.textContent.toLowerCase();

                    const customText = [
                        ...section.querySelectorAll(
                            '[data-control-search]'
                        )
                    ]
                        .map(element => element.dataset.controlSearch || '')
                        .join(' ')
                        .toLowerCase();

                    const matched =
                        searchableText.includes(query) ||
                        customText.includes(query);

                    section.classList.toggle(
                        'hidden',
                        !matched
                    );
                });
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Section navigation
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        function (event) {

            const button =
                event.target.closest(
                    '[data-scroll-section]'
                );

            if (!button) {
                return;
            }

            const section =
                document.getElementById(
                    button.dataset.scrollSection
                );

            if (!section) {
                return;
            }

            section.classList.remove(
                'hidden'
            );

            section.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Control center cards
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        function (event) {

            const button =
                event.target.closest(
                    '[data-control-group]'
                );

            if (!button) {
                return;
            }

            const group =
                button.dataset.controlGroup;

            const target =
                document.getElementById(group);

            if (!target) {
                return;
            }

            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Reset
    |--------------------------------------------------------------------------
    */

    if (resetButton) {

        resetButton.addEventListener(
            'click',
            function () {

                if (!isDirty) {
                    return;
                }

                const confirmed =
                    window.confirm(
                        'هل تريد إلغاء جميع التغييرات غير المحفوظة؟'
                    );

                if (!confirmed) {
                    return;
                }

                form.reset();

                window.requestAnimationFrame(
                    refreshUI
                );
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Submit protection
    |--------------------------------------------------------------------------
    */

    form.addEventListener(
        'submit',
        function (event) {

            if (isSubmitting) {

                event.preventDefault();

                return;
            }

            isSubmitting = true;

            if (saveButton) {

                saveButton.disabled = true;

                saveButton.innerHTML =
                    '<i class="fa-solid fa-spinner fa-spin text-xs"></i> جارٍ الحفظ...';
            }
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Keyboard shortcuts
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'keydown',
        function (event) {

            if (
                (event.ctrlKey || event.metaKey) &&
                event.key.toLowerCase() === 's'
            ) {

                event.preventDefault();

                if (!isSubmitting) {
                    form.requestSubmit();
                }

                return;
            }

            if (
                (event.ctrlKey || event.metaKey) &&
                event.key.toLowerCase() === 'k'
            ) {

                event.preventDefault();

                if (searchInput) {

                    searchInput.focus();
                    searchInput.select();
                }

                return;
            }

            if (
                event.key === 'Escape' &&
                document.activeElement === searchInput
            ) {

                searchInput.value = '';

                searchInput.dispatchEvent(
                    new Event('input')
                );

                searchInput.blur();
            }
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Unsaved navigation protection
    |--------------------------------------------------------------------------
    */

    window.addEventListener(
        'beforeunload',
        function (event) {

            if (!isDirty || isSubmitting) {
                return;
            }

            event.preventDefault();
            event.returnValue = '';
        }
    );

    document.addEventListener(
        'click',
        function (event) {

            if (!isDirty || isSubmitting) {
                return;
            }

            const link =
                event.target.closest(
                    'a[href]'
                );

            if (!link) {
                return;
            }

            const href =
                link.getAttribute('href');

            if (
                !href ||
                href === '#' ||
                link.target === '_blank'
            ) {
                return;
            }

            const confirmed =
                window.confirm(
                    'لديك تغييرات غير محفوظة. هل تريد مغادرة الصفحة؟'
                );

            if (!confirmed) {
                event.preventDefault();
            }
        }
    );

    /*
    |--------------------------------------------------------------------------
    | Initial state
    |--------------------------------------------------------------------------
    */

    refreshUI();

})();
</script>
@endpush