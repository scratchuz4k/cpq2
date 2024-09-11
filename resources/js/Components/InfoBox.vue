<script setup>
defineProps({
    order: Object,
})

</script>
<template>
    <div class="row mb-4">
        <div class="col">
            <div id="price_header">
                <p class="mb-0">
                    <b>Solution Type:</b> {{ order?.solution_type_name }}
                </p>
                <p class="mb-0" v-if="order?.address_line1">
                    <b>Address:</b> {{ order?.address_line1 }}
                </p>
            </div>
        </div>
    </div>
</template>
