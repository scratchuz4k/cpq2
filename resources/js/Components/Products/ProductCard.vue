<script setup>
import { Dialog, IconField, Galleria, InputIcon, InputText } from 'primevue';
import { ref, computed, watch } from 'vue';
import _ from 'lodash';
import { updateQuoteItem, removeQuoteItem, createQuoteItem } from '@composables/useQuoteItem';
import { quote, getQuote } from '@/Composables/useQuote';
import { isDisabled } from '@/Composables/useQuoteSteps';
import queryString from 'qs';

const props = defineProps({
    index: Number,
    hasSelect: Boolean,
    showAsColumn: Boolean,
    modelValue: Object,
    showFeatures: Boolean,
    addInternalProduct: Boolean
});

const isBlocked = ref(false);
const displayBasic = ref(false);
const showModal = ref(false);
const loadingFeatures = ref(false);
const hasSuppliers = ref(false);
const emit = defineEmits(['update:modelValue', 'select']);
const proxyValue = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
});

const checkQuantity = _.throttle(async (product, temp) => {
    isDisabled.value = true;
    isBlocked.value = true;
    if (temp < 0) {
        product.quantity = 0;
        isDisabled.value = false;
        isBlocked.value = false;
        return;
    }

    if (temp > product.stock) {
        notyf.open({
            type: 'warning',
            message: 'You can only add up to the number of units in stock. The quantity is automatically adjusted'
        });
        temp = product.stock ?? 0;
    }
    product.quantity = temp;

    let code = product.sku ?? product.code;
    let item = quote.items.find((x) => x.product_code == code && x.data?.type == 'main_product');
    let oldQuantity = item ? item.quantity : 0;

    if (!item) {
        if (temp == 0) {
            return;
        }

        await createQuoteItem({
            quote_id: quote.id,
            product_code: code,
            quantity: temp,
            contract_length: product.contract_length,
            product_id: props.addInternalProduct ? product.id : null,
            name: product.name,
            raw_price: product.price ? product.price.net : 0,
            price: product.price ? product.price.net_spa : 0,
            price_plan_id: product.price_plan_id,
            data: { type: 'main_product' }
        });
    } else {
        if (temp == 0) {
            let groupItems = quote.items.filter((x) => x.group == item.group);

            if (groupItems.length > 1) {
                let result = await swal.fire({
                    width: '400px',
                    title: 'Are you sure?',
                    text: 'This item includes other associated items, removing it will also remove them from the quote.'
                });

                if (['cancel', 'backdrop'].includes(result.dismiss)) {
                    proxyValue.value.quantity = oldQuantity;
                    isDisabled.value = false;
                    isBlocked.value = false;
                    return;
                }
            }

            removeQuoteItem(item.id);
        } else {
            await updateQuoteItem(item.id, { quantity: temp });
        }
    }

    isDisabled.value = false;
    isBlocked.value = false;

    await getQuote(quote.id);
}, 500);

const select = () => {
    if (!props.hasSelect) {
        return;
    }
    emit('select', proxyValue);
};

const searchInput = ref('');
const temp = ref([]);
const getFeaturesTable = () => {
    temp.value = [];

    if (!proxyValue.value.features || !props.showFeatures) {
        return;
    }
    let lowerSearch = searchInput.value.toLowerCase();
    let features = proxyValue.value.features.filter((obj) => {
        return (
            obj.dimension.value.toLowerCase().includes(lowerSearch) ||
            obj.dimension_type.label.toLowerCase().includes(lowerSearch)
        );
    });

    features.forEach((e) => {
        let gIndex = temp.value.findIndex((t) => t.name == e.dimension_type.group);
        if (gIndex == -1) {
            temp.value.push({ name: e.dimension_type.group, show: true, features: [] });
            gIndex = temp.value.length - 1;
        }

        let index = temp.value[gIndex].features.findIndex((t) => t.key == e.dimension_type.key);
        if (index == -1) {
            temp.value[gIndex].features.push({ key: e.dimension_type.key, label: e.dimension_type.label, items: [] });
            index = temp.value[gIndex].features.length - 1;
        }
        temp.value[gIndex].features[index].items.push(e.dimension.value);
    });
};

const openModal = async () => {
    loadingFeatures.value = true;
    showModal.value = !showModal.value;
    if (!proxyValue.value.features) {
        let query = queryString.stringify({
            limit: 500,
            filters: JSON.stringify([
                {
                    column: 'product_id',
                    operator: 'equals',
                    value: proxyValue.value.id
                }
            ])
        });

        let res = await axios.get('/api/shop-blocks/product-dimensions?' + query);
        proxyValue.value.features = res.data?.data;
    }
    await getFeaturesTable();
    loadingFeatures.value = false;
};
const handleGalleriaKeyUp = (e) => {
    if (e.key === 'Escape') {
        displayBasic.value = false;
    }
};

const handleGalleriaOverlayClick = (e) => {
    if (e.target.classList.contains('p-overlay-mask')) {
        displayBasic.value = false;
    }
};

