<script setup>
import queryString from 'query-string';
import { Dialog, IconField, Galleria, InputIcon, InputText } from 'primevue';
import { ref, computed, watch } from 'vue';
import _ from 'lodash';
import { updateQuoteItem, removeQuoteItem, createQuoteItem } from '@composables/useQuoteItem';
import { quote, getQuote } from '@/Composables/useQuote';
const props = defineProps({
    index: Number,
    showAsColumn: Boolean,
    modelValue: Object
});

const showModal = ref(false);
const emit = defineEmits(['update:modelValue', 'select']);
const proxyValue = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
});

const checkQuantity = _.debounce(async (product, temp) => {
    if (product.stock <= temp) {
        temp = product.stock;
    }
    product.quantity = temp;

    let item = quote.items.find((x) => x.product_code == product.sku);
    if (!item) {
        await createQuoteItem({
            quote_id: quote.id,
            product_code: product.sku,
            product_id: product.id,
            quantity: product.quantity,
            contract_length: product.contract_length,
            name: product.name,
            raw_price: product.price ? product.price.net : 0,
            price: product.price ? product.price.net_spa : 0,
            price_plan_id: product.price_plan_id
        });
    } else {
        if (product.quantity == 0) {
            await removeQuoteItem(item.id);
        } else {
            await updateQuoteItem(item.id, { quantity: product.quantity });
        }
    }

    await getQuote(quote.id);
}, 500);

const temp = ref([]);
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
                <div class="d-flex justify-content-between">
                    <p class="bold truncate-1-lines" :class="[showAsColumn ? 'truncate-2-lines mb-2' : 'me-3']">
                        {{ proxyValue.name }}
                    </p>
                    <div :class="[!showAsColumn ? 'd-flex justify-content-end' : 'position-absolute top-0 end-0 mt-2']">
                        <div v-if="!showAsColumn">
                            <p class="bold big">
                                {{ format.GBP(proxyValue.price?.net_spa || 0) }}
                            </p>
                        </div>
                    </div>
                </div>
                <p :class="[showAsColumn ? 'truncate-3-lines' : null]" v-html="proxyValue.name"></p>
                <a
                    class="fw-bold small"
                    type="button"
                    style="text-decoration: none"
                    @click="() => (showModal = !showModal)"
                    >More Info<font-awesome-icon class="ps-2" :icon="['fas', 'plus']"
                /></a>
                <div v-if="showAsColumn">
                    <p class="bold big mt-3">
                        {{ format.GBP(proxyValue.price?.net_spa || 0) }}
                    </p>
                </div>
                <div class="mt-3">
                    <div
                        class="d-flex"
                        :style="{
                            flexDirection: showAsColumn ? 'column' : 'row'
                        }"
                    >
                        <div
                            :style="{
                                width: showAsColumn ? '100%' : '150px'
                            }"
                        >
                            <InputQuantity
                                :id="'hardware-' + index"
                                v-model="proxyValue.quantity"
                                @change="(val) => checkQuantity(proxyValue, val, true)"
                            />
                        </div>

                        <template v-if="!showAsColumn">
                            <button
                                :id="'btn-product-' + index + '-add-25'"
                                class="btn btn-sm ms-4 btn-add-custom-quantity"
                                @click="checkQuantity(proxyValue, proxyValue.quantity + 25, true)"
                            >
                                +25
                            </button>
                            <button
                                :id="'btn-product-' + index + '-add-50'"
                                class="btn btn-sm ms-2 btn-add-custom-quantity"
                                @click="checkQuantity(proxyValue, proxyValue.quantity + 50, true)"
                            >
                                +50
                            </button>
                            <button
                                :id="'btn-product-' + index + '-add-100'"
                                class="btn btn-sm ms-2 me-4 btn-add-custom-quantity"
                                @click="checkQuantity(proxyValue, proxyValue.quantity + 100, true)"
                            >
                                +100
                            </button>
                        </template>
                    </div>
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
        </Dialog>
    </div>
</template>
<style scoped>
.table tbody th {
    width: 30%;
}

.table tbody td {
    width: 70%;
}
.truncate-1-lines {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.5em; /* or a fixed px value, e.g. 24px */
    height: calc(1.5em * 1); /* 3 lines × line-height */
}

.truncate-2-lines {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 2;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.5em; /* or a fixed px value, e.g. 24px */
    height: calc(1.5em * 2); /* 3 lines × line-height */
}

.truncate-3-lines {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 3;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.5em; /* or a fixed px value, e.g. 24px */
    height: calc(1.5em * 3); /* 3 lines × line-height */
    font-size: 14px !important;
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

.no_image {
    width: 140px;
}
</style>
