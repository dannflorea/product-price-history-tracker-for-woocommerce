document.addEventListener('DOMContentLoaded', function () {
    // Init inline charts at page load
    initCharts();

    // Popup handling
    document.querySelectorAll('.wizewpph-popup-trigger').forEach(button => {
        button.addEventListener('click', function () {
            const productId = this.dataset.productId;
            const modal = document.getElementById('wizewpph-modal-' + productId);
            if (!modal) return;

            const container = modal.querySelector('.wizewpph-modal-chart-container');
            if (!container.dataset.initialized) {
                // Get data directly from button attributes
                const labels = JSON.parse(button.dataset.labels);
                const data = JSON.parse(button.dataset.data);
                const template = button.dataset.template || 'basic';

                // Create canvas dynamically
                const canvas = document.createElement('canvas');
                container.appendChild(canvas);

                // Draw chart
                drawChart(canvas, labels, data, template);

                container.dataset.initialized = '1';
            }

            modal.style.display = 'block';
        });
    });

    document.querySelectorAll('.wizewpph-modal-close').forEach(closeBtn => {
        closeBtn.addEventListener('click', function () {
            this.closest('.wizewpph-modal').style.display = 'none';
        });
    });

    window.addEventListener('click', function (e) {
        if (e.target.classList.contains('wizewpph-modal')) {
            e.target.style.display = 'none';
        }
    });
});

function initCharts(scope = document) {
    const chartContainers = scope.querySelectorAll('.wizewpph-chart-container:not([data-initialized])');

    chartContainers.forEach(container => {
        const canvas = container.querySelector('canvas');
        const labels = JSON.parse(canvas.dataset.labels);
        const data = JSON.parse(canvas.dataset.data);
        const template = canvas.dataset.template || 'basic';

        drawChart(canvas, labels, data, template);

        container.setAttribute('data-initialized', '1');
    });
}

function drawChart(canvas, labels, data, template) {
    let chartType = 'line';
    let tension = 0;
    let backgroundColor = null;

    if (template === 'smooth') {
        tension = 0.4;
        backgroundColor = getGradient(canvas);
    } else if (template === 'bar') {
        chartType = 'bar';
        backgroundColor = 'rgba(0,115,170,0.3)';
    } else {
        backgroundColor = 'rgba(0,115,170,0.1)';
    }

    new Chart(canvas, {
        type: chartType,
        data: {
            labels: labels,
            datasets: [{
                label: 'Price',
                data: data,
                fill: true,
                borderColor: '#0073aa',
                backgroundColor: backgroundColor,
                tension: tension,
                pointRadius: 3,
                pointHoverRadius: 5
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: false } }
        }
    });
}

function getGradient(canvas) {
    const ctx = canvas.getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(0,115,170,0.3)');
    gradient.addColorStop(1, 'rgba(0,115,170,0)');
    return gradient;
}
