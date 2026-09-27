<template>
    <div class="qty-form justify-content-center d-flex align-items-center gap-2">
        <input
            type="number"
            v-model.number="qty"
            min="1"
            max="99"
            class="qty-input form-control text-center"
            :aria-label="`Quantité pour ${productName}`"
            style="width:70px;"
        >
        <button
            @click="update"
            :disabled="loading"
            class="btn btn-sm btn-outline-primary"
            title="Mettre à jour"
        >
            <i :class="loading ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-rotate'"></i>
        </button>
        <button
            @click="remove"
            :disabled="loading"
            class="btn btn-sm btn-link text-danger p-0"
            title="Retirer du panier"
        >
            <i class="fa-solid fa-trash-can"></i>
        </button>
    </div>
</template>

<script setup>
import { ref, getCurrentInstance, onMounted } from 'vue';

const props = defineProps({
    itemId:         { type: Number, required: true },
    quantity:       { type: Number, required: true },
    updateUrl:      { type: String, required: true },
    removeUrl:      { type: String, required: true },
    unitPriceCents: { type: Number, required: true },
    currency:       { type: String, default: 'EUR' },
    productName:    { type: String, default: '' },
});

const qty     = ref(props.quantity);
const loading = ref(false);

let mountEl = null;

onMounted(() => {
    mountEl = getCurrentInstance().vnode.el;

    // Hide the static fallback delete column once Vue takes over
    const row = mountEl.closest('tr');
    if (row) {
        const deleteFallback = row.querySelector('[data-delete-fallback]');
        if (deleteFallback) deleteFallback.style.display = 'none';
    }
});

async function update() {
    loading.value = true;

    try {
        const res = await window.axios.patch(
            props.updateUrl,
            { quantity: qty.value },
            { headers: { Accept: 'application/json' } },
        );

        // Update the row's subtotal cell
        const row = mountEl?.closest('tr');
        if (row) {
            const subtotalEl = row.querySelector('[data-subtotal]');
            if (subtotalEl) {
                subtotalEl.textContent = formatPrice(props.unitPriceCents * qty.value, props.currency);
            }
            row.dataset.subtotalCents = props.unitPriceCents * qty.value;
            row.dataset.itemQty       = qty.value;
        }

        updateCartBadges(res.data.cart_count);
        refreshSummaryTotals();
    } catch (err) {
        console.error('Cart update error:', err);
    } finally {
        loading.value = false;
    }
}

async function remove() {
    loading.value = true;

    try {
        const res = await window.axios.delete(
            props.removeUrl,
            { headers: { Accept: 'application/json' } },
        );

        const row = mountEl?.closest('tr');
        if (row) row.remove();

        updateCartBadges(res.data.cart_count);
        refreshSummaryTotals();
    } catch (err) {
        console.error('Cart remove error:', err);
    } finally {
        loading.value = false;
    }
}

function formatPrice(cents, currency) {
    return (cents / 100).toFixed(2).replace('.', ',') + '\u00a0' + currency;
}

function updateCartBadges(count) {
    document.querySelectorAll('[data-cart-count]').forEach(el => {
        el.textContent = count;
    });
}

function refreshSummaryTotals() {
    let totalCents = 0;
    let totalQty   = 0;

    document.querySelectorAll('[data-cart-row]').forEach(row => {
        totalCents += parseInt(row.dataset.subtotalCents || '0');
        totalQty   += parseInt(row.dataset.itemQty       || '0');
    });

    const totalEl = document.getElementById('cart-total-amount');
    if (totalEl) totalEl.textContent = formatPrice(totalCents, props.currency);

    const countEl = document.getElementById('cart-items-count');
    if (countEl) countEl.textContent = `Articles (${totalQty})`;
}
</script>