watch(displayBasic, (newVal) => {
    if (newVal) {
        document.addEventListener('keyup', handleGalleriaKeyUp);
        document.addEventListener('click', handleGalleriaOverlayClick, true);
    } else {
        document.removeEventListener('keyup', handleGalleriaKeyUp);
        document.removeEventListener('click', handleGalleriaOverlayClick, true);
    }
});

watch(searchInput, () => {
    getFeaturesTable();
});
</script>
<template>
    <div class="col" :style="{ flexDirection: showAsColumn ? 'column' : 'row' }">
        <div
            class="panel p-4 mb-3"
            :class="[!showAsColumn ? 'w-100 d-flex ' : 'p-3']"
            style="position: relative; top: 0; right: 0"
        >
            <div v-if="proxyValue.primary_image" :class="[!showAsColumn ? 'me-4' : '']">
                <div class="d-flex justify-content-center align-items-middle">
                    <a class="product-gallery-link" @click="() => (displayBasic = !displayBasic)">
                        <img
                            :src="proxyValue.primary_image"
                            style="object-fit: contain"
                            :style="{
                                width: '140px',
                                height: '140px'
                            }"
                        />
                        <span><font-awesome-icon :icon="['fas', 'magnifying-glass']" style="font-size: 1.5rem" /></span>
                    </a>
                </div>
            </div>

            <div class="w-100">
                <div
                    class="d-flex justify-content-between"
                    :class="[showAsColumn ? 'mt-3' : '0']"
                    :style="{ flexDirection: showAsColumn ? 'column' : 'row', gap: showAsColumn ? '10px' : '0' }"
                >
                    <h3
                        v-if="!showAsColumn"
                        class="bold truncate-1-lines m-0"
                        :class="[showAsColumn ? 'truncate-2-lines mb-2' : 'me-3']"
                    >
                        {{ proxyValue.name }}
                    </h3>
                    <p v-else class="truncate-2-lines bold">{{ proxyValue.name }}</p>
                    <div class="d-flex gap-2">
                        <div v-if="proxyValue.bundles && proxyValue.bundles.length > 0">
                            <p class="badge badge-bundle" style="white-space: nowrap">Bundle</p>
                        </div>
                        <div v-if="proxyValue.stock > 0">
                            <p class="badge badge-success" style="white-space: nowrap">
                                <span :id="'hardware-' + index + '-stock'">
                                    {{ proxyValue.stock }}
                                </span>
                                in stock
                            </p>
                        </div>
                    </div>
                </div>
                <p class="product-description truncate-2-lines my-3" v-html="proxyValue.description"></p>
                <div class="actions d-flex justify-content-between" v-if="!showAsColumn">
                    <div class="buttons">
                        <button id="btn-show-details" class="btn btn-sm btn-outline-primary me-2" @click="openModal()">
                            View product details
                        </button>
                        <button
                            v-if="hasSelect && !showAsColumn"
                            id="btn-compare"
                            class="btn btn-sm btn-outline-primary me-2"
                            @click="select()"
                        >
                            Compare
                        </button>
                        <button v-if="hasSuppliers" id="btn-show-suppliers" class="btn btn-sm btn-outline-primary me-2">
                            50 Suppliers
                            <font-awesome-icon :icon="['fas', 'arrow-right']" class="ms-2" />
                        </button>
                    </div>
                    <div class="actions d-flex align-items-center">
                        <InputQuantity
                            :id="'product-' + index"
                            style="max-width: 130px"
                            :disabled="isBlocked"
                            v-model="proxyValue.quantity"
                            @change="(val) => checkQuantity(proxyValue, val, true)"
                        />
                        <p class="price bold big ps-1 ms-3 text-end">
                            {{ format.GBP(proxyValue.price?.net_spa || 0) }}
                        </p>
                    </div>
                </div>
                <div v-else>
                    <div class="buttons">
                        <button id="btn-show-details" class="btn btn-sm btn-outline-primary" @click="openModal()">
                            View product details
                        </button>
                        <button
                            v-if="hasSelect"
                            id="btn-compare"
                            class="btn btn-sm btn-outline-primary mt-2"
                            @click="select()"
                        >
                            Compare
                        </button>
                        <button v-if="hasSuppliers" id="btn-show-suppliers" class="btn btn-sm btn-outline-primary">
                            50 Suppliers
                            <font-awesome-icon :icon="['fas', 'arrow-right']" class="ms-2" />
                        </button>
                    </div>
                    <p class="bold big my-2">
                        {{ format.GBP(proxyValue.price?.net_spa || 0) }}
                    </p>
                    <div class="actions d-flex align-items-center">
                        <InputQuantity
                            :id="'product-' + index"
                            style="max-width: 120px"
                            :disabled="isBlocked"
                            v-model="proxyValue.quantity"
                            @change="(val) => checkQuantity(proxyValue, val)"
                        />
                    </div>
                </div>
                <div v-if="proxyValue.bundles && proxyValue.bundles.length > 0" class="mt-3">
                    <hr class="my-2" />
                    <ProductCardBundlesList :bundles="proxyValue.bundles" />
                </div>
                <div v-if="proxyValue.addons && proxyValue.addons.length > 0" class="mt-3">
                    <hr class="my-2" />
                    <ProductCardAddonsList v-model="proxyValue" />
                </div>
            </div>
        </div>
        <Dialog
            v-model:visible="showModal"
            modal
            :header="proxyValue.model?.name"
            :style="{ width: '80rem' }"
            :draggable="false"
        >
            <template #header>
                <h2>Product Information ({{ proxyValue.name }})</h2>
            </template>
            <div class="panel h-100">
                <h3 class="mb-3">General</h3>
                <table class="table">
                    <tbody>
                        <tr>
                            <th>Sku</th>
                            <td>
                                {{ proxyValue.sku }}
                            </td>
                        </tr>
                        <tr>
                            <th>Model</th>
                            <td>
                                {{ proxyValue.model?.name }}
                            </td>
                        </tr>
                        <tr>
                            <th>Name</th>
                            <td>
                                {{ proxyValue.name }}
                            </td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td>
                                <div v-html="proxyValue.description"></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="loadingFeatures" class="row">
                <div class="col text-center">Loading...</div>
            </div>
            <div v-else class="d-flex justify-content-between align-items-end mb-3 mt-5">
                <h3>Features</h3>
                <div>
                    <IconField>
                        <InputIcon>
                            <font-awesome-icon icon="fas fa-search" />
                        </InputIcon>
                        <InputText
                            :id="'select-' + proxyValue.id + '-search'"
                            v-model="searchInput"
                            placeholder="Search"
                            type="search"
                        />
                    </IconField>
                </div>
            </div>

            <div v-if="temp.length" class="row">
                <div class="col">
                    <table class="table">
                        <tbody>
                            <template v-for="group in temp">
                                <tr>
                                    <th colspan="2" class="sticky-row">
                                        <div @click="() => (group.show = !group.show)" style="cursor: pointer">
                                            <div class="d-flex justify-content-between w-100">
                                                <p>{{ group.name }}</p>
                                                <div class="px-2">
                                                    <font-awesome-icon
                                                        class="w-100"
                                                        v-if="group.show"
                                                        style="color: var(--color-purple)"
                                                        :icon="['fas', 'chevron-down']"
                                                    />
                                                    <font-awesome-icon
                                                        class="w-100"
                                                        v-else
                                                        style="color: var(--color-purple)"
                                                        :icon="['fas', 'chevron-up']"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </th>
                                </tr>
                                <tr v-if="group.show" v-for="row in group.features">
                                    <th>{{ row.label }}</th>
                                    <td>{{ row.items[0] }}</td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
            <div v-else>No Features Available</div>
        </Dialog>

        <Galleria
            v-model:visible="displayBasic"
            :value="proxyValue.images"
            :fullScreen="true"
            :numVisible="10"
            :showItemNavigators="proxyValue.images && proxyValue.images.length > 1 ? true : false"
            :showThumbnails="proxyValue.images && proxyValue.images.length > 1 ? true : false"
            style="z-index: 100"
        >
            <template #item="slotProps">
                <div style="width: 100%; height: 100%; background: white; display: flex; justify-content: center">
                    <img
                        :src="slotProps.item.url"
                        :alt="slotProps.item.alt"
                        style="
                            max-height: 85vh;
                            height: auto;
                            width: auto;
                            object-fit: contain;
                            display: block;
                            margin: 0 auto;
                        "
                    />
                </div>
            </template>
            <template #thumbnail="slotProps">
                <img
                    :src="slotProps.item.url"
                    :alt="slotProps.item.alt"
                    style="display: block; width: 100px; height: 100px; object-fit: contain; margin: 0 auto"
                />
            </template>
        </Galleria>
    </div>
