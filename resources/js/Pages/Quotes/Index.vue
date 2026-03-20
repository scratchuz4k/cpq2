<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { can, hasRole } from '@composables/useAuth';
import dayjs from 'dayjs';
import axios from 'axios';
import Datatable from '@/Components/Datatable.vue';

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
const has_quote_approval = tenant.configurations.has_quote_approval ?? '0';

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
    try {
        await axios.put(`/api/quote-approvals/${data.id}`, { approver_id: user.id });

        emitter.emit('datatable-refresh');
        notyf.success('Quote assigned with success');
    } catch (err) {
        notyf.error(err?.response?.data?.message ?? 'Something went wrong');
    }
};

const items = ref([{ label: 'Home', url: '/' }, { label: 'Quotes' }]);
</script>

<template>
    <Head title="Quotes" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header :breadcrumbs="items" title="Quotes">
                <template #buttons>
                    <Link href="/quotes/create" id="account-create-btn" class="btn btn-primary me-2">
                        <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                        New Quote
                    </Link>
                </template>
            </Header>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Datatable
                    :allow="{}"
                    :columns="[
                        { data: 'id', title: '#', style: 'width:5%' },
                        { data: 'account.name', title: 'Account', style: 'width:13%' },
                        { data: 'solution_type.name', title: 'Solution', style: 'width:16%' },
                        { data: 'address_postcode', title: 'Postcode', style: 'width:8%' },
                        { data: 'user.name', title: 'Owner', style: 'width:15%' },
                        {
                            data: 'latest_approval.approver.name',
                            title: 'Approver',
                            style: 'width:15%',
                            hide: has_quote_approval === '0'
                        },
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
                            >{{ value.toUpperCase() }}</span
                        >
                        <span v-else class="badge text-bg-secondary">{{
                            data.approvals[0] && !['PENDING', 'REJECTED'].includes(value)
                                ? data.approvals[0]?.status.toUpperCase()
                                : value
                        }}</span>
                    </template>
                    <template #updated_at="{ data, value }">
                        {{ format.date(data.approvals[0]?.updated_at ?? value) }}
                    </template>
                    <template #actions="{ data, value, index }">
                        <div class="d-flex justify-content-end">
                            <button
                                v-if="
                                    data.status === 'PENDING' &&
                                    (can('update quote_approval') || hasRole('Super Admin')) &&
                                    !data.latest_approval.approver &&
                                    data.latest_approval.flow !== 'Client review'
                                "
                                :id="'datatable-workbasket-btn-' + index + '-assign'"
                                class="btn px-2 py-0 mx-1 border-0"
                                @click="saveApproval(data.approvals[0])"
                            >
                                <font-awesome-icon icon="fas fa-user-plus" />
                            </button>
                            <Link
                                v-if="['CONFIGURE', 'PRICE', 'QUOTE', 'REJECTED'].includes(data.status)"
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
                                v-if="data.status !== ''"
                                :id="'datatable-btn-' + index + '-delete'"
                                class="btn px-2 py-0 mx-1 border-0"
                                @click="deleteRecord(data.id)"
                            >
                                <font-awesome-icon icon="fas fa-trash-alt" />
                            </button>
                        </div>
                    </template>
                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
