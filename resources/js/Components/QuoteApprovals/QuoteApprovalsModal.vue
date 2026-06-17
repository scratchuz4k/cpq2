<template>
    <div
        class="approvals-group-button flex ms-2 p-0"
        v-if="
            ['quote', 'pending', 'approved', 'completed', 'rejected'].includes(quote.status.toLowerCase()) &&
            canToggleDialog()
        "
    >
        <button
            id="quote-approval-history"
            v-if="['completed', 'rejected'].includes(quote.status.toLowerCase())"
            @click="toggleDialog()"
            class="btn btn-primary"
            :style="{
                borderRadius: canToggleDialog() ? '5px 0 0 5px' : '5px'
            }"
        >
            Workflow History
        </button>

        <button
            v-else
            id="quote-approval-start"
            class="btn btn-primary"
            :disabled="
                items.some((x) => x.status.toLowerCase() === 'pending') ||
                (!can('create quote_approval') && !hasRole('Super Admin'))
            "
            @click="sendToOrder()"
            style="border-radius: 5px 0 0 5px"
        >
            <font-awesome-icon v-if="getButtonText() == 'Send to Order'" :icon="['fas', 'thumbtack']" class="me-2" />
            <font-awesome-icon v-else :icon="['fas', 'thumbs-up']" class="me-2" />
            <span>{{ getButtonText() }}</span>
        </button>

        <button
            id="quote-approval-show-dialog"
            v-if="canToggleDialog()"
            class="btn btn-primary"
            @click="toggleDialog()"
            style="border-radius: 0 5px 5px 0"
        >
            <font-awesome-icon :icon="['fas', 'ellipsis-vertical']" />
        </button>
    </div>
    <Dialog
        v-model:visible="showDialog"
        modal
        :style="{ marginTop: '4rem', minWidth: '80vw' }"
        :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
        :draggable="false"
        position="top"
    >
        <template #header>
            <h2>Quote Approval Flow</h2>
        </template>
        <div>
            <table class="table">
                <thead>
                    <tr class="border-bottom border-black">
                        <th>Date</th>
                        <th>Approver</th>
                        <th>Flow</th>
                        <th>Comments</th>
                        <th class="text-center">Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(item, index) in items"
                        :key="item.id"
                        class="quote-approval-row align-middle"
                        :id="'quote-approval-row-' + index"
                    >
                        <td style="width: 15%" :id="'quote-approval-date-' + index">
                            {{ format.date(item.updated_at) }}
                        </td>
                        <td style="width: 20%" :id="'quote-approval-approver-' + index">{{ item.approver?.name }}</td>
                        <td style="width: 10%" :id="'quote-approval-flow-' + index">{{ item.flow }}</td>
                        <td style="width: 60%" :id="'quote-approval-comments-' + index">
                            <input
                                v-if="
                                    item.status.toLowerCase() === 'pending' &&
                                    (can('update quote_approval') || hasRole('Super Admin'))
                                "
                                type="text"
                                class="form-control"
                                v-model="item.comments"
                            />
                            <p v-else style="white-space: pre-line">
                                {{ item.comments }}
                            </p>
                        </td>
                        <td class="text-center" :id="'quote-approval-status-' + index" style="width: 10%">
                            <p
                                class="badge"
                                :class="{
                                    'badge-success': item.status.toLowerCase() === 'approved',
                                    'badge-warning': item.status.toLowerCase() === 'pending',
                                    'badge-danger': item.status.toLowerCase() === 'rejected'
                                }"
                            >
                                {{ format.capitalizeFirstLetter(item.status) }}
                            </p>
                        </td>
                        <td style="width: 10%">
                            <div
                                class="d-flex"
                                v-if="
                                    (!item.approver || item.approver?.id === user.id) &&
                                    item.status.toLowerCase() === 'pending' &&
                                    (can('update quote_approval') || hasRole('Super Admin'))
                                "
                            >
                                <button
                                    :id="'quote-approval-approve-' + index"
                                    class="btn btn-sm btn-primary ms-2"
                                    @click="() => updateQuoteApproval(item, 'approved')"
                                >
                                    <font-awesome-icon :icon="['fas', 'check']" />
                                </button>
                                <button
                                    :id="'quote-approval-reject-' + index"
                                    class="btn btn-sm btn-secondary ms-2"
                                    @click="() => updateQuoteApproval(item, 'rejected')"
                                >
                                    <font-awesome-icon :icon="['fas', 'x']" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <Paginator
                v-model:first="meta.first"
                :rows="meta.limit"
                :totalRecords="meta.total"
                :rowsPerPageOptions="[10, 20, 40]"
                @page="handlePaginator"
            >
            </Paginator>
        </div>
    </Dialog>
</template>
<script setup>
import Dialog from 'primevue/dialog';
import { ref, onMounted, reactive, watch } from 'vue';
import { quote, getQuote } from '@composables/useQuote';
import queryString from 'qs';
import Paginator from 'primevue/paginator';
import { usePage } from '@inertiajs/vue3';
import { can, hasRole } from '@/Composables/useAuth';

const { props } = usePage();

