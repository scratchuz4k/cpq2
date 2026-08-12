<template>
    <div class="filters rounded bg-white p-3 mb-3">
        <div class="row">
            <div class="col-9">
                <!-- Primary filters — always visible (4 per row) -->
                <div class="filters-grid">
                    <template v-for="(filter, key) in filterComponent.filter((f) => f.show)" :key="key">
                        <component
                            :is="filter.component"
                            v-if="showFilter(filter.name) && (!hasMoreFilters ? key <= 3 : true)"
                            v-model="proxyValue[filter.name]"
                            :categories="proxyValue.categories"
                        ></component>
                    </template>
                </div>
            </div>

            <div class="col-3">
                <div class="d-flex align-items-start justify-content-end gap-1 flex-shrink-0 h-100">
                    <button class="btn btn-sm text-primary" @click="clearFilters">
                        <FontAwesomeIcon :icon="['fas', 'arrow-rotate-left']"></FontAwesomeIcon>
                        Reset All
                    </button>
                    <button
                        v-if="filterComponent.filter((f) => f.show).length > 4"
                        class="btn btn-sm"
                        :class="[hasMoreFilters ? 'btn-primary' : 'btn-outline-primary']"
                        @click="moreFilters"
                    >
                        <FontAwesomeIcon :icon="['fas', 'filter']"></FontAwesomeIcon>
                        {{ hasMoreFilters ? 'Less' : 'More' }} Filters
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
import { computed, ref } from 'vue';
import { quote } from '@/Composables/useQuote';

const props = defineProps({
    modelValue: Object,
    filters: {
        type: Array,
        default: () => ['categories', 'brands', 'models', 'storage']
    }
});

const filterComponent = [
    {
        show: props.filters.includes('categories'),
        name: 'categories',
        component: 'ShopBlocksCategories'
    },
    {
        show: props.filters.includes('brands'),
        name: 'brands',
        component: 'ShopBlocksBrands'
    },
    {
        show: props.filters.includes('models'),
        name: 'models',
        component: 'ShopBlocksModels'
    },
    {
        show: props.filters.includes('storage'),
        name: 'storage',
        component: 'ShopBlocksStorages'
    }
];

const emit = defineEmits(['update:modelValue']);
const proxyValue = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
});

const hasMoreFilters = ref(false);

const showFilter = (name) => props.filters.includes(name);

const moreFilters = () => {
    hasMoreFilters.value = !hasMoreFilters.value;
};

const clearFilters = () => {
    proxyValue.value.categories = [];
    proxyValue.value.brands = [];
    proxyValue.value.models = [];
    proxyValue.value.storage = [];

    if (quote.solution_type.code == 4) {
        proxyValue.value.categories = [{ id: 22 }];
    }
};
</script>

<style scoped>
.filters-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.75rem;
}
</style>
