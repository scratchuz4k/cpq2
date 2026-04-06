<template>
    <InputNumber :useGrouping="false" :max="9999999999.99" :min="0" :maxFractionDigits="2" fluid @input="checkValue" />
</template>
<script setup>
import InputNumber from 'primevue/inputnumber';
import { checkValue } from '@/Composables/input.js';
</script>
<style></style>
