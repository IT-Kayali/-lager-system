(() => {
    function parseTrend(canvas) {
        try {
            const parsed = JSON.parse(canvas.dataset.salesTrend || '{}');

            return {
                labels: Array.isArray(parsed.labels) ? parsed.labels : [],
                revenue: Array.isArray(parsed.data) ? parsed.data : [],
                orders: Array.isArray(parsed.orders) ? parsed.orders : [],
            };
        } catch {
            return {
                labels: [],
                revenue: [],
                orders: [],
            };
        }
    }

    function initStatisticsRuntime() {
        const root = document.querySelector('[data-statistics-runtime]');

        if (!root || root.dataset.statisticsRuntimeBound === '1') {
            return;
        }

        root.dataset.statisticsRuntimeBound = '1';

        const period = root.querySelector('[data-statistics-period]');
        const customDates = root.querySelector('[data-custom-dates]');

        const syncCustomDates = () => {
            if (!period || !customDates) {
                return;
            }

            customDates.hidden = period.value !== 'custom';
        };

        period?.addEventListener('change', syncCustomDates);
        syncCustomDates();

        const canvas = root.querySelector('[data-sales-trend-chart]');
        const ChartCtor = window.Chart;

        if (!canvas || typeof ChartCtor !== 'function') {
            return;
        }

        const trend = parseTrend(canvas);
        const mode = canvas.dataset.chartMode === 'orders'
            ? 'orders'
            : 'revenue';

        const isOrders = mode === 'orders';

        new ChartCtor(canvas, {
            type: 'line',
            data: {
                labels: trend.labels,
                datasets: [
                    {
                        label: isOrders ? 'Verkäufe' : 'Umsatz €',
                        data: isOrders ? trend.orders : trend.revenue,
                        tension: 0.3,
                        fill: false,
                        pointRadius: 4,
                        pointHoverRadius: 5,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: isOrders
                            ? {
                                precision: 0,
                            }
                            : {},
                    },
                },
            },
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            initStatisticsRuntime,
            { once: true }
        );
    } else {
        initStatisticsRuntime();
    }
})();
