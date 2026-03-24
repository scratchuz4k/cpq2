<template>
    <div class="panel" v-if="showApproval">
        <p class="big bold">Action Required:</p>
        <p class="small mt-2">Approve or reject this quote to move the deal forward.</p>
        <div class="d-flex mt-2">
            <button class="btn btn-primary me-2" @click="() => updateQuoteApproval('approved')">Approve</button>
            <button class="btn btn-outline-danger bold" @click="() => updateQuoteApproval('rejected')">Reject</button>
        </div>
        <Dialog
            v-model:visible="showModal"
            modal
            :style="{ marginTop: '4rem', minWidth: '40vw' }"
            :draggable="false"
            position="top"
        >
            <template #header>
                <h2>Reject Comments</h2>
            </template>
            <div class="container m-0 p-0">
                <form @submit.prevent="updateQuoteApproval('rejected', true)" class="flex flex-col gap-4">
                    <div class="flex flex-col gap-1">
                        <Textarea v-model="approval.comments" rows="5" cols="30" style="resize: none" class="w-100" />
                    </div>
                    <Button
                        type="submit"
                        severity="secondary"
                        label="Submit"
                        class="btn btn-primary text-white w-100"
                    />
                </form>
            </div>
        </Dialog>
    </div>
</template>
<script setup>
import { can, hasRole } from '@composables/useAuth';
import { quote, getQuote } from '@composables/useQuote';
import { ref, onMounted, reactive, watch } from 'vue';
import { Dialog, Button, Textarea } from 'primevue';

const approval = reactive({});
const showApproval = ref(false);
const showModal = ref(false);

const updateQuoteApproval = async (status, fromModal = false) => {
    if (!approval.id) {
        notyf.error('No quote Approval found!');
        return;
    }
    if (status == 'rejected' && !approval.comments) {
        if (fromModal) {
            notyf.error('Please fill the comments box');
        }
        showModal.value = true;
        return;
    }
    try {
        let res = await axios.put(route('api.quote-approvals.update-client', { uuid: quote.uuid }), {
            status: status.toUpperCase(),
            comments: approval.comments
        });
        showApproval.value = false;
        notyf.success(`Quote ${status} with success!`);
    } catch (ex) {
        notyf.error(ex.response?.data?.message ?? 'Something went wrong!');
    }
};
const init = () => {
    let temp = quote.approvals.find((x) => x.status == 'PENDING' && x.flow == 'Client review');
    if (temp) {
        Object.assign(approval, temp);
        showApproval.value = true;
    }
};

onMounted(() => {
    init();
});

watch(
    () => [quote.approvals],
    () => {
        init();
    }
);
</script>
