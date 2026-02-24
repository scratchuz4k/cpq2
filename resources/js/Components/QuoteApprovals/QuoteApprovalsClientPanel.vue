<template>
    <div class="panel">
        <p class="big bold">Action Required:</p>
        <p class="small mt-2">Approve or reject this quote to move the deal forward.</p>
        <template v-if="can('update quote_approval') || hasRole('Super Admin')">
            <div class="d-flex mt-2">
                <button class="btn btn-primary me-2" @click="() => updateQuoteApproval('approved')">Approve</button>
                <button class="btn btn-outline-danger bold" @click="() => updateQuoteApproval('rejected')">
                    Reject
                </button>
            </div>
            <a class="btn btn-sm border-0 mt-2" @click="showComments = !showComments" role="button">
                <font-awesome-icon class="me-1" :icon="['fas', showComments ? 'minus' : 'plus']"></font-awesome-icon>
                {{ showComments ? 'Hide' : 'Add' }} Comments
            </a>
            <div v-if="showComments">
                <input class="form-control form-control-sm" v-model="approval.comments" />
            </div>
        </template>
        <div class="mt-2" v-else>
            <p class="bold">Missing required permissions</p>
        </div>
    </div>
</template>
<script setup>
import { can, hasRole } from '@composables/useAuth';
import { quote, getQuote } from '@composables/useQuote';
import { ref, onMounted, reactive, watch } from 'vue';

const approval = reactive({});
const showComments = ref(false);

const updateQuoteApproval = async (status) => {
    if (!approval.id) {
        notyf.error('No quote Approval found!');
        return;
    }
    try {
        let res = await axios.put(route('api.quote-approvals.update', { id: approval.id }), {
            status: status.toUpperCase(),
            comments: approval.comments
        });
        notyf.success(`Quote ${status} with success!`);
        await getQuote(quote.id);
    } catch (ex) {
        notyf.error(ex.response?.data?.message ?? 'Something went wrong!');
    }
};
const init = () => {
    let temp = quote.approvals.find((x) => x.status == 'PENDING' && x.flow == 'Client review');
    if (temp) {
        Object.assign(approval, temp);
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
