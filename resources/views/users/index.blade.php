{{-- resources/views/users/index.blade.php --}}

@extends('layouts.app')

@section('title', 'المستخدمون')

@section('content')

    {{-- =========================================================
         PAGE HEADER
    ========================================================== --}}

    <x-page-header
        title="المستخدمون"
        subtitle="إدارة الموظفين والحسابات والصلاحيات والحالة التشغيلية"
        icon="fa-users"
    />

    {{-- =========================================================
         QUICK STATS
    ========================================================== --}}

    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach($stats as $stat)
            <x-metric-card
                :label="$stat['label']"
                :value="$stat['value']"
                :icon="$stat['icon']"
                :color="$stat['color']"
            />
        @endforeach
    </section>

    {{-- =========================================================
         SECONDARY OVERVIEW
    ========================================================== --}}

    <section class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

        <div class="card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-dim">نسبة الحسابات النشطة</p>

                    <p class="mt-2 text-2xl font-bold text-fg">
                        {{ $overview['active_percentage'] }}%
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-white/10 bg-white/5">
                    <i class="fa-solid fa-chart-pie text-brand"></i>
                </div>
            </div>

            <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-white/5">
                <div
                    class="h-full rounded-full bg-brand"
                    style="width: {{ min(100, $overview['active_percentage']) }}%"
                ></div>
            </div>
        </div>

        <div class="card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-dim">نسبة الموظفين تحت إشراف</p>

                    <p class="mt-2 text-2xl font-bold text-fg">
                        {{ $overview['supervised_percentage'] }}%
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-white/10 bg-white/5">
                    <i class="fa-solid fa-user-group text-info"></i>
                </div>
            </div>

            <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-white/5">
                <div
                    class="h-full rounded-full bg-info"
                    style="width: {{ min(100, $overview['supervised_percentage']) }}%"
                ></div>
            </div>
        </div>

        <div class="card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-dim">الأدوار المستخدمة</p>

                    <p class="mt-2 text-2xl font-bold text-fg">
                        {{ $overview['roles_used'] }}
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-white/10 bg-white/5">
                    <i class="fa-solid fa-shield-halved text-accent"></i>
                </div>
            </div>

            <p class="mt-3 text-xs text-dim">
                من إجمالي الأدوار المعرفة بالنظام
            </p>
        </div>

        <div class="card">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-dim">الشركات المستخدمة</p>

                    <p class="mt-2 text-2xl font-bold text-fg">
                        {{ $overview['companies_used'] }}
                    </p>
                </div>

                <div class="flex h-11 w-11 items-center justify-center rounded-xl border border-white/10 bg-white/5">
                    <i class="fa-solid fa-building text-warning"></i>
                </div>
            </div>

            <p class="mt-3 text-xs text-dim">
                شركات التقسيط المرتبطة بالحسابات
            </p>
        </div>

    </section>

    {{-- =========================================================
         FILTERS
    ========================================================== --}}

    <form
        method="GET"
        action="{{ route('users.index') }}"
        class="card filter-bar mb-6"
        id="usersFilterForm"
    >

        {{-- Search --}}
        <div class="min-w-[240px] flex-1">

            <label for="search" class="form-label">
                بحث
            </label>

            <div class="relative">

                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-dim"></i>

                <input
                    id="search"
                    name="search"
                    type="search"
                    value="{{ $filters['search'] }}"
                    placeholder="الاسم، الكود، البريد، الهاتف، الدور..."
                    class="form-input pl-9"
                >

            </div>

        </div>

        {{-- Role --}}
        <div class="w-48">

            <label for="role" class="form-label">
                الدور
            </label>

            <select
                id="role"
                name="role"
                class="form-input"
                onchange="this.form.submit()"
            >

                <option value="">
                    كل الأدوار
                </option>

                @foreach($roles as $role)
                    <option
                        value="{{ $role }}"
                        @selected($filters['role'] === $role)
                    >
                        {{ $role }}
                    </option>
                @endforeach

            </select>

        </div>

        {{-- Status --}}
        <div class="w-44">

            <label for="status" class="form-label">
                الحالة
            </label>

            <select
                id="status"
                name="status"
                class="form-input"
                onchange="this.form.submit()"
            >

                <option value="">
                    كل الحالات
                </option>

                @foreach($statuses as $key => $label)

                    <option
                        value="{{ $key }}"
                        @selected($filters['status'] === $key)
                    >
                        {{ $label }}
                    </option>

                @endforeach

            </select>

        </div>

        {{-- Filter --}}
        <button
            type="submit"
            class="btn btn-primary"
        >
            <i class="fa-solid fa-filter text-xs"></i>
            تصفية
        </button>

        {{-- Reset --}}
        <a
            href="{{ route('users.index') }}"
            class="btn btn-secondary"
            title="إعادة ضبط"
        >
            <i class="fa-solid fa-rotate-right"></i>
        </a>

        {{-- Create --}}
        <a
            href="{{ route('users.create') }}"
            class="btn btn-primary"
        >
            <i class="fa-solid fa-user-plus"></i>
            إضافة موظف
        </a>

    </form>

    {{-- =========================================================
         ACTIVE FILTER SUMMARY
    ========================================================== --}}

    @if($filters['search'] !== '' || $filters['role'] !== '' || $filters['status'] !== '')

        <div class="mb-4 flex flex-wrap items-center gap-2">

            <span class="text-xs text-dim">
                الفلاتر الحالية:
            </span>

            @if($filters['search'] !== '')
                <span class="badge badge-outline-info">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    {{ $filters['search'] }}
                </span>
            @endif

            @if($filters['role'] !== '')
                <span class="badge badge-outline-info">
                    <i class="fa-solid fa-shield-halved"></i>
                    {{ $filters['role'] }}
                </span>
            @endif

            @if($filters['status'] !== '')
                <span class="badge badge-outline-info">
                    <i class="fa-solid fa-circle"></i>
                    {{ $statuses[$filters['status']] ?? $filters['status'] }}
                </span>
            @endif

            <span class="text-xs text-dim">
                {{ $filteredUsersCount }} من {{ $allUsersCount }}
            </span>

        </div>

    @endif

    {{-- =========================================================
         MAIN CONTENT
    ========================================================== --}}

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">

        {{-- =====================================================
             USERS TABLE
        ====================================================== --}}

        <div class="xl:col-span-2">

            <div class="table-wrap">

                <table class="data-table whitespace-nowrap">

                    <thead>

                        <tr>

                            <th>#</th>

                            <th>الموظف</th>

                            <th>الدور</th>

                            <th>المشرف</th>

                            <th>الشركات</th>

                            <th>الحالة</th>

                            <th>الصلاحيات</th>

                            <th></th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($users as $index => $user)

                            <tr>

                                {{-- Number --}}
                                <td class="text-dim">
                                    {{ $index + 1 }}
                                </td>

                                {{-- User --}}
                                <td>

                                    <div class="flex items-center gap-3">

                                        <x-avatar
                                            :name="$user->name"
                                            size="h-9 w-9"
                                        />

                                        <div>

                                            <div class="font-semibold text-fg">
                                                {{ $user->name }}
                                            </div>

                                            <div class="mt-0.5 text-[11px] text-dim">
                                                {{ $user->code ?? 'بدون كود' }}
                                            </div>

                                            <div
                                                class="mt-0.5 text-[11px] text-dim"
                                                dir="ltr"
                                            >
                                                {{ $user->email }}
                                            </div>

                                        </div>

                                    </div>

                                </td>

                                {{-- Role --}}
                                <td>

                                    <span class="badge badge-outline-info">

                                        <i class="fa-solid fa-shield-halved"></i>

                                        {{ $user->role_label }}

                                    </span>

                                </td>

                                {{-- Supervisor --}}
                                <td>

                                    @if($user->supervisor_name !== 'بدون مشرف')

                                        <div class="flex items-center gap-2">

                                            <i class="fa-solid fa-user-group text-xs text-info"></i>

                                            <span class="text-fg">
                                                {{ $user->supervisor_name }}
                                            </span>

                                        </div>

                                    @else

                                        <span class="text-xs text-dim">
                                            بدون مشرف
                                        </span>

                                    @endif

                                </td>

                                {{-- Companies --}}
                                <td>

                                    @if(count($user->company_names))

                                        <div class="flex max-w-[220px] flex-wrap gap-1">

                                            @foreach(array_slice($user->company_names, 0, 2) as $company)

                                                <span class="badge badge-neutral">
                                                    {{ $company }}
                                                </span>

                                            @endforeach

                                            @if(count($user->company_names) > 2)

                                                <span class="badge badge-neutral">
                                                    +{{ count($user->company_names) - 2 }}
                                                </span>

                                            @endif

                                        </div>

                                    @else

                                        <span class="text-xs text-dim">
                                            لا توجد شركات
                                        </span>

                                    @endif

                                </td>

                                {{-- Status --}}
                                <td>

                                    <span class="badge badge-{{ $user->status_meta['tone'] }}">

                                        <i class="fa-solid {{ $user->status_meta['icon'] }}"></i>

                                        {{ $user->status_meta['label'] }}

                                    </span>

                                </td>

                                {{-- Permissions --}}
                                <td>

                                    <div class="min-w-[120px]">

                                        <div class="flex items-center justify-between text-[11px]">

                                            <span class="text-dim">
                                                الدور
                                            </span>

                                            <span class="font-semibold text-fg">
                                                {{ $user->role_permission_count }}
                                            </span>

                                        </div>

                                        @if($user->override_count > 0)

                                            <div class="mt-1 text-[10px] text-warning">

                                                <i class="fa-solid fa-sliders"></i>

                                                {{ $user->override_count }}
                                                تخصيص

                                            </div>

                                        @endif

                                    </div>

                                </td>

                                {{-- Actions --}}
                                <td>

                                    <div class="flex items-center gap-1">

                                        <a
                                            href="{{ route('users.show', $user->id) }}"
                                            class="icon-btn icon-btn-info"
                                            title="عرض المستخدم"
                                            aria-label="عرض المستخدم"
                                        >
                                            <i class="fa-solid fa-eye"></i>
                                        </a>

                                        @if(!$user->is_system)

                                            <a
                                                href="{{ route('users.edit', $user->id) }}"
                                                class="icon-btn icon-btn-warning"
                                                title="تعديل المستخدم"
                                                aria-label="تعديل المستخدم"
                                            >
                                                <i class="fa-solid fa-pen"></i>
                                            </a>

                                            <form
                                                method="POST"
                                                action="{{ route('users.destroy', $user->id) }}"
                                                class="inline"
                                                onsubmit="return confirm('هل أنت متأكد من حذف هذا الموظف؟');"
                                            >

                                                @csrf

                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="icon-btn icon-btn-danger"
                                                    title="حذف المستخدم"
                                                    aria-label="حذف المستخدم"
                                                >
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>

                                            </form>

                                        @else

                                            <span
                                                class="icon-btn icon-btn-secondary opacity-50"
                                                title="حساب نظام"
                                            >
                                                <i class="fa-solid fa-lock"></i>
                                            </span>

                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="8">

                                    <x-empty-state
                                        text="لا توجد حسابات مطابقة للفلاتر الحالية"
                                    />

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

            {{-- Result footer --}}
            <div class="mt-3 flex flex-wrap items-center justify-between gap-3 text-xs text-dim">

                <span>
                    عرض {{ $filteredUsersCount }} حساب
                    من أصل {{ $allUsersCount }}
                </span>

                <span>
                    نشط:
                    <strong class="text-brand">
                        {{ $filteredStatusCounts['active'] }}
                    </strong>

                    · موقوف:
                    <strong class="text-warning">
                        {{ $filteredStatusCounts['suspended'] }}
                    </strong>

                    · معطل:
                    <strong>
                        {{ $filteredStatusCounts['inactive'] }}
                    </strong>
                </span>

            </div>

        </div>

        {{-- =====================================================
             RIGHT SIDEBAR ANALYTICS
        ====================================================== --}}

        <div class="space-y-6">

            {{-- Status --}}
            <div class="card">

                <div class="mb-4 flex items-center justify-between">

                    <div>

                        <h3 class="font-bold text-fg">
                            توزيع الحالات
                        </h3>

                        <p class="mt-1 text-xs text-dim">
                            الحالة الحالية للحسابات
                        </p>

                    </div>

                    <i class="fa-solid fa-chart-pie text-info"></i>

                </div>

                <div class="space-y-3">

                    @foreach($statusStats as $status)

                        <div>

                            <div class="mb-1 flex items-center justify-between text-xs">

                                <span class="flex items-center gap-2">

                                    <i class="fa-solid {{ $status->icon }}"></i>

                                    {{ $status->label }}

                                </span>

                                <strong class="text-fg">
                                    {{ $status->count }}
                                </strong>

                            </div>

                            @php
                                $percentage = $allUsersCount > 0
                                    ? round(($status->count / $allUsersCount) * 100)
                                    : 0;
                            @endphp

                            <div class="h-1.5 overflow-hidden rounded-full bg-white/5">

                                <div
                                    class="h-full rounded-full bg-current {{ $status->tone === 'success' ? 'text-brand' : ($status->tone === 'danger' ? 'text-danger' : 'text-warning') }}"
                                    style="width: {{ $percentage }}%"
                                ></div>

                            </div>

                        </div>

                    @endforeach

                </div>

            </div>

            {{-- Roles --}}
            <div class="card">

                <div class="mb-4 flex items-center justify-between">

                    <div>

                        <h3 class="font-bold text-fg">
                            توزيع الأدوار
                        </h3>

                        <p class="mt-1 text-xs text-dim">
                            عدد المستخدمين لكل دور
                        </p>

                    </div>

                    <i class="fa-solid fa-shield-halved text-accent"></i>

                </div>

                <div class="space-y-3">

                    @forelse($roleStats as $role)

                        <div class="flex items-center justify-between rounded-lg border border-white/5 bg-white/[0.02] px-3 py-2">

                            <div class="flex items-center gap-2">

                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/5">

                                    <i class="fa-solid fa-user-shield text-info text-xs"></i>

                                </span>

                                <span class="text-sm text-fg">
                                    {{ $role->label }}
                                </span>

                            </div>

                            <span class="badge badge-neutral">
                                {{ $role->count }}
                            </span>

                        </div>

                    @empty

                        <p class="text-xs text-dim">
                            لا توجد أدوار.
                        </p>

                    @endforelse

                </div>

            </div>

            {{-- Supervisors --}}
            <div class="card">

                <div class="mb-4 flex items-center justify-between">

                    <div>

                        <h3 class="font-bold text-fg">
                            توزيع الموظفين
                        </h3>

                        <p class="mt-1 text-xs text-dim">
                            حسب المشرف المباشر
                        </p>

                    </div>

                    <i class="fa-solid fa-user-group text-brand"></i>

                </div>

                <div class="space-y-2">

                    @foreach($supervisorStats->take(6) as $supervisor)

                        <div class="flex items-center justify-between border-b border-white/5 pb-2 last:border-0">

                            <span class="text-sm text-fg">
                                {{ $supervisor->name }}
                            </span>

                            <span class="text-xs font-bold text-info">
                                {{ $supervisor->count }}
                            </span>

                        </div>

                    @endforeach

                </div>

            </div>

            {{-- Companies --}}
            <div class="card">

                <div class="mb-4 flex items-center justify-between">

                    <div>

                        <h3 class="font-bold text-fg">
                            الشركات
                        </h3>

                        <p class="mt-1 text-xs text-dim">
                            الحسابات المرتبطة بكل شركة
                        </p>

                    </div>

                    <i class="fa-solid fa-building text-warning"></i>

                </div>

                <div class="space-y-2">

                    @forelse($companyStats->take(6) as $company)

                        <div class="flex items-center justify-between">

                            <span class="truncate text-sm text-fg">
                                {{ $company->name }}
                            </span>

                            <span class="badge badge-neutral">
                                {{ $company->count }}
                            </span>

                        </div>

                    @empty

                        <p class="text-xs text-dim">
                            لا توجد شركات.
                        </p>

                    @endforelse

                </div>

            </div>

        </div>

    </div>

    {{-- =========================================================
         RECENT ACTIVITY
    ========================================================== --}}

    <section class="mt-6">

        <div class="card">

            <div class="mb-5 flex items-center justify-between">

                <div>

                    <h3 class="font-bold text-fg">
                        آخر نشاط للمستخدمين
                    </h3>

                    <p class="mt-1 text-xs text-dim">
                        مثال على الأحداث التي يمكن عرضها من Activity Logs
                    </p>

                </div>

                <i class="fa-solid fa-clock-rotate-left text-info"></i>

            </div>

            <div class="grid grid-cols-1 gap-3 md:grid-cols-2 xl:grid-cols-4">

                @foreach($recentActivity as $activity)

                    <div class="rounded-xl border border-white/5 bg-white/[0.02] p-3">

                        <div class="flex items-start gap-3">

                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white/5">

                                <i class="fa-solid {{ $activity->icon }} {{ $activity->color }}"></i>

                            </span>

                            <div class="min-w-0">

                                <p class="text-sm font-semibold text-fg">
                                    {{ $activity->text }}
                                </p>

                                <p class="mt-1 text-xs text-dim">
                                    {{ $activity->user }}
                                    ·
                                    {{ $activity->time }}
                                </p>

                            </div>

                        </div>

                    </div>

                @endforeach

            </div>

        </div>

    </section>

@endsection