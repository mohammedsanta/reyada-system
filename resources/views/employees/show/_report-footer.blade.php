<div class="mt-6 flex flex-wrap items-center justify-between gap-3 text-[11px] text-dim">

    <div class="flex flex-wrap items-center gap-3">

        <span>
            <i class="fa-solid fa-chart-line ml-1 text-info"></i>
            تقرير أداء الموظف
        </span>

        <span>•</span>

        <span>
            {{ $report['employee']['code'] }}
        </span>

        @if(
            $report['filters']['month_name'] &&
            $report['filters']['year']
        )

            <span>•</span>

            <span>
                {{ $report['filters']['month_name'] }}
                {{ $report['filters']['year'] }}
            </span>

        @endif

    </div>


    <button
        type="button"
        onclick="window.scrollTo({ top: 0, behavior: 'smooth' })"
        class="transition hover:text-fg"
        title="العودة للأعلى"
    >
        <i class="fa-solid fa-arrow-up ml-1"></i>
        للأعلى
    </button>

</div>