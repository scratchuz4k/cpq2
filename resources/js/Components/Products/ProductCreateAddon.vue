<template>
    <ProductCreateRelation
        id="addons"
        route="product-addons"
        :product="props.product"
        column="addon_id"
        :queries="[
            {
                group: 'parent',
                column: 'related.id',
                operator: FilterOperators.IS_NULL,
                value: props.product.id
            }
        ]"
    />
</template>

<script setup>
import ProductCreateRelation from './ProductCreateRelation.vue';
import { FilterOperators } from '@composables/useApi';

const props = defineProps({
    product: Object,
    queries: Array
});
</script>
