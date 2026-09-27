<template>
  <section class="showcase-section">
    <!-- Subtle top separator glow -->
    <div class="section-glow" aria-hidden="true"></div>
    <!-- Dot grid -->
    <div class="section-dots" aria-hidden="true"></div>

    <div class="showcase-inner">

      <!-- Header -->
      <div class="showcase-header">
        <div>
          <span class="showcase-eyebrow">Sélection Premium</span>
          <h2 class="showcase-title">Articles Phares</h2>
        </div>
        <a :href="productsUrl" class="see-all-link">
          Voir tout
          <svg class="see-all-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="16" height="16">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
          </svg>
        </a>
      </div>

      <!-- Product grid -->
      <div class="product-grid">
        <div
          v-for="(product, index) in products"
          :key="product.id"
          class="product-card"
          :style="{ animationDelay: `${index * 110}ms` }"
        >
          <!-- Image area -->
          <div class="card-img-wrap">
            <!-- Featured badge -->
            <span class="feat-badge">
              <svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
              Coup de cœur
            </span>
            <img
              v-if="product.image_url"
              :src="product.image_url"
              :alt="product.name"
              class="card-img"
              loading="lazy"
              decoding="async"
            />
            <div v-else class="card-img-placeholder" aria-hidden="true">
              <svg class="placeholder-icon" fill="none" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                <path d="M7 2L2 7l3 3 2-2v12h10V8l2 2 3-3-5-5c-1 2.5-4 2.5-5 2.5S8 4.5 7 2z"/>
              </svg>
            </div>
            <!-- Scan sweep on hover -->
            <div class="card-sweep" aria-hidden="true"></div>
          </div>

          <!-- Card body -->
          <div class="card-body">
            <div class="card-meta">{{ product.category_name || 'Catégorie' }}</div>
            <h3 class="card-name">{{ product.name }}</h3>

            <div class="card-footer-row">
              <span class="card-price">{{ product.formatted_price }}</span>

              <form method="POST" :action="addToCartUrl" class="cart-form">
                <input type="hidden" name="_token" :value="csrfToken">
                <input type="hidden" name="product_id" :value="product.id">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="cart-btn" title="Ajouter au panier" aria-label="Ajouter au panier">
                  <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                  </svg>
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>
</template>

<script setup>
defineProps({
  products:     { type: Array,  required: true },
  productsUrl:  { type: String, required: true },
  addToCartUrl: { type: String, required: true },
  csrfToken:    { type: String, required: true },
});
</script>

<style scoped>
/* ── Section wrapper ──────────────────────────────────────── */
.showcase-section {
  position: relative;
  background: #050A14;
  padding: 6rem 0 7rem;
  overflow: hidden;
}

.section-glow {
  position: absolute;
  top: 0; left: 0; right: 0; height: 1px;
  background: linear-gradient(90deg,
    transparent 0%,
    rgba(59,130,246,0.35) 30%,
    rgba(139,92,246,0.35) 70%,
    transparent 100%);
  pointer-events: none;
}

.section-dots {
  position: absolute; inset: 0;
  background-image: radial-gradient(rgba(99,102,241,0.14) 1px, transparent 1px);
  background-size: 32px 32px;
  pointer-events: none;
}

/* ── Inner container ──────────────────────────────────────── */
.showcase-inner {
  position: relative; z-index: 1;
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 1.5rem;
}

/* ── Header row ───────────────────────────────────────────── */
.showcase-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 3rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.showcase-eyebrow {
  display: block;
  font-size: 0.72rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.14em;
  color: #60A5FA;
  margin-bottom: 0.45rem;
}

.showcase-title {
  font-size: clamp(1.9rem, 3.5vw, 2.7rem);
  font-weight: 800;
  letter-spacing: -0.03em;
  color: #E2E8F0;
  line-height: 1.1;
  margin: 0;
}

.see-all-link {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  font-size: 0.88rem;
  font-weight: 700;
  color: #60A5FA;
  text-decoration: none;
  padding: 8px 18px;
  border: 1px solid rgba(59,130,246,0.25);
  border-radius: 99px;
  background: rgba(59,130,246,0.06);
  transition: background 0.2s, border-color 0.2s, box-shadow 0.2s;
  white-space: nowrap;
}
.see-all-link:hover {
  background: rgba(59,130,246,0.14);
  border-color: rgba(59,130,246,0.45);
  box-shadow: 0 0 18px rgba(59,130,246,0.2);
  color: #93C5FD;
  text-decoration: none;
}
.see-all-arrow {
  transition: transform 0.22s;
  flex-shrink: 0;
}
.see-all-link:hover .see-all-arrow { transform: translateX(3px); }

