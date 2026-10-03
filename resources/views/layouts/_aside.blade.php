{{-- =========================================================
    resources/views/layout/_aside.blade.php
    SIDEBAR with ALL project pages.
    - To add / move / rename a page: edit ONLY the $menu array below.
    - A page whose route does not exist yet is shown anyway (link "#") and works by itself as soon as its route is added.
    - An item with "children" becomes a tree: click the title to open it; every child has a dot on its right.
========================================================= --}}
@php
    // ---- counters shown as red badges. TODO (DB) ----
    $unread  = 3;   // auth()->user()->unreadNotifications()->count()
    $pending = 3;   // Payment::where('status', 'pending')->count()

    // ---- small helpers ----
    $routeExists = fn (?string $name) => $name && \Illuminate\Support\Facades\Route::has($name);
    $url         = fn (?string $name) => $routeExists($name) ? route($name) : '#';
    $isActive    = fn ($patterns) => $patterns && request()->routeIs(...(array) $patterns);

    // ---- THE MENU ----
    // single item : label, icon, color, route, pattern (optional), badge (optional)
    // tree item   : label, icon, color, children[] -> each child: label, route, pattern (optional, default = the route itself)
    $menu = [

        [
            'title' => 'الرئيسية', 'dot' => 'bg-[#00ff66]',
            'items' => [
                ['label' => 'الرئيسية',  'icon' => 'fa-house', 'color' => 'green', 'route' => 'dashboard', 'pattern' => 'dashboard'],
                ['label' => 'الإشعارات', 'icon' => 'fa-bell',  'color' => 'yellow', 'route' => 'notifications.index', 'badge' => $unread ?: null],

                ['label' => 'إدارة المستخدمين', 'icon' => 'fa-users', 'color' => 'green', 'children' => [
                    ['label' => 'قائمة المستخدمين', 'route' => 'users.index', 'pattern' => ['users.index', 'users.show', 'users.edit', 'users.permissions.*', 'users.assignments.*', 'users.team']],
                    ['label' => 'إضافة موظف جديد',  'route' => 'users.create'],
                    ['label' => 'الرتب النظامية',   'route' => 'roles.index', 'pattern' => ['roles.index', 'roles.edit']],
                    ['label' => 'إضافة رتبة',       'route' => 'roles.create'],
                ]],

                ['label' => 'نظرة عامة',     'icon' => 'fa-chart-pie',  'color' => 'green', 'route' => 'overview.index'],
                ['label' => 'أداء الموظفين', 'icon' => 'fa-user-check', 'color' => 'green', 'route' => 'employees.index'],
            ],
        ],

        [
            'title' => 'مركز العمليات', 'dot' => 'bg-[#00aaff]',
            'items' => [
                ['label' => 'العمليات', 'icon' => 'fa-briefcase',    'color' => 'blue', 'route' => 'operations.index'],
                ['label' => 'العملاء',  'icon' => 'fa-address-book', 'color' => 'blue', 'route' => 'clients.index'],

                ['label' => 'البنوك', 'icon' => 'fa-building-columns', 'color' => 'green', 'children' => [
                    // "كل البنوك" stays active on every page that belongs to a bank (panel, clients, PTP, DCR, complaints...)
                    ['label' => 'كل البنوك', 'route' => 'banks.index', 'pattern' => [
                        'banks.index', 'banks.edit', 'banks.show', 'banks.panel', 'banks.clients.*', 'banks.scope.*',
                        'banks.distribution.*', 'banks.dcr.*', 'banks.ptp.*', 'banks.complaints.*', 'banks.visits.*', 'banks.archives.*',
                    ]],
                    ['label' => 'إضافة بنك', 'route' => 'banks.create'],
                ]],

                ['label' => 'شركات التقسيط', 'icon' => 'fa-building', 'color' => 'blue', 'children' => [
                    ['label' => 'كل الشركات',  'route' => 'installment-companies.index', 'pattern' => ['installment-companies.index', 'installment-companies.edit', 'installment-companies.panel']],
                    ['label' => 'إضافة شركة',  'route' => 'installment-companies.create'],
                ]],

                ['label' => 'تأكيد التحصيلات',   'icon' => 'fa-circle-check', 'color' => 'green', 'route' => 'confirmations.index', 'badge' => $pending ?: null],
                ['label' => 'المستودعات الشهرية', 'icon' => 'fa-box-archive',  'color' => 'red',   'route' => 'archives.index'],
            ],
        ],

        [
            'title' => 'الإدارة', 'dot' => 'bg-[#a855f7]',
            'items' => [
                ['label' => 'التقارير', 'icon' => 'fa-file-lines', 'color' => 'purple', 'children' => [
                    ['label' => 'إنشاء تقرير', 'route' => 'reports.index'],
                    ['label' => 'سجل التصدير', 'route' => 'reports.exports'],
                ]],

                ['label' => 'سجل النشاط', 'icon' => 'fa-clock-rotate-left', 'color' => 'orange', 'route' => 'activity-logs.index'],

                ['label' => 'الإعدادات', 'icon' => 'fa-gear', 'color' => 'purple', 'children' => [
                    ['label' => 'أنواع القروض',   'route' => 'loan-types.index'],
                    ['label' => 'المحافظات',      'route' => 'governorates.index'],
                    ['label' => 'إعدادات النظام', 'route' => 'settings.index'],
                ]],
            ],
        ],
    ];

    // ---- colors of a tree title (full class names so Tailwind can detect them) ----
    $palette = [
        'green'  => ['text' => 'text-[#00ff66]',    'iconBg' => 'bg-[#00ff66]/10',    'hover' => 'group-hover:bg-[#00ff66]/10 group-hover:text-[#00ff66]',       'border' => 'border-[#00ff66]/10',    'from' => 'from-[#00ff66]/10',    'dot' => 'bg-[#00ff66] shadow-[0_0_8px_#00ff66]', 'idle' => 'text-[#687582]'],
        'blue'   => ['text' => 'text-[#00aaff]',    'iconBg' => 'bg-[#00aaff]/10',    'hover' => 'group-hover:bg-[#00aaff]/10 group-hover:text-[#00aaff]',       'border' => 'border-[#00aaff]/10',    'from' => 'from-[#00aaff]/10',    'dot' => 'bg-[#00aaff] shadow-[0_0_8px_#00aaff]', 'idle' => 'text-[#687582]'],
        'purple' => ['text' => 'text-[#a855f7]',    'iconBg' => 'bg-[#a855f7]/10',    'hover' => 'group-hover:bg-[#a855f7]/10 group-hover:text-[#a855f7]',       'border' => 'border-[#a855f7]/10',    'from' => 'from-[#a855f7]/10',    'dot' => 'bg-[#a855f7] shadow-[0_0_8px_#a855f7]', 'idle' => 'text-[#687582]'],
        'orange' => ['text' => 'text-orange-400',   'iconBg' => 'bg-orange-400/10',   'hover' => 'group-hover:bg-orange-400/10 group-hover:text-orange-400',     'border' => 'border-orange-400/10',   'from' => 'from-orange-400/10',   'dot' => 'bg-orange-400 shadow-[0_0_8px_#fb923c]', 'idle' => 'text-[#687582]'],
        'yellow' => ['text' => 'text-yellow-400',   'iconBg' => 'bg-yellow-400/10',   'hover' => 'group-hover:bg-yellow-400/10',                                  'border' => 'border-yellow-400/10',   'from' => 'from-yellow-400/10',   'dot' => 'bg-yellow-400 shadow-[0_0_8px_#facc15]', 'idle' => 'text-yellow-400/80'],
        'red'    => ['text' => 'text-red-400',      'iconBg' => 'bg-red-400/10',      'hover' => 'group-hover:bg-red-400/10',                                     'border' => 'border-red-400/10',      'from' => 'from-red-400/10',      'dot' => 'bg-red-400 shadow-[0_0_8px_#f87171]', 'idle' => 'text-red-400/80'],
    ];

    // ---- the logged in user (footer) ----
    $authUser  = auth()->user();
    $userName  = $authUser?->name ?? 'المدير';
    $userRole  = data_get($authUser, 'role.label', 'Administrator');
