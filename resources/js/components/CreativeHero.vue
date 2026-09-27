<template>
  <header class="creative-hero" ref="heroRef" @mousemove="onMouseMove" @mouseleave="onMouseLeave">

    <!-- ── Background layers ─────────────────────────────── -->
    <div class="hero-bg" aria-hidden="true">
      <div class="bg-noise"></div>
      <div class="bg-grid"></div>
      <div class="orb orb-1"></div>
      <div class="orb orb-2"></div>
      <div class="orb orb-3"></div>
    </div>

    <!-- ── ASCII / Retro terminal overlay ────────────────── -->
    <aside class="ascii-box" :class="{ visible: contentVisible }" aria-hidden="true">
      <div class="ascii-chrome">
        <span class="chrome-dot r"></span>
        <span class="chrome-dot y"></span>
        <span class="chrome-dot g"></span>
        <span class="chrome-label">ITtshirt3 :: terminal</span>
      </div>
      <pre class="ascii-logo">{{ ASCII_LOGO }}</pre>
      <div class="term-body">
        <div v-for="(line, i) in completedTermLines" :key="i" class="term-line done">
          <span class="ps1">›</span>{{ line }}
        </div>
        <div v-if="activeTermLine !== null" class="term-line">
          <span class="ps1">›</span>{{ activeTermLine }}<span class="block-cur">█</span>
        </div>
      </div>
    </aside>

    <!-- ── Main content grid ──────────────────────────────── -->
    <div class="hero-inner">
      <div class="hero-grid">

        <!-- LEFT ── text ──────────────────────────────────── -->
        <div class="hero-left" :style="leftShift">

          <div class="badge-pill" :class="{ visible: contentVisible }">
            <Sparkles :size="13" class="badge-sparkle" />
            <span>Collection Premium 2026</span>
            <span class="badge-pulse-ring" aria-hidden="true"></span>
          </div>

          <h1 class="hero-h1" :class="{ visible: contentVisible }">
            Le mouvement<br>
            <span class="grad-text">{{ typedText }}</span><span
              class="caret"
              :class="{ 'caret-off': cursorHidden }">|</span>
          </h1>

          <p class="hero-sub" :class="{ visible: contentVisible }">
            Découvrez notre sélection exclusive de t-shirts pensés pour votre
            quotidien. Qualité premium, coupes modernes et engagement durable.
          </p>

          <div class="cta-row" :class="{ visible: contentVisible }">
            <a :href="productsUrl" class="btn-glow">
              <ShoppingBag :size="16" />
              <span>Explorer le catalogue</span>
              <ArrowRight :size="14" class="btn-arrow-icon" />
            </a>
            <a href="#univers" class="btn-outline">
              <LayoutGrid :size="14" />
              <span>Nos univers</span>
            </a>
          </div>

          <div class="stats-row" :class="{ visible: contentVisible }">
            <div class="stat">
              <Package :size="17" class="stat-ico" />
              <div>
                <b class="stat-n">{{ displayedProductCount }}</b>
                <span class="stat-l">produits</span>
              </div>
            </div>
            <div class="stat-div" aria-hidden="true"></div>
            <div class="stat">
              <Layers :size="17" class="stat-ico" />
              <div>
                <b class="stat-n">{{ displayedCategoryCount }}</b>
                <span class="stat-l">univers</span>
              </div>
            </div>
            <div class="stat-div" aria-hidden="true"></div>
            <div class="stat">
              <Star :size="17" class="stat-ico star-filled" />
              <div>
                <b class="stat-n">4.9</b>
                <span class="stat-l">rating</span>
              </div>
            </div>
          </div>

        </div>

        <!-- RIGHT ── FRAMEGEN visual showcase ─────────────── -->
        <div class="hero-right" :style="rightShift" :class="{ visible: contentVisible }">

          <!-- LUMNI premium light halo behind image -->
          <div class="lumni-halo" aria-hidden="true"></div>

          <!-- FRAMEGEN frame with reticle corners -->
          <div class="frame-box">
            <span class="tic tl" aria-hidden="true"></span>
            <span class="tic tr" aria-hidden="true"></span>
            <span class="tic bl" aria-hidden="true"></span>
            <span class="tic br" aria-hidden="true"></span>

            <!-- Unsplash royalty-free product image -->
            <img
              src="https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=640&q=85&auto=format&fit=crop"
              alt="T-shirt premium collection 2026"
              class="product-img"
              loading="eager"
              decoding="async"
              v-show="!imgFailed"
              @error="imgFailed = true"
            />
            <!-- Fallback gradient when image fails -->
            <div v-if="imgFailed" class="img-fallback" aria-hidden="true"></div>

            <!-- Scanlines static texture -->
            <div class="scanlines" aria-hidden="true"></div>
            <!-- Sweep scan line animation -->
            <div class="scan-sweep" aria-hidden="true"></div>
            <!-- Dot mesh overlay -->
            <div class="dot-mesh" aria-hidden="true"></div>

            <!-- FRAMEGEN AI label -->
            <div class="fg-tag">
              <Cpu :size="10" />
              <span>AI ENHANCED VISUAL</span>
              <span class="fg-blink" aria-hidden="true">●</span>
            </div>

            <!-- Metadata tag -->
            <div class="meta-tag" aria-hidden="true">640×960 · HDR · UQ+</div>
          </div>

          <!-- Floating glassmorphism chips -->
          <div class="glass-chip chip-a">
            <CheckCircle :size="18" class="chip-ico emerald" />
            <div class="chip-body">
              <span class="chip-title">Éco-responsable</span>
              <span class="chip-sub">Coton GOTS certifié</span>
            </div>
          </div>

          <div class="glass-chip chip-b">
            <Zap :size="18" class="chip-ico amber" />
            <div class="chip-body">
              <span class="chip-title">Livraison express</span>
              <span class="chip-sub">Expédition 48 h</span>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- Scroll cue -->
    <div class="scroll-cue" :class="{ visible: contentVisible }" aria-hidden="true">
      <span class="scroll-label">Découvrir</span>
      <ChevronDown :size="18" class="scroll-chevron" />
    </div>

  </header>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import {
  Sparkles, ShoppingBag, ArrowRight, LayoutGrid,
  Package, Layers, Star, Cpu, CheckCircle, Zap, ChevronDown,
} from '@lucide/vue';

