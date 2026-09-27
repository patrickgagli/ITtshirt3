<template>
    <div>
        <div class="d-flex gap-2 flex-wrap align-items-center">
            <div class="input-group" style="width:110px;">
                <input
                    type="number"
                    v-model.number="quantity"
                    min="1"
                    max="99"
                    class="form-control form-control-lg text-center"
                    aria-label="Quantité"
                >
            </div>
            <button
                @click="addToCart"
                :disabled="loading"
                class="btn btn-primary btn-lg px-5 py-3 rounded-3 shadow"
            >
                <span v-if="loading">
                    <i class="fa-solid fa-spinner fa-spin me-2"></i>Ajout en cours…
                </span>
                <span v-else-if="added">
                    <i class="fa-solid fa-check me-2"></i>Ajouté au panier !
                </span>
                <span v-else>
                    <i class="fa-solid fa-cart-plus me-2"></i>Ajouter au panier
                </span>
            </button>
        </div>
        <p v-if="error" class="text-danger mt-2 mb-0 small">{{ error }}</p>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
    productId:     { type: Number,  required: true },
    authenticated: { type: Boolean, default: false }, // kept for backward-compat; no longer gates the button
    storeUrl:      { type: String,  required: true },
    loginUrl:      { type: String,  default: '/login' },
});

const quantity = ref(1);
const loading  = ref(false);
const added    = ref(false);
const error    = ref('');

async function addToCart() {
    loading.value = true;
    error.value   = '';
    added.value   = false;

    try {
        const res = await window.axios.post(
            props.storeUrl,
            { product_id: props.productId, quantity: quantity.value },
            { headers: { Accept: 'application/json' } },
        );

        added.value = true;
        updateCartBadges(res.data.cart_count);
        setTimeout(() => { added.value = false; }, 3000);
    } catch (err) {
        error.value = err.response?.data?.message ?? "Erreur lors de l'ajout au panier.";
    } finally {
        loading.value = false;
    }
}

function updateCartBadges(count) {
    document.querySelectorAll('[data-cart-count]').forEach(el => {
        el.textContent = count;
    });
}
</script>
