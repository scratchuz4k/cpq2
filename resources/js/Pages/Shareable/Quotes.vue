<script setup>
import ShareLayout from '@/Layouts/ShareLayout.vue';
import { Head } from '@inertiajs/vue3';
const props = defineProps({
    model: Object,
});
</script>
<template>

    <Head title="Share" />
    <ShareLayout>
        <QuotePreview v-model="props.model"></QuotePreview>
    </ShareLayout>
</template>
