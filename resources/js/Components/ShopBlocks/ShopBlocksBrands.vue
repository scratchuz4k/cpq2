<script setup>
import { computed, watch } from 'vue';
import { categories } from '@composables/custom/shopblocks';

const props = defineProps({
    categories: Array,
    modelValue: Object
});

const emit = defineEmits(['update:modelValue']);
const proxyValue = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
});

const getFilters = () => {
    let filters = [];
    filters.push({
        column: 'products.category.id',
        operator: 'in',
        value: props.categories?.map((x) => x.id).join(',')
    });

    filters.push({
        column: 'products.stock',
        operator: 'greater_than_or_equal_to',
        value: 1
    });

    return filters;
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
        id="select-brand"
        url="/shop-blocks/brands"
        :fields="['name']"
        v-model="proxyValue"
        placeholder="Brand"
        :query="getFilters()"
    >
    </InputSelectMultiple>
    <!-- <ul style="padding: 0; list-style: none">
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
    </ul> -->
</template>
