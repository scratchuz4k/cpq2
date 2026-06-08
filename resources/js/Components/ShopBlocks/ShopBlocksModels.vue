<script setup>
import { computed, watch } from 'vue';

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
        id="select-model"
        url="/shop-blocks/models"
        :fields="['name']"
        v-model="proxyValue"
        placeholder="Model"
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
        <template v-for="model in models">
            <li v-if="proxyValue?.id != model.id">
                <div
                    class="btn btn-light d-flex justify-content-between pointer"
                    :class="[model.id == proxyValue?.id ? 'btn-category-current' : '']"
                    @click="proxyValue = model"
                >
                    <p class="m-0 text-left">{{ model.name }}</p>
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
