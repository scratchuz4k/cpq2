<script setup>
import { computed } from 'vue';

const props = defineProps({
    modelValue: Object,
    categories: {
        type: [Array, Number],
        default: () => []
    }
});

const emit = defineEmits(['update:modelValue']);
const proxyValue = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
});

const query = computed(() => {
    let filters = [{ column: 'is_active', operator: 'equals', value: 1 }];
    if (Array.isArray(props.categories) && props.categories.length) {
        filters.push({ column: 'id', operator: 'in', value: props.categories });
    } else if (props.categories) {
        filters.push({ column: 'id', operator: 'equals', value: props.categories });
    }
    return filters;
});
</script>
<template>
    <InputSelect
        id="select-category"
        url="/categories"
        :fields="['name']"
        v-model="proxyValue"
        placeholder="Select a Category"
        :query="query"
    >
    </InputSelect>
</template>
