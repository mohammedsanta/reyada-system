<section class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">

    <x-metric-card
        label="تحقيق المستهدف"
        :value="$report['performance']['target_achievement'] . '%'"
        unit="إجمالي الفترة"
        icon="fa-bullseye"
        :color="$report['performance']['performance_tone']"
    />

    <x-metric-card
        label="إجمالي التحصيل"
        :value="number_format($report['trend']['collected_total'])"
        unit="EGP"
        icon="fa-money-bill-trend-up"
        color="brand"
    />

    <x-metric-card
        label="إجمالي الحالات"
        :value="number_format($report['performance']['total_cases'])"
        unit="حالة"
        icon="fa-folder-open"
        color="info"
    />

    <x-metric-card
        label="نجاح الوعود"
        :value="$report['promises']['success_rate'] . '%'"
        unit="من الوعود المحسومة"
        icon="fa-handshake"
        color="warning"
    />

</section>