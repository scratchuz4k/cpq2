<template>
    <div class="panel" v-if="approval.flow != 'Client review'">
        <p class="big bold">Action Required:</p>
        <p class="small mt-2">Approve or reject this quote to move the deal forward.</p>
        <template v-if="can('update quote_approval') || hasRole('Super Admin')">
            <div class="d-flex mt-2">
                <button class="btn btn-primary me-2" @click="() => updateQuoteApproval('approved')">Approve</button>
                <button class="btn btn-outline-danger bold" @click="() => updateQuoteApproval('rejected')">
                    Reject
                </button>
            </div>
            <button class="btn btn-sm mt-2" @click="showComments = !showComments">
                <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                {{ showComments ? 'Hide' : 'Add' }} Comments
            </button>
            <div v-if="showComments">
                <input class="form-control form-control-sm" v-model="approval.comments" />
            </div>
        </template>
        <div class="mt-2" v-else>
            <p class="bold">Missing required permissions</p>
        </div>
    </div>
    <div v-else class="panel">
        <p class="big bold">Action Required:</p>
        <div class="my-3">
            <label for="expiration_data" class="form-label"> Currently in client review, here's the link. </label>
            <div class="input-group">
                <input
                    id="input-shareable-link"
                    type="text"
                    class="form-control"
                    :value="route('share_link', { token: shareLink.token })"
                    disabled
                />

                <button
                    class="input-group-text"
                    @click="copyToClipboard(route('share_link', { token: shareLink.token }))"
                >
                    <font-awesome-icon role="button" :icon="['far', 'clipboard']" />
                </button>
            </div>
            <div class="input-group mt-2">
                <input
                    id="input-shareable-link"
                    type="password"
                    class="form-control"
                    :value="approval.configs?.link_password"
                    disabled
                />

                <button class="input-group-text" @click="copyToClipboard(approval.configs?.link_password)">
                    <font-awesome-icon role="button" :icon="['far', 'clipboard']" />
                </button>
            </div>
            <p class="small mt-3">
                Expires at: {{ approval.configs?.link_expiration_date ?? 'without expiration date' }}
            </p>
        </div>
    </div>
</template>
<script setup>
import { can, hasRole } from '@composables/useAuth';
import { quote, getQuote } from '@composables/useQuote';
import { ref, onMounted, reactive, watch } from 'vue';

const approval = reactive({});
const shareLink = reactive({});
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
    let temp = quote.approvals.find((x) => x.status == 'PENDING');
    if (temp) {
        Object.assign(approval, temp);
    }
    let clientApproval = quote.approvals.find((x) => x.status == 'PENDING' && x.flow == 'Client review');
    if (clientApproval) {
        Object.assign(shareLink, clientApproval.shared_link);
    }
};

const copyToClipboard = async (msg) => {
    try {
        await navigator.clipboard.writeText(msg);
        notyf.success('Copied to clipboard!');
    } catch (err) {
        notyf.error('Failed to copy text: ' + err);
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
