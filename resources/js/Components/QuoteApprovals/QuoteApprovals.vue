<template>
    <button id="btn-shareable-open" class="btn btn-outline-primary me-2" @click="() => (showDialog = !showDialog)">
        <font-awesome-icon :icon="['fas', 'thumbs-up']"></font-awesome-icon>
        Approvals
    </button>
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
        <div class="mb-4">
            <button
                v-if="!items.find((x) => ['pending', 'approved'].includes(x.status))"
                class="btn btn-primary"
                @click="startApproval()"
            >
                Send to Approval
            </button>
        </div>
        <div>
            <table class="table">
                <thead>
                    <tr class="border-bottom border-black">
                        <th>Approved By</th>
                        <th>Flow</th>
                        <th>Comments</th>
                        <th class="text-center">Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in items" :key="item.id" class="align-middle">
                        <td style="width: 15%">{{ item.approver?.name }}</td>
                        <td style="width: 10%">{{ item.flow }}</td>
                        <td style="width: 50%">{{ item.comments }}</td>
                        <td class="text-center">
                            <p
                                class="badge rounded-pill m-1"
                                :class="{
                                    'bg-success text-white': item.status === 'approved',
                                    'bg-danger text-white': item.status === 'rejected'
                                }"
                            >
                                {{ format.capitalizeFirstLetter(item.status) }}
                            </p>
                        </td>
                        <td style="width: 10%">
                            <div class="flex" v-if="item.status === 'pending'">
                                <button
                                    class="btn btn-sm btn-primary me-2"
                                    @click="() => updateQuoteApproval(item.id, 'approved')"
                                >
                                    <font-awesome-icon :icon="['fas', 'check']" />
                                </button>
                                <button
                                    class="btn btn-sm btn-secondary"
                                    @click="() => updateQuoteApproval(item.id, 'rejected')"
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
import { quote } from '@composables/useQuote';
import queryString from 'query-string';
import Paginator from 'primevue/paginator';

const showDialog = ref(false);

const props = defineProps({});
const items = ref([]);
const meta = reactive({
    limit: 10,
    page: 1,
    total: 0,
    first: 1
});

const getApprovals = async () => {
    let query = queryString.stringify({
        order: 'desc',
        orderBy: 'created_at'
    });
    let res = await axios.get(route('api.quote-approvals.index') + '?' + query);
    items.value = res.data.data;
    meta.total = res.data.meta?.total ?? res.data.total;
    meta.first = (meta.page - 1) * meta.limit;
};

const startApproval = async () => {
    let res = await axios.post(route('api.quote-approvals.store'), {
        quote_id: quote.id
    });
    getApprovals();
};

const updateQuoteApproval = async (id, status) => {
    let res = await axios.put(route('api.quote-approvals.update', { id: id }), {
        status: status
    });
    getApprovals();
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
