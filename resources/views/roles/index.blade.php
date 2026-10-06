{{-- resources/views/roles/index.blade.php --}}
@extends('layouts.app')

@section('title', 'الرتب النظامية')

@php
    /*
    |--------------------------------------------------------------------------
    | SAFE COLLECTION
    |--------------------------------------------------------------------------
    | Supports both Collection and paginator.
    |--------------------------------------------------------------------------
    */
    $roleCollection = collect($roles);

    /*
    |--------------------------------------------------------------------------
    | MAIN COUNTERS
    |--------------------------------------------------------------------------
    */
    $visibleRoles = $roleCollection->count();

    $totalRoles = method_exists($roles, 'total')
        ? $roles->total()
        : $visibleRoles;

    $systemRoles = $roleCollection
        ->filter(fn ($role) => (bool) $role->is_system)
        ->count();

    $customRoles = $roleCollection
        ->filter(fn ($role) => ! (bool) $role->is_system)
        ->count();

    $totalUsers = $roleCollection
        ->sum(fn ($role) => (int) ($role->users_count ?? 0));

    $rolesWithUsers = $roleCollection
        ->filter(fn ($role) => (int) ($role->users_count ?? 0) > 0)
        ->count();

    $rolesWithoutUsers = max(0, $visibleRoles - $rolesWithUsers);

    $totalAssignedPermissions = $roleCollection
        ->sum(fn ($role) => (int) ($role->permissions_count ?? 0));

    /*
    |--------------------------------------------------------------------------
    | PERMISSION COVERAGE
    |--------------------------------------------------------------------------
    */
    $permissionPercent = $totalPermissions > 0
        ? (int) round(
            ($totalAssignedPermissions / max(1, $visibleRoles)) /
            $totalPermissions *
            100
        )
        : 0;

    $permissionPercent = min(100, max(0, $permissionPercent));

    /*
    |--------------------------------------------------------------------------
    | HIGHEST / LOWEST LEVEL
    |--------------------------------------------------------------------------
    */
    $highestLevel = $roleCollection
        ->max(fn ($role) => (int) ($role->level ?? 0));

    $lowestLevel = $roleCollection
        ->min(fn ($role) => (int) ($role->level ?? 0));

    /*
    |--------------------------------------------------------------------------
    | MOST USED ROLE
    |--------------------------------------------------------------------------
    */
    $mostUsedRole = $roleCollection
        ->sortByDesc(fn ($role) => (int) ($role->users_count ?? 0))
        ->first();

    /*
    |--------------------------------------------------------------------------
    | MOST PERMISSIONS ROLE
    |--------------------------------------------------------------------------
    */
    $mostPowerfulRole = $roleCollection
        ->sortByDesc(fn ($role) => (int) ($role->permissions_count ?? 0))
        ->first();

    /*
    |--------------------------------------------------------------------------
    | SEARCH / FILTER SUPPORT
    |--------------------------------------------------------------------------
    | These values are optional, so the page remains compatible with the
    | current controller.
    |--------------------------------------------------------------------------
    */
    $currentSearch = request('search', '');
    $currentType = request('type', '');

    $hasFilters = $currentSearch !== '' || $currentType !== '';

    /*
    |--------------------------------------------------------------------------
    | PAGINATION SAFETY
    |--------------------------------------------------------------------------
    */
    $hasPagination = method_exists($roles, 'links');

    /*
    |--------------------------------------------------------------------------
    | ROLE DISTRIBUTION
    |--------------------------------------------------------------------------
    */
    $roleLevels = $roleCollection
        ->groupBy(fn ($role) => $role->level ?? 0)
        ->map(fn ($group) => $group->count())
        ->sortKeys();

@endphp

