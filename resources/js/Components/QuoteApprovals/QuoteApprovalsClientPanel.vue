<template>
    <div class="panel" v-if="showApproval">
        <p class="big bold">Action Required:</p>
        <p class="small mt-2">Approve or reject this quote to move the deal forward.</p>
        <div class="d-flex mt-2">
            <button class="btn btn-primary me-2" @click="() => updateQuoteApproval('approved')">Approve</button>
            <button class="btn btn-outline-danger bold" @click="() => updateQuoteApproval('rejected')">Reject</button>
        </div>
        <button class="btn btn-sm mt-2" @click="showComments = !showComments">
            <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
            {{ showComments ? 'Hide' : 'Add' }} Comments
        </button>
        <div v-if="showComments">
            <input class="form-control form-control-sm" v-model="approval.comments" />
        </div>
    </div>
    <div class="panel">
        <p class="small mt-2">Quote was approved with success.</p>
    </div>
</template>
<script setup>
import { can, hasRole } from '@composables/useAuth';
import { quote, getQuote } from '@composables/useQuote';
import { ref, onMounted, reactive, watch } from 'vue';

const approval = reactive({});
const showApproval = ref(false);
const showComments = ref(false);

const updateQuoteApproval = async (status) => {
    if (!approval.id) {
        notyf.error('No quote Approval found!');
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
