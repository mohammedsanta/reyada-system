<section class="mb-6">

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">

        <div>

            <h2 class="flex items-center gap-2 text-lg font-bold text-fg">
                <i class="fa-solid fa-gauge-high text-brand"></i>
                ملخص الأداء
            </h2>

            <p class="mt-1 text-xs text-muted">
                أهم مؤشرات الموظف خلال الفترة المحددة.
            </p>

        </div>

        <span class="badge badge-outline-info">
            {{ $report['banks']['count'] }} بنك
        </span>

    </div>


    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">

        @foreach($report['stats'] as $stat)

            <x-metric-card
                :label="$stat['label']"
                :value="$stat['value']"
                :unit="$stat['unit']"
                :icon="$stat['icon']"
                :color="$stat['color']"
            />

        @endforeach

    </div>

</section>