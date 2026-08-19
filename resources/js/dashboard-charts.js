import Chart from 'chart.js/auto';

// Fixed categorical order (CVD-validated) — never cycle/reorder per-render.
const palette = {
    blue: '#3987e5',
    orange: '#d95926',
    aqua: '#199e70',
    yellow: '#c98500',
    magenta: '#d55181',
    green: '#008300',
    violet: '#9085e9',
    red: '#e66767',
};
const categoricalOrder = ['blue', 'orange', 'aqua', 'yellow', 'magenta', 'green', 'violet', 'red'];

// Read the live theme's CSS variables rather than hardcoding dark-mode hex,
// so charts repaint correctly in light mode and on live theme toggles.
function cssVar(name) {
    const raw = getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    return raw ? `rgb(${raw})` : '#000';
}
function gridColor() { return cssVar('--color-border-strong'); }
function mutedText() { return cssVar('--color-ink-muted'); }
function surfaceGap() { return cssVar('--color-surface-raised'); }

Chart.defaults.font.family = 'Figtree, ui-sans-serif, system-ui, sans-serif';

const chartInstances = [];
function trackChart(chart) {
    chartInstances.push(chart);
    return chart;
}

function readJson(id) {
    const el = document.getElementById(id);
    return el ? JSON.parse(el.textContent) : null;
}

function currencySymbol() {
    return readJson('dashboard-currency') ?? '₦';
}

function money(value) {
    return currencySymbol() + Number(value).toLocaleString(undefined, { maximumFractionDigits: 0 });
}

function initSalesTrend() {
    const data = readJson('sales-trend-data');
    const canvas = document.getElementById('sales-trend-chart');
    if (!data || !canvas) return;

    trackChart(new Chart(canvas, {
        type: 'line',
        data: {
            labels: data.map((d) => d.date),
            datasets: [{
                label: 'Sales',
                data: data.map((d) => d.value),
                borderColor: palette.blue,
                backgroundColor: palette.blue + '1a',
                fill: true,
                borderWidth: 2,
                pointRadius: 0,
                pointHoverRadius: 5,
                pointHoverBackgroundColor: palette.blue,
                pointHoverBorderColor: surfaceGap(),
                pointHoverBorderWidth: 2,
                tension: 0.3,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (ctx) => ' ' + money(ctx.parsed.y) } },
            },
            scales: {
                x: { grid: { display: false }, ticks: { maxTicksLimit: 7, color: mutedText() } },
                y: { grid: { color: gridColor() }, ticks: { callback: (v) => money(v), color: mutedText() } },
            },
        },
    }));
}

function initCategoryMix() {
    const data = readJson('category-mix-data');
    const canvas = document.getElementById('category-mix-chart');
    if (!data || !canvas || data.length === 0) return;

    const colors = data.map((_, i) => palette[categoricalOrder[i % categoricalOrder.length]]);

    trackChart(new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: data.map((d) => d.category),
            datasets: [{
                data: data.map((d) => d.total),
                backgroundColor: colors,
                borderColor: surfaceGap(),
                borderWidth: 2,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { color: mutedText(), boxWidth: 12, padding: 12 } },
                tooltip: { callbacks: { label: (ctx) => ` ${ctx.label}: ${money(ctx.parsed)}` } },
            },
        },
    }));
}

function initComparison() {
    const data = readJson('comparison-data');
    const canvas = document.getElementById('comparison-chart');
    if (!data || !canvas) return;

    trackChart(new Chart(canvas, {
        type: 'line',
        data: {
            labels: data.this.map((d) => 'Day ' + d.day),
            datasets: [
                {
                    label: 'This period',
                    data: data.this.map((d) => d.value),
                    borderColor: palette.blue,
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    pointRadius: 0,
                    tension: 0.3,
                },
                {
                    label: 'Previous period',
                    data: data.previous.map((d) => d.value),
                    borderColor: palette.orange,
                    backgroundColor: 'transparent',
                    borderWidth: 2,
                    borderDash: [4, 4],
                    pointRadius: 0,
                    tension: 0.3,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { position: 'bottom', labels: { color: mutedText(), boxWidth: 20, padding: 12 } },
                tooltip: { callbacks: { label: (ctx) => ` ${ctx.dataset.label}: ${money(ctx.parsed.y)}` } },
            },
            scales: {
                x: { grid: { display: false }, ticks: { maxTicksLimit: 7, color: mutedText() } },
                y: { grid: { color: gridColor() }, ticks: { callback: (v) => money(v), color: mutedText() } },
            },
        },
    }));
}

function initSparkline(canvasId, dataId, color) {
    const data = readJson(dataId);
    const canvas = document.getElementById(canvasId);
    if (!data || !canvas) return;

    trackChart(new Chart(canvas, {
        type: 'line',
        data: {
            labels: data.map((d) => d.date),
            datasets: [{
                data: data.map((d) => d.value),
                borderColor: color,
                backgroundColor: 'transparent',
                borderWidth: 2,
                pointRadius: 0,
                pointHoverRadius: 3,
                tension: 0.3,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (ctx) => ' ' + Number(ctx.parsed.y).toLocaleString() } },
            },
            scales: { x: { display: false }, y: { display: false } },
        },
    }));
}

function renderAllCharts() {
    chartInstances.splice(0).forEach((chart) => chart.destroy());
    initSalesTrend();
    initCategoryMix();
    initComparison();
    initSparkline('kpi-transactions-chart', 'kpi-transactions-data', palette.blue);
    initSparkline('kpi-customers-chart', 'kpi-customers-data', palette.aqua);
    initSparkline('kpi-refunds-chart', 'kpi-refunds-data', palette.red);
}

document.addEventListener('DOMContentLoaded', renderAllCharts);
// Theme can change live (toggle or "auto" + OS preference change) — grid/text
// colors above were baked in at creation time, so charts must be rebuilt.
window.addEventListener('theme-changed', renderAllCharts);
