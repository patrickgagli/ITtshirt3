<script setup>
import { computed, onMounted, onBeforeUnmount, ref } from 'vue';
import { Chart, BarController, BarElement, LinearScale, CategoryScale, Tooltip, Legend } from 'chart.js';

Chart.register(BarController, BarElement, LinearScale, CategoryScale, Tooltip, Legend);

const props = defineProps({
    labels:           { type: Array,  required: true },
    submissions:      { type: Array,  required: true },
    totalSubmissions: { type: Number, required: true },
    topSubjects:      { type: Array,  required: true },
});

const canvas = ref(null);
let chart = null;

const windowTotal = computed(() => props.submissions.reduce((a, b) => a + b, 0));

onMounted(() => {
    chart = new Chart(canvas.value, {
        type: 'bar',
        data: {
            labels: props.labels,
            datasets: [{
                label: 'Demandes de contact',
                data: props.submissions,
                backgroundColor: 'rgba(13, 202, 240, 0.7)',
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
                <i class="fa-solid fa-envelope me-2" style="color: #0dcaf0;"></i>Demandes de contact
            </h5>
            <div class="text-muted small">
                <span class="me-3"><strong>{{ windowTotal }}</strong> sur 30 jours</span>
                <span><strong>{{ totalSubmissions }}</strong> au total</span>
            </div>
        </div>
        <div style="position: relative; height: 200px;">
            <canvas ref="canvas"></canvas>
        </div>
        <div v-if="topSubjects.length" class="mt-3">
            <p class="text-muted small mb-1 fw-semibold">Sujets fréquents (30 j) :</p>
            <ul class="list-unstyled mb-0">
                <li v-for="item in topSubjects" :key="item.subject" class="d-flex justify-content-between small text-muted border-bottom py-1">
                    <span class="text-truncate me-2" style="max-width: 75%;">{{ item.subject }}</span>
                    <span class="fw-semibold">{{ item.count }}</span>
                </li>
            </ul>
        </div>
        <p v-else class="text-muted small mt-2 mb-0">Aucune demande sur les 30 derniers jours.</p>
    </div>
</template>
