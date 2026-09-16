(() => {
    function parseSalesTrend(canvas) {
        try {
            const parsed = JSON.parse(canvas.dataset.salesTrend || '{}');

            return {
                labels: Array.isArray(parsed.labels) ? parsed.labels : [],
                data: Array.isArray(parsed.data) ? parsed.data : [],
            };
        } catch {
            return {
                labels: [],
                data: [],
            };
        }
    }

    function initStatisticsRuntime() {
        const root = document.querySelector('[data-statistics-runtime]');

        if (!root || root.dataset.statisticsRuntimeBound === '1') {
            return;
        }

        root.dataset.statisticsRuntimeBound = '1';

        const period = root.querySelector('#statistics-period');
        const dates = root.querySelector('#statistics-custom-dates');

        if (period && dates) {
            period.addEventListener('change', () => {
                dates.style.display = period.value === 'custom'
                    ? 'grid'
                    : 'none';
            });
        }

        const canvas = root.querySelector('[data-sales-trend-chart]');

        if (!canvas || typeof window.Chart !== 'function') {
            return;
        }

        const chartData = parseSalesTrend(canvas);

        new window.Chart(canvas, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [
                    {
                        label: 'Umsatz €',
                        data: chartData.data,
                        tension: 0.3,
                        fill: false,
                    },
                ],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false,
                    },
                },
                scales: {
                    y: {
                        beginAtZero: true,
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
