<script setup>
import { DatatableOptions } from '@/Components/Datatables/DatatableOptions.js';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { can, hasRole } from '@composables/useAuth';
import dayjs from 'dayjs';
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
                emitter.emit('datatable-refresh:dashboard-table');
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

        emitter.emit('datatable-refresh:dashboard-table');
        notyf.success('Quote assigned with success');
    } catch (err) {
        notyf.error(err?.response?.data?.message ?? 'Something went wrong');
    }
};

const options = new DatatableOptions('quotes', 'dashboard-table');
options.addColumn('id', '#');
options.addColumn('account.name', 'Account');
options.addColumn('solution_type.name', 'Solution');
options.addColumn('address_postcode', 'Postcode');
options.addColumn('user.name', 'Owner');
options.addColumn('latest_approval.approver.name', 'Approver', { isHidden: has_quote_approval === '0' });
options.addColumn('status', 'Status');
options.addColumn('updated_at', 'Updated At');
// Not displayed - only here so the keyword search also matches on them.
options.addColumn('items.data', 'Data', { isHidden: true, isSortable: false });
options.addColumn('items.name', 'Name', { isHidden: true, isSortable: false });
options.addColumn('address_data', 'Address', { isHidden: true });
options.addColumn('account.contacts.email', 'Email', { isHidden: true, isSortable: false });

options.addColumnFilter(
    'user.name',
    'select',
    props.users.map((x) => x.name)
);
options.addColumnFilter(
    'solution_type.name',
    'select',
    props.solutions.map((x) => x.name)
);
options.addColumnFilter('status', 'select', props.status);

options.setSort('desc', 'id');
options.setAllowedActions({
    allowExport: can('export quote') || hasRole('Super Admin')
});

const items = ref([{ label: 'Home', url: '/' }, { label: 'Quotes' }]);
</script>

<template>
    <Head title="Quotes" />
    <AuthenticatedLayout>
        <Header :breadcrumbs="items" title="Quotes">
            <template #buttons>
                <Link href="/quotes/create" id="account-create-btn" class="btn btn-primary me-2">
                    <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                    New Quote
                </Link>
            </template>
        </Header>
        <NewDatatable :options="options">
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
                <span v-else class="badge text-bg-secondary">{{ value.toUpperCase() }}</span>
            </template>
            <template #updated_at="{ data, value }">
                <p class="small">{{ format.date(data.approvals[0]?.updated_at ?? value) }}</p>
            </template>
            <template #row-actions="{ data, index, close }">
                <button
                    v-if="
                        data.status === 'PENDING' &&
                        (can('update quote_approval') || hasRole('Super Admin')) &&
                        !data.latest_approval.approver &&
                        data.latest_approval.flow !== 'Client review'
                    "
                    type="button"
                    :id="'datatable-workbasket-btn-' + index + '-assign'"
                    class="datatable-actions-item"
                    @click="
                        () => {
                            saveApproval(data.approvals[0]);
                            close();
                        }
                    "
                >
                    <font-awesome-icon :icon="['fas', 'user-plus']" class="me-2" />
                    Assign to me
                </button>
                <Link
                    v-if="['CONFIGURE', 'PRICE', 'QUOTE', 'REJECTED'].includes(data.status)"
                    :id="'datatable-btn-' + index + '-edit'"
                    :href="'/quotes/' + data.id + '/edit'"
                    class="datatable-actions-item"
                >
                    <font-awesome-icon :icon="['fas', 'pen-to-square']" class="me-2" />
                    Edit
                </Link>
                <Link
                    v-if="data.status !== ''"
                    :id="'datatable-btn-' + index + '-show'"
                    :href="'/quotes/' + data.id"
                    class="datatable-actions-item"
                >
                    <font-awesome-icon :icon="['fas', 'eye']" class="me-2" />
                    View
                </Link>
                <button
                    v-if="data.status !== ''"
                    type="button"
                    :id="'datatable-btn-' + index + '-delete'"
                    class="datatable-actions-item datatable-actions-item-danger"
                    @click="
                        () => {
                            deleteRecord(data.id);
                            close();
                        }
                    "
                >
                    <font-awesome-icon :icon="['fas', 'trash']" class="me-2" />
                    Delete
                </button>
            </template>
        </NewDatatable>
    </AuthenticatedLayout>
</template>
