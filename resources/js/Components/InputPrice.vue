<template>
    <InputNumber :useGrouping="false" :maxFractionDigits="2" fluid @input="checkValue" />
</template>
<script setup>
import InputNumber from 'primevue/inputnumber';
import { checkValue } from '@/Composables/input.js';
</script>
<style></style>