const props = defineProps({
  productCount:  { type: Number, required: true },
  categoryCount: { type: Number, required: true },
  lastImport:    { type: String, required: true },
  productsUrl:   { type: String, required: true },
});

/* ── reactive state ─────────────────────────────────────── */
const heroRef            = ref(null);
const contentVisible     = ref(false);
const displayedProductCount  = ref(0);
const displayedCategoryCount = ref(0);
const typedText          = ref('');
const cursorHidden       = ref(false);
const mouseX             = ref(0.5);
const mouseY             = ref(0.5);
const completedTermLines = ref([]);
const activeTermLine     = ref(null);
const imgFailed          = ref(false);

/* ── ASCII logo & terminal sequence ─────────────────────── */
const ASCII_LOGO =
`╔══════════════════╗
║  ITTSHIRT³  v1.0 ║
║  2026 · PREMIUM  ║
╚══════════════════╝`;

const TERM_LINES = [
  'init store --env=prod',
  'load catalog → 20 items ✓',
  'stripe connect → ready',
  'server :8000 › live ✓',
];

/* ── parallax (mouse-tracking) ──────────────────────────── */
const leftShift = computed(() => ({
  transform: `translate(${(mouseX.value - 0.5) * -10}px, ${(mouseY.value - 0.5) * -6}px)`,
  transition: 'transform 0.5s cubic-bezier(.25,.46,.45,.94)',
  willChange: 'transform',
}));
const rightShift = computed(() => ({
  transform: `translate(${(mouseX.value - 0.5) * 14}px, ${(mouseY.value - 0.5) * 10}px)`,
  transition: 'transform 0.5s cubic-bezier(.25,.46,.45,.94)',
  willChange: 'transform',
}));

