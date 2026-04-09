<template>
    <ProductCreateRelation id="addons" route="product-addons" :product="product" column="addon_id" :queries="queries" />
</template>

<script setup>
import ProductCreateRelation from './ProductCreateRelation.vue';

defineProps({
    product: Object,
    queries: Array
});
</script>