const tenant = props.tenant ?? null;
const user = props.auth.user ?? null;
const showDialog = ref(false);
const items = ref([]);
const meta = reactive({
    limit: 10,
    page: 1,
    total: 0,
    first: 1
});

const has_quote_approval = tenant.configurations.has_quote_approval == '1';
const quote_approval_flow = tenant.configurations.quote_approval_flow ?? 'Internal Review';
const has_client_approval = tenant.configurations.has_client_approval == '1';

const getApprovals = async () => {
    let query = queryString.stringify({
        limit: meta.limit,
        page: meta.page,
        filters: [{ column: 'quote_id', operator: 'equals', value: quote.id }],
        order: 'desc'
    });
    try {
        let res = await axios.get(route('api.quote-approvals.index') + '?' + query);
        items.value = res.data.data;
        meta.total = res.data.meta?.total ?? res.data.total;
        meta.first = (meta.page - 1) * meta.limit;
    } catch (ex) {
        notyf.error(ex.response?.data?.message ?? 'Something went wrong!');
    }
};

const updateQuoteApproval = async (item, status) => {
    try {
        let res = await axios.put(route('api.quote-approvals.update', { id: item.id }), {
            status: status.toUpperCase(),
            comments: item.comments
        });
        notyf.success(`Quote ${status} with success!`);
        showDialog.value = !showDialog.value;
        getApprovals();
        await getQuote(quote.id);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } catch (ex) {
        notyf.error(ex.response?.data?.message ?? 'Something went wrong!');
    }
};

const sendToOrder = async () => {
    if (!has_quote_approval) {
        try {
            let res = await axios.put(`/api/quotes/${quote.id}`, {
                status: 'COMPLETED'
            });
            await getQuote(quote.id);
            notyf.success(`Quote completed with success!`);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } catch (ex) {
            notyf.error(ex.response?.data?.message ?? 'Something went wrong!');
        }
        return;
    }

    if (
        quote.status.toLowerCase() != 'approved' &&
        !items.value.find((x) => ['pending'].includes(x.status.toLowerCase()))
    ) {
        try {
            let res = await axios.post(route('api.quote-approvals.store'), {
                quote_id: quote.id
            });
            await getQuote(quote.id);
            getApprovals();
            if (quote.status.toLowerCase() !== 'approved') {
                notyf.success(`Quote successfully sent for approval!`);
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } catch (ex) {
            notyf.error(ex.response?.data?.message ?? 'Something went wrong!');
        }
    }

    if (quote.status.toLowerCase() === 'approved') {
        try {
            let res = await axios.post(`/api/quotes/${quote.id}/complete`);
            await getQuote(quote.id);
            notyf.success(`Quote completed with success!`);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } catch (ex) {
            notyf.error(ex.response?.data?.message ?? 'Something went wrong!');
        }
    }
};

const canToggleDialog = () => {
    return can('viewAny quote_approval') || hasRole('Super Admin');
};

const toggleDialog = () => {
    if (!canToggleDialog()) {
        return;
    }
    showDialog.value = !showDialog.value;
    getApprovals();
    getQuote(quote.id);
};
const handlePaginator = (ev) => {
    meta.limit = ev.rows;
    meta.page = ev.page + 1;
    getApprovals();
};

const getButtonText = () => {
    if (!has_quote_approval) {
        return 'Send to Order';
    }

    const quoteStatus = quote.status.toLowerCase();
    const hasMarginInvalid = quote.items.some((x) => !x.is_margin_valid);
    const lastApproval = items.value.length ? items.value[0] : null;
    const lastApprovalStatus = lastApproval ? lastApproval.status.toLowerCase() : null;
    const lastApprovalFlow = lastApproval ? lastApproval.flow : null;

    // Pending quote status
    if (quoteStatus === 'pending' && lastApprovalStatus === 'pending') {
        return lastApprovalFlow === 'Client review' ? 'Waiting Client Approval' : 'Waiting for Approval';
    }
    if (quoteStatus === 'approved') {
        return 'Send to Order';
    }

    // Margin review flow
    if (quote_approval_flow === 'Margin review') {
        if (hasMarginInvalid) {
            if (lastApproval && lastApprovalStatus === 'rejected') return 'Send to Approval';

            if (lastApproval && lastApprovalStatus === 'approved') {
                if (lastApprovalFlow === 'Client review') return 'Send to Order';
                return !has_client_approval ? 'Send to Order' : 'Send to Client Approval';
            }

            return 'Send to Approval';
        } else {
            if (!has_client_approval) return 'Send to Order';
            return 'Send to Client Approval';
        }
    }

    // Internal review flow
    if (quote_approval_flow === 'Internal review') {
        if (!lastApproval) return 'Send to Approval';
        if (has_client_approval && lastApprovalStatus === 'approved') return 'Send to Client Approval';
        return 'Send to Approval';
    }
};

onMounted(() => {
    getApprovals();
});

watch(quote, () => {
    getApprovals();
    getButtonText();
});
</script>
<style>
.approvals-group-button {
    display: inline-block;
    gap: 0.5rem;
    margin: 0;
    padding: 0;
    background-color: var(--color-purple);
    border-radius: 5px;
}
</style>