const onMouseMove = e => {
  const r = heroRef.value?.getBoundingClientRect();
  if (!r) return;
  mouseX.value = (e.clientX - r.left) / r.width;
  mouseY.value = (e.clientY - r.top)  / r.height;
};
const onMouseLeave = () => { mouseX.value = 0.5; mouseY.value = 0.5; };

/* ── counter animation ──────────────────────────────────── */
const animateCount = (refVar, target, duration) => {
  let t0 = null;
  const step = ts => {
    if (!t0) t0 = ts;
    const p = Math.min((ts - t0) / duration, 1);
    refVar.value = Math.floor(p * target);
    if (p < 1) requestAnimationFrame(step);
  };
  requestAnimationFrame(step);
};

const PHRASE = "s'habille de style.";

/* ── lifecycle ──────────────────────────────────────────── */
onMounted(() => {
  requestAnimationFrame(() => { contentVisible.value = true; });

  setTimeout(() => {
    animateCount(displayedProductCount,  props.productCount,  1400);
    animateCount(displayedCategoryCount, props.categoryCount, 1400);
  }, 400);

  // Headline typewriter
  setTimeout(() => {
    let i = 0;
    const t = setInterval(() => {
      if (i < PHRASE.length) {
        typedText.value += PHRASE[i++];
      } else {
        clearInterval(t);
        let b = 0;
        const bt = setInterval(() => {
          cursorHidden.value = !cursorHidden.value;
          if (++b >= 10) { clearInterval(bt); cursorHidden.value = true; }
        }, 500);
      }
    }, 75);
  }, 700);

  // Terminal line typewriter
  let li = 0;
  const typeNext = () => {
    if (li >= TERM_LINES.length) return;
    const line = TERM_LINES[li];
    let ci = 0;
    activeTermLine.value = '';
    const t = setInterval(() => {
      if (ci < line.length) {
        activeTermLine.value += line[ci++];
      } else {
        clearInterval(t);
        completedTermLines.value.push(line);
        activeTermLine.value = null;
        li++;
        setTimeout(typeNext, 350);
      }
    }, 38);
  };
  setTimeout(typeNext, 900);
});
</script>

<style scoped>
/* ── Hero wrapper ─────────────────────────────────────── */
.creative-hero {
  position: relative;
  min-height: clamp(600px, 92vh, 960px);
  display: flex;
  flex-direction: column;
  background: #050A14;
  color: #E2E8F0;
  overflow: hidden;
  font-family: 'Inter', system-ui, sans-serif;
}

/* ── Background layers ──────────────────────────────── */
.hero-bg {
  position: absolute;
  inset: 0;
  pointer-events: none;
}

/* SVG noise grain */
.bg-noise {
  position: absolute;
  inset: 0;
  opacity: 0.035;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='256' height='256'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
  background-size: 256px 256px;
}

/* Dot grid */
.bg-grid {
  position: absolute;
  inset: 0;
  background-image: radial-gradient(rgba(99, 102, 241, 0.22) 1px, transparent 1px);
  background-size: 32px 32px;
  opacity: 0.5;
}

/* Glow orbs */
.orb {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  animation: orbFloat 14s ease-in-out infinite alternate;
  will-change: transform;
}
.orb-1 {
  width: 55vw; height: 55vw;
  top: -15%; left: -10%;
  background: radial-gradient(circle, rgba(59,130,246,0.18) 0%, transparent 65%);
}
.orb-2 {
  width: 45vw; height: 45vw;
  bottom: -20%; right: -5%;
  background: radial-gradient(circle, rgba(139,92,246,0.16) 0%, transparent 65%);
  animation-delay: -4s; animation-duration: 17s;
}
.orb-3 {
  width: 35vw; height: 35vw;
  top: 40%; left: 55%;
  background: radial-gradient(circle, rgba(16,185,129,0.10) 0%, transparent 65%);
  animation-delay: -9s; animation-duration: 21s;
}
@keyframes orbFloat {
  0%   { transform: translate(0, 0) scale(1); }
  33%  { transform: translate(3%, 5%) scale(1.06); }
  66%  { transform: translate(-2%, 3%) scale(0.96); }
  100% { transform: translate(0, -4%) scale(1.02); }
}

