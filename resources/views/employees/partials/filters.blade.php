{{-- resources/views/employees/partials/filters.blade.php --}}

@php
    $selectedInstitution = $filterState['institution'];
    $selectedSearch = $filterState['search'];
    $selectedMonth = $filterState['month'];
    $selectedYear = $filterState['year'];

    $monthName = $filterState['monthName'];
    $hasFilters = $filterState['hasFilters'];
@endphp
<form
    method="GET"
    action="{{ route('employees.index') }}"
    class="card mb-8"
    id="employeeFilters"
>
    <div class="mb-5 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-info/15 text-info">
                <i class="fa-solid fa-filter"></i>
            </span>

            <div>
                <h2 class="text-sm font-bold text-fg">فلاتر الأداء المتقدمة</h2>
                <p class="mt-0.5 text-[11px] text-muted">
                    تحليل الأداء الشهري ومقارنة الاتجاهات
                </p>
            </div>
        </div>

        @if($hasFilters)
            <span class="hidden rounded-full border border-info/20 bg-info/5 px-3 py-1 text-[10px] text-info sm:inline-flex">
                <i class="fa-solid fa-filter ml-1"></i>
                يوجد فلتر مفعل
            </span>
        @endif
    </div>

    <div class="flex flex-wrap items-end gap-4">

        {{-- Institution --}}
        <div>
            <span class="form-label">
                <i class="fa-solid fa-building-columns ml-1 text-info"></i>
                المؤسسة
            </span>

            <div class="flex flex-wrap gap-2">
                <label>
                    <input
                        type="radio"
                        name="institution"
                        value=""
                        class="pill-radio sr-only"
                        @checked($selectedInstitution === '')
                        onchange="this.form.submit()"
                    >
                    <span class="pill">الكل</span>
                </label>

                @foreach(($institutions ?? []) as $institution)
                    <label>
                        <input
                            type="radio"
                            name="institution"
                            value="{{ $institution }}"
                            class="pill-radio sr-only"
                            @checked($selectedInstitution === $institution)
                            onchange="this.form.submit()"
                        >
                        <span class="pill">{{ $institution }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Search --}}
        <div class="min-w-[220px] flex-1">
            <label for="search" class="sr-only">بحث</label>

            <div class="relative">
                <i class="fa-solid fa-magnifying-glass pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-xs text-dim"></i>

                <input
                    id="search"
                    name="search"
                    type="search"
                    value="{{ $selectedSearch }}"
                    placeholder="ابحث بالاسم أو كود الموظف..."
                    class="form-input pl-9"
                    autocomplete="off"
                >

                <span class="pointer-events-none absolute right-3 top-1/2 hidden -translate-y-1/2 rounded border border-border bg-white/5 px-1.5 py-0.5 text-[9px] text-muted sm:block">
                    Ctrl K
                </span>
            </div>
        </div>

        {{-- Month --}}
        <div class="w-36">
            <label for="month" class="form-label">الشهر</label>

            <select id="month" name="month" class="form-input">
                @foreach(($months ?? []) as $number => $name)
                    <option
                        value="{{ $number }}"
                        @selected((string) $selectedMonth === (string) $number)
                    >
                        {{ $name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Year --}}
        <div class="w-28">
            <label for="year" class="form-label">السنة</label>

            <select id="year" name="year" class="form-input">
                @foreach(($years ?? []) as $year)
                    <option
                        value="{{ $year }}"
                        @selected((string) $selectedYear === (string) $year)
                    >
                        {{ $year }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Apply --}}
        <button type="submit" class="btn btn-primary" id="applyFilters">
            <i class="fa-solid fa-magnifying-glass text-xs"></i>
            تطبيق
        </button>

        {{-- Reset --}}
        @if($hasFilters)
            <a
                href="{{ route('employees.index') }}"
                class="btn btn-secondary"
                title="إزالة كل الفلاتر"
            >
                <i class="fa-solid fa-filter-circle-xmark"></i>
                <span class="hidden sm:inline">تصفير</span>
            </a>
        @endif

    </div>

    {{-- Active filters --}}
    @if($hasFilters)
        <div class="mt-5 flex flex-wrap items-center gap-2 border-t border-border pt-4">
            <span class="text-[11px] text-muted">الفلاتر الحالية:</span>

            @if($selectedInstitution !== '')
                <span class="inline-flex items-center gap-1.5 rounded-full bg-info/10 px-2.5 py-1 text-[10px] text-info">
                    <i class="fa-solid fa-building-columns"></i>
                    {{ $selectedInstitution }}
                </span>
            @endif

            @if($selectedSearch !== '')
                <span class="inline-flex max-w-[220px] items-center gap-1.5 rounded-full bg-accent/10 px-2.5 py-1 text-[10px] text-accent">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <span class="truncate">{{ $selectedSearch }}</span>
                </span>
            @endif

            @if($monthName)
                <span class="inline-flex items-center gap-1.5 rounded-full bg-warning/10 px-2.5 py-1 text-[10px] text-warning">
                    <i class="fa-regular fa-calendar"></i>
                    {{ $monthName }}
                </span>
            @endif

            @if($selectedYear !== '')
                <span class="inline-flex items-center gap-1.5 rounded-full bg-brand/10 px-2.5 py-1 text-[10px] text-brand">
                    <i class="fa-regular fa-calendar-days"></i>
                    {{ $selectedYear }}
                </span>
            @endif
        </div>
    @endif
</form>