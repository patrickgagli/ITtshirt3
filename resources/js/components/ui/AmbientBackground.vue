<script setup>
import { onMounted, onUnmounted, ref } from 'vue';

const isVisible = ref(false);

onMounted(() => {
    // Smooth page transition effect for the whole page (keyframe-based, fires reliably)
    document.body.classList.add('page-entering');

    // Reveal background blobs after a short delay
    setTimeout(() => {
        isVisible.value = true;
    }, 100);
});

onUnmounted(() => {
    document.body.classList.remove('page-entering');
});
</script>

<template>
    <div class="ambient-background" :class="{ 'is-visible': isVisible }">
        <div class="ambient-blob ambient-blob-1"></div>
        <div class="ambient-blob ambient-blob-2"></div>
        <div class="ambient-blob ambient-blob-3"></div>
    </div>
</template>

<style scoped>
.ambient-background {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    z-index: -1;
    overflow: hidden;
    opacity: 0;
    pointer-events: none;
    transition: opacity 1.5s ease-in-out;
    background: #050A14;
}

.ambient-background.is-visible {
    opacity: 1;
}

.ambient-blob {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    opacity: 0.4;
    animation: float 20s infinite alternate ease-in-out;
    will-change: transform;
}

.ambient-blob-1 {
    top: -10%;
    left: -10%;
    width: 50vw;
    height: 50vw;
    background: radial-gradient(circle, rgba(13,110,253,0.15) 0%, rgba(13,110,253,0) 70%);
    animation-delay: 0s;
}

.ambient-blob-2 {
    bottom: -20%;
    right: -10%;
    width: 60vw;
    height: 60vw;
    background: radial-gradient(circle, rgba(111,66,193,0.1) 0%, rgba(111,66,193,0) 70%);
    animation-delay: -5s;
    animation-duration: 25s;
}

.ambient-blob-3 {
    top: 40%;
    left: 60%;
    width: 40vw;
    height: 40vw;
    background: radial-gradient(circle, rgba(25,135,84,0.08) 0%, rgba(25,135,84,0) 70%);
    animation-delay: -10s;
    animation-duration: 30s;
}

@keyframes float {
    0% {
        transform: translate(0, 0) scale(1);
    }
    33% {
        transform: translate(3%, 5%) scale(1.05);
    }
    66% {
        transform: translate(-3%, 2%) scale(0.95);
    }
    100% {
        transform: translate(0, -5%) scale(1);
    }
}
</style>
