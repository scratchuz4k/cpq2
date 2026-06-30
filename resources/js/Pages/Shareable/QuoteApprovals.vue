<script setup>
import ShareLayout from '@/Layouts/ShareLayout.vue';
import { setQuote, quote } from '@composables/useQuote';
import { Head } from '@inertiajs/vue3';
const props = defineProps({
    model: Object,
    pricePlans: Object
});

setQuote(props.model);
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
            <div class="col" style="text-align: right">
                <button @click="getPdf()" class="btn btn-secondary mx-2">
                    <font-awesome-icon :icon="['fas', 'file-pdf']"></font-awesome-icon>
                    Download PDF
                </button>
            </div>
        </div>
        <div class="row">
            <div class="col mb-5 price-summary">
                <div class="panel d-flex justify-content-center">
                    <div id="print" class="page">
                        <QuotePreview v-model="props.model" :pricePlans="pricePlans"></QuotePreview>
                    </div>
                </div>
            </div>
            <div class="col-4 ps-xl-5"
                v-if="quote.approvals.find((x) => x.status == 'PENDING' && x.flow == 'Client review')">
                <QuoteApprovalsClientPanel></QuoteApprovalsClientPanel>
            </div>
        </div>
    </ShareLayout>
</template>
