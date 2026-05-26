document.addEventListener('DOMContentLoaded', function () {
    initConfirmDelete();
    initCanvasCharts();
});

function initConfirmDelete() {
    const forms = document.querySelectorAll('[data-confirm]');

    forms.forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const message = form.getAttribute('data-confirm') || 'Tem certeza?';

            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    });
}

function initCanvasCharts() {
    const charts = document.querySelectorAll('[data-chart="bar"]');

    charts.forEach(function (canvas) {
        drawBarChart(canvas);
    });
}

function drawBarChart(canvas) {
    const ctx = canvas.getContext('2d');

    if (!ctx) {
        return;
    }

    const labels = JSON.parse(canvas.dataset.labels || '[]');
    const values = JSON.parse(canvas.dataset.values || '[]');

    const width = canvas.width;
    const height = canvas.height;

    ctx.clearRect(0, 0, width, height);

    const paddingLeft = 55;
    const paddingRight = 20;
    const paddingTop = 25;
    const paddingBottom = 55;

    const chartWidth = width - paddingLeft - paddingRight;
    const chartHeight = height - paddingTop - paddingBottom;

    const maxValue = Math.max(...values, 1);

    ctx.strokeStyle = '#e5e7eb';
    ctx.lineWidth = 1;

    ctx.beginPath();
    ctx.moveTo(paddingLeft, paddingTop);
    ctx.lineTo(paddingLeft, height - paddingBottom);
    ctx.lineTo(width - paddingRight, height - paddingBottom);
    ctx.stroke();

    if (values.length === 0) {
        ctx.fillStyle = '#6b7280';
        ctx.font = '16px Arial';
        ctx.fillText('Sem dados para exibir', paddingLeft + 20, paddingTop + 40);
        return;
    }

    const slotWidth = chartWidth / values.length;
    const barWidth = slotWidth * 0.55;

    values.forEach(function (value, index) {
        const barHeight = (value / maxValue) * chartHeight;
        const x = paddingLeft + index * slotWidth + (slotWidth - barWidth) / 2;
        const y = height - paddingBottom - barHeight;

        ctx.fillStyle = '#4f46e5';
        ctx.fillRect(x, y, barWidth, barHeight);

        ctx.fillStyle = '#1f2937';
        ctx.font = '13px Arial';
        ctx.textAlign = 'center';
        ctx.fillText(String(value), x + barWidth / 2, y - 8);

        const label = labels[index] || '';
        const shortLabel = label.length > 12 ? label.substring(0, 12) + '...' : label;

        ctx.save();
        ctx.translate(x + barWidth / 2, height - paddingBottom + 18);
        ctx.rotate(-0.25);
        ctx.fillStyle = '#6b7280';
        ctx.font = '12px Arial';
        ctx.fillText(shortLabel, 0, 0);
        ctx.restore();
    });
}