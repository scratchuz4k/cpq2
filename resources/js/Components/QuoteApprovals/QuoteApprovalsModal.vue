<template>
    <div
        v-if="['quote'].includes(quote.status.toLowerCase()) && has_quote_approval == '0'"
        class="approvals-group-button flex me-2 p-0"
    >
        <button id="quote-approval-history" @click="sendToOrder()" class="btn btn-primary">
            <font-awesome-icon
                v-if="quote.items.some((x) => !x.is_margin_valid)"
                :icon="['fas', 'thumbs-up']"
                class="me-2"
            />
            <font-awesome-icon v-else :icon="['fas', 'thumbtack']" class="me-2" />
            <span> Send to Order </span>
        </button>
    </div>
    <div
        class="approvals-group-button flex me-2 p-0"
        v-if="
            ['quote', 'pending', 'pending_client', 'approved', 'completed', 'rejected'].includes(
                quote.status.toLowerCase()
            ) && canToggleDialog()
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

        <template v-else>
            <button
                id="quote-approval-start"
                v-if="quote.status.toLowerCase() === 'approved'"
                class="btn btn-primary"
                :disabled="items.some((x) => x.status.toLowerCase() === 'pending')"
                @click="sendToOrder()"
                :style="{
                    borderRadius: canToggleDialog() ? '5px 0 0 5px' : '5px'
                }"
            >
                <font-awesome-icon
                    v-if="quote.items.some((x) => !x.is_margin_valid)"
                    :icon="['fas', 'thumbs-up']"
                    class="me-2"
                />
                <font-awesome-icon v-else :icon="['fas', 'thumbtack']" class="me-2" />
                <span> Send to Order </span>
            </button>

            <button
                id="quote-approval-start"
                v-else
                class="btn btn-primary"
                :disabled="items.some((x) => x.status.toLowerCase() === 'pending') || !can('create quote_approval')"
                @click="sendToOrder()"
                style="border-radius: 5px 0 0 5px"
            >
                <font-awesome-icon :icon="['fas', 'thumbs-up']" class="me-2" />
                <span>{{ getButtonText() }}</span>
            </button>
        </template>

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
                            <p v-else>
                                {{ item.comments }}
                            </p>
                        </td>
                        <td class="text-center" :id="'quote-approval-status-' + index" style="width: 10%">
                            <p
                                class="badge rounded-pill"
                                :class="{
                                    'bg-success text-white': item.status.toLowerCase() === 'approved',
                                    'bg-danger text-white': item.status.toLowerCase() === 'pending',
                                    'bg-error text-white': item.status.toLowerCase() === 'rejected'
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
import queryString from 'query-string';
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
const has_quote_approval = tenant.configurations.has_quote_approval ?? '0';
const quote_approval_flow = tenant.configurations.quote_approval_flow ?? 'Internal Review';
const has_client_approval = !!tenant.configurations.has_client_approval ?? false;

const getApprovals = async () => {
    let query = queryString.stringify({
        limit: meta.limit,
        page: meta.page,
        filters: JSON.stringify([{ column: 'quote_id', operator: 'equals', value: quote.id }]),
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
        //window.location = '/dashboard';
    } catch (ex) {
        notyf.error(ex.response?.data?.message ?? 'Something went wrong!');
    }
};

const sendToOrder = async () => {
    if (has_quote_approval == '0') {
        try {
            let res = await axios.put(`/api/quotes/${quote.id}`, {
                status: 'COMPLETED'
            });
            await getQuote(quote.id);
            notyf.success(`Quote completed with success!`);
            //window.location = '/dashboard';
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
            notyf.success(`Quote successfully sent for approval!`);
            //window.location = '/dashboard';
        } catch (ex) {
            notyf.error(ex.response?.data?.message ?? 'Something went wrong!');
        }
    }

    if (quote.status.toLowerCase() === 'approved') {
        try {
            let res = await axios.post(`/api/quotes/${quote.id}/complete`);
            await getQuote(quote.id);
            notyf.success(`Quote completed with success!`);
            //window.location = '/dashboard';
        } catch (ex) {
            notyf.error(ex.response?.data?.message ?? 'Something went wrong!');
        }
    }
};

const canToggleDialog = () => {
    return has_quote_approval === '1' && (can('viewAny quote_approval') || hasRole('Super Admin'));
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
    if (quote_approval_flow == 'Margin review' && !quote.items.some((x) => x.is_margin_valid)) {
        return 'Send to Order';
    }

    let lastApproval = items.value.length ? items.value[0] : null;

    if (!lastApproval) {
        return 'Send to Approval';
    }

    if (has_client_approval && lastApproval.status.toLowerCase() === 'approved') {
        return 'Send to Client Approval';
    }

    if (quote.status.toLowerCase() == 'pending_client') {
        return 'Waiting Client Approval';
    }

    if (quote.status.toLowerCase() == 'pending') {
        return 'Waiting for Approval';
    }

    return 'Send to Approval';
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
