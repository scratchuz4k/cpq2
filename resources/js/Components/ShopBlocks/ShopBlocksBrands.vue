<script setup>
import { computed, reactive, ref, watch } from 'vue';
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
const meta = reactive({
    last_page: 0,
    current: 1
});
const brands = ref([]);
const getShopBlocksBrands = async () => {
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
    let string = queryString.stringify({
        limit: 10,
        page: meta.current,
        filters: JSON.stringify(
            items.length ? [{ column: 'products.category.id', operator: 'in', value: items.map((x) => x.id) }] : []
        )
    });
    let res = await axios.get('/api/shop-blocks/brands?' + string);
    meta.last_page = res.data.meta?.last_page;
    brands.value.push(...res.data.data);
};

const nextPage = () => {
    meta.current++;
    getShopBlocksBrands();
};

watch(
    () => props.category,
    () => {
        meta.current = 1;
        meta.last_page = 0;
        brands.value = [];
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
        <li v-if="meta.current != meta.last_page">
            <div class="btn btn-light d-flex justify-content-between pointer" @click="nextPage()">
                <p class="m-0 text-left">Load More...</p>
            </div>
        </li>
    </ul>
</template>
