{{-- resources/views/notifications/index.blade.php --}}
@extends('layouts.app')

@section('title', 'الإشعارات')

@php
    /*
    |--------------------------------------------------------------------------
    | Notification Types
    |--------------------------------------------------------------------------
    | Keep all Tailwind classes explicit so Tailwind can detect them.
    |--------------------------------------------------------------------------
    */
    $types = [
        'payment' => [
            'icon' => 'fa-money-bill-wave',
            'classes' => 'bg-brand/15 text-brand',
            'label' => 'تحصيلات',
        ],

        'ptp' => [
            'icon' => 'fa-handshake',
            'classes' => 'bg-warning/15 text-warning',
            'label' => 'وعود دفع',
        ],

        'complaint' => [
            'icon' => 'fa-headset',
            'classes' => 'bg-pink/15 text-pink',
            'label' => 'شكاوى',
        ],

        'distribution' => [
            'icon' => 'fa-sitemap',
            'classes' => 'bg-accent/15 text-accent',
            'label' => 'توزيع',
        ],

        'visit' => [
            'icon' => 'fa-person-walking',
            'classes' => 'bg-cyan/15 text-cyan',
            'label' => 'زيارات',
        ],

        'system' => [
            'icon' => 'fa-circle-info',
            'classes' => 'bg-info/15 text-info',
            'label' => 'النظام',
        ],
    ];


    /*
    |--------------------------------------------------------------------------
    | Notification Statistics
    |--------------------------------------------------------------------------
    */

    $totalNotifications = $items->count();

    $readNotifications = max(0, $totalNotifications - $unread);

    /*
    |--------------------------------------------------------------------------
    | Type Counters
    |--------------------------------------------------------------------------
    */

    $typeCounts = [];

    foreach ($types as $type => $config) {
        $typeCounts[$type] = collect($items)->where('type', $type)->count();
    }

    /*
    |--------------------------------------------------------------------------
    | Current Filter
    |--------------------------------------------------------------------------
    */

    $currentType = request('type', 'all');

@endphp


