<script setup>
import format from '@/Composables/format.js';
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { quote } from '@composables/useQuote';
import { computed, onMounted, ref, watch } from 'vue';
import SvgIcon from '@/Components/Custom/SvgIcon.vue';

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
                acc.productsTotal += item.total;
            } else if (item.data?.type === 'addon_product') {
                acc.addonTotal += item.total;
            }

            if (item.price_plan?.code === 'MONTHLY') {
                acc.recurringTotal += item.total;
            } else {
                acc.oneOffTotal += item.total;
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
                <div class="d-flex">
                    <div class="flex-grow-1 mb-0 text-truncate w-100">
                        <p class="small fw-semibold product-name truncate-1-lines" :title="item.name">
                            {{ item.name }}
                        </p>
                        <div>
                            <p v-if="item.price_plan?.code === 'MONTHLY'" class="small contract-length">
                                {{ item.renewal_terms_value }} {{ format.friendlyString(item.renewal_terms_unit) }}
                            </p>
                        </div>
                    </div>
                    <div class="text-end px-1" style="width: 60px">
                        <p class="small product-quantity" v-if="item.quantity > 1">(x{{ `${item.quantity}` }})</p>
                    </div>
                    <div class="text-end" style="width: 100px">
                        <p class="small fw-semibold product-price">
                            {{ format.GBP(item.original_price * item.quantity) }}
                        </p>
                    </div>
                </div>

                <div
                    v-for="addon in quote?.items.filter(
                        (addon) => addon.data?.type === 'addon_product' && addon.group === item.group
                    )"
                    class="addons-wrapper ps-2 py-1 d-flex"
                >
                    <div class="flex-grow-1 mb-0 text-truncate w-100">
                        <p class="small product-name text-muted truncate-1-lines" :title="addon.name">
                            <font-awesome-icon
                                :icon="['fas', 'plus']"
                                :style="{ color: '#0000001A' }"
                                class="my-auto"
                            />
                            {{ addon.name }}
                        </p>
                        <div>
                            <p
                                v-if="addon.price_plan?.code === 'MONTHLY'"
                                class="small text-muted contract-length ps-3"
                            >
                                {{ addon.renewal_terms_value }} {{ format.friendlyString(addon.renewal_terms_unit) }}
                            </p>
                        </div>
                    </div>
                    <div class="text-end px-1" style="width: 60px">
                        <p class="small text-muted product-quantity" v-if="addon.quantity > 1">
                            (x{{ `${addon.quantity}` }})
                        </p>
                    </div>
                    <div class="text-end" style="width: 100px">
                        <p class="small text-muted product-price">
                            {{ format.GBP(addon.original_price * addon.quantity) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <hr />
        <div class="total">
            <div class="d-flex justify-content-between mb-2 sub-total-recurring">
                <p class="small product-name d-flex justify-content-center">
                    Recurring Total
                    <span
                        class="d-flex justify-content-center ms-1 mt-1"
                        title="Total before discounts, final amount may vary."
                        data-bs-original-title="Total before discounts, final amount may vary."
                        v-tooltip
                    >
                        <SvgIcon width="16" height="16" name="info" />
                    </span>
                </p>
                <p class="small product-price ps-2 text-right">{{ format.GBP(recurringTotal) }}</p>
            </div>
            <div class="d-flex justify-content-between mb-2 sub-total-one-off">
                <p class="small product-name d-flex justify-content-center">
                    One-Off Total
                    <span
                        class="d-flex justify-content-center ms-1 mt-1"
                        title="Total before discounts, final amount may vary."
                        data-bs-original-title="Total before discounts, final amount may vary."
                        v-tooltip
                    >
                        <SvgIcon width="16" height="16" name="info" />
                    </span>
                </p>
                <p class="small product-price ps-2 text-right">{{ format.GBP(oneOffTotal) }}</p>
            </div>
            <div class="d-flex justify-content-between">
                <p class="small product-name d-flex justify-content-center">
                    <span class="fw-semibold">Total </span>
                    <span
                        class="d-flex justify-content-center ms-1 mt-1"
                        title="Total before discounts, final amount may vary."
                        data-bs-original-title="Total before discounts, final amount may vary."
                        v-tooltip
                    >
                        <SvgIcon width="16" height="16" name="info" />
                    </span>
                </p>
                <p class="small fw-semibold product-price ps-2 text-right">{{ format.GBP(quoteTotal) }}</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.quote-items {
    max-height: 31vh;
    overflow-y: auto;
}

.quote-items .truncate-1-lines {
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
