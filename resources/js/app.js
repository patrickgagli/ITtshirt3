import './bootstrap';
import { createApp } from 'vue';

import AddToCartButton  from './components/AddToCartButton.vue';
import WishlistToggle   from './components/WishlistToggle.vue';
import CartItemControl  from './components/CartItemControl.vue';
import CreativeHero     from './components/CreativeHero.vue';
import DynamicProductShowcase from './components/DynamicProductShowcase.vue';
import SalesChart            from './components/admin/SalesChart.vue';
import InventoryChart        from './components/admin/InventoryChart.vue';
import TopCategoriesChart    from './components/admin/TopCategoriesChart.vue';
import CustomerInsightsChart from './components/admin/CustomerInsightsChart.vue';
import ContactRequestsChart  from './components/admin/ContactRequestsChart.vue';
import AmbientBackground     from './components/ui/AmbientBackground.vue';

/**
 * Modern UI System Initialization
 */

// 1. Ambient Background Injection
const bgContainer = document.createElement('div');
bgContainer.id = 'ambient-background-container';
document.body.prepend(bgContainer);
createApp(AmbientBackground).mount(bgContainer);

// 2. Global Scroll Reveal Observer
const revealObserver = new IntersectionObserver((entries, observer) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('is-revealed');
            observer.unobserve(entry.target); // Run once
        }
    });
}, { root: null, rootMargin: '0px', threshold: 0.1 });

// Allow a slight delay for Vue islands to render before observing
setTimeout(() => {
    document.querySelectorAll('.reveal-on-scroll').forEach(el => {
        revealObserver.observe(el);
    });
}, 100);

/**
 * Island mounting: each [data-vue="component-name"] element gets its own
 * Vue application instance.  Props are read from the element's data-props
 * attribute as a JSON object encoded by Blade.
 */
const registry = {
    'add-to-cart':      AddToCartButton,
    'wishlist-toggle':  WishlistToggle,
    'cart-item-control': CartItemControl,
    'creative-hero':    CreativeHero,
    'dynamic-product-showcase': DynamicProductShowcase,
    'admin-sales-chart':             SalesChart,
    'admin-inventory-chart':         InventoryChart,
    'admin-top-categories-chart':    TopCategoriesChart,
    'admin-customer-insights-chart': CustomerInsightsChart,
    'admin-contact-requests-chart':  ContactRequestsChart,
};

document.querySelectorAll('[data-vue]').forEach(el => {
    const Component = registry[el.dataset.vue];
    if (!Component) return;

    const props = JSON.parse(el.dataset.props || '{}');
    createApp(Component, props).mount(el);
});