@section('content')

    {{-- =========================================================
        PAGE HEADER
    ========================================================== --}}

    <x-page-header
        title="الإشعارات"
        subtitle="مركز التنبيهات والأحداث المهمة في نظام التحصيل"
        icon="fa-bell"
    >

        <x-slot:actions>

            <div class="flex flex-wrap items-center gap-2">

                {{-- Refresh --}}
                <a
                    href="{{ request()->fullUrl() }}"
                    class="btn btn-secondary btn-sm"
                    title="تحديث الإشعارات"
                >
                    <i class="fa-solid fa-rotate"></i>
                    <span class="hidden sm:inline">تحديث</span>
                </a>


                {{-- Mark all as read --}}
                @if($unread > 0)

                    <form
                        method="POST"
                        action="{{ route('notifications.read-all') }}"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="btn btn-secondary btn-sm"
                        >
                            <i class="fa-solid fa-check-double"></i>
                            تحديد الكل كمقروء
                        </button>
                    </form>

                @endif

            </div>

        </x-slot:actions>

    </x-page-header>


    {{-- =========================================================
        FLASH MESSAGES
    ========================================================== --}}

    <x-flash />


    {{-- =========================================================
        OVERVIEW / STATISTICS
    ========================================================== --}}

    <section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

        {{-- Total --}}
        <div class="card">

            <div class="flex items-center gap-3">

                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-info/15 text-info">
                    <i class="fa-solid fa-bell"></i>
                </span>

                <div class="min-w-0 flex-1">

                    <div class="text-[11px] font-medium text-dim">
                        إجمالي الإشعارات
                    </div>

                    <div class="mt-1 text-2xl font-bold text-fg">
                        {{ number_format($totalNotifications) }}
                    </div>

                </div>

            </div>

        </div>


        {{-- Unread --}}
        <div class="card border-brand/20">

            <div class="flex items-center gap-3">

                <span class="relative flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-brand/15 text-brand">

                    <i class="fa-solid fa-envelope"></i>

                    @if($unread > 0)
                        <span class="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-brand px-1 text-[9px] font-bold text-black">
                            {{ $unread > 99 ? '99+' : $unread }}
                        </span>
                    @endif

                </span>

                <div class="min-w-0 flex-1">

                    <div class="text-[11px] font-medium text-dim">
                        غير المقروءة
                    </div>

                    <div class="mt-1 text-2xl font-bold text-brand">
                        {{ number_format($unread) }}
                    </div>

                </div>

            </div>

        </div>


        {{-- Read --}}
        <div class="card">

            <div class="flex items-center gap-3">

                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-accent/15 text-accent">
                    <i class="fa-solid fa-envelope-open"></i>
                </span>

                <div class="min-w-0 flex-1">

                    <div class="text-[11px] font-medium text-dim">
                        المقروءة
                    </div>

                    <div class="mt-1 text-2xl font-bold text-fg">
                        {{ number_format($readNotifications) }}
                    </div>

                </div>

            </div>

        </div>


        {{-- Attention --}}
        <div class="card">

            <div class="flex items-center gap-3">

                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-warning/15 text-warning">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </span>

                <div class="min-w-0 flex-1">

                    <div class="text-[11px] font-medium text-dim">
                        تحتاج متابعة
                    </div>

                    <div class="mt-1 text-2xl font-bold text-warning">
                        {{ number_format($unread) }}
                    </div>

                </div>

            </div>

        </div>

    </section>


    {{-- =========================================================
        NOTIFICATION TYPE SUMMARY
    ========================================================== --}}

    <section class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">

        @foreach($types as $type => $config)

            <a
                href="{{ route('notifications.index', ['type' => $type]) }}"
                class="card group flex items-center gap-3 transition hover:-translate-y-0.5 hover:bg-white/[0.04]"
            >

                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $config['classes'] }}">
                    <i class="fa-solid {{ $config['icon'] }} text-sm"></i>
                </span>

                <span class="min-w-0">

                    <span class="block truncate text-xs font-semibold text-fg transition group-hover:text-brand">
                        {{ $config['label'] }}
                    </span>

                    <span class="mt-0.5 block text-[11px] text-dim">
                        {{ number_format($typeCounts[$type] ?? 0) }}
                    </span>

                </span>

            </a>

        @endforeach

    </section>


    {{-- =========================================================
        FILTER + SEARCH
    ========================================================== --}}

    <section class="mb-6">

        <div class="card">

            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">

                {{-- Filter --}}
                <div class="flex flex-wrap items-center gap-2">

                    <span class="mr-1 text-xs font-semibold text-muted">
                        عرض:
                    </span>


                    <a
                        href="{{ route('notifications.index') }}"
                        class="segmented-item {{ $filter === 'all' && $currentType === 'all' ? 'segmented-item-active' : '' }}"
                    >
                        الكل

                        <span class="badge badge-neutral">
                            {{ number_format($totalNotifications) }}
                        </span>
                    </a>


                    <a
                        href="{{ route('notifications.index', ['filter' => 'unread']) }}"
                        class="segmented-item {{ $filter === 'unread' ? 'segmented-item-active' : '' }}"
                    >
                        غير المقروءة

                        <span class="badge badge-danger">
                            {{ number_format($unread) }}
                        </span>
                    </a>


                    @foreach($types as $type => $config)

                        <a
                            href="{{ route('notifications.index', ['type' => $type]) }}"
                            class="segmented-item {{ $currentType === $type ? 'segmented-item-active' : '' }}"
                        >
                            {{ $config['label'] }}
                        </a>

                    @endforeach

                </div>


                {{-- Search --}}
                <div class="relative w-full xl:max-w-sm">

                    <i class="fa-solid fa-magnifying-glass pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-dim"></i>

                    <input
                        id="notificationSearch"
                        type="search"
                        placeholder="البحث في الإشعارات..."
                        class="input w-full pr-9"
                        autocomplete="off"
                    >

                </div>

            </div>

        </div>

    </section>


    {{-- =========================================================
        NOTIFICATION LIST HEADER
    ========================================================== --}}

    <section class="mb-3 flex flex-wrap items-center justify-between gap-3">

        <div class="flex items-center gap-2">

            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/[0.05] text-muted">
                <i class="fa-solid fa-list"></i>
            </span>

            <div>

                <h2 class="text-sm font-bold text-fg">
                    مركز الإشعارات
                </h2>

                <p class="text-[11px] text-dim">
                    أحدث التنبيهات والأحداث المسجلة
                </p>

            </div>

        </div>


        <div class="flex items-center gap-2">

            <span
                id="visibleNotificationCount"
                class="badge badge-neutral"
            >
                {{ $items->count() }} إشعار
            </span>

        </div>

    </section>


    {{-- =========================================================
        NOTIFICATIONS
    ========================================================== --}}

    <div
        id="notificationList"
        class="space-y-3"
    >

        @forelse($items as $item)

            @php
                $notificationType = $types[$item->type] ?? [
                    'icon' => 'fa-bell',
                    'classes' => 'bg-white/10 text-muted',
                    'label' => 'عام',
                ];

                $isUnread = ! $item->read;
            @endphp


            <form
                method="POST"
                action="{{ route('notifications.read', $item->id) }}"
                class="notification-item"
                data-title="{{ strtolower($item->title) }}"
                data-body="{{ strtolower($item->body) }}"
                data-type="{{ $item->type }}"
                data-read="{{ $item->read ? 'read' : 'unread' }}"
            >

                @csrf


                <button
                    type="submit"
                    @class([
                        'card flex w-full items-start gap-4 text-right transition',
                        'border-brand/25 bg-brand/[0.025]' => $isUnread,
                        'hover:bg-white/[0.04]' => true,
                    ])
                >

                    {{-- =================================================
                        ICON
                    ================================================== --}}

                    <span
                        class="relative flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $notificationType['classes'] }}"
                    >

                        <i class="fa-solid {{ $notificationType['icon'] }}"></i>


                        @if($isUnread)

                            <span class="absolute -right-1 -top-1 h-2.5 w-2.5 rounded-full bg-brand shadow-[0_0_8px_var(--color-brand)]"></span>

                        @endif

                    </span>


                    {{-- =================================================
                        CONTENT
                    ================================================== --}}

                    <span class="min-w-0 flex-1">

                        <span class="flex flex-wrap items-center gap-2">

                            <span class="text-sm font-bold text-fg">
                                {{ $item->title }}
                            </span>


                            <span class="badge {{ $notificationType['classes'] }}">

                                {{ $notificationType['label'] }}

                            </span>


                            @if($isUnread)

                                <span class="badge badge-success">
                                    جديد
                                </span>

                            @endif

                        </span>


                        <span class="mt-2 block text-xs leading-6 text-muted">
                            {{ $item->body }}
                        </span>


                        {{-- Notification metadata --}}
                        <span class="mt-3 flex flex-wrap items-center gap-x-4 gap-y-2 text-[11px] text-dim">

                            <span class="inline-flex items-center gap-1.5">

                                <i class="fa-regular fa-clock"></i>

                                {{ $item->time }}

                            </span>


                            @if($isUnread)

                                <span class="inline-flex items-center gap-1.5 text-brand">

                                    <i class="fa-solid fa-circle text-[6px]"></i>

                                    تحتاج إلى المراجعة

                                </span>

                            @else

                                <span class="inline-flex items-center gap-1.5">

                                    <i class="fa-solid fa-check"></i>

                                    تمت القراءة

                                </span>

                            @endif

                        </span>

                    </span>


                    {{-- =================================================
                        RIGHT SIDE
                    ================================================== --}}

                    <span class="hidden shrink-0 flex-col items-end gap-3 sm:flex">

                        <span class="text-[11px] text-dim">
                            {{ $item->time }}
                        </span>


                        @if($isUnread)

                            <span
                                class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand/10 text-brand"
                                title="تحديد كمقروء"
                            >

                                <i class="fa-solid fa-check text-[10px]"></i>

                            </span>

                        @else

                            <span
                                class="flex h-7 w-7 items-center justify-center rounded-lg bg-white/[0.04] text-dim"
                                title="تمت القراءة"
                            >

                                <i class="fa-solid fa-check-double text-[10px]"></i>

                            </span>

                        @endif


                        <i class="fa-solid fa-chevron-left text-[9px] text-dim"></i>

                    </span>

                </button>

            </form>

        @empty

            {{-- =================================================
                EMPTY STATE
            ================================================== --}}

            <div
                id="emptyNotifications"
                class="card"
            >

                <x-empty-state
                    icon="fa-bell-slash"
                    text="لا توجد إشعارات"
                />

            </div>

        @endforelse


        {{-- Search empty state --}}
        <div
            id="searchEmptyState"
            class="card hidden"
        >

            <x-empty-state
                icon="fa-magnifying-glass"
                text="لم يتم العثور على إشعارات مطابقة للبحث"
            />

        </div>

    </div>


    {{-- =========================================================
        PAGINATION
    ========================================================== --}}

    @if(method_exists($items, 'links'))

        <div class="mt-6">

            {{ $items->links() }}

        </div>

    @endif


    {{-- =========================================================
        FOOTER SUMMARY
    ========================================================== --}}

    @if($items->count() > 0)

        <section class="mt-6">

            <div class="card">

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">

                    <div class="flex items-center gap-3">

                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-info/15 text-info">
                            <i class="fa-solid fa-circle-info text-sm"></i>
                        </span>

                        <div>

                            <div class="text-xs font-semibold text-fg">
                                حالة مركز الإشعارات
                            </div>

                            <div class="mt-0.5 text-[11px] text-dim">
                                لديك {{ number_format($unread) }} إشعار غير مقروء
                            </div>

                        </div>

                    </div>


                    <div class="flex flex-wrap items-center gap-3 text-[11px] text-dim">

                        <span class="inline-flex items-center gap-1.5">

                            <span class="h-2 w-2 rounded-full bg-brand"></span>

                            غير مقروء

                        </span>


                        <span class="inline-flex items-center gap-1.5">

                            <span class="h-2 w-2 rounded-full bg-white/20"></span>

                            مقروء

                        </span>

                    </div>

                </div>

            </div>

        </section>

    @endif