/* ── Product grid ─────────────────────────────────────────── */
.product-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 1.5rem;
}
@media (min-width: 640px)  { .product-grid { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1024px) { .product-grid { grid-template-columns: repeat(4, 1fr); } }

/* ── Product card ─────────────────────────────────────────── */
.product-card {
  background: rgba(15, 23, 42, 0.72);
  border: 1px solid rgba(255,255,255,0.07);
  border-radius: 1.1rem;
  overflow: hidden;
  backdrop-filter: blur(12px);
  transition: border-color 0.3s, box-shadow 0.3s, transform 0.3s;
  animation: cardReveal 0.6s ease both;
  will-change: transform;
}
.product-card:hover {
  border-color: rgba(59,130,246,0.35);
  box-shadow:
    0 0 28px rgba(59,130,246,0.12),
    0 20px 48px rgba(0,0,0,0.55);
  transform: translateY(-5px);
}
@keyframes cardReveal {
  from { opacity: 0; transform: translateY(22px); }
  to   { opacity: 1; transform: none; }
}

/* ── Image wrapper ────────────────────────────────────────── */
.card-img-wrap {
  position: relative;
  height: 240px;
  background: rgba(5,10,20,0.8);
  overflow: hidden;
}

.feat-badge {
  position: absolute;
  top: 12px; left: 12px; z-index: 3;
  display: inline-flex; align-items: center; gap: 5px;
  padding: 4px 10px;
  background: rgba(139,92,246,0.22);
  border: 1px solid rgba(139,92,246,0.35);
  border-radius: 99px;
  font-size: 0.66rem; font-weight: 700;
  color: #C4B5FD;
  backdrop-filter: blur(6px);
}

.card-img {
  width: 100%; height: 100%;
  object-fit: cover;
  filter: brightness(0.82) saturate(0.9);
  transition: transform 0.45s ease, filter 0.3s;
}
.product-card:hover .card-img {
  transform: scale(1.05);
  filter: brightness(0.9) saturate(1.0);
}

.card-img-placeholder {
  display: flex; align-items: center; justify-content: center;
  width: 100%; height: 100%;
  background: linear-gradient(145deg, #0A1220 0%, #111827 100%);
}
.placeholder-icon {
  width: 72px; height: 72px;
  color: rgba(148,163,184,0.25);
  transition: color 0.3s, transform 0.3s;
}
.product-card:hover .placeholder-icon {
  color: rgba(148,163,184,0.45);
  transform: scale(1.08);
}

/* Sweep line on hover */
.card-sweep {
  position: absolute; left: 0; right: 0; height: 2px; top: -4px; z-index: 2;
  background: linear-gradient(90deg, transparent, rgba(59,130,246,0.55), transparent);
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.2s;
}
.product-card:hover .card-sweep {
  opacity: 1;
  animation: sweepOnce 0.7s linear;
}
@keyframes sweepOnce {
  0%   { top: -4px; opacity: 0.9; }
  100% { top: calc(100% + 4px); opacity: 0.2; }
}

/* ── Card body ────────────────────────────────────────────── */
.card-body {
  padding: 1.15rem 1.2rem 1.2rem;
}
.card-meta {
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.1em;
  color: #475569;
  margin-bottom: 0.3rem;
}
.card-name {
  font-size: 1rem;
  font-weight: 700;
  color: #E2E8F0;
  margin: 0 0 1rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.3;
}
.card-footer-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 0.5rem;
}
.card-price {
  font-size: 1.1rem;
  font-weight: 800;
  color: #F1F5F9;
  letter-spacing: -0.01em;
}

/* ── Cart button ──────────────────────────────────────────── */
.cart-form { margin: 0; }
.cart-btn {
  display: inline-flex; align-items: center; justify-content: center;
  width: 36px; height: 36px; border-radius: 50%;
  background: rgba(59,130,246,0.1);
  border: 1px solid rgba(59,130,246,0.22);
  color: #60A5FA;
  cursor: pointer;
  transition: background 0.2s, border-color 0.2s, box-shadow 0.2s, transform 0.15s;
  flex-shrink: 0;
  outline: none;
}
.cart-btn:hover {
  background: rgba(59,130,246,0.22);
  border-color: rgba(59,130,246,0.5);
  box-shadow: 0 0 16px rgba(59,130,246,0.3);
  transform: scale(1.08);
  color: #93C5FD;
}
.cart-btn:focus-visible {
  box-shadow: 0 0 0 3px rgba(59,130,246,0.35);
}
</style>
