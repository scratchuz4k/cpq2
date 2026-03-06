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
        </template>
        <div class="mt-2" v-else>
            <p class="bold">Missing required permissions</p>
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
    <div v-else class="panel">
        <p class="big bold">Action Required:</p>
        <div class="my-3">
            <label for="expiration_data" class="form-label"
                >The quote is currently under client review. You can access it here:
            </label>
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
                    id="input-shareable-link-password"
                    :type="showPassword ? 'text' : 'password'"
                    class="form-control pe-5"
                    :value="approval.configs?.link_password"
                    disabled
                />

                <button
                    class="input-group-text text-muted bg-transparent border-0"
                    @click="showPassword = !showPassword"
                    style="position: absolute; right: 36px; top: 0; bottom: 0; z-index: 1"
                >
                    <font-awesome-icon role="button" :icon="['far', showPassword ? 'eye-slash' : 'eye']" />
                </button>
                <button class="input-group-text" @click="copyToClipboard(approval.configs?.link_password)">
                    <font-awesome-icon role="button" :icon="['far', 'clipboard']" />
                </button>
            </div>
        </div>
    </div>
</template>
<script setup>
import { can, hasRole } from '@composables/useAuth';
import { quote, getQuote } from '@composables/useQuote';
import { ref, onMounted, reactive, watch } from 'vue';
import { Dialog, Button, Textarea } from 'primevue';

const approval = reactive({});
const shareLink = reactive({});
const showPassword = ref(false);
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
        let res = await axios.put(route('api.quote-approvals.update', { id: approval.id }), {
            status: status.toUpperCase(),
            comments: approval.comments
        });
        notyf.success(`Quote ${status} with success!`);
        await getQuote(quote.id);
        showModal.value = false;
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
