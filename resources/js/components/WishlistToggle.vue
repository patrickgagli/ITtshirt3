<template>
    <div class="mt-3">
        <template v-if="authenticated">
            <button
                @click="toggle"
                :disabled="loading"
                :class="['btn', 'px-4', 'py-3', 'rounded-3',
                         inWishlistState ? 'btn-outline-danger' : 'btn-outline-secondary']"
            >
                <i :class="['me-2', inWishlistState ? 'fa-solid fa-heart' : 'fa-regular fa-heart']"></i>
                <span v-if="inWishlistState">Retirer de ma liste de souhaits</span>
                <span v-else>Ajouter à ma liste de souhaits</span>
            </button>
        </template>
        <template v-else>
            <a :href="loginUrl" class="btn btn-outline-secondary px-4 py-3 rounded-3">
                <i class="fa-regular fa-heart me-2"></i>Ajouter à ma liste de souhaits
            </a>
        </template>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
    productId:      { type: Number,  required: true },
    inWishlist:     { type: Boolean, default: false },
    wishlistItemId: { type: Number,  default: null },
    authenticated:  { type: Boolean, default: false },
    storeUrl:       { type: String,  required: true },
    destroyBaseUrl: { type: String,  required: true },
    loginUrl:       { type: String,  default: '/login' },
});

const inWishlistState  = ref(props.inWishlist);
const currentItemId    = ref(props.wishlistItemId);
const loading          = ref(false);

async function toggle() {
    loading.value = true;

    try {
        if (inWishlistState.value) {
            const res = await window.axios.delete(
                `${props.destroyBaseUrl}/${currentItemId.value}`,
                { headers: { Accept: 'application/json' } },
            );
            inWishlistState.value = false;
            currentItemId.value   = null;
            updateWishlistBadges(res.data.wishlist_count);
        } else {
            const res = await window.axios.post(
                props.storeUrl,
                { product_id: props.productId },
                { headers: { Accept: 'application/json' } },
            );
            inWishlistState.value = true;
            currentItemId.value   = res.data.wishlist_item_id;
            updateWishlistBadges(res.data.wishlist_count);
        }
    } catch (err) {
        console.error('Wishlist toggle error:', err);
    } finally {
        loading.value = false;
    }
}

function updateWishlistBadges(count) {
    document.querySelectorAll('[data-wishlist-count]').forEach(el => {
        el.textContent = count;
    });
}
</script>
