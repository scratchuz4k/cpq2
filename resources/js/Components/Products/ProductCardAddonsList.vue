<script setup>
import { ref, watch, onMounted, onBeforeUnmount, computed } from 'vue';
import _ from 'lodash';
import { Api, FilterOperators } from '@/Composables/useApi';
import InputQuantity from '@/Components/InputQuantity.vue';
import { quote, getQuote } from '@/Composables/useQuote';
import { updateQuoteItem, removeQuoteItem, createQuoteItem } from '@/Composables/useQuoteItem';

const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    }
});

const visible = ref(false);
const searchQuery = ref('');
const sentinel = ref(null);

const api = new Api('product-addons');
api.meta.limit = 10;
api.meta.total = props.modelValue.addons?.length || 0;

const addons = ref([]);
const addonQuantities = ref({});

const hasMore = computed(() => addons.value.length < api.meta.total);

const selectedCount = computed(() => {
    return Object.values(addonQuantities.value).filter((q) => q > 0).length;
});

const appendPage = (items) => {
    const parentItem = quote.items?.find(
        (x) => x.product_code == props.modelValue.sku && x.data?.type == 'main_product'
    );
    items.forEach((item) => {
        addons.value.push(item);
        const addonId = item.addon.id;
        if (!(addonId in addonQuantities.value)) {
            const quoteItem = quote.items?.find(
                (x) =>
                    x.product_code == item.addon.sku && x.data?.type == 'addon_product' && x.group == parentItem?.group
            );
            addonQuantities.value[addonId] = quoteItem ? quoteItem.quantity : 0;
        }
    });
};

const loadNextPage = async () => {
    if (api.isLoading.value || !hasMore.value) return;
    await api.getData();
    appendPage(api.items.value);
    api.meta.page++;
};

const resetAndLoad = async () => {
    api.meta.page = 1;
    addons.value = [];
    api.clearFilters();
    api.setColumns(['addon.name', 'addon.sku']);
    api.addFilter('product_id', FilterOperators.EQUALS, props.modelValue.id);
    api.setSearch(searchQuery.value || null);
    await api.getData();
    appendPage(api.items.value);
    api.meta.page = 2;
};

const debouncedSearch = _.debounce(resetAndLoad, 400);

watch(searchQuery, () => {
    debouncedSearch();
});

let observer = null;

const setupObserver = () => {
    if (observer) observer.disconnect();
    observer = new IntersectionObserver(
        (entries) => {
            if (entries[0].isIntersecting) loadNextPage();
        },
        { threshold: 0.1 }
    );
    if (sentinel.value) observer.observe(sentinel.value);
};

watch(sentinel, (el) => {
    if (el) setupObserver();
});

onMounted(async () => {
    await resetAndLoad();
    setupObserver();
});

onBeforeUnmount(() => {
    if (observer) observer.disconnect();
});

const checkQuantity = _.debounce(async (addon, val) => {
    let quantity = val;

    if (quantity > addon.stock) {
        notyf.open({
            type: 'warning',
            message: 'You can only add up to the number of units in stock. The quantity is automatically adjusted'
        });
        quantity = addon.stock;
    }

    addonQuantities.value[addon.id] = quantity;

    const parentItem = quote.items?.find(
        (x) => x.product_code == props.modelValue.sku && x.data?.type == 'main_product'
    );
    const quoteItem = quote.items?.find(
        (x) => x.product_code == addon.sku && x.data?.type == 'addon_product' && x.group == parentItem?.group
    );

    if (!quoteItem) {
        if (quantity > 0) {
            if (!hasParentSelected()) {
                notyf.open({
                    type: 'warning',
                    message: 'You need to add the main product to the quote before adding add-ons'
                });
                addonQuantities.value[addon.id] = 0;
                return;
            }

            await createQuoteItem({
                quote_id: quote.id,
                product_code: addon.sku,
                product_id: addon.id,
                quantity: quantity,
                contract_length: addon.contract_length,
                name: addon.name,
                raw_price: addon.price ? addon.price.net : 0,
                price: addon.price ? addon.price.net_spa : 0,
                price_plan_id: addon.price_plan_id,
                group: parentItem?.group ?? null,
                data: { type: 'addon_product' }
            });
        }
    } else {
        if (quantity === 0) {
            await removeQuoteItem(quoteItem.id);
        } else {
            await updateQuoteItem(quoteItem.id, { quantity });
        }
    }

    await getQuote(quote.id);
}, 500);

