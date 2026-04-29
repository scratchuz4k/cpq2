<script setup>
import format from '@/Composables/format.js';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { quote } from '@composables/useQuote';
import { computed, onMounted, ref, watch } from 'vue';

const props = defineProps({
    modelValue: Object
});

const productsTotal = ref(0);
const addonTotal = ref(0);
const recurringTotal = ref(0);
const oneOffTotal = ref(0);
const quoteTotal = ref(0);

const totals = () => {
    const {
        productsTotal: products,
        addonTotal: addons,
        recurringTotal: recurringItems,
        oneOffTotal: oneOffItems
    } = quote?.items.reduce(
        (acc, item) => {
            if (item.data?.type === 'main_product') {
                acc.productsTotal += item.original_price * item.contract_length * item.quantity;
            } else if (item.data?.type === 'addon_product') {
                acc.addonTotal += item.original_price * item.contract_length * item.quantity;
            }

            if (item.price_plan?.code === 'MONTHLY') {
                acc.recurringTotal += item.original_price * item.contract_length * item.quantity;
            } else {
                acc.oneOffTotal += item.original_price * item.contract_length * item.quantity;
            }
            return acc;
        },
        {
            productsTotal: 0,
            addonTotal: 0,
            recurringTotal: 0,
            oneOffTotal: 0
        }
    );

    quoteTotal.value = recurringItems + oneOffItems;
    productsTotal.value = products;
    addonTotal.value = addons;
    recurringTotal.value = recurringItems;
    oneOffTotal.value = oneOffItems;
};

onMounted(() => {
    totals();
});

watch(
    () => [quote.items],
    async () => {
        totals();
    }
);
</script>

<template>
    <div class="order-summary-sidebar bg-white rounded p-4">
        <h3>Order summary</h3>
        <div class="quote-items mt-3" v-if="quote?.items?.length">
            <div
                v-for="item in quote?.items.filter((item) => item.data?.type === 'main_product')"
                class="products-wrapper mb-2"
            >
                <div class="d-flex justify-content-between mb-0">
                    <span class="d-flex">
                        <p class="small fw-semibold product-name truncate-2-lines">
                            {{ item.name }}
                        </p>
                        <p class="small product-quantity ps-1" v-if="item.quantity > 1">(x{{ `${item.quantity}` }})</p>
                    </span>
                    <p class="small fw-semibold product-price ps-2 text-right">
                        {{ format.GBP(item.original_price * item.quantity) }}
                    </p>
                </div>
                <div>
                    <p v-if="item.price_plan?.code === 'MONTHLY'" class="small contract-length">
                        {{ item.contract_length }} months
                    </p>
                </div>
                <div
                    v-for="addon in quote?.items.filter(
                        (addon) => addon.data?.type === 'addon_product' && addon.group === item.group
                    )"
                    class="addons-wrapper ps-2 py-1"
                >
                    <div class="addon d-flex text-muted justify-content-between">
                        <span class="d-flex">
                            <p class="small product-name truncate-2-lines">
                                <font-awesome-icon
                                    :icon="['fas', 'plus']"
                                    :style="{ color: '#0000001A' }"
                                    class="my-auto"
                                />
                                {{ addon.name }}
                            </p>
                            <p class="small product-quantity ps-1" v-if="addon.quantity > 1">
                                (x{{ `${addon.quantity}` }})
                            </p>
                        </span>
                        <p class="small product-price ps-2 text-right">
                            {{ format.GBP(addon.original_price * addon.quantity) }}
                        </p>
                    </div>
                    <div>
                        <p v-if="addon.price_plan?.code === 'MONTHLY'" class="small text-muted contract-length ps-3">
                            {{ addon.contract_length }} months
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <hr />
        <div class="subtotals text-muted">
            <div class="subtotal main-products d-flex justify-content-between mb-2">
                <p class="small">
                    Products ({{
                        quote?.items
                            .filter((item) => item.data?.type === 'main_product')
                            .reduce((total, item) => total + (item.quantity || 0), 0)
                    }})
                </p>
                <p class="small product-price ps-2 text-right">{{ format.GBP(productsTotal) }}</p>
            </div>
            <div class="subtotal addon-products d-flex justify-content-between">
                <p class="small">
                    Add-ons ({{
                        quote?.items
                            .filter((item) => item.data?.type === 'addon_product')
                            .reduce((total, item) => total + (item.quantity || 0), 0)
                    }})
                </p>
                <p class="small product-price ps-2 text-right">{{ format.GBP(addonTotal) }}</p>
            </div>
        </div>
        <hr />
        <div class="total">
            <div class="d-flex justify-content-between mb-2 sub-total-recurring">
                <p class="small product-name">
                    Recurring Total
                    <tooltip-with-icon
                        class="ms-1 mt-1"
                        :info_text="'Total before discounts, final amount may vary.'"
                    />
                </p>
                <p class="small product-price ps-2 text-right">{{ format.GBP(recurringTotal) }}</p>
            </div>
            <div class="d-flex justify-content-between mb-2 sub-total-one-off">
                <p class="small product-name">
                    One Off Total
                    <tooltip-with-icon
                        class="ms-1 mt-1"
                        :info_text="'Total before discounts, final amount may vary.'"
                    />
                </p>
                <p class="small product-price ps-2 text-right">{{ format.GBP(oneOffTotal) }}</p>
            </div>
            <div class="d-flex justify-content-between">
                <p class="small product-name">
                    <span class="fw-semibold">Total </span>
                    <tooltip-with-icon
                        class="ms-1 mt-1"
                        :info_text="'Total before discounts, final amount may vary.'"
                    />
                </p>
                <p class="small fw-semibold product-price ps-2 text-right">{{ format.GBP(quoteTotal) }}</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.quote-items {
    max-height: 30vh;
    overflow-y: auto;
}

.quote-items .truncate-2-lines {
    height: auto;
}
.quote-items .addons-wrapper {
    border-left: 2px solid #e5e5e5;
}

.order-summary-sidebar {
    position: sticky;
    top: 1rem;
}
hr {
    border-top: 1px solid var(--color-black);
    opacity: 0.1;
}
</style>
