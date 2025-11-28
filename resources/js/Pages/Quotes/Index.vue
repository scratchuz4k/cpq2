<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { can } from '@composables/useAuth';
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
        axios.put(`/api/quote-approvals/${data.id}`, { approver_id: user.id });

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
            <Header :breadcrumbs="items" title="Quotes"></Header>
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
                            data: 'approvals.approver.name',
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
                            data.approvals[0] && !['PENDING_APPROVAL', 'DECLINED'].includes(value)
                                ? data.approvals[0]?.status.toUpperCase()
                                : value
                        }}</span>
                    </template>
                    <template #updated_at="{ data, value }">
                        {{ format.date(data.approvals[0]?.updated_at ?? value) }}
                    </template>
                    <template #actions="{ data, value, index }">
                        <div
                            v-if="!['COMPLETED', 'EXPIRED', 'PENDING_APPROVAL', 'VALIDATED'].includes(data.status)"
                            class="d-flex justify-content-end"
                        >
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
                            <button
                                v-if="
                                    data.status === 'PENDING_APPROVAL' &&
                                    can('update quote_approval') &&
                                    !data.approvals[0]?.approver
                                "
                                :id="'datatable-workbasket-btn-' + index + '-delete'"
                                class="btn px-2 py-0 mx-1 border-0"
                                @click="saveApproval(data.approvals[0])"
                            >
                                <font-awesome-icon icon="fas fa-user-plus" />
                            </button>
                            <Link
                                :id="'datatable-btn-' + index + '-show'"
                                :href="'quotes/' + data.id"
                                class="btn px-2 py-0 mx-1 border-0"
                            >
                                <font-awesome-icon :icon="['fas', 'eye']" />
                            </Link>
                        </div>
                    </template>
                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
