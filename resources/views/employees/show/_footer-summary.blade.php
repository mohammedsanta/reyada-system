<section class="mt-6 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">

    {{-- Total promises --}}
    <div class="card">

        <div class="flex items-center justify-between">

            <div>

                <p class="text-[11px] text-muted">
                    إجمالي الوعود
                </p>

                <p class="mt-1 text-xl font-bold text-fg">
                    {{ number_format($report['promises']['total']) }}
                </p>

            </div>

            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-info/10 text-info">
                <i class="fa-solid fa-handshake"></i>
            </span>

        </div>

    </div>


    {{-- Kept --}}
    <div class="card">

        <div class="flex items-center justify-between">

            <div>

                <p class="text-[11px] text-muted">
                    نسبة المحقق كلياً
                </p>

                <p class="mt-1 text-xl font-bold text-brand">
                    {{ $report['promises']['kept_rate'] }}%
                </p>

            </div>

            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand/10 text-brand">
                <i class="fa-solid fa-circle-check"></i>
            </span>

        </div>

    </div>


    {{-- Average bank collection --}}
    <div class="card">

        <div class="flex items-center justify-between">

            <div>

                <p class="text-[11px] text-muted">
                    متوسط تحصيل البنك
                </p>

                <p class="mt-1 text-xl font-bold text-fg">
                    {{ number_format($report['banks']['average_collection']) }}
                </p>

                <p class="mt-0.5 text-[10px] text-dim">
                    EGP
                </p>

            </div>

            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-warning/10 text-warning">
                <i class="fa-solid fa-building-columns"></i>
            </span>

        </div>

    </div>


    {{-- Best efficiency bank --}}
    <div class="card">

        <div class="flex items-center justify-between gap-3">

            <div class="min-w-0">

                <p class="text-[11px] text-muted">
                    أعلى كفاءة بنك
                </p>

                @if($report['banks']['highest_efficiency'])

                    <p class="mt-1 truncate text-sm font-bold text-fg">
                        {{ $report['banks']['highest_efficiency']->name }}
                    </p>

                    <p class="mt-0.5 text-[11px] text-brand">

                        {{ number_format(
                            (float) ($report['banks']['highest_efficiency']->efficiency ?? 0)
                        ) }}%

                    </p>

                @else

                    <p class="mt-1 text-sm text-dim">
                        لا توجد بيانات
                    </p>

                @endif

            </div>

            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-brand/10 text-brand">
                <i class="fa-solid fa-chart-line"></i>
            </span>

        </div>

    </div>

</section>