<template>
    <InputNumber :useGrouping="false" :max="9999999999.99" :min="0" :maxFractionDigits="2" fluid />
</template>
<script setup>
import InputNumber from 'primevue/inputnumber';
</script>
<style></style>
