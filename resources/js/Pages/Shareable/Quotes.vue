<script setup>
import ShareLayout from '@/Layouts/ShareLayout.vue';
import { Head, router } from '@inertiajs/vue3';
const props = defineProps({
    model: Object,
    pricePlans: Object
});
const getPdf = () => {
    axios
        .post(`/quotes/${props.model.uuid}/download`, {}, { responseType: 'blob' })
        .then((response) => {
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', `quotes-${props.model.id}.pdf`); // Change filename as needed
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        })
        .catch((error) => {
            notyf.error(error?.response?.data?.message ?? 'Something went wrong');
        });
};
</script>
<template>
    <Head title="Share" />
    <ShareLayout>
        <div class="row mb-3">
            <div class="col"></div>
            <div class="col" style="text-align: right">
                <button @click="getPdf()" class="btn btn-secondary mx-2">
                    <font-awesome-icon :icon="['fas', 'file-pdf']"></font-awesome-icon>
                    Download PDF
                </button>
            </div>
        </div>
        <QuotePreview v-model="props.model" :pricePlans="pricePlans"></QuotePreview>
    </ShareLayout>
</template>