</template>
<style scoped>
.table tbody th {
    width: 30%;
}

.table tbody td {
    width: 70%;
}

h3 {
    font-size: 22px;
}

.product-description {
    font-size: 14px;
}

.shopblock-product:hover {
    cursor: pointer;
    box-shadow: var(--color-purple) 0px 1px 8px 0px;
}

.product-gallery-link {
    display: block;
    cursor: pointer;
    position: relative;
    width: 140px;
    height: 140px;
}
.product-gallery-link:after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    background: rgba(255, 255, 255, 0);
    transition: background 0.2s ease-in-out;
}

.product-gallery-link:hover:after {
    background: rgba(255, 255, 255, 0.33);
}

.product-gallery-link span {
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    right: 0;
    margin: auto;
    width: 30px;
    height: 30px;
    display: block;
    text-align: center;
    opacity: 0;
    transition: opacity 0.2s ease-in-out;
}

.product-gallery-link:hover span {
    opacity: 1;
}

.p-galleria-close-button {
    z-index: 1000 !important;
}

.sticky-row {
    background: whitesmoke;
    position: sticky;
    right: 0;
    top: -1px;
    z-index: 3;
    border-bottom: 1px solid #dee2e6;
    border-top: 1px solid #dee2e6;
}
.sticky-row:after {
    content: '';
    position: absolute;
    left: 0;
    bottom: -1px;
    width: 100%;
    height: 1px;
    background: #dee2e6;
    z-index: 1;
    overflow: hidden;
}

.price {
    min-width: 90px;
}
</style>
