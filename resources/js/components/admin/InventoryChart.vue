<script setup>
import { computed, onMounted, onBeforeUnmount, ref } from 'vue';
import { Chart, DoughnutController, ArcElement, Tooltip, Legend } from 'chart.js';

Chart.register(DoughnutController, ArcElement, Tooltip, Legend);

const props = defineProps({
    inStock:    { type: Number, required: true },
    lowStock:   { type: Number, required: true },
    outOfStock: { type: Number, required: true },
    untracked:  { type: Number, required: true },
});

const canvas = ref(null);
let chart = null;

const total = computed(() => props.inStock + props.lowStock + props.outOfStock + props.untracked);

onMounted(() => {
    chart = new Chart(canvas.value, {
        type: 'doughnut',
        data: {
            labels: ['En stock', 'Stock faible', 'Rupture', 'Non suivi'],
            datasets: [{
                data: [props.inStock, props.lowStock, props.outOfStock, props.untracked],
                backgroundColor: ['#198754', '#ffc107', '#dc3545', '#6c757d'],
                borderWidth: 0,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: (ctx) => {
                            const v = ctx.parsed;
                            const pct = total.value ? ((v / total.value) * 100).toFixed(1) : 0;
                            return `${ctx.label}: ${v} (${pct} %)`;
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
        <h5 class="fw-bold mb-2">
            <i class="fa-solid fa-boxes-stacked me-2 text-warning"></i>Inventaire
        </h5>
        <p class="text-muted small mb-2">{{ total }} produit(s) au total.</p>
        <div style="position: relative; height: 260px;">
            <canvas ref="canvas"></canvas>
        </div>
    </div>
</template>
