<script setup>
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-bs5';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from "vue";
import Button from "primevue/button";
import axios from "axios";

DataTable.use(DataTablesCore);
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
    },
})

const deleteRecord = async (id) => {
    swal.fire({
        title: 'Are you sure?'
    }).then(async (result) => {
        if (['cancel', 'backdrop'].includes(result.dismiss)) {
            return
        }
        try {
            let res = await axios.delete('/api' + props.route + '/' + id)
            notyf.success('Removed with success');
            return
        } catch (err) {
            notyf.error(err?.response?.data?.message ?? 'Something went wrong')
        }

    })

}

const createOrder = async (id) => {
    let result = await swal.fire({
        title: 'Do you want to create an Order from this Quote?'
    })

    if (['cancel', 'backdrop'].includes(result.dismiss)) {
        return false
    }
    try {
        let res = await axios.put('/api/quotes/' + id,
            { status: 'REGISTRY'}
        )

        notyf.success('Order created with success');
    } catch (err) {
        debugger
        notyf.error(err?.response?.data?.message ?? 'Something went wrong')
        return false
    }
    return true
}

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'Quotes' },
]);
</script>

<template>

    <Head title="Quotes" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Quotes" :breadcrumbs="items"></Header>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Datatable :tableProps="{ id: 'dashboard-table' }" :allow="{}" :columns="[
                    { data: 'id', title: '#', style: 'width:5%' },
                    { data: 'account.name', title: 'Account', style: 'width:15%' },
                    { data: 'solution_type.name', title: 'Solution', style: 'width:20%' },
                    { data: 'user.name', title: 'Owner', style: 'width:20%' },
                    { data: 'status', title: 'Status', style: 'width:10%' },
                    { data: 'updated_at', title: 'Last Updated', style: 'width:15%' },
                    { data: 'actions', title: '', style: 'width:15%' },
                    { data: 'items.data', title: 'Last Updated', hide: true },
                    { data: 'items.name', title: 'Last Updated', hide: true },
                    { data: 'account.contacts.email', title: 'Last Updated', hide: true },
                ]" :filters="{
                    'user.name': { type: 'options', options: users.map(x => x.name) },
                    'solution_type.name': { type: 'options', options: solutions.map(x => x.name) },
                    'status': { type: 'options', options: status }
                }" route="/quotes">
                    <template #status="{ value }">
                        <span class="badge text-bg-secondary">{{ value }}</span>
                    </template>
                    <template #updated_at="{ value }">
                        {{ format.date(value) }}
                    </template>
                    <template #actions="{ data, value, index }">
                        <div v-if="!['REGISTRY', 'ORDERED'].includes(data.status)" class="d-flex justify-content-end">
                            <button type="button" title="Send to Order" :id="'datatable-btn-' + index + '-crete-order'"
                                @click="createOrder(data.id)" class="btn px-2 py-0 mx-1 border-0">
                                <font-awesome-icon :icon="['fas', 'thumbtack']" />  {{ data.id}}
                            </button>
                            <Link :id="'datatable-btn-' + index + '-edit'" :href="'quotes/' + data.id + '/edit'"
                                class="btn px-2 py-0 mx-1 border-0">
                            <font-awesome-icon :icon="['fas', 'edit']" />
                            </Link>
                            <Link :id="'datatable-btn-' + index + '-show'" :href="'quotes/' + data.id"
                                class="btn px-2 py-0 mx-1 border-0">
                            <font-awesome-icon :icon="['fas', 'eye']" />
                            </Link>
                            <button :id="'datatable-btn-' + index + '-delete'" class="btn px-2 py-0 mx-1 border-0"
                                @click="deleteRecord(data.id)">
                                <font-awesome-icon icon="fas fa-trash-alt" />
                            </button>
                        </div>
                        <div v-else class="d-flex justify-content-end">
                            <Link :id="'datatable-btn-' + index + '-show'" :href="'quotes/' + data.id + '/preview'"
                                class="btn px-2 py-0 mx-1 border-0">
                            <font-awesome-icon :icon="['fas', 'eye']" />
                            </Link>
                        </div>
                    </template>
                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
