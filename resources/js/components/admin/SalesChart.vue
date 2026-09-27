<script setup>
import { computed, onMounted, onBeforeUnmount, ref } from 'vue';
import {
    Chart,
    LineController,
    LineElement,
    PointElement,
    LinearScale,
    CategoryScale,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js';

Chart.register(LineController, LineElement, PointElement, LinearScale, CategoryScale, Tooltip, Legend, Filler);

const props = defineProps({
    labels:  { type: Array, required: true },
    revenue: { type: Array, required: true },
    orders:  { type: Array, required: true },
});

const canvas = ref(null);
let chart = null;

const totalRevenue = computed(() => props.revenue.reduce((a, b) => a + b, 0));
const totalOrders  = computed(() => props.orders.reduce((a, b) => a + b, 0));

onMounted(() => {
    chart = new Chart(canvas.value, {
        type: 'line',
        data: {
            labels: props.labels,
            datasets: [
                {
                    label: 'Chiffre d\u2019affaires (\u20ac)',
                    data: props.revenue,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.15)',
                    yAxisID: 'y',
                    tension: 0.3,
                    fill: true,
                },
                {
                    label: 'Commandes',
                    data: props.orders,
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.10)',
                    yAxisID: 'y1',
                    tension: 0.3,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: {
                y:  { beginAtZero: true, position: 'left',  title: { display: true, text: '\u20ac' } },
                y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false }, ticks: { stepSize: 1 } },
            },
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: (ctx) => {
                            const v = ctx.parsed.y;
                            if (ctx.dataset.yAxisID === 'y') {
                                return `${ctx.dataset.label}: ${v.toFixed(2)} \u20ac`;
                            }
                            return `${ctx.dataset.label}: ${v}`;
                        },
                    },
                },
            },
        },
    });
});

onBeforeUnmount(() => chart?.destroy());
</script>

<template>
    <div>
        <div class="d-flex justify-content-between align-items-baseline mb-2">
            <h5 class="fw-bold mb-0">
                <i class="fa-solid fa-chart-line me-2 text-primary"></i>Ventes &mdash; 30 derniers jours
            </h5>
            <div class="text-muted small">
                <span class="me-3"><strong>{{ totalRevenue.toFixed(2) }} &euro;</strong> total</span>
                <span><strong>{{ totalOrders }}</strong> commande(s)</span>
            </div>
        </div>
        <div style="position: relative; height: 280px;">
            <canvas ref="canvas"></canvas>
        </div>
    </div>
</template>
