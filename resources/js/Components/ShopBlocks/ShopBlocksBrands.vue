<script setup>
import { computed, ref, watch } from 'vue';
import queryString from 'query-string';
import { categories } from '@composables/custom/shopblocks';

const props = defineProps({
    category: Object,
    modelValue: Object
});

const emit = defineEmits(['update:modelValue']);
const proxyValue = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
});
const brands = ref([]);
const getShopBlocksBrands = async () => {
    brands.value = [];
    let array = props.category ? [props.category] : [];
    let items = [];
    if (props.category) {
        do {
            let current = array.pop();
            items.push(current);
            let temp = categories.value.filter((x) => x.parent_id == current.id);
            array.push(...temp);
        } while (array.length != 0);
    }
    let current_page = 1;
    let string = queryString.stringify({
        limit: 10,
        page: current_page,
        filters: JSON.stringify(
            items.length ? [{ column: 'products.category.id', operator: 'in', value: items.map((x) => x.id) }] : []
        )
    });
    let res = await axios.get('/api/shop-blocks/brands?' + string);
    brands.value.push(...res.data.data);
};

watch(
    () => props.category,
    () => {
        getShopBlocksBrands();
    }
);
getShopBlocksBrands();
</script>
<template>
    <ul style="padding: 0; list-style: none">
        <li v-if="proxyValue">
            <div
                class="btn btn-light d-flex justify-content-between pointer btn-category-current"
                @click="proxyValue = null"
            >
                <p class="m-0 text-left">{{ proxyValue.name }}</p>
            </div>
        </li>
        <template v-for="brand in brands">
            <li v-if="proxyValue?.id != brand.id">
                <div
                    class="btn btn-light d-flex justify-content-between pointer"
                    :class="[brand.id == proxyValue?.id ? 'btn-category-current' : '']"
                    @click="proxyValue = brand"
                >
                    <p class="m-0 text-left">{{ brand.name }}</p>
                </div>
            </li>
        </template>
    </ul>
</template>
