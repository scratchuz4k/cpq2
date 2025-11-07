<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { createOrder } from '@/Composables/useOrders.js';
import dayjs from 'dayjs';
import { can, hasRole } from '@/Composables/useAuth';
import queryString from 'query-string';
import axios from 'axios';

const props = defineProps({
    users: {
        type: Array,
        default: []
    },
    solutions: {
        type: Array,
        default: []
    },
    accounts: {
        type: Array,
        default: []
    },
    status: {
        type: Array,
        default: []
    }
});

const page = usePage();
const user = page.props.auth.user;
const tenant = page.props.tenant;
const createOrderAndRefresh = async (id) => {
    const res = await createOrder(id);
    if (res) {
        emitter.emit('datatable-refresh');
    }
};

const expirableStatuses = ['QUOTE', 'PRICE', 'CONFIGURE'];

const dateToExpire = (date) => {
    return dayjs(date)
        .add(tenant.configurations?.quote_expiration_days ?? 30, 'day')
        .format('DD/MM/YYYY');
};

const deleteRecord = (id) => {
    swal.fire({
        title: 'Are you sure?'
    }).then(async (result) => {
        if (['cancel', 'backdrop'].includes(result.dismiss)) {
            return;
        }
        try {
            let res = await axios.delete('/api/quotes/' + id);
            if (res) {
                emitter.emit('datatable-refresh');
            }
            notyf.success('Removed with success');
        } catch (err) {
            notyf.error(err?.response?.data?.message ?? 'Something went wrong');
        }
    });
};

const saveApproval = async (data) => {
    axios.put(`/api/quote-approvals/${data.id}`, { blocker_id: user.id });
    emitter.emit('datatable-refresh:workbasket-table');
};

const items = ref([{ label: 'Home', url: '/' }, { label: 'Quotes' }]);
</script>

