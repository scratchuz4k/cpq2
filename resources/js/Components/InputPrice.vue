<template>
    <InputNumber
        :useGrouping="false"
        :max="9999999999.99"
        :min="0"
        :maxFractionDigits="2"
        fluid
        @input="checkPriceValue"
    />
</template>
<script setup>
import InputNumber from 'primevue/inputnumber';
import { checkPriceValue } from '@/Composables/input.js';
</script>
<style></style>
