<section class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-3">

    {{-- Collection vs target --}}
    <x-panel
        title="التحصيل مقابل المستهدف (ألف EGP)"
        class="xl:col-span-2"
    >

        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">

            <div class="flex flex-wrap items-center gap-4 text-[11px]">

                <span class="flex items-center gap-2 text-muted">
                    <span class="h-2.5 w-2.5 rounded-full bg-brand"></span>
                    المحصل
                </span>

                <span class="flex items-center gap-2 text-muted">
                    <span class="h-2.5 w-2.5 rounded-full bg-info"></span>
                    المستهدف
                </span>

            </div>

            <span class="badge badge-outline-{{ $report['performance']['performance_tone'] }}">
                {{ $report['performance']['target_achievement'] }}%
            </span>

        </div>


        <div class="relative h-[260px]">
            <canvas id="collectChart"></canvas>
        </div>


        <div class="mt-4 grid grid-cols-2 gap-3 border-t border-white/[0.06] pt-4">

            <div>

                <p class="text-[11px] text-muted">
                    إجمالي المحصل
                </p>

                <p class="mt-1 text-sm font-bold text-brand">
                    EGP {{ number_format($report['trend']['collected_total']) }}
                </p>

            </div>

            <div class="text-left">

                <p class="text-[11px] text-muted">
                    إجمالي المستهدف
                </p>

                <p class="mt-1 text-sm font-bold text-info">
                    EGP {{ number_format($report['trend']['target_total']) }}
                </p>

            </div>

        </div>

    </x-panel>


    {{-- Promise chart --}}
    <x-panel title="نتائج وعود الدفع">

        <div class="relative flex h-[260px] justify-center">
            <canvas id="promiseChart"></canvas>
        </div>


        <div class="mt-4 grid grid-cols-2 gap-2 border-t border-white/[0.06] pt-4">

            <div class="rounded-lg bg-brand/5 p-3">

                <p class="text-[10px] text-muted">
                    محقق كلياً
                </p>

                <p class="mt-1 font-bold text-brand">
                    {{ number_format($report['promises']['kept']) }}
                </p>

            </div>

            <div class="rounded-lg bg-info/5 p-3">

                <p class="text-[10px] text-muted">
                    محقق جزئياً
                </p>

                <p class="mt-1 font-bold text-info">
                    {{ number_format($report['promises']['partial']) }}
                </p>

            </div>

            <div class="rounded-lg bg-danger/5 p-3">

                <p class="text-[10px] text-muted">
                    مكسور
                </p>

                <p class="mt-1 font-bold text-danger">
                    {{ number_format($report['promises']['broken']) }}
                </p>

            </div>

            <div class="rounded-lg bg-warning/5 p-3">

                <p class="text-[10px] text-muted">
                    نشط
                </p>

                <p class="mt-1 font-bold text-warning">
                    {{ number_format($report['promises']['active']) }}
                </p>

            </div>

        </div>

    </x-panel>

</section>