<script setup>
import { ref, watch, onMounted, onBeforeUnmount, computed } from 'vue';
import _ from 'lodash';
import { Api, FilterOperators } from '@/Composables/useApi';
import { quote, getQuote } from '@/Composables/useQuote';
import { updateQuoteItem, removeQuoteItem, createQuoteItem } from '@/Composables/useQuoteItem';
import { isDisabled } from '@/Composables/useQuoteSteps';

const props = defineProps({
    modelValue: {
        type: Object,
        required: true
    }
});

const isBlocked = ref(false);
const visible = ref(false);
const searchQuery = ref('');
const sentinel = ref(null);
const loaded = ref(false);

const api = new Api('product-addons');
api.meta.limit = 10;
api.meta.total = props.modelValue.addons?.length || 0;

const addons = ref([]);

const hasMore = computed(() => addons.value.length < api.meta.total);

const selectedCount = computed(() => {
    let group = quote.items?.find(
        (x) => x.product_code == props.modelValue.sku && x.data?.type == 'main_product'
    )?.group;
    return quote.items.filter((item) => item.group == group && item.data?.type == 'addon_product').length;
});

const appendPage = (items) => {
    const parentItem = quote.items?.find(
        (x) => x.product_code == props.modelValue.sku && x.data?.type == 'main_product'
    );
    items.forEach((item) => {
        const addonId = item.addon.id;
        const quoteItem = quote.items?.find(
            (x) => x.product_code == item.addon.sku && x.data?.type == 'addon_product' && x.group == parentItem?.group
        );
        if (!item.addon.contract_length) {
            item.addon.contract_length = item.addon.price_plan?.code == 'SINGLE' ? 1 : 12;
        }
        item.addon.quantity = quoteItem ? quoteItem.quantity : 0;
        addons.value.push(item);
    });
};

const loadNextPage = async () => {
    if (api.isLoading.value || !hasMore.value) return;
    api.clearFilters();
    api.setColumns(['addon.name', 'addon.sku']);
    api.addFilter('product_id', FilterOperators.EQUALS, props.modelValue.id);
    api.setSearch(searchQuery.value || null);
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
    setupObserver();
});

onBeforeUnmount(() => {
    if (observer) observer.disconnect();
});

let currentPromise = Promise.resolve();

const checkQuantity = _.throttle(async (addon, val) => {
    isDisabled.value = true;
    isBlocked.value = true;
    return (currentPromise = currentPromise.then(async () => {
        try {
            let quantity = val;

            if (quantity > addon.stock) {
                notyf.open({
                    type: 'warning',
                    message:
                        'You can only add up to the number of units in stock. The quantity is automatically adjusted'
                });
                quantity = addon.stock;
            }

            addon.quantity = quantity;

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
                        addon.quantity = 0;
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
        } catch (error) {
            console.error('Error updating addon quantity:', error);
            notyf.open({
                type: 'error',
                message: 'An error occurred while updating the addon quantity, please try again.'
            });
        } finally {
            isBlocked.value = false;
            isDisabled.value = false;
        }
    }));
});

const hasParentSelected = () => {
    let code = props.modelValue.sku ?? props.modelValue.code;
    return quote.items?.some((x) => x.product_code == code && x.data?.type === 'main_product' && x.quantity > 0);
};

watch(
    () => quote.items,
    async () => {
        const wasVisible = visible.value;
        if (hasParentSelected()) {
            visible.value = true;
            if (!wasVisible && !loaded.value) {
                loaded.value = true;
                await resetAndLoad();
            }
        } else {
            visible.value = false;
        }
        let code = props.modelValue.sku ?? props.modelValue.code;
        let group = quote.items?.find((x) => x.product_code == code && x.data?.type === 'main_product')?.group;
        addons.value.forEach((addonItem) => {
            const item = quote.items?.find(
                (x) => x.product_code === addonItem.addon.sku && x.data?.type === 'addon_product' && x.group == group
            );
            addonItem.addon.quantity = item ? item.quantity : 0;
        });
    },
    { deep: true }
);
onMounted(async () => {
    if (hasParentSelected()) {
        visible.value = true;
        loaded.value = true;
        await resetAndLoad();
    }
    setupObserver();
});
</script>

<template>
    <div>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <p class="small text-muted mb-0">
                This product has {{ modelValue.addons.length }} add-on<span v-if="modelValue.addons.length !== 1"
                    >s</span
                >
                available
            </p>
            <span v-if="selectedCount > 0" class="badge badge-muted">{{ selectedCount }} selected</span>
        </div>

        <template v-if="visible">
            <div class="mb-2">
                <div class="input-group input-group-sm">
                    <input
                        v-model="searchQuery"
                        type="text"
                        class="form-control border-end-0"
                        placeholder="Search by add on"
                    />
                    <span class="input-group-text bg-white">
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
                            v-if="Object.keys(item.addon).includes('stock')"
                            class="badge me-3 text-nowrap"
                            :class="item.addon.stock > 0 ? 'badge-success' : 'badge-danger'"
                        >
                            {{ item.addon.stock > 0 ? item.addon.stock + ' in stock' : 'Out of stock' }}
                        </span>

                        <div style="width: 130px" class="me-3 flex-shrink-0">
                            <InputQuantity
                                :id="'addon-' + item.id"
                                :modelValue="item.addon.quantity"
                                :disabled="!hasParentSelected() || isBlocked"
                                @change="(val) => checkQuantity(item.addon, val)"
                                @update:modelValue="(val) => (item.addon.quantity = val)"
                            />
                        </div>

                        <p class="mb-0 text-nowrap small fw-semibold text-end" style="width: 57px">
                            {{ format.GBP(item.addon.price?.net_spa || 0) }}
                        </p>
                    </div>

                    <div v-if="!addons.length" class="text-center py-2">
                        <p class="small text-muted mb-0">No add-ons found</p>
                    </div>

                    <div ref="sentinel">
                        <div v-if="api.isLoading.value && addons.length" class="py-1 text-center py-2">
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
    max-height: 147px;
    overflow-y: auto;
}
</style>
