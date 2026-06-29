<script setup>
import { temp } from '@composables/useQuoteItem';
import { integerOnly, checkValue } from '@/Composables/input.js';
import queryString from 'qs';
import { ref, watch } from 'vue';
defineProps({
    code: String,
    column: Object,
    isGroup: Boolean,
    preview: Boolean,
    index: Number
});
const item = defineModel();

const stock = ref(null);

const handleQuantityInput = (e) => {
    const target = e.target ? e.target : e.originalEvent.target;
    const value = parseFloat(target.value.replace(',', '.'));
    const max = stock.value ?? 9999;

    if (value > max) {
        temp.quantity = max;
        notyf.open({
            type: 'warning',
            message: 'You can only add up to the number of units in stock. The quantity is automatically adjusted'
        });
    }
};
const getProductStock = async () => {
    if (item.value.product_id) {
        return;
    }

    let query = queryString.stringify({
        filters: [
            {
                column: 'sku',
                operator: 'equals',
                value: item.value.product_code
            }
        ]
    });
    try {
        let res = await axios.get('/api/shop-blocks/products?' + query);
        stock.value = res.data.data[0].stock;
    } catch (err) {
        notyf.error(err?.response?.data?.message ?? 'Something went wrong');
    }
};

watch(
    () => item.value.is_editing,
    (isEditing) => {
        if (isEditing && stock.value === null) {
            getProductStock();
        }
    }
);
</script>
<template>
    <template v-if="!isGroup">
        <p class="small" v-if="!item.is_editing" :id="code + '-' + index + '-quantity'">x{{ item.quantity }}</p>
        <input
            :id="'input-' + code + '-' + index + '-quantity'"
            v-else
            class="form-control text-center p-0 px-1"
            type="number"
            min="1"
            max="9999"
            v-model="temp.quantity"
            @keydown="integerOnly"
            @input="checkValue"
            @blur="handleQuantityInput"
        />
    </template>
</template>