/* ── ASCII terminal ─────────────────────────────────── */
.ascii-box {
  position: absolute;
  top: 1.5rem;
  right: 1.5rem;
  z-index: 20;
  width: 224px;
  background: rgba(0, 0, 0, 0.72);
  border: 1px solid rgba(34, 197, 94, 0.28);
  border-radius: 8px;
  backdrop-filter: blur(14px);
  font-family: 'Fira Code', 'Courier New', 'Consolas', monospace;
  font-size: 0.7rem;
  color: #4ADE80;
  overflow: hidden;
  opacity: 0;
  transform: translateY(-12px);
  transition: opacity 0.8s ease 1.3s, transform 0.8s ease 1.3s;
}
.ascii-box.visible { opacity: 1; transform: none; }

.ascii-chrome {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 6px 10px;
  background: rgba(0, 0, 0, 0.45);
  border-bottom: 1px solid rgba(34, 197, 94, 0.14);
}
.chrome-dot {
  width: 8px; height: 8px; border-radius: 50%;
  flex-shrink: 0;
}
.chrome-dot.r { background: #FF5F57; }
.chrome-dot.y { background: #FFBD2E; }
.chrome-dot.g { background: #28C840; }
.chrome-label {
  margin-left: 4px;
  font-size: 0.62rem;
  color: rgba(74, 222, 128, 0.55);
  letter-spacing: 0.04em;
}

.ascii-logo {
  margin: 0;
  padding: 8px 10px 5px;
  font-size: 0.6rem;
  line-height: 1.35;
  color: rgba(74, 222, 128, 0.82);
  white-space: pre;
  border-bottom: 1px solid rgba(34, 197, 94, 0.14);
  text-shadow: 0 0 8px rgba(74, 222, 128, 0.4);
}

.term-body {
  padding: 6px 10px 9px;
  display: flex;
  flex-direction: column;
  gap: 3px;
}
.term-line {
  display: flex;
  gap: 5px;
  color: rgba(74, 222, 128, 0.9);
  font-size: 0.68rem;
  line-height: 1.45;
}
.term-line.done { color: rgba(74, 222, 128, 0.5); }
.ps1 { color: #60A5FA; flex-shrink: 0; }
.block-cur { animation: blkBlink 0.9s step-end infinite; }
@keyframes blkBlink { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }

/* ── Layout ─────────────────────────────────────────── */
.hero-inner {
  position: relative;
  z-index: 10;
  flex: 1;
  display: flex;
  align-items: center;
  padding: 5rem 2rem 6rem;
  max-width: 1280px;
  margin: 0 auto;
  width: 100%;
  box-sizing: border-box;
}

.hero-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 3rem;
  width: 100%;
}
@media (min-width: 1024px) {
  .hero-grid { grid-template-columns: 1fr 1fr; gap: 4rem; align-items: center; }
}

/* ── LEFT — text ────────────────────────────────────── */
.hero-left {
  display: flex;
  flex-direction: column;
  gap: 1.6rem;
}

/* Badge */
.badge-pill {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 7px 15px;
  border-radius: 99px;
  background: rgba(59, 130, 246, 0.11);
  border: 1px solid rgba(59, 130, 246, 0.28);
  backdrop-filter: blur(8px);
  font-size: 0.8rem;
  font-weight: 600;
  color: #93C5FD;
  position: relative;
  width: fit-content;
  opacity: 0;
  transform: translateY(16px);
  transition: opacity 0.7s ease 0.1s, transform 0.7s ease 0.1s;
}
.badge-pill.visible { opacity: 1; transform: none; }
.badge-sparkle { color: #60A5FA; flex-shrink: 0; }
.badge-pulse-ring {
  position: absolute;
  inset: -2px;
  border-radius: 99px;
  border: 1px solid rgba(59, 130, 246, 0.35);
  animation: pulseBorder 2.5s ease-in-out infinite;
}
@keyframes pulseBorder {
  0%, 100% { opacity: 1; transform: scale(1); }
  50%       { opacity: 0; transform: scale(1.09); }
}

/* Headline */
.hero-h1 {
  font-size: clamp(2.6rem, 5vw, 4.2rem);
  font-weight: 800;
  line-height: 1.1;
  letter-spacing: -0.03em;
  opacity: 0;
  transform: translateY(20px);
  transition: opacity 0.7s ease 0.25s, transform 0.7s ease 0.25s;
}
.hero-h1.visible { opacity: 1; transform: none; }

.grad-text {
  background: linear-gradient(135deg, #60A5FA 0%, #A78BFA 52%, #34D399 100%);
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  color: transparent;
  text-shadow: none;
}

.caret {
  display: inline-block;
  margin-left: 2px;
  color: #60A5FA;
  animation: caretBlink 1s step-end infinite;
}
.caret.caret-off { opacity: 0; animation: none; }
@keyframes caretBlink { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }

/* Subheading */
.hero-sub {
  font-size: clamp(0.95rem, 1.5vw, 1.1rem);
  line-height: 1.72;
  color: #94A3B8;
  max-width: 46ch;
  opacity: 0;
  transform: translateY(16px);
  transition: opacity 0.7s ease 0.4s, transform 0.7s ease 0.4s;
}
.hero-sub.visible { opacity: 1; transform: none; }

/* CTA row */
.cta-row {
  display: flex;
  flex-wrap: wrap;
  gap: 0.85rem;
  align-items: center;
  opacity: 0;
  transform: translateY(14px);
  transition: opacity 0.7s ease 0.55s, transform 0.7s ease 0.55s;
}
.cta-row.visible { opacity: 1; transform: none; }

.btn-glow {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 13px 24px;
  background: linear-gradient(135deg, #2563EB, #7C3AED);
  border-radius: 99px;
  font-size: 0.92rem;
  font-weight: 700;
  color: #fff;
  text-decoration: none;
  box-shadow: 0 0 28px rgba(59, 130, 246, 0.45), 0 4px 18px rgba(0,0,0,0.35);
  transition: box-shadow 0.3s, transform 0.2s;
  position: relative;
  overflow: hidden;
}
.btn-glow::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(255,255,255,0.14), transparent);
  border-radius: inherit;
  pointer-events: none;
}
.btn-glow:hover {
  box-shadow: 0 0 48px rgba(59, 130, 246, 0.65), 0 8px 28px rgba(0,0,0,0.45);
  transform: translateY(-2px);
  color: #fff;
  text-decoration: none;
}
.btn-arrow-icon { transition: transform 0.25s; }
.btn-glow:hover .btn-arrow-icon { transform: translateX(3px); }

.btn-outline {
  display: inline-flex;
  align-items: center;
  gap: 7px;
  padding: 12px 22px;
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.15);
  border-radius: 99px;
  font-size: 0.92rem;
  font-weight: 600;
  color: #CBD5E1;
  text-decoration: none;
  backdrop-filter: blur(8px);
  transition: background 0.3s, border-color 0.3s, transform 0.2s, color 0.2s;
}
.btn-outline:hover {
  background: rgba(255,255,255,0.1);
  border-color: rgba(255,255,255,0.3);
  transform: translateY(-2px);
  color: #fff;
  text-decoration: none;
}

/* Stats row */
.stats-row {
  display: flex;
  align-items: center;
  gap: 1.25rem;
  flex-wrap: wrap;
  opacity: 0;
  transform: translateY(12px);
  transition: opacity 0.7s ease 0.7s, transform 0.7s ease 0.7s;
}
.stats-row.visible { opacity: 1; transform: none; }
.stat { display: flex; align-items: center; gap: 10px; }
.stat-ico { color: #60A5FA; flex-shrink: 0; }
.stat-ico.star-filled { color: #FBBF24; }
.stat-n {
  display: block;
  font-size: 1.4rem;
  font-weight: 800;
  line-height: 1;
  color: #F1F5F9;
}
.stat-l {
  display: block;
  font-size: 0.7rem;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #64748B;
  margin-top: 2px;
}
.stat-div {
  width: 1px;
  height: 2.5rem;
  background: rgba(255,255,255,0.1);
}

/* ── RIGHT — FRAMEGEN showcase ──────────────────────── */
.hero-right {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px;
  opacity: 0;
  transform: translateX(30px);
  transition: opacity 0.8s ease 0.3s, transform 0.8s ease 0.3s;
}
.hero-right.visible { opacity: 1; transform: none; }

/* LUMNI premium light halo */
.lumni-halo {
  position: absolute;
  width: 90%;
  padding-top: 90%;
  top: 50%; left: 50%;
  transform: translate(-50%, -50%);
  border-radius: 50%;
  background: radial-gradient(ellipse at 50% 40%,
    rgba(139, 92, 246, 0.38) 0%,
    rgba(59, 130, 246, 0.24) 30%,
    rgba(16, 185, 129, 0.08) 58%,
    transparent 70%);
  filter: blur(44px);
  pointer-events: none;
  z-index: 0;
  animation: lumniPulse 5s ease-in-out infinite alternate;
}
@keyframes lumniPulse {
  0%   { opacity: 0.8; transform: translate(-50%, -50%) scale(1); }
  100% { opacity: 1.0; transform: translate(-50%, -50%) scale(1.07); }
}

/* FRAMEGEN wrapper */
.frame-box {
  position: relative;
  z-index: 1;
  width: min(380px, 90vw);
  aspect-ratio: 2 / 3;
  border-radius: 16px;
  overflow: hidden;
  box-shadow:
    0 0 0 1px rgba(59, 130, 246, 0.22),
    0 28px 72px rgba(0, 0, 0, 0.65),
    inset 0 1px 0 rgba(255,255,255,0.07);
}

/* Corner reticles (FRAMEGEN targeting brackets) */
.tic {
  position: absolute;
  z-index: 4;
  width: 22px; height: 22px;
  border-color: rgba(99, 179, 237, 0.85);
  border-style: solid;
  pointer-events: none;
}
.tic.tl { top: -1px; left: -1px;  border-width: 2px 0 0 2px; border-radius: 2px 0 0 0; }
.tic.tr { top: -1px; right: -1px; border-width: 2px 2px 0 0; border-radius: 0 2px 0 0; }
.tic.bl { bottom: -1px; left: -1px;  border-width: 0 0 2px 2px; border-radius: 0 0 0 2px; }
.tic.br { bottom: -1px; right: -1px; border-width: 0 2px 2px 0; border-radius: 0 0 2px 0; }

/* Product image */
.product-img {
  display: block;
  width: 100%; height: 100%;
  object-fit: cover;
  filter: contrast(1.06) saturate(0.88) brightness(0.86);
  transition: filter 0.4s;
}
.frame-box:hover .product-img {
  filter: contrast(1.12) saturate(1.0) brightness(0.9);
}

/* Fallback gradient */
.img-fallback {
  width: 100%; height: 100%;
  background: linear-gradient(145deg, #0F172A 0%, #1E293B 50%, #0F172A 100%);
}

/* Static scanlines */
.scanlines {
  position: absolute; inset: 0; z-index: 2;
  background: repeating-linear-gradient(
    0deg,
    transparent,
    transparent 2px,
    rgba(0, 0, 0, 0.055) 2px,
    rgba(0, 0, 0, 0.055) 4px
  );
  pointer-events: none;
}

/* Animated sweep line */
.scan-sweep {
  position: absolute;
  left: 0; right: 0; height: 2px;
  z-index: 3;
  background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.55), transparent);
  animation: sweepDown 4s linear infinite;
  pointer-events: none;
}
@keyframes sweepDown {
  0%   { top: -4px; opacity: 0.9; }
  88%  { top: calc(100% + 4px); opacity: 0.3; }
  100% { top: calc(100% + 4px); opacity: 0; }
}

/* Dot mesh overlay */
.dot-mesh {
  position: absolute; inset: 0; z-index: 1;
  background-image: radial-gradient(rgba(99, 179, 237, 0.22) 1px, transparent 1px);
  background-size: 20px 20px;
  opacity: 0.25;
  mix-blend-mode: screen;
  pointer-events: none;
}

/* FRAMEGEN AI badge */
.fg-tag {
  position: absolute; bottom: 10px; left: 10px; z-index: 5;
  display: inline-flex; align-items: center; gap: 5px;
  padding: 4px 9px;
  background: rgba(0, 0, 0, 0.68);
  border: 1px solid rgba(59, 130, 246, 0.3);
  border-radius: 4px;
  font-family: 'Fira Code', 'Courier New', monospace;
  font-size: 0.6rem; font-weight: 600; color: #93C5FD;
  backdrop-filter: blur(4px);
  letter-spacing: 0.06em;
  pointer-events: none;
}
.fg-blink { color: #4ADE80; animation: blkBlink 1.3s step-end infinite; }

/* Metadata tag */
.meta-tag {
  position: absolute; top: 10px; right: 10px; z-index: 5;
  font-family: 'Fira Code', 'Courier New', monospace;
  font-size: 0.58rem; color: rgba(148, 163, 184, 0.55);
  letter-spacing: 0.04em;
  pointer-events: none;
}

/* Floating glassmorphism chips */
.glass-chip {
  position: absolute; z-index: 10;
  display: flex; align-items: center; gap: 10px;
  padding: 11px 15px;
  background: rgba(15, 23, 42, 0.78);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 12px;
  backdrop-filter: blur(18px);
  box-shadow: 0 8px 32px rgba(0,0,0,0.4);
  min-width: 165px;
  animation: chipFloat 4s ease-in-out infinite alternate;
}
.chip-a { top: 14%; left: -38px; animation-delay: 0s; }
.chip-b { bottom: 17%; right: -32px; animation-delay: -2s; }
@keyframes chipFloat {
  0%   { transform: translateY(0); }
  100% { transform: translateY(-9px); }
}
.chip-ico { flex-shrink: 0; }
.chip-ico.emerald { color: #34D399; }
.chip-ico.amber   { color: #FBBF24; }
.chip-body { display: flex; flex-direction: column; }
.chip-title { font-size: 0.8rem; font-weight: 700; color: #E2E8F0; line-height: 1.2; }
.chip-sub   { font-size: 0.68rem; color: #64748B; margin-top: 2px; }

/* ── Scroll cue ─────────────────────────────────────── */
.scroll-cue {
  position: absolute; bottom: 1.5rem; left: 50%;
  transform: translateX(-50%);
  display: flex; flex-direction: column; align-items: center; gap: 4px;
  color: rgba(148, 163, 184, 0.45);
  font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.12em;
  opacity: 0;
  transition: opacity 0.7s ease 1.6s;
  z-index: 10;
}
.scroll-cue.visible { opacity: 1; }
.scroll-chevron { animation: scrollBounce 2s ease-in-out infinite; }
@keyframes scrollBounce {
  0%, 100% { transform: translateY(0); }
  50%       { transform: translateY(5px); }
}

/* ── Responsive ─────────────────────────────────────── */
@media (max-width: 1023px) {
  .ascii-box { display: none; }
  .hero-inner { padding: 4rem 1.5rem 5rem; }
  .chip-a { left: -18px; }
  .chip-b { right: -18px; }
}
@media (max-width: 639px) {
  .glass-chip { display: none; }
  .hero-right { padding: 20px; }
  .frame-box { width: min(300px, 80vw); }
  .hero-inner { padding: 3rem 1.25rem 4rem; }
}
</style>