@endsection


{{-- =========================================================
    PAGE JAVASCRIPT
========================================================== --}}

@push('scripts')

<script>

    document.addEventListener('DOMContentLoaded', function () {

        /*
        |--------------------------------------------------------------------------
        | Notification Search
        |--------------------------------------------------------------------------
        */

        const searchInput = document.getElementById('notificationSearch');
        const notificationList = document.getElementById('notificationList');
        const searchEmptyState = document.getElementById('searchEmptyState');
        const visibleCount = document.getElementById('visibleNotificationCount');

        if (!searchInput || !notificationList) {
            return;
        }


        const notificationItems = Array.from(
            notificationList.querySelectorAll('.notification-item')
        );


        function updateNotificationSearch() {

            const query = searchInput.value
                .trim()
                .toLowerCase();


            let visible = 0;


            notificationItems.forEach(function (item) {

                const title = item.dataset.title || '';
                const body = item.dataset.body || '';
                const type = item.dataset.type || '';

                const searchableText =
                    `${title} ${body} ${type}`;


                const matches =
                    query === '' ||
                    searchableText.includes(query);


                item.classList.toggle('hidden', !matches);


                if (matches) {
                    visible++;
                }

            });


            /*
            |--------------------------------------------------------------------------
            | Counter
            |--------------------------------------------------------------------------
            */

            if (visibleCount) {

                visibleCount.textContent =
                    `${visible} إشعار`;

            }


            /*
            |--------------------------------------------------------------------------
            | Search empty state
            |--------------------------------------------------------------------------
            */

            if (searchEmptyState) {

                const showEmpty =
                    query !== '' &&
                    visible === 0;

                searchEmptyState.classList.toggle(
                    'hidden',
                    !showEmpty
                );

            }

        }


        searchInput.addEventListener(
            'input',
            updateNotificationSearch
        );


        /*
        |--------------------------------------------------------------------------
        | Escape clears search
        |--------------------------------------------------------------------------
        */

        searchInput.addEventListener(
            'keydown',
            function (event) {

                if (event.key === 'Escape') {

                    searchInput.value = '';

                    updateNotificationSearch();

                    searchInput.blur();

                }

            }
        );

    });

</script>

@endpush