const hasParentSelected = () => {
    let code = props.modelValue.sku ?? props.modelValue.code;
    return quote.items?.some((x) => x.product_code == code && x.data?.type === 'main_product' && x.quantity > 0);
};

watch(
    () => quote.items,
    async () => {
        if (hasParentSelected()) {
            visible.value = true;
        } else {
            visible.value = false;
        }
        addonQuantities.value = [];
        let code = props.modelValue.sku ?? props.modelValue.code;
        let group = quote.items?.find((x) => x.product_code == code && x.data?.type === 'main_product')?.group;
        quote.items
            ?.filter((x) => x.data?.type === 'addon_product' && x.group == group)
            .forEach((item) => {
                const addon = addons.value.find((a) => a.addon.sku === item.product_code);
                if (addon) {
                    addonQuantities.value[addon.addon.id] = item.quantity;
                }
            });
    },
    { deep: true }
);
onMounted(async () => {
    if (hasParentSelected()) {
        visible.value = true;
    }
});
</script>

<template>
    <div>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <p class="small text-muted mb-0">
                This product has {{ api.meta.total }} add-on<span v-if="api.meta.total !== 1">s</span>
                available
            </p>
            <span v-if="selectedCount > 0" class="badge bg-secondary">{{ selectedCount }} selected</span>
        </div>

        <template v-if="visible">
            <div class="mb-2">
                <div class="input-group input-group-sm">
                    <input v-model="searchQuery" type="text" class="form-control" placeholder="search by add on" />
                    <span class="input-group-text">
                        <font-awesome-icon :icon="['fas', 'magnifying-glass']" />
                    </span>
                </div>
            </div>

            <div v-if="api.isLoading.value && !addons.length" class="text-center py-3">
                <font-awesome-icon :icon="['fas', 'spinner']" spin />
            </div>

            <template v-else>
                <div class="addon-list">
                    <div
                        v-for="item in addons"
                        :key="item.id"
                        class="d-flex align-items-center justify-content-between py-2 border-bottom"
                    >
                        <p class="mb-0 me-3 flex-grow-1 small fw-semibold">{{ item.addon.name }}</p>

                        <span
                            class="badge me-3 text-nowrap addon-stock-badge"
                            :class="item.addon.stock > 0 ? 'addon-stock-badge' : 'bg-danger'"
                        >
                            {{ item.addon.stock > 0 ? item.addon.stock + ' in stock' : 'Out of stock' }}
                        </span>

                        <div style="width: 140px" class="me-3 flex-shrink-0">
                            <InputQuantity
                                :id="'addon-' + item.id"
                                :modelValue="addonQuantities[item.addon.id] ?? 0"
                                :disabled="item.addon.stock <= 0"
                                @change="(val) => checkQuantity(item.addon, val)"
                                @update:modelValue="(val) => (addonQuantities[item.addon.id] = val)"
                            />
                        </div>

                        <p class="mb-0 text-nowrap small fw-semibold text-end" style="width: 60px">
                            {{ format.GBP(item.addon.price?.net_spa || 0) }}
                        </p>
                    </div>

                    <div v-if="!addons.length" class="text-center py-2">
                        <p class="small text-muted mb-0">No add-ons found</p>
                    </div>

                    <div ref="sentinel" class="py-1">
                        <div v-if="api.isLoading.value && addons.length" class="text-center py-2">
                            <font-awesome-icon :icon="['fas', 'spinner']" spin />
                        </div>
                    </div>
                </div>
            </template>

            <a
                class="small fw-bold mt-2 d-inline-block"
                style="text-decoration: none; cursor: pointer"
                @click="visible = false"
            >
                Hide Add-ons
            </a>
        </template>

        <a
            v-else
            class="small fw-bold mt-2 d-inline-block"
            style="text-decoration: none; cursor: pointer"
            @click="visible = true"
        >
            Show Add-ons
        </a>
    </div>
</template>

<style scoped>
.addon-stock-badge {
    background-color: #d4edda;
    color: #155724;
}

.addon-list {
    max-height: calc(4 * 48px);
    overflow-y: auto;
}
</style>
