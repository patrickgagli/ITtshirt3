<script setup>
import { onMounted, onBeforeUnmount, ref } from 'vue';
import { Chart, BarController, BarElement, LinearScale, CategoryScale, Tooltip, Legend } from 'chart.js';

Chart.register(BarController, BarElement, LinearScale, CategoryScale, Tooltip, Legend);

const props = defineProps({
    labels:  { type: Array, required: true },
    revenue: { type: Array, required: true },
});

const canvas = ref(null);
let chart = null;

onMounted(() => {
    chart = new Chart(canvas.value, {
        type: 'bar',
        data: {
            labels: props.labels,
            datasets: [{
                label: 'Chiffre d\u2019affaires (\u20ac)',
                data: props.revenue,
                backgroundColor: 'rgba(13, 110, 253, 0.7)',
                borderRadius: 6,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => `${ctx.parsed.x.toFixed(2)} \u20ac`,
                    },
                },
            },
            scales: {
                x: { beginAtZero: true, title: { display: true, text: '\u20ac' } },
            },
        },
    });
});

onBeforeUnmount(() => chart?.destroy());
</script>

<template>
    <div>
        <h5 class="fw-bold mb-2">
            <i class="fa-solid fa-tags me-2 text-primary"></i>Top catégories par CA
        </h5>
        <p v-if="!labels.length" class="text-muted small mb-0">Aucune commande payée pour le moment.</p>
        <div v-else style="position: relative; height: 260px;">
            <canvas ref="canvas"></canvas>
        </div>
    </div>
</template>
