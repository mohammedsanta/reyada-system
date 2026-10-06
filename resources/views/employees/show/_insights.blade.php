<section class="card mb-6">

    <div class="mb-5 flex items-center gap-3">

        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-info/15 text-info">
            <i class="fa-solid fa-lightbulb"></i>
        </span>

        <div>

            <h2 class="text-sm font-bold text-fg">
                مؤشرات سريعة
            </h2>

            <p class="mt-0.5 text-[11px] text-muted">
                قراءة مختصرة للأرقام المتاحة في التقرير الحالي.
            </p>

        </div>

    </div>


    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

        {{-- Latest achievement --}}
        <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-4">

            <div class="flex items-center justify-between">

                <span class="text-xs text-muted">
                    آخر تحقيق للمستهدف
                </span>

                <i class="fa-solid fa-bullseye text-info"></i>

            </div>

            @php
                $latestAchievement = $report['trend']['latest_achievement'];
            @endphp

            <div class="mt-2 text-xl font-bold
                {{ $latestAchievement >= 100
                    ? 'text-brand'
                    : ($latestAchievement >= 80 ? 'text-warning' : 'text-danger') }}">
                {{ $latestAchievement }}%
            </div>

            <p class="mt-1 text-[11px] text-dim">

                {{ number_format($report['trend']['latest_collected']) }}

                من

                {{ number_format($report['trend']['latest_target']) }}

                EGP

            </p>

        </div>


        {{-- Average monthly collection --}}
        <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-4">

            <div class="flex items-center justify-between">

                <span class="text-xs text-muted">
                    متوسط التحصيل الشهري
                </span>

                <i class="fa-solid fa-chart-column text-brand"></i>

            </div>

            <div class="mt-2 text-xl font-bold text-brand">

                {{ number_format($report['trend']['average_monthly_collection']) }}

            </div>

            <p class="mt-1 text-[11px] text-dim">
                EGP / شهر
            </p>

        </div>


        {{-- Active promises --}}
        <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-4">

            <div class="flex items-center justify-between">

                <span class="text-xs text-muted">
                    الوعود النشطة
                </span>

                <i class="fa-solid fa-clock text-warning"></i>

            </div>

            <div class="mt-2 text-xl font-bold text-warning">

                {{ number_format($report['promises']['active']) }}

            </div>

            <p class="mt-1 text-[11px] text-dim">

                من إجمالي
                {{ number_format($report['promises']['total']) }}
                وعد

            </p>

        </div>


        {{-- Broken promises --}}
        <div class="rounded-xl border border-white/[0.06] bg-white/[0.02] p-4">

            <div class="flex items-center justify-between">

                <span class="text-xs text-muted">
                    الوعود المكسورة
                </span>

                <i class="fa-solid fa-triangle-exclamation text-danger"></i>

            </div>

            <div class="mt-2 text-xl font-bold
                {{ $report['promises']['broken'] > 0 ? 'text-danger' : 'text-brand' }}">

                {{ number_format($report['promises']['broken']) }}

            </div>

            <p class="mt-1 text-[11px] text-dim">

                نسبة {{ $report['promises']['broken_rate'] }}%

            </p>

        </div>

    </div>

</section>