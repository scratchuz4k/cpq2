<script setup>
import format from '@/Composables/format.js';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { quote } from '@composables/useQuote';
</script>

<template>
    <div class="col-3">
        <div class="price-mobile-sidebar commission-calculator px-4 pt-4 pb-2 mb-3">
            <h3 class="mb-3">Order summary</h3>
            <div class="quote-items">
                <div
                    v-for="item in quote?.items.filter((item) => item.data?.type === 'main_product')"
                    class="product mb-3"
                >
                    <div class="d-flex justify-content-between mb-1">
                        <p class="small fw-semibold product-name">{{ item.name }}</p>
                        <p class="small fw-semibold product-price text-right">
                            {{ format.GBP(item.original_price * item.quantity) }}
                        </p>
                    </div>
                    <div
                        v-for="addon in quote?.items.filter(
                            (addon) => addon.data?.type === 'addon_product' && addon.group === item.group
                        )"
                        class="addons-wrapper ps-2 py-1"
                    >
                        <div class="addon d-flex text-muted justify-content-between">
                            <p class="small product-name ps-1">
                                <font-awesome-icon
                                    :icon="['fas', 'plus']"
                                    :style="{ color: '#0000001A' }"
                                    class="my-auto"
                                />
                                {{ addon.name }}
                            </p>
                            <p class="small product-price text-right">
                                {{ format.GBP(addon.original_price * addon.quantity) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <hr />
            <div class="subtotals text-muted">
                <div class="subtotal main-products d-flex justify-content-between mb-2">
                    <p class="small">
                        Products ({{ quote?.items.filter((item) => item.data?.type === 'main_product').length }})
                    </p>
                    <p class="small product-price text-right">{{ format.GBP(productsTotal) }}</p>
                </div>
                <div class="subtotal addon-products d-flex justify-content-between mb-2">
                    <p class="small">
                        Add-ons ({{ quote?.items.filter((item) => item.data?.type === 'addon_product').length }})
                    </p>
                    <p class="small product-price text-right">{{ format.GBP(addonTotal) }}</p>
                </div>
            </div>
            <hr />
            <div class="total">
                <div class="d-flex justify-content-between mb-2">
                    <p class="small fw-semibold">Total</p>
                    <p class="small fw-semibold product-price text-right">{{ format.GBP(quote.total) }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.quote-items {
    max-height: 10vh;
    overflow-y: auto;

    .addons-wrapper {
        border-left: 2px solid #0000001a;
    }
}
hr {
    border-top: 1px solid var(--color-black);
    opacity: 0.1;
}
</style>
