<script setup>
defineProps({
    code: String,
    column: Object,
    isParent: Boolean,
    preview: Boolean,
    index: Number
})
const item = defineModel()
const setAltImg = (event) => {
    event.target.src = '/images/no_image.png'
}

</script>
<template>
    <img v-if="item.data.providerKey" class="img-provider" @error="setAltImg"
            :src="`/images/providers/${format.toSnakeCase(item.data?.connectivitySupplierGroup)}.png`" :alt="item.data?.connectivitySupplierGroup"
            :title="item.data?.connectivitySupplierGroup" />
</template>
