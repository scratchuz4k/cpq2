<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
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
        { column: 'products.category.code', operator: 'equals', value: 22 }
    ];

    // let array = props.category ? [props.category] : [];
    // let items = [];
    // if (props.category) {
    //     do {
    //         let current = array.pop();
    //         items.push(current);
    //         let temp = categories.value.filter((x) => x.parent_id == current.id);
    //         array.push(...temp);
    //     } while (array.length != 0);
    // }
    // return items.length ? [{ column: 'products.category.id', operator: 'in', value: items.map((x) => x.id) }] : [];
};

// onMounted(() => {
//     getFilters();
// });

watch(
    () => props.category,
    () => {
        getFilters();
    },
    { immediate: true }
);
</script>
{{ proxyValue }}
<template>
    <InputSelectMultiple
        id="select-storage"
        url="/shop-blocks/dimensions"
        :fields="['value']"
        v-model="proxyValue"
        placeholder="Select a Storage"
        :query="filters"
    >
    </InputSelectMultiple>
</template>
