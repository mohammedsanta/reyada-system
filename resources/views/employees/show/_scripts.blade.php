<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    (() => {

        /*
        |--------------------------------------------------------------------------
        | Theme
        |--------------------------------------------------------------------------
        */

        const css = getComputedStyle(document.documentElement);

        const token = (name, fallback) =>
            css.getPropertyValue(`--color-${name}`).trim() || fallback;

        const brand = token('brand', '#00ff66');
        const info = token('info', '#00aaff');
        const warning = token('warning', '#facc15');
        const danger = token('danger', '#f87171');
        const muted = token('muted', '#8c98a5');
        const line = token('line', 'rgba(255,255,255,0.06)');
        const fg = token('fg', '#ffffff');


        /*
        |--------------------------------------------------------------------------
        | Global Chart Configuration
        |--------------------------------------------------------------------------
        */

        Chart.defaults.font.family = 'Cairo';
        Chart.defaults.color = muted;

        Chart.defaults.plugins.tooltip.backgroundColor =
            'rgba(17,20,24,0.96)';

        Chart.defaults.plugins.tooltip.titleColor = fg;
        Chart.defaults.plugins.tooltip.bodyColor = muted;
        Chart.defaults.plugins.tooltip.borderColor = line;
        Chart.defaults.plugins.tooltip.borderWidth = 1;
        Chart.defaults.plugins.tooltip.padding = 12;
        Chart.defaults.plugins.tooltip.displayColors = true;


        /*
        |--------------------------------------------------------------------------
        | Collection Chart
        |--------------------------------------------------------------------------
        */

        const collectCanvas =
            document.getElementById('collectChart');

        if (collectCanvas) {

            new Chart(collectCanvas, {

                type: 'bar',

                data: {

                    labels: @json($labels ?? []),

                    datasets: [

                        {
                            label: 'المحصل',

                            data: @json(
                                $report['trend']['collected']->values()
                            ),

                            backgroundColor: brand,

                            borderRadius: 6,

                            borderSkipped: false,

                            maxBarThickness: 38,
                        },

                        {
                            type: 'line',

                            label: 'المستهدف',

                            data: @json(
                                $report['trend']['target']->values()
                            ),

                            borderColor: info,

                            backgroundColor: info,

                            borderWidth: 2,

                            borderDash: [6, 6],

                            pointRadius: 3,

                            pointHoverRadius: 5,

                            pointBackgroundColor: info,

                            pointBorderWidth: 0,

                            tension: 0.3,

                            fill: false,
                        },

                    ],

                },

                options: {

                    responsive: true,

                    maintainAspectRatio: false,

                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },

                    scales: {

                        x: {

                            grid: {
                                color: line,
                                drawBorder: false,
                            },

                            ticks: {
                                color: muted,
                            },

                        },

                        y: {

                            beginAtZero: true,

                            grid: {
                                color: line,
                                drawBorder: false,
                            },

                            ticks: {

                                color: muted,

                                callback(value) {
                                    return value + 'k';
                                },

                            },

                        },

                    },

                    plugins: {

                        legend: {

                            position: 'bottom',

                            labels: {
                                usePointStyle: true,
                                padding: 18,
                            },

                        },

                        tooltip: {

                            callbacks: {

                                label(context) {

                                    const value =
                                        Number(context.raw || 0);

                                    return context.dataset.label +
                                        ': ' +
                                        new Intl.NumberFormat('en-US')
                                            .format(value) +
                                        ' EGP';
                                },

                            },

                        },

                    },

                },

            });

        }


        /*
        |--------------------------------------------------------------------------
        | Promise Chart
        |--------------------------------------------------------------------------
        */

        const promiseCanvas =
            document.getElementById('promiseChart');

        if (promiseCanvas) {

            new Chart(promiseCanvas, {

                type: 'doughnut',

                data: {

                    labels: [
                        'محقق كلياً',
                        'محقق جزئياً',
                        'مكسور',
                        'نشط'
                    ],

                    datasets: [

                        {

                            data: @json(
                                $report['promises']['chart']
                            ),

                            backgroundColor: [
                                brand,
                                info,
                                danger,
                                warning
                            ],

                            borderWidth: 0,

                            hoverOffset: 5,

                        },

                    ],

                },

                options: {

                    responsive: true,

                    maintainAspectRatio: false,

                    cutout: '70%',

                    plugins: {

                        legend: {

                            position: 'bottom',

                            labels: {
                                usePointStyle: true,
                                padding: 14,
                            },

                        },

                        tooltip: {

                            callbacks: {

                                label(context) {

                                    const value =
                                        Number(context.raw || 0);

                                    return context.label +
                                        ': ' +
                                        new Intl.NumberFormat('en-US')
                                            .format(value);
                                },

                            },

                        },

                    },

                },

            });

        }


        /*
        |--------------------------------------------------------------------------
        | Copy Employee Code
        |--------------------------------------------------------------------------
        */

        window.copyEmployeeCode = function () {

            const code = @json(
                $report['employee']['code']
            );

            if (!navigator.clipboard) {
                return;
            }

            navigator.clipboard.writeText(code).then(() => {

                const buttons =
                    document.querySelectorAll(
                        '[onclick="copyEmployeeCode()"]'
                    );

                buttons.forEach(button => {

                    const original = button.innerHTML;

                    button.innerHTML =
                        '<i class="fa-solid fa-check text-brand text-xs"></i>';

                    setTimeout(() => {
                        button.innerHTML = original;
                    }, 1400);

                });

            });

        };


        /*
        |--------------------------------------------------------------------------
        | Keyboard Shortcuts
        |--------------------------------------------------------------------------
        */

        document.addEventListener('keydown', event => {

            if (
                (event.ctrlKey || event.metaKey) &&
                event.key.toLowerCase() === 'k'
            ) {

                event.preventDefault();

                const firstFilter =
                    document.querySelector(
                        '#performanceFilter select'
                    );

                firstFilter?.focus();
            }


            if (
                (event.ctrlKey || event.metaKey) &&
                event.key === 'Enter'
            ) {

                const form =
                    document.getElementById(
                        'performanceFilter'
                    );

                if (form) {

                    event.preventDefault();

                    form.submit();
                }

            }


            if (event.key === 'Escape') {

                const active =
                    document.activeElement;

                if (
                    active &&
                    (
                        active.tagName === 'INPUT' ||
                        active.tagName === 'SELECT'
                    )
                ) {
                    active.blur();
                }

            }

        });


        /*
        |--------------------------------------------------------------------------
        | Filter Loading State
        |--------------------------------------------------------------------------
        */

        const performanceFilter =
            document.getElementById('performanceFilter');

        const applyFilterBtn =
            document.getElementById('applyFilterBtn');

        if (
            performanceFilter &&
            applyFilterBtn
        ) {

            performanceFilter.addEventListener(
                'submit',
                () => {

                    applyFilterBtn.disabled = true;

                    applyFilterBtn.innerHTML =
                        '<i class="fa-solid fa-spinner fa-spin text-xs"></i> جاري التحديث...';

                }
            );

        }

    })();
</script>