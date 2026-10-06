{{-- Employee identity + filters --}}

<div class="card mb-6">

    <div class="flex flex-wrap items-center justify-between gap-5">

        {{-- Employee identity --}}
        <div class="flex min-w-0 items-center gap-4">

            <x-avatar
                :name="$report['employee']['name']"
                color="brand"
                size="h-14 w-14 text-sm"
            />

            <div class="min-w-0">

                <div class="flex flex-wrap items-center gap-2">

                    <p class="text-base font-bold text-fg">
                        {{ $report['employee']['name'] }}
                    </p>

                    <span class="badge badge-outline-info">
                        {{ $report['employee']['code'] }}
                    </span>

                    <button
                        type="button"
                        class="text-dim transition hover:text-info"
                        title="نسخ كود الموظف"
                        onclick="copyEmployeeCode()"
                        aria-label="نسخ كود الموظف"
                    >
                        <i class="fa-regular fa-copy text-xs"></i>
                    </button>

                </div>

                <div class="mt-1.5 flex flex-wrap items-center gap-3 text-xs text-muted">

                    <span class="flex items-center gap-1.5">
                        <i class="fa-solid fa-user-shield text-info"></i>
                        {{ $report['employee']['role'] }}
                    </span>

                    @if($report['employee']['supervisor'])

                        <span class="text-dim">•</span>

                        <span class="flex items-center gap-1.5">
                            <i class="fa-solid fa-user-tie text-warning"></i>
                            المشرف: {{ $report['employee']['supervisor'] }}
                        </span>

                    @endif

                </div>

            </div>

        </div>


        {{-- Current period --}}
        <div class="flex flex-wrap items-center gap-3">

            @if($report['filters']['month_name'])

                <span class="badge badge-outline-info">
                    <i class="fa-regular fa-calendar ml-1"></i>
                    {{ $report['filters']['month_name'] }}
                </span>

            @endif

            @if($report['filters']['year'])

                <span class="badge badge-outline-info">
                    {{ $report['filters']['year'] }}
                </span>

            @endif

            <span class="badge badge-outline-{{ $report['performance']['performance_tone'] }}">

                <i class="fa-solid {{ $report['performance']['performance_icon'] }} ml-1"></i>

                {{ $report['performance']['performance_label'] }}

            </span>

        </div>

    </div>


    {{-- Filter --}}
    <div class="mt-5 border-t border-white/[0.06] pt-5">

        <div class="flex flex-wrap items-end justify-between gap-4">

            <div>

                <p class="text-xs font-bold text-fg">
                    <i class="fa-solid fa-sliders ml-1 text-info"></i>
                    فترة التحليل
                </p>

                <p class="mt-1 text-[11px] text-muted">
                    اختر الشهر والسنة لتحديث مؤشرات الأداء والرسوم البيانية.
                </p>

            </div>


            <form
                id="performanceFilter"
                method="GET"
                action="{{ route('employees.show', ['code' => $user->employee_code]) }}"
                class="flex flex-wrap items-end gap-3"
            >

                <x-select-field
                    name="month"
                    label="الشهر"
                    :options="$months"
                    :value="$filters['month']"
                    onchange="this.form.submit()"
                />

                <x-select-field
                    name="year"
                    label="السنة"
                    :options="$years"
                    :value="$filters['year']"
                    onchange="this.form.submit()"
                />

                <button
                    type="submit"
                    class="btn btn-primary btn-sm"
                    id="applyFilterBtn"
                >
                    <i class="fa-solid fa-filter text-xs"></i>
                    تطبيق
                </button>

                <a
                    href="{{ route('employees.show', ['code' => $user->employee_code]) }}"
                    class="btn btn-secondary btn-sm"
                    title="إزالة الفلاتر"
                >
                    <i class="fa-solid fa-rotate-left text-xs"></i>
                    إعادة
                </a>

            </form>

        </div>

    </div>

</div>