<template>
    <Head title="Quotes" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header :breadcrumbs="items" title="Quotes"></Header>
            <tabs>
                <tab :selected="true" title="General">
                    <Datatable
                        :allow="{}"
                        :columns="[
                            { data: 'id', title: '#', style: 'width:5%' },
                            { data: 'account.name', title: 'Account', style: 'width:13%' },
                            { data: 'solution_type.name', title: 'Solution', style: 'width:16%' },
                            { data: 'address_postcode', title: 'Postcode', style: 'width:8%' },
                            { data: 'user.name', title: 'Owner', style: 'width:15%' },
                            { data: 'status', title: 'Status', style: 'width:10%' },
                            { data: 'updated_at', title: 'Updated At', style: 'width:15%' },
                            { data: 'actions', title: '', style: 'width:13%' },
                            { data: 'items.data', title: 'Data', hide: true },
                            { data: 'items.name', title: 'Name', hide: true },
                            { data: 'address_data', title: 'Address', hide: true },
                            { data: 'account.contacts.email', title: 'Email', hide: true }
                        ]"
                        :filters="{
                            'user.name': { type: 'options', options: users.map((x) => x.name) },
                            'solution_type.name': {
                                type: 'options',
                                options: solutions.map((x) => x.name)
                            },
                            status: { type: 'options', options: status }
                        }"
                        :tableProps="{ id: 'dashboard-table' }"
                        route="/quotes"
                        :sort="{ orderBy: 'id', order: 'desc' }"
                    >
                        <template #status="{ data, value }">
                            <span
                                v-if="expirableStatuses.includes(data.status)"
                                class="badge text-bg-secondary"
                                v-tooltip
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                :title="'This quote will expire on ' + dateToExpire(data.created_at)"
                                >{{ value }}</span
                            >
                            <span v-else class="badge text-bg-secondary">{{ value }}</span>
                        </template>
                        <template #updated_at="{ value }">
                            {{ format.date(value) }}
                        </template>
                        <template #actions="{ data, value, index }">
                            <div
                                v-if="!['COMPLETED', 'EXPIRED'].includes(data.status)"
                                class="d-flex justify-content-end"
                            >
                                <button
                                    v-if="['QUOTE', 'REJECTED'].includes(data.status)"
                                    :id="'datatable-btn-' + index + '-crete-order'"
                                    :hidden="!(tenant.configurations.orders_flow_is_active === '1')"
                                    class="btn px-2 py-0 mx-1 border-0"
                                    title="Send to Order"
                                    type="button"
                                    @click="createOrderAndRefresh(data.id)"
                                >
                                    <font-awesome-icon :icon="['fas', 'thumbtack']" />
                                </button>
                                <Link
                                    :id="'datatable-btn-' + index + '-edit'"
                                    :href="'quotes/' + data.id + '/edit'"
                                    class="btn px-2 py-0 mx-1 border-0"
                                >
                                    <font-awesome-icon :icon="['fas', 'edit']" />
                                </Link>
                                <Link
                                    v-if="data.status !== ''"
                                    :id="'datatable-btn-' + index + '-show'"
                                    :href="'quotes/' + data.id"
                                    class="btn px-2 py-0 mx-1 border-0"
                                >
                                    <font-awesome-icon :icon="['fas', 'eye']" />
                                </Link>
                                <button
                                    :id="'datatable-btn-' + index + '-delete'"
                                    class="btn px-2 py-0 mx-1 border-0"
                                    @click="deleteRecord(data.id)"
                                >
                                    <font-awesome-icon icon="fas fa-trash-alt" />
                                </button>
                            </div>
                            <div v-else class="d-flex justify-content-end">
                                <Link
                                    :id="'datatable-btn-' + index + '-show'"
                                    :href="'quotes/' + data.id + '/preview'"
                                    class="btn px-2 py-0 mx-1 border-0"
                                >
                                    <font-awesome-icon :icon="['fas', 'eye']" />
                                </Link>
                            </div>
                        </template>
                    </Datatable>
                </tab>
                <tab title="Workbasket" v-if="hasRole('Super Admin') || can('update quote_approval')">
                    <Datatable
                        id="workbasket-table"
                        :allow="{}"
                        :columns="[
                            { data: 'quote.id', title: '#', style: 'width:5%' },
                            { data: 'quote.account.name', title: 'Account', style: 'width:13%' },
                            { data: 'quote.address_postcode', title: 'Postcode', style: 'width:8%', hide: true },
                            { data: 'quote.user.name', title: 'Owner', style: 'width:15%' },
                            { data: 'quote.solution_type.name', title: 'Solution', style: 'width:10%' },
                            { data: 'blocker.name', title: 'Blocker', style: 'width:13%' },
                            { data: 'status', title: 'Last State', style: 'width:15%' },
                            { data: 'updated_at', title: 'Updated', style: 'width:15%' },
                            { data: 'actions', title: '', style: 'width:13%' },
                            { data: 'quote.items.data', title: 'Data', hide: true },
                            { data: 'quote.items.name', title: 'Name', hide: true },
                            { data: 'quote.address_data', title: 'Address', hide: true },
                            { data: 'quote.account.contacts.email', title: 'Email', hide: true }
                        ]"
                        :filters="{
                            'user.name': { type: 'options', options: users.map((x) => x.name) },
                            'solution_type.name': {
                                type: 'options',
                                options: solutions.map((x) => x.name)
                            },
                            status: { type: 'options', options: status }
                        }"
                        :query="[
                            { column: 'status', operator: 'in', value: 'pending', function: 'orWhere' },
                            {
                                group: [
                                    { column: 'blocker_id', operator: 'is_null', value: '', function: 'orWhere' },
                                    { column: 'blocker_id', operator: 'equals', value: user.id }
                                ]
                            }
                        ]"
                        :tableProps="{ id: 'workbasket-table' }"
                        route="/quote-approvals"
                        :sort="{ orderBy: 'id', order: 'desc' }"
                    >
                        <template #status="{ data, value }">
                            <span
                                v-if="expirableStatuses.includes(data.status)"
                                class="badge text-bg-secondary"
                                v-tooltip
                                data-bs-toggle="tooltip"
                                data-bs-placement="top"
                                :title="'This quote will expire on ' + dateToExpire(data.created_at)"
                                >{{ value }}</span
                            >
                            <span v-else class="badge text-bg-secondary">{{ value }}</span>
                        </template>
                        <template #approvals.status="{ data, value }">
                            <span class="badge text-bg-secondary">{{ value }}</span>
                        </template>
                        <template #updated_at="{ value }">
                            {{ format.date(value) }}
                        </template>
                        <template #actions="{ data, value, index }">
                            <div class="d-flex justify-content-end">
                                <button
                                    v-if="!data.blocker"
                                    :id="'datatable-workbasket-btn-' + index + '-delete'"
                                    class="btn px-2 py-0 mx-1 border-0"
                                    @click="saveApproval(data)"
                                >
                                    <font-awesome-icon icon="fas fa-user-plus" />
                                </button>
                                <Link
                                    :id="'datatable-workbasket-btn-' + index + '-edit'"
                                    :href="'quotes/' + data.quote.id"
                                    class="btn px-2 py-0 mx-1 border-0"
                                >
                                    <font-awesome-icon :icon="['fas', 'eye']" />
                                </Link>
                            </div>
                        </template>
                    </Datatable>
                </tab>
            </tabs>
        </div>
    </AuthenticatedLayout>
</template>
