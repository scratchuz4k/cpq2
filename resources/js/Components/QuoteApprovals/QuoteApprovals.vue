<template>
    <div
        class="approvals-group-button flex me-2 p-0"
        v-if="['quote', 'need_approval', 'validated', 'completed'].includes(quote.status.toLowerCase())"
    >
        <button
            v-if="quote.status.toLowerCase() === 'completed'"
            class="btn btn-primary"
            :disabled="true"
            :style="{
                borderRadius: canToggleDialog() ? '5px 0 0 5px' : '5px'
            }"
        >
            Quote Complete
        </button>

        <template v-else>
            <button
                v-if="
                    has_quote_approval === '0' ||
                    quote_approval_flow === 'margin' ||
                    (quote_approval_flow === 'internal' && quote.status.toLowerCase() === 'validated')
                "
                class="btn btn-primary"
                :disabled="items.some((x) => x.status === 'pending')"
                @click="sendToOrder()"
                :style="{
                    borderRadius: canToggleDialog() ? '5px 0 0 5px' : '5px'
                }"
            >
                <font-awesome-icon :icon="['fas', 'thumbtack']" class="me-2" />
                Send to Order
            </button>

            <button
                v-else-if="
                    tenant.configurations.quote_approval_flow === 'internal' &&
                    quote.status.toLowerCase() !== 'validated'
                "
                class="btn btn-primary"
                :disabled="items.some((x) => x.status === 'pending')"
                @click="startApproval()"
                style="border-radius: 5px 0 0 5px"
            >
                <font-awesome-icon :icon="['fas', 'thumbs-up']" class="me-2" />
                Send to Approval
            </button>
        </template>

        <button
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
        :style="{ marginTop: '4rem', minWidth: '60vw' }"
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
                        <th>Signed By</th>
                        <th>Flow</th>
                        <th>Comments</th>
                        <th class="text-center">Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in items" :key="item.id" class="align-middle">
                        <td style="width: 15%">{{ format.date(item.updated_at) }}</td>
                        <td style="width: 15%">{{ item.approver?.name }}</td>
                        <td style="width: 10%">{{ item.flow }}</td>
                        <td style="width: 50%">
                            <input
                                v-if="
                                    item.status === 'pending' &&
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
                        <td class="text-center">
                            <p
                                class="badge rounded-pill"
                                :class="{
                                    'bg-success text-white': item.status === 'approved',
                                    'bg-danger text-white': item.status === 'rejected'
                                }"
                            >
                                {{ format.capitalizeFirstLetter(item.status) }}
                            </p>
                        </td>
                        <td style="width: 10%">
                            <div
                                class="flex"
                                v-if="
                                    item.status === 'pending' &&
                                    (can('update quote_approval') || hasRole('Super Admin'))
                                "
                            >
                                <button
                                    class="btn btn-sm btn-primary me-2"
                                    @click="() => updateQuoteApproval(item, 'approved')"
                                >
                                    <font-awesome-icon :icon="['fas', 'check']" />
                                </button>
                                <button
                                    class="btn btn-sm btn-secondary"
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
import { ref, onMounted, reactive } from 'vue';
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
const quote_approval_flow = tenant.configurations.quote_approval_flow ?? 'internal';

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
        notyf.error(ex.response.data.message);
    }
};

const startApproval = async () => {
    let res = await axios.post(route('api.quote-approvals.store'), {
        quote_id: quote.id
    });
    getApprovals();
    await getQuote(quote.id);
};

const updateQuoteApproval = async (item, status) => {
    let res = await axios.put(route('api.quote-approvals.update', { id: item.id }), {
        status: status,
        comments: item.comments
    });
    getApprovals();
    await getQuote(quote.id);
};

const sendToOrder = async () => {
    if (has_quote_approval == '0') {
        try {
            let res = await axios.put(`/api/quotes/${quote.id}`, {
                status: 'COMPLETED'
            });
            await getQuote(quote.id);
        } catch (ex) {
            notyf.error(ex.response.data.message);
        }
        return;
    }
    //  &&(can('create quote_approval') || hasRole('Super Admin'))
    if (quote.status != 'validated' && !items.value.find((x) => ['pending'].includes(x.status))) {
        try {
            let res = await axios.post(route('api.quote-approvals.store'), {
                quote_id: quote.id
            });
            await getQuote(quote.id);
            getApprovals();
        } catch (ex) {
            notyf.error(ex.response.data.message);
        }
    }

    if (quote.status.toLowerCase() == 'validated') {
        try {
            let res = await axios.post(`/api/quotes/${quote.id}/complete`);
            await getQuote(quote.id);
        } catch (ex) {
            notyf.error(ex.response.data.message);
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

onMounted(() => {
    getApprovals();
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
