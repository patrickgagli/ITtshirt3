<script setup>
import { computed, onMounted, onBeforeUnmount, ref } from 'vue';
import { Chart, BarController, BarElement, LinearScale, CategoryScale, Tooltip, Legend } from 'chart.js';

Chart.register(BarController, BarElement, LinearScale, CategoryScale, Tooltip, Legend);

const props = defineProps({
    labels:       { type: Array,  required: true },
    newUsers:     { type: Array,  required: true },
    totalBuyers:  { type: Number, required: true },
    repeatBuyers: { type: Number, required: true },
});

const canvas = ref(null);
let chart = null;

const repeatRate = computed(() => {
    if (!props.totalBuyers) return 0;
    return ((props.repeatBuyers / props.totalBuyers) * 100).toFixed(1);
});

const totalNew = computed(() => props.newUsers.reduce((a, b) => a + b, 0));

onMounted(() => {
    chart = new Chart(canvas.value, {
        type: 'bar',
        data: {
            labels: props.labels,
            datasets: [{
                label: 'Nouveaux clients',
                data: props.newUsers,
                backgroundColor: 'rgba(111, 66, 193, 0.7)',
                borderRadius: 4,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
        },
    });
});

onBeforeUnmount(() => chart?.destroy());
</script>

<template>
    <div>
        <div class="d-flex justify-content-between align-items-baseline mb-2">
            <h5 class="fw-bold mb-0">
                <i class="fa-solid fa-users me-2 text-purple" style="color: #6f42c1;"></i>Clients
            </h5>
            <div class="text-muted small">
                <span class="me-3"><strong>{{ totalNew }}</strong> nouveau(x)</span>
                <span><strong>{{ repeatRate }} %</strong> de fidèles</span>
            </div>
        </div>
        <p class="text-muted small mb-2">
            {{ repeatBuyers }} sur {{ totalBuyers }} acheteur(s) ont passé plus d'une commande.
        </p>
        <div style="position: relative; height: 220px;">
            <canvas ref="canvas"></canvas>
        </div>
    </div>
</template>