@endphp

<aside
    class="fixed right-0 top-0 z-50 flex h-screen w-64 flex-col border-l border-white/5 bg-[#0b0f12] text-white shadow-2xl shadow-black/40"
    dir="rtl"
>

    {{-- =====================================================
        HEADER
    ====================================================== --}}
    <div class="flex-shrink-0 border-b border-white/5 px-5 py-5">
        <a href="{{ $url('dashboard') }}" class="flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-gradient-to-br from-[#00ff66] to-[#00b84d] text-black shadow-lg shadow-[#00ff66]/10">
                <i class="fa-solid fa-chart-line text-lg"></i>
            </div>

            <div>
                <h1 class="text-base font-bold tracking-wide text-white">كولكتس</h1>
                <p class="mt-0.5 text-[10px] text-[#64707c]">نظام إدارة التحصيل</p>
            </div>
        </a>
    </div>


    {{-- =====================================================
        MENU (scrollable)
    ====================================================== --}}
    <nav class="flex-1 overflow-y-auto px-3 py-5" style="scrollbar-width: thin; scrollbar-color: #263038 transparent;">

        @foreach($menu as $section)
            <div @class(['mb-6' => ! $loop->last])>

                {{-- section title --}}
                <div class="mb-3 flex items-center gap-2 px-3">
                    <span class="h-1 w-1 rounded-full {{ $section['dot'] }}"></span>
                    <span class="text-[10px] font-bold uppercase tracking-widest text-[#52606d]">{{ $section['title'] }}</span>
                </div>

                @foreach($section['items'] as $item)

                    @if(isset($item['children']))

                        {{-- ================= TREE (opens on click) ================= --}}
                        @php
                            $c            = $palette[$item['color']];
                            $groupActive  = collect($item['children'])->contains(fn ($child) => $isActive($child['pattern'] ?? $child['route']));
                        @endphp

                        <details class="group/tree mb-1" @if($groupActive) open @endif>

                            <summary
                                @class([
                                    'group relative flex cursor-pointer list-none items-center gap-3 overflow-hidden rounded-xl px-3 py-2.5 text-sm [&::-webkit-details-marker]:hidden',
                                    "border bg-gradient-to-r to-transparent {$c['border']} {$c['from']} {$c['text']}" => $groupActive,
                                    'text-[#8c98a5] transition-all duration-200 hover:bg-white/[0.04] hover:text-white' => ! $groupActive,
                                ])
                            >
                                <span
                                    @class([
                                        'flex h-8 w-8 items-center justify-center rounded-lg',
                                        "{$c['iconBg']} {$c['text']}" => $groupActive,
                                        "bg-white/[0.03] transition {$c['idle']} {$c['hover']}" => ! $groupActive,
                                    ])
                                >
                                    <i class="fa-solid {{ $item['icon'] }} text-xs"></i>
                                </span>

                                <span @class(['flex-1', 'font-semibold' => $groupActive])>{{ $item['label'] }}</span>

                                {{-- arrow: points left when closed, down when open --}}
                                <i class="fa-solid fa-chevron-left text-[9px] opacity-60 transition-transform duration-200 group-open/tree:-rotate-90"></i>
                            </summary>

                            {{-- children: a vertical tree line on the right, one dot next to each title --}}
                            <ul class="relative mr-[1.75rem] mt-1 mb-2 space-y-0.5 border-r border-white/10">
                                @foreach($item['children'] as $child)
                                    @php $childActive = $isActive($child['pattern'] ?? $child['route']); @endphp

                                    <li>
                                        <a
                                            href="{{ $url($child['route']) }}"
                                            @if($childActive) aria-current="page" @endif
                                            @class([
                                                'relative flex items-center rounded-lg py-1.5 pr-5 pl-2 text-[13px] transition-colors duration-200',
                                                'font-semibold text-white' => $childActive,
                                                'text-[#8c98a5] hover:bg-white/[0.04] hover:text-white' => ! $childActive,
                                            ])
                                        >
                                            {{-- the dot sits on the tree line, on the right of the title --}}
                                            <span
                                                @class([
                                                    'absolute right-[-3.5px] top-1/2 h-[7px] w-[7px] -translate-y-1/2 rounded-full',
                                                    $c['dot'] => $childActive,
                                                    'bg-[#33414d]' => ! $childActive,
                                                ])
                                            ></span>

                                            {{ $child['label'] }}

                                            @unless($routeExists($child['route']))
                                                <span class="mr-auto rounded bg-white/[0.06] px-1.5 py-0.5 text-[9px] text-[#5f6b77]">قريباً</span>
                                            @endunless
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </details>

                    @else

                        {{-- ================= SINGLE PAGE ================= --}}
                        <x-sidebar-link
                            :route="$item['route']"
                            :pattern="$item['pattern'] ?? null"
                            :icon="$item['icon']"
                            :color="$item['color']"
                            :badge="$item['badge'] ?? null"
                        >
                            {{ $item['label'] }}
                        </x-sidebar-link>

                    @endif

                @endforeach
            </div>
        @endforeach

    </nav>


    {{-- =====================================================
        USER FOOTER (+ account menu)
    ====================================================== --}}
    <div class="flex-shrink-0 border-t border-white/5 p-3">
        <div class="flex items-center gap-3 rounded-xl border border-white/5 bg-white/[0.025] p-3">

            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-[#00ff66]/10 text-xs font-bold text-[#00ff66]">
                {{ mb_strtoupper(mb_substr($userName, 0, 1)) }}
            </div>

            <div class="min-w-0 flex-1">
                <p class="truncate text-xs font-semibold text-white">{{ $userName }}</p>
                <p class="truncate text-[10px] text-[#5f6b77]">{{ $userRole }}</p>
            </div>

            {{-- account menu (opens upwards; closes when you click outside: see resources/js/app.js) --}}
            <details class="relative" data-menu>
                <summary class="flex h-8 w-8 cursor-pointer list-none items-center justify-center rounded-lg text-[#65717d] transition hover:text-white [&::-webkit-details-marker]:hidden">
                    <i class="fa-solid fa-ellipsis-vertical"></i>
                </summary>

                <div class="absolute bottom-full left-0 z-50 mb-2 w-52 rounded-xl border border-white/10 bg-[#11161a] p-1 shadow-xl shadow-black/50">
                    <a href="{{ $url('account.edit') }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs text-[#8c98a5] transition hover:bg-white/[0.06] hover:text-white">
                        <i class="fa-solid fa-user w-4 text-[#00aaff]"></i> حسابي
                    </a>

                    <a href="{{ $routeExists('account.edit') ? route('account.edit') . '#password' : '#' }}" class="flex items-center gap-2 rounded-lg px-3 py-2 text-xs text-[#8c98a5] transition hover:bg-white/[0.06] hover:text-white">
                        <i class="fa-solid fa-key w-4 text-yellow-400"></i> تغيير كلمة المرور
                    </a>

                    <form method="POST" action="{{ $routeExists('logout') ? route('logout') : '#' }}">
                        @csrf
                        <button type="submit" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-xs text-red-400 transition hover:bg-red-400/10">
                            <i class="fa-solid fa-right-from-bracket w-4"></i> تسجيل الخروج
                        </button>
                    </form>
                </div>
            </details>

        </div>
    </div>

</aside>