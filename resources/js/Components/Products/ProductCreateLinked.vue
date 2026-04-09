<template>
    <ProductCreateRelation
        id="links"
        route="product-links"
        :product="product"
        column="child_id"
        :queries="queries"
        warningMessage="Linked products can only be added to the main product if they are configured in different steps."
        :isRowDisabled="sharesStep"
    />
</template>

<script setup>
import ProductCreateRelation from './ProductCreateRelation.vue';

const props = defineProps({
    product: Object,
    queries: Array
});

const sharesStep = (item) => item.steps.some((s) => props.product.steps?.some((ps) => ps.id === s.id));
</script>
