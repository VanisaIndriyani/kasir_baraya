(() => {
    const el = document.getElementById('salesChart');
    if (!el || !window.EB_DASH) return;
    const ctx = el.getContext('2d');

    const labels = window.EB_DASH.labels || [];
    const values = window.EB_DASH.values || [];

    const theme = document.documentElement.getAttribute('data-bs-theme') || 'light';
    const gridColor = theme === 'dark' ? 'rgba(148,163,184,.18)' : 'rgba(17,24,39,.08)';
    const textColor = theme === 'dark' ? '#e5e7eb' : '#111827';

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: 'Pendapatan',
                data: values,
                borderColor: '#e30613',
                backgroundColor: 'rgba(227,6,19,.14)',
                tension: 0.35,
                fill: true,
                pointRadius: 3,
                pointHoverRadius: 5,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => {
                            const v = Number(ctx.raw || 0);
                            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(v);
                        }
                    }
                }
            },
            scales: {
                x: { grid: { color: gridColor }, ticks: { color: textColor } },
                y: { grid: { color: gridColor }, ticks: { color: textColor } }
            }
        }
    });
})();

