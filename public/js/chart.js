/**
 * CLAIMORA - Dynamic Interactive 6-Month Claims Bar Chart
 * Pure SVG responsive rendering with interactive tooltips and live database data.
 */

document.addEventListener('DOMContentLoaded', () => {
    initClaimTrendsChart();
});

function initClaimTrendsChart() {
    const container = document.getElementById('chart-claims-container');
    if (!container) return;

    // Read dynamic data from data-chart attribute if available
    let chartData = null;
    const rawData = container.getAttribute('data-chart');
    if (rawData) {
        try {
            chartData = JSON.parse(rawData);
        } catch (e) {
            console.error('Invalid chart data JSON:', e);
        }
    }

    // Default structure if not provided
    if (!chartData) {
        chartData = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
            pengajuan: [0, 0, 0, 0, 0, 0],
            disetujui: [0, 0, 0, 0, 0, 0],
            maxVal: 10,
            has_data: false
        };
    }

    renderSvgChart(container, chartData);

    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            renderSvgChart(container, chartData);
        }, 150);
    });
}

function renderSvgChart(container, data) {
    const width = container.clientWidth || 450;
    const height = 240;
    const padding = { top: 24, right: 20, bottom: 40, left: 45 };

    const chartWidth = Math.max(100, width - padding.left - padding.right);
    const chartHeight = Math.max(50, height - padding.top - padding.bottom);

    const labels = data.labels || [];
    const count = labels.length;
    if (count === 0) {
        container.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--text-muted);font-size:13px;">Belum ada data klaim untuk periode ini.</div>';
        return;
    }

    const maxVal = Math.max(5, data.maxVal || 10);
    const barGroupWidth = chartWidth / count;
    const barWidth = Math.min(22, Math.max(8, (barGroupWidth - 14) / 2));

    // Calculate grid steps (4 horizontal guide lines)
    const step = maxVal / 3;
    const gridSteps = [0, Math.round(step), Math.round(step * 2), maxVal];

    let yAxisGrid = '';
    gridSteps.forEach(val => {
        const y = padding.top + chartHeight - (val / maxVal) * chartHeight;
        yAxisGrid += `
            <line x1="${padding.left}" y1="${y}" x2="${width - padding.right}" y2="${y}" stroke="#e5ede8" stroke-dasharray="3,3" />
            <text x="${padding.left - 8}" y="${y + 4}" fill="#859d90" font-size="11" text-anchor="end" font-weight="600">${val}</text>
        `;
    });

    let barsSvg = '';
    labels.forEach((month, i) => {
        const groupX = padding.left + i * barGroupWidth + (barGroupWidth / 2);

        const valPengajuan = (data.pengajuan && data.pengajuan[i]) ? Number(data.pengajuan[i]) : 0;
        const valDisetujui = (data.disetujui && data.disetujui[i]) ? Number(data.disetujui[i]) : 0;

        const hPengajuan = maxVal > 0 ? (valPengajuan / maxVal) * chartHeight : 0;
        const yPengajuan = padding.top + chartHeight - hPengajuan;
        const xPengajuan = groupX - barWidth - 2;

        const hDisetujui = maxVal > 0 ? (valDisetujui / maxVal) * chartHeight : 0;
        const yDisetujui = padding.top + chartHeight - hDisetujui;
        const xDisetujui = groupX + 2;

        barsSvg += `
            <!-- Month Label -->
            <text x="${groupX}" y="${height - 12}" fill="#526b5e" font-size="11.5" font-weight="700" text-anchor="middle">${month}</text>

            <!-- Pengajuan Bar (Light/Medium Green) -->
            <rect class="chart-bar" x="${xPengajuan}" y="${yPengajuan}" width="${barWidth}" height="${Math.max(2, hPengajuan)}" rx="3" fill="#a7d7b5"
                  style="transition: all 0.3s ease; cursor: pointer;">
                <title>${month} - Pengajuan: ${valPengajuan} klaim</title>
            </rect>

            <!-- Disetujui Bar (Deep Forest Green) -->
            <rect class="chart-bar" x="${xDisetujui}" y="${yDisetujui}" width="${barWidth}" height="${Math.max(2, hDisetujui)}" rx="3" fill="#1b4433"
                  style="transition: all 0.3s ease; cursor: pointer;">
                <title>${month} - Disetujui: ${valDisetujui} klaim</title>
            </rect>
        `;
    });

    const svg = `
        <svg width="100%" height="${height}" viewBox="0 0 ${width} ${height}" style="overflow: visible;">
            ${yAxisGrid}
            ${barsSvg}
        </svg>
    `;

    container.innerHTML = svg;
}
