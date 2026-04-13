<template>
    <ProductCreateRelation
        id="links"
        route="product-links"
        :product="product"
        column="child_id"
        :queries="linkedProductQueries"
        warningMessage="Linked products can only be added to the main product if they are configured in different steps."
        :isRowDisabled="sharesStep"
    />
</template>

<script setup>
import ProductCreateRelation from './ProductCreateRelation.vue';
import { FilterOperators } from '@composables/useApi';

const props = defineProps({
    product: Object,
    queries: Array
});

const parentIds = (props.product.parents ?? []).map((p) => p.id);

const linkedProductQueries = [
    ...(parentIds.length > 0
        ? [
              {
                  group: 'exclude-parents',
                  column: 'id',
                  operator: FilterOperators.NOT_IN,
                  value: parentIds
              }
          ]
        : [])
];

const sharesStep = (item) => item.steps.some((s) => props.product.steps?.some((ps) => ps.id === s.id));
</script>
