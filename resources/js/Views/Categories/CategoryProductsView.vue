<script setup>
import queryString from 'query-string';
import { ref, watch } from 'vue';
import { quote, getQuote } from "@composables/useQuote";
import _ from 'lodash';
import {
    updateQuoteItem,
    removeQuoteItem,
    createQuoteItem,
} from "@composables/useQuoteItem";

const props = defineProps({
    category: Object,
    account: Object,
});

const products = ref([])
const emit = defineEmits(['category_total_products']);

const getProducts = async () => {
    let query = queryString.stringify({
        limit: 5,
        filters: JSON.stringify([
            {
                column: "categories.id",
                operator: "equals",
                value: props.category.id,
            },
        ]),
        account_id: props.account.id,
    });
    let res = await axios.get("/api/products" + "?" + query);
    res.data.data.forEach((e) => {
        let temp = quote.items.find((x) => x.product_code == e.code);
        e.quantity = temp ? temp.quantity : 0;
        e.contract_length = 12;
    });
    products.value = res.data.data;
};

const checkQuantity = _.debounce(async (product, temp) => {
    if (product.stock <= temp) {
        temp = product.stock;
    }
    product.quantity = temp;
    if (product.quantity == 0) {
        return;
    }

    let item = quote.items.find((x) => x.product_code == product.code);
    if (!item) {
        createQuoteItem({
            quote_id: quote.id,
            product_code: product.code,
            product_id: product.id,
            quantity: product.quantity,
            contract_length: product.contract_length,
            name: product.name,
            raw_price: product.price ? product.price.net : 0,
            price: product.price ? product.price.net_spa : 0,
            price_plan_id: product.price_plan_id,
        });
    } else {
        if (product.quantity == 0) {
            removeQuoteItem(item.id);
        } else {
            updateQuoteItem(item.id, { quantity: product.quantity });
        }
    }

    getQuote(quote.id);
}, 200);

getProducts()

watch(products, (products) => {
    emit('category_total_products', products.length);
});
</script>

<template>
    <div v-for="(product, index) in products" class="mobile-item mobile-product mb-3">
        <div class="d-flex justify-content-between">
            <div class="d-flex align-items-center mb-2">
                <h4 class="m-0">{{ product.name }}</h4>
                <p class="m-0 ms-2">
                    {{ product.description }}
                    <span v-if="product.price && Object.keys(product.price).length > 0">
                        / {{ GBP.format(product.price.net_spa) }}
                    </span>
                </p>
            </div>
            <font-awesome-icon :icon="['fa', 'circle-info']" />
        </div>
        <div class="mobile-product-quantity">
            <label>Quantity</label>
            <div class="d-flex align-items-center pb-3">
                <InputQuantity :id="'bolt-' + index" v-model="product.quantity"
                    @change="(ev) => checkQuantity(product, ev)" />
                <button :id="'btn-plan-' + index + '-add-25'" type=" button"
                    class="btn btn-sm ms-4 btn-add-custom-quantity"
                    @click="checkQuantity(product, product.quantity + 25)">
                    +25
                </button>
                <button :id="'btn-plan-' + index + '-add-50'" type=" button"
                    class="btn btn-sm mx-2 btn-add-custom-quantity"
                    @click="checkQuantity(product, product.quantity + 50)">
                    +50
                </button>
                <button :id="'btn-plan-' + index + '-add-100'" type=" button" class="btn btn-sm btn-add-custom-quantity"
                    @click="checkQuantity(product, product.quantity + 100)">
                    +100
                </button>
            </div>
        </div>
    </div>
</template>

<style></style>