@section('content')

    {{-- ================================================================
         PAGE HEADER
         ================================================================ --}}
    <x-page-header
        title="الرتب النظامية"
        subtitle="تحديد صلاحيات كل رتبة في النظام"
        icon="fa-user-shield"
    >
        <x-slot:actions>

            <a
                href="{{ route('users.index') }}"
                class="btn btn-secondary btn-sm"
                title="عرض المستخدمين"
                aria-label="عرض المستخدمين"
            >
                <i class="fa-solid fa-users text-xs"></i>
                المستخدمون
            </a>

            <a
                href="{{ route('roles.create') }}"
                class="btn btn-primary btn-sm"
                title="إضافة رتبة نظامية جديدة"
                aria-label="إضافة رتبة نظامية جديدة"
            >
                <i class="fa-solid fa-plus text-xs"></i>
                إضافة رتبة
            </a>

        </x-slot:actions>
    </x-page-header>

    <x-flash />


    {{-- ================================================================
         VALIDATION ERROR
         ================================================================ --}}
    @error('role')

        <div class="alert alert-danger mb-6">
            <i class="fa-solid fa-circle-exclamation ml-1"></i>
            {{ $message }}
        </div>

    @enderror


    {{-- ================================================================
         OVERVIEW / KPI CARDS
         ================================================================ --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5 mb-6">

        {{-- Total Roles --}}
        <div class="card relative overflow-hidden">

            <div class="flex items-center justify-between gap-3">

                <div>

                    <div class="text-xs text-muted mb-1">
                        إجمالي الرتب
                    </div>

                    <div class="text-2xl font-bold text-fg">
                        {{ number_format($totalRoles) }}
                    </div>

                    <div class="mt-1 text-[11px] text-dim">
                        الرتب الموجودة بالنظام
                    </div>

                </div>

                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-info/15 text-info">
                    <i class="fa-solid fa-user-shield"></i>
                </span>

            </div>

        </div>


        {{-- System Roles --}}
        <div class="card relative overflow-hidden">

            <div class="flex items-center justify-between gap-3">

                <div>

                    <div class="text-xs text-muted mb-1">
                        رتب النظام
                    </div>

                    <div class="text-2xl font-bold text-cyan">
                        {{ number_format($systemRoles) }}
                    </div>

                    <div class="mt-1 text-[11px] text-dim">
                        رتب محمية
                    </div>

                </div>

                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-cyan/15 text-cyan">
                    <i class="fa-solid fa-lock"></i>
                </span>

            </div>

        </div>


        {{-- Custom Roles --}}
        <div class="card relative overflow-hidden">

            <div class="flex items-center justify-between gap-3">

                <div>

                    <div class="text-xs text-muted mb-1">
                        الرتب المخصصة
                    </div>

                    <div class="text-2xl font-bold text-brand">
                        {{ number_format($customRoles) }}
                    </div>

                    <div class="mt-1 text-[11px] text-dim">
                        يمكن تعديلها وحذفها
                    </div>

                </div>

                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand/15 text-brand">
                    <i class="fa-solid fa-user-gear"></i>
                </span>

            </div>

        </div>


        {{-- Users --}}
        <div class="card relative overflow-hidden">

            <div class="flex items-center justify-between gap-3">

                <div>

                    <div class="text-xs text-muted mb-1">
                        المستخدمون
                    </div>

                    <div class="text-2xl font-bold text-warning">
                        {{ number_format($totalUsers) }}
                    </div>

                    <div class="mt-1 text-[11px] text-dim">
                        مستخدم مرتبط بالرتب
                    </div>

                </div>

                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-warning/15 text-warning">
                    <i class="fa-solid fa-users"></i>
                </span>

            </div>

        </div>


        {{-- Permissions --}}
        <div class="card relative overflow-hidden">

            <div class="flex items-center justify-between gap-3">

                <div>

                    <div class="text-xs text-muted mb-1">
                        إجمالي الصلاحيات
                    </div>

                    <div class="text-2xl font-bold text-accent">
                        {{ number_format($totalPermissions) }}
                    </div>

                    <div class="mt-1 text-[11px] text-dim">
                        الصلاحيات المتاحة للنظام
                    </div>

                </div>

                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-accent/15 text-accent">
                    <i class="fa-solid fa-key"></i>
                </span>

            </div>

        </div>

    </div>


    {{-- ================================================================
         QUICK INTELLIGENCE
         ================================================================ --}}
    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3 mb-6">

        {{-- Role Usage --}}
        <div class="card">

            <div class="flex items-center justify-between mb-4">

                <div>

                    <div class="text-sm font-bold text-fg">
                        توزيع استخدام الرتب
                    </div>

                    <div class="text-[11px] text-dim mt-1">
                        الرتب المرتبطة بالمستخدمين
                    </div>

                </div>

                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand/15 text-brand">
                    <i class="fa-solid fa-chart-column"></i>
                </span>

            </div>

            <div class="space-y-3">

                <div class="flex items-center justify-between">

                    <span class="text-xs text-muted">
                        رتب مستخدمة
                    </span>

                    <span class="badge badge-outline-info">
                        {{ number_format($rolesWithUsers) }}
                    </span>

                </div>

                <div class="flex items-center justify-between">

                    <span class="text-xs text-muted">
                        رتب بدون مستخدمين
                    </span>

                    <span class="badge badge-outline-info">
                        {{ number_format($rolesWithoutUsers) }}
                    </span>

                </div>

                @if($mostUsedRole)

                    <div class="mt-3 rounded-lg border border-white/5 bg-white/[0.02] p-3">

                        <div class="text-[11px] text-dim">
                            أكثر رتبة استخدامًا
                        </div>

                        <div class="mt-1 flex items-center justify-between gap-2">

                            <span class="font-semibold text-fg truncate">
                                {{ $mostUsedRole->label }}
                            </span>

                            <span class="badge badge-outline-info">
                                {{ number_format($mostUsedRole->users_count ?? 0) }}
                            </span>

                        </div>

                    </div>

                @endif

            </div>

        </div>


        {{-- Permission Intelligence --}}
        <div class="card">

            <div class="flex items-center justify-between mb-4">

                <div>

                    <div class="text-sm font-bold text-fg">
                        تغطية الصلاحيات
                    </div>

                    <div class="text-[11px] text-dim mt-1">
                        مستوى الصلاحيات الممنوحة للرتب
                    </div>

                </div>

                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-accent/15 text-accent">
                    <i class="fa-solid fa-shield-halved"></i>
                </span>

            </div>

            <div class="mb-2 flex items-center justify-between text-[11px] text-muted">

                <span>
                    الصلاحيات الموزعة
                </span>

                <span>
                    {{ number_format($totalAssignedPermissions) }}
                </span>

            </div>

            <div class="progress">
                <div
                    class="h-full rounded-full bg-brand"
                    style="width: {{ $permissionPercent }}%"
                ></div>
            </div>

            <div class="mt-2 flex items-center justify-between text-[11px]">

                <span class="text-dim">
                    إجمالي الصلاحيات المتاحة
                </span>

                <span class="font-semibold text-brand">
                    {{ $permissionPercent }}%
                </span>

            </div>

            @if($mostPowerfulRole)

                <div class="mt-4 rounded-lg border border-white/5 bg-white/[0.02] p-3">

                    <div class="text-[11px] text-dim">
                        أعلى عدد صلاحيات
                    </div>

                    <div class="mt-1 flex items-center justify-between gap-2">

                        <span class="font-semibold text-fg truncate">
                            {{ $mostPowerfulRole->label }}
                        </span>

                        <span class="badge badge-outline-info">
                            {{ number_format($mostPowerfulRole->permissions_count ?? 0) }}
                        </span>

                    </div>

                </div>

            @endif

        </div>


        {{-- Levels --}}
        <div class="card">

            <div class="flex items-center justify-between mb-4">

                <div>

                    <div class="text-sm font-bold text-fg">
                        مستويات الرتب
                    </div>

                    <div class="text-[11px] text-dim mt-1">
                        التوزيع حسب المستوى الإداري
                    </div>

                </div>

                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-warning/15 text-warning">
                    <i class="fa-solid fa-layer-group"></i>
                </span>

            </div>

            <div class="space-y-2">

                @forelse($roleLevels->take(6) as $level => $count)

                    <div class="flex items-center justify-between rounded-lg border border-white/5 bg-white/[0.02] px-3 py-2">

                        <div class="flex items-center gap-2">

                            <span class="h-2 w-2 rounded-full bg-warning"></span>

                            <span class="text-xs text-muted">
                                المستوى {{ $level }}
                            </span>

                        </div>

                        <span class="badge badge-outline-info">
                            {{ number_format($count) }}
                        </span>

                    </div>

                @empty

                    <div class="text-xs text-dim">
                        لا توجد بيانات للمستويات.
                    </div>

                @endforelse

            </div>

            @if($highestLevel !== null)

                <div class="mt-4 flex items-center justify-between text-[11px] text-dim">

                    <span>
                        أعلى مستوى
                    </span>

                    <span class="font-semibold text-fg">
                        {{ $highestLevel }}
                    </span>

                </div>

            @endif

        </div>

    </div>


    {{-- ================================================================
         SEARCH / FILTER BAR
         ================================================================ --}}
    <form
        method="GET"
        action="{{ route('roles.index') }}"
        class="card filter-bar mb-6"
        id="rolesFilterForm"
    >

        {{-- Search --}}
        <div class="min-w-[260px] flex-1">

            <label
                for="roleSearch"
                class="sr-only"
            >
                البحث في الرتب
            </label>

            <div class="relative">

                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-dim"></i>

                <input
                    id="roleSearch"
                    name="search"
                    type="search"
                    value="{{ $currentSearch }}"
                    placeholder="ابحث باسم الرتبة، الاسم البرمجي أو الوصف..."
                    class="form-input pl-9"
                    autocomplete="off"
                >

            </div>

        </div>


        {{-- Type --}}
        <div class="w-48">

            <label
                for="roleType"
                class="form-label"
            >
                <i class="fa-solid fa-filter ml-1 text-info"></i>
                نوع الرتبة
            </label>

            <select
                id="roleType"
                name="type"
                class="form-input"
                onchange="this.form.submit()"
            >

                <option value="">
                    كل الرتب
                </option>

                <option
                    value="system"
                    @selected($currentType === 'system')
                >
                    رتب النظام
                </option>

                <option
                    value="custom"
                    @selected($currentType === 'custom')
                >
                    الرتب المخصصة
                </option>

            </select>

        </div>


        {{-- Search button --}}
        <button
            type="submit"
            class="btn btn-primary"
            title="تطبيق البحث"
            aria-label="تطبيق البحث"
        >
            <i class="fa-solid fa-magnifying-glass"></i>
        </button>


        {{-- Reset --}}
        <a
            href="{{ route('roles.index') }}"
            class="btn btn-secondary"
            title="إعادة ضبط الفلاتر"
            aria-label="إعادة ضبط الفلاتر"
        >
            <i class="fa-solid fa-rotate-right"></i>
        </a>

    </form>


    {{-- ================================================================
         ACTIVE FILTERS
         ================================================================ --}}
    @if($hasFilters)

        <div class="mb-4 flex flex-wrap items-center gap-2">

            <span class="text-[11px] text-dim">
                <i class="fa-solid fa-filter ml-1"></i>
                الفلاتر الحالية:
            </span>

            @if($currentSearch !== '')

                <span class="tag">
                    <i class="fa-solid fa-magnifying-glass text-[10px] text-info"></i>
                    {{ $currentSearch }}
                </span>

            @endif

            @if($currentType !== '')

                <span class="tag">

                    <i class="fa-solid fa-layer-group text-[10px] text-accent"></i>

                    {{ $currentType === 'system' ? 'رتب النظام' : 'الرتب المخصصة' }}

                </span>

            @endif

        </div>

    @endif


    {{-- ================================================================
         TABLE INFORMATION BAR
         ================================================================ --}}
    <div class="mb-3 flex flex-wrap items-center justify-between gap-3">

        <div class="flex items-center gap-3">

            <div class="flex items-center gap-2 text-sm font-bold text-fg">

                <i class="fa-solid fa-list text-info"></i>

                قائمة الرتب

            </div>

            <span class="badge badge-outline-info">

                {{ number_format($visibleRoles) }}

                معروض

            </span>

        </div>


        <div class="flex flex-wrap items-center gap-3 text-[11px] text-dim">

            <span>
                <i class="fa-solid fa-lock text-cyan ml-1"></i>
                {{ number_format($systemRoles) }} نظام
            </span>

            <span>
                <i class="fa-solid fa-user-gear text-brand ml-1"></i>
                {{ number_format($customRoles) }} مخصصة
            </span>

            <span>
                <i class="fa-solid fa-key text-accent ml-1"></i>
                {{ number_format($totalPermissions) }} صلاحية
            </span>

        </div>

    </div>


    {{-- ================================================================
         ROLES TABLE
         ================================================================ --}}
    <div class="table-wrap">

        <table class="data-table whitespace-nowrap">

            <thead>

                <tr>

                    <th>
                        الرتبة
                    </th>

                    <th>
                        الاسم البرمجي
                    </th>

                    <th>
                        الوصف
                    </th>

                    <th>
                        المستوى
                    </th>

                    <th>
                        الموظفون
                    </th>

                    <th class="w-56">
                        الصلاحيات
                    </th>

                    <th>
                        إجراءات
                    </th>

                </tr>

            </thead>


            <tbody>

                @forelse($roles as $role)

                    @php

                        $rolePermissions = (int) ($role->permissions_count ?? 0);

                        $percent = $totalPermissions > 0
                            ? (int) round(($rolePermissions / $totalPermissions) * 100)
                            : 0;

                        $percent = min(100, max(0, $percent));

                        $userCount = (int) ($role->users_count ?? 0);

                    @endphp


                    <tr>

                        {{-- ==================================================
                             ROLE NAME
                             ================================================== --}}
                        <td>

                            <div class="flex items-center gap-2">

                                @if($role->is_system)

                                    <span
                                        class="badge badge-owner gap-1.5 uppercase"
                                        title="رتبة محمية من النظام"
                                    >

                                        {{ $role->label }}

                                        <i class="fa-solid fa-lock text-[9px]"></i>

                                    </span>

                                @else

                                    <span
                                        class="badge badge-outline-info"
                                        title="رتبة مخصصة"
                                    >
                                        {{ $role->label }}
                                    </span>

                                @endif

                            </div>

                        </td>


                        {{-- ==================================================
                             PROGRAMMATIC NAME
                             ================================================== --}}
                        <td>

                            <div class="flex items-center gap-2">

                                <span
                                    class="font-mono text-xs"
                                    dir="ltr"
                                    title="الاسم البرمجي للرتبة"
                                >
                                    {{ $role->name }}
                                </span>

                                <button
                                    type="button"
                                    class="text-dim hover:text-info transition"
                                    title="نسخ الاسم البرمجي"
                                    aria-label="نسخ الاسم البرمجي"
                                    onclick="copyRoleName(@js($role->name), this)"
                                >
                                    <i class="fa-regular fa-copy text-[10px]"></i>
                                </button>

                            </div>

                        </td>


                        {{-- ==================================================
                             DESCRIPTION
                             ================================================== --}}
                        <td class="whitespace-normal min-w-[220px]">

                            @if($role->description)

                                <span
                                    class="text-sm"
                                    title="{{ $role->description }}"
                                >
                                    {{ $role->description }}
                                </span>

                            @else

                                <span class="text-dim text-xs">
                                    لا يوجد وصف
                                </span>

                            @endif

                        </td>


                        {{-- ==================================================
                             LEVEL
                             ================================================== --}}
                        <td>

                            <span
                                class="badge badge-outline-info"
                                title="المستوى الإداري"
                            >
                                <i class="fa-solid fa-layer-group text-[9px] ml-1"></i>
                                {{ $role->level }}
                            </span>

                        </td>


                        {{-- ==================================================
                             USERS COUNT
                             ================================================== --}}
                        <td>

                            @if($userCount > 0)

                                <span
                                    class="inline-flex items-center gap-1.5 font-semibold text-fg"
                                    title="عدد المستخدمين المرتبطين بالرتبة"
                                >

                                    <i class="fa-solid fa-users text-[10px] text-info"></i>

                                    {{ number_format($userCount) }}

                                </span>

                            @else

                                <span
                                    class="text-dim text-xs"
                                    title="لا يوجد مستخدمون مرتبطون بهذه الرتبة"
                                >
                                    بدون مستخدمين
                                </span>

                            @endif

                        </td>


                        {{-- ==================================================
                             PERMISSIONS
                             ================================================== --}}
                        <td>

                            <div class="mb-1 flex justify-between text-[11px] text-muted">

                                <span>
                                    {{ number_format($rolePermissions) }}
                                    /
                                    {{ number_format($totalPermissions) }}
                                </span>

                                <span class="font-semibold text-fg">
                                    {{ $percent }}%
                                </span>

                            </div>

                            <div class="progress">

                                <div
                                    class="h-full rounded-full bg-brand"
                                    style="width: {{ $percent }}%"
                                    title="{{ $percent }}% من الصلاحيات"
                                ></div>

                            </div>

                            @if($rolePermissions === 0)

                                <div class="mt-1 text-[10px] text-dim">
                                    لا توجد صلاحيات
                                </div>

                            @elseif($percent >= 80)

                                <div class="mt-1 text-[10px] text-brand">
                                    صلاحيات واسعة
                                </div>

                            @elseif($percent >= 40)

                                <div class="mt-1 text-[10px] text-info">
                                    صلاحيات متوسطة
                                </div>

                            @else

                                <div class="mt-1 text-[10px] text-warning">
                                    صلاحيات محدودة
                                </div>

                            @endif

                        </td>


                        {{-- ==================================================
                             ACTIONS
                             ================================================== --}}
                        <td>

                            @if($role->is_system)

                                <span
                                    class="inline-flex items-center gap-1.5 text-xs text-dim"
                                    title="لا يمكن تعديل أو حذف رتبة النظام"
                                >

                                    <i class="fa-solid fa-lock text-[10px] text-cyan"></i>

                                    رتبة نظام

                                </span>

                            @else

                                <div class="flex items-center gap-2">

                                    <a
                                        href="{{ route('roles.edit', $role->id) }}"
                                        class="btn btn-secondary btn-sm"
                                        title="تعديل الرتبة"
                                        aria-label="تعديل الرتبة"
                                    >
                                        <i class="fa-solid fa-pen text-[10px]"></i>
                                        تعديل
                                    </a>


                                    <form
                                        method="POST"
                                        action="{{ route('roles.destroy', $role->id) }}"
                                        onsubmit="return confirm('هل أنت متأكد من حذف الرتبة؟ سيتم حذفها فقط إذا لم تكن مرتبطة ببيانات تمنع الحذف.')"
                                    >

                                        @csrf

                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="btn btn-danger btn-sm"
                                            title="حذف الرتبة"
                                            aria-label="حذف الرتبة"
                                        >
                                            <i class="fa-solid fa-trash text-[10px]"></i>
                                            حذف
                                        </button>

                                    </form>

                                </div>

                            @endif

                        </td>

                    </tr>

                @empty

                    {{-- ==================================================
                         EMPTY STATE
                         ================================================== --}}
                    <tr>

                        <td colspan="7">

                            <div class="py-4">

                                <x-empty-state
                                    icon="fa-user-shield"
                                    text="{{ $hasFilters ? 'لا توجد رتب مطابقة للفلاتر الحالية' : 'لا توجد رتب نظامية' }}"
                                />

                                @if($hasFilters)

                                    <div class="mt-3 text-center">

                                        <a
                                            href="{{ route('roles.index') }}"
                                            class="btn btn-secondary btn-sm"
                                        >
                                            <i class="fa-solid fa-rotate-right"></i>
                                            عرض جميع الرتب
                                        </a>

                                    </div>

                                @endif

                            </div>

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>


    {{-- ================================================================
         PAGINATION
         ================================================================ --}}
    @if($hasPagination)

        <div class="mt-5">

            {{ $roles->withQueryString()->links() }}

        </div>

    @endif


    {{-- ================================================================
         FOOTER SUMMARY
         ================================================================ --}}
    @if($visibleRoles > 0)

        <div class="mt-5 card">

            <div class="flex flex-wrap items-center justify-between gap-4">

                <div class="flex flex-wrap items-center gap-4 text-[11px] text-dim">

                    <span>
                        <i class="fa-solid fa-user-shield text-info ml-1"></i>
                        {{ number_format($visibleRoles) }}
                        رتبة معروضة
                    </span>

                    <span>
                        <i class="fa-solid fa-lock text-cyan ml-1"></i>
                        {{ number_format($systemRoles) }}
                        نظام
                    </span>

                    <span>
                        <i class="fa-solid fa-user-gear text-brand ml-1"></i>
                        {{ number_format($customRoles) }}
                        مخصصة
                    </span>

                    <span>
                        <i class="fa-solid fa-users text-warning ml-1"></i>
                        {{ number_format($totalUsers) }}
                        مستخدم
                    </span>

                    <span>
                        <i class="fa-solid fa-key text-accent ml-1"></i>
                        {{ number_format($totalPermissions) }}
                        صلاحية متاحة
                    </span>

                </div>


                <div class="text-[11px] text-dim">

                    @if($hasFilters)

                        <span>
                            النتائج مفلترة حسب البحث الحالي
                        </span>

                    @else

                        <span>
                            جميع الرتب النظامية
                        </span>

                    @endif

                </div>

            </div>

        </div>

    @endif


    {{-- ================================================================
         CLIENT-SIDE UX
         ================================================================ --}}
    <script>

        /*
        |--------------------------------------------------------------------------
        | COPY ROLE NAME
        |--------------------------------------------------------------------------
        */
        function copyRoleName(value, button) {

            if (!navigator.clipboard) {
                return;
            }

            navigator.clipboard.writeText(value).then(function () {

                const icon = button.querySelector('i');

                if (!icon) {
                    return;
                }

                const originalClass = icon.className;

                icon.className = 'fa-solid fa-check text-brand';

                setTimeout(function () {
                    icon.className = originalClass;
                }, 1200);

            });

        }


        /*
        |--------------------------------------------------------------------------
        | KEYBOARD SHORTCUT
        |--------------------------------------------------------------------------
        | Ctrl + K / Cmd + K -> focus search.
        |--------------------------------------------------------------------------
        */
        document.addEventListener('keydown', function (event) {

            if (
                (event.ctrlKey || event.metaKey) &&
                event.key.toLowerCase() === 'k'
            ) {

                const search = document.getElementById('roleSearch');

                if (!search) {
                    return;
                }

                event.preventDefault();

                search.focus();
                search.select();

            }

        });


        /*
        |--------------------------------------------------------------------------
        | ESCAPE -> CLEAR SEARCH
        |--------------------------------------------------------------------------
        */
        document.addEventListener('DOMContentLoaded', function () {

            const search = document.getElementById('roleSearch');
            const form = document.getElementById('rolesFilterForm');

            if (!search) {
                return;
            }

            search.addEventListener('keydown', function (event) {

                if (event.key === 'Escape' && search.value !== '') {

                    search.value = '';

                    if (form) {
                        form.submit();
                    }

                }

            });

        });

    </script>

@endsection