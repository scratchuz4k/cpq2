<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import queryString from 'qs';

const props = defineProps({
    categories: Array,
    modelValue: Object
});

const emit = defineEmits(['update:modelValue']);
const proxyValue = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
});
const filters = ref([]);

const getFilters = async () => {
    let query = queryString.stringify({
        limit: 1,
        filters: JSON.stringify([
            { column: 'key', operator: 'contains', value: 'internal-storage-capacity' },
            { column: 'group', operator: 'equals', value: 'Storage' }
        ])
    });

    let res = await axios.get('/api/shop-blocks/dimension-types?' + query);

    if (!res.data?.data?.length) {
        return;
    }

    filters.value = [
        { column: 'dimension_type_id', operator: 'equals', value: res.data.data[0].id },
        { column: 'products.category.id', operator: 'in', value: props.categories?.map((x) => x.id).join(',') },
        { column: 'products.stock', operator: 'greater_than_or_equal_to', value: 1 }
    ];
};

watch(
    () => props.categories,
    () => {
        getFilters();
    },
    { immediate: true }
);
</script>
<template>
    <InputSelectMultiple
        id="select-storage"
        url="/shop-blocks/dimensions"
        :fields="['value']"
        v-model="proxyValue"
        placeholder="Storage"
        :query="filters"
    >
    </InputSelectMultiple>
</template>
