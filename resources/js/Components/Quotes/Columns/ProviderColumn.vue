<script setup>
defineProps({
    code: String,
    column: Object,
    isGroup: Boolean,
    preview: Boolean,
    index: Number
})
const item = defineModel()
const setAltImg = (event) => {
    event.target.src = '/images/no_image.png'
}

</script>
<template>
    <div>
        <img class="img-provider" @error="setAltImg"
            :src="`/images/providers/${item.data?.providerKey}.png`" :alt="item.data?.providerName"
            :title="item.data?.providerName" />
    </div>
</template>
