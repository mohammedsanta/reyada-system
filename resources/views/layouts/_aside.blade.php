{{-- =========================================================
    MODERN DARK SIDEBAR
========================================================= --}}
<aside
    class="fixed right-0 top-0 z-50 flex h-screen w-64 flex-col
           border-l border-white/5 bg-[#0b0f12]
           text-white shadow-2xl shadow-black/40"
    dir="rtl"
>

    {{-- HEADER --}}
    <div class="flex-shrink-0 border-b border-white/5 px-5 py-5">
        <div class="flex items-center gap-3">
            <div class="flex h-11 w-11 items-center justify-center rounded-xl
                        bg-gradient-to-br from-[#00ff66] to-[#00b84d]
                        text-black shadow-lg shadow-[#00ff66]/10">
                <i class="fa-solid fa-chart-line text-lg"></i>
            </div>

            <div>
                <h1 class="text-base font-bold tracking-wide text-white">Reyada</h1>
                <p class="mt-0.5 text-[10px] text-[#64707c]">نظام إدارة التحصيل</p>
            </div>
        </div>
    </div>


    {{-- SCROLLABLE MENU --}}
    <div
        class="flex-1 overflow-y-auto px-3 py-5"
        style="scrollbar-width: thin; scrollbar-color: #263038 transparent;"
    >

        {{-- MAIN --}}
        <div class="mb-6">
            <div class="mb-3 flex items-center gap-2 px-3">
                <span class="h-1 w-1 rounded-full bg-[#00ff66]"></span>
                <span class="text-[10px] font-bold uppercase tracking-widest text-[#52606d]">الرئيسية</span>
            </div>

            <x-sidebar-link route="dashboard" pattern="dashboard" icon="fa-house">
                الرئيسية
            </x-sidebar-link>

            <x-sidebar-link route="notifications.index" icon="fa-bell" color="yellow" :badge="3">
                الإشعارات
            </x-sidebar-link>

            <x-sidebar-link route="users.index" icon="fa-users">
                إدارة المستخدمين
            </x-sidebar-link>

            <x-sidebar-link route="overview.index" icon="fa-chart-pie">
                نظرة عامة
            </x-sidebar-link>

            <x-sidebar-link route="employees.index" icon="fa-user-check">
                أداء الموظفين
            </x-sidebar-link>
        </div>


        {{-- OPERATIONS --}}
        <div class="mb-6">
            <div class="mb-3 flex items-center gap-2 px-3">
                <span class="h-1 w-1 rounded-full bg-[#00aaff]"></span>
                <span class="text-[10px] font-bold uppercase tracking-widest text-[#52606d]">مركز العمليات</span>
            </div>

            <x-sidebar-link route="operations.index" icon="fa-briefcase" color="blue">
                العمليات
            </x-sidebar-link>

            <x-sidebar-link route="banks.index" icon="fa-building-columns" color="green">
                البنوك
            </x-sidebar-link>

            <x-sidebar-link route="installment-companies.index" icon="fa-building" color="blue">
                شركات التقسيط
            </x-sidebar-link>

            <x-sidebar-link route="confirmations.index" icon="fa-circle-check" color="green">
                تأكيد التسهيلات
            </x-sidebar-link>

            <x-sidebar-link route="archives.index" icon="fa-box-archive" color="red">
                المستودعات الشهرية
            </x-sidebar-link>
        </div>


        {{-- MANAGEMENT --}}
        <div>
            <div class="mb-3 flex items-center gap-2 px-3">
                <span class="h-1 w-1 rounded-full bg-[#a855f7]"></span>
                <span class="text-[10px] font-bold uppercase tracking-widest text-[#52606d]">الإدارة</span>
            </div>

            <x-sidebar-link route="reports.index" icon="fa-file-lines" color="purple">
                التقارير
            </x-sidebar-link>

            <x-sidebar-link route="activity-logs.index" icon="fa-clock-rotate-left" color="orange">
                سجل النشاط
            </x-sidebar-link>
        </div>

    </div>


    {{-- USER FOOTER --}}
    <div class="flex-shrink-0 border-t border-white/5 p-3">
        <div class="flex items-center gap-3 rounded-xl border border-white/5 bg-white/[0.025] p-3">
            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg
                        bg-[#00ff66]/10 text-xs font-bold text-[#00ff66]">
                A
            </div>

            <div class="min-w-0 flex-1">
                <p class="truncate text-xs font-semibold text-white">المدير</p>
                <p class="truncate text-[10px] text-[#5f6b77]">Administrator</p>
            </div>

            <button type="button" class="text-[#65717d] transition hover:text-white">
                <i class="fa-solid fa-ellipsis-vertical"></i>
            </button>
        </div>
    </div>

</aside>