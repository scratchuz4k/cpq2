<script setup>
import { DatatableOptions } from '@/Components/Datatables/DatatableOptions.js';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import { Dialog, Button, InputText } from 'primevue';
import { can, hasRole } from '@/Composables/useAuth';

const props = defineProps({
    results: {
        type: Object,
        default: null
    }
});

const showGroupModel = ref(false);
const showForm = ref(false);
const selectedItems = ref([]);
const isLoading = ref(false);

const onFileChange = (event) => {
    formFile.file = event.target.files[0];
};

const formFile = reactive({
    file: null
});

const newGroup = ref(null);
const submitFileForm = () => {
    isLoading.value = true;
    router.post(
        `/users/import`,
        {
            file: formFile.file
        },
        {
            onSuccess: () => {
                notyf.success(`File imported successfully`);
                isLoading.value = false;
            },
            onError: (errors) => {
                let string = '';
                Object.keys(errors).forEach((key) => {
                    string += errors[key] + '<br>';
                });
                notyf.error(string);
                isLoading.value = false;
            }
        }
    );
};

const saveNewGroup = async () => {
    try {
        await axios.post('/api/groups', { name: newGroup.value });
        showGroupModel.value = false;
        newGroup.value = '';
        emitter.emit('datatable-refresh:groups-table');
        notyf.success('Created with Success');
    } catch (ex) {
        notyf.error(ex.response?.data?.message ?? 'Something went wrong!');
        showGroupModel.value = false;
    }
};

const userOptions = new DatatableOptions('users', 'users-table');
userOptions.addColumn('id', '#');
userOptions.addColumn('is_active', 'Active');
userOptions.addColumn('sso', 'SSO');
userOptions.addColumn('name', 'Name');
userOptions.addColumn('email', 'Email');
userOptions.addColumn('commission_rate', 'Commission');
userOptions.addColumn('roles.name', 'Role', { isSortable: false });
userOptions.addColumn('groups.name', 'Group', { isSortable: false });
userOptions.addColumnFilter('is_active', 'checkbox');
userOptions.addColumnFilter('sso', 'checkbox');
userOptions.setAllowedActions({
    update: true,
    destroy: true,
    allowExport: can('export user') || hasRole('Super Admin'),
    allowImport: true
});

const groupOptions = new DatatableOptions('groups', 'groups-table');
groupOptions.addColumn('id', '#');
groupOptions.addColumn('name', 'Name');
groupOptions.addColumn('created_at', 'Created At');
groupOptions.addColumn('updated_at', 'Updated At');
groupOptions.setAllowedActions({
    update: true,
    destroy: true,
    allowExport: can('export group') || hasRole('Super Admin')
});

const items = ref([{ label: 'Home', url: '/' }, { label: 'Settings', url: '/settings' }, { label: 'Users' }]);
</script>
<template>
    <Head title="Users" />
    <AuthenticatedLayout>
        <Header title="Users" :breadcrumbs="items">
            <template #buttons>
                <button
                    v-if="hasRole('Super Admin') || can('viewAny group')"
                    type="button"
                    id="link-to-create-group"
                    class="btn btn-outline-primary me-2"
                    @click="showGroupModel = !showGroupModel"
                >
                    <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                    New Group
                </button>
                <Link id="link-to-create-user" :href="route('users.create')" class="btn btn-primary me-2">
                    <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                    New User
                </Link>
            </template>
        </Header>
        <tabs id="users">
            <tab title="Users" :selected="true">
                <Dialog
                    v-model:visible="showForm"
                    id="csv-import-form"
                    modal
                    :style="{ marginTop: '4rem' }"
                    :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
                    :draggable="false"
                    position="top"
                >
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mb-4 pb-4 border-bottom">
                        <form @submit.prevent="submitFileForm" enctype="multipart/form-data">
                            <h4>Import Users from CSV</h4>
                            <p>
                                Your CSV must contain the following columns: name*, email*, password, sso, managername.
                                * must not be blank
                            </p>
                            <input type="file" accept=".csv" required @input="onFileChange" />
                            <button type="submit" class="btn btn-primary ms-2" :disabled="isLoading">
                                <span v-if="isLoading">
                                    <font-awesome-icon :icon="['fa', 'spinner']" class="fa-spin"></font-awesome-icon>
                                    Uploading...
                                </span>
                                <span v-else>Upload</span>
                            </button>
                        </form>
                    </div>
                </Dialog>
                <div class="mt-3">
                    <NewDatatable :options="userOptions">
                        <template #sso="{ value }">
                            <font-awesome-icon
                                v-if="value === true"
                                :icon="['fas', 'circle-check']"
                                style="font-size: 1.125rem"
                                class="text-primary sso-login-active"
                            />
                            <font-awesome-icon
                                v-else
                                :icon="['far', 'circle-xmark']"
                                style="font-size: 1.125rem"
                                class="text-secondary sso-login-inactive"
                            />
                        </template>
                        <template #is_active="{ data, value }">
                            <ToggleActive :data="data" :value="value" route="users" :eventName="'treetable-refresh'" />
                        </template>
                        <template #commission_rate="{ value }">
                            <p class="small">{{ value }}%</p>
                        </template>
                    </NewDatatable>
                </div>
            </tab>
            <tab title="Groups" v-if="hasRole('Super Admin') || can('viewAny group')">
                <div class="mt-3">
                    <NewDatatable :options="groupOptions">
                        <template #updated_at="{ value }">
                            <p class="small">{{ format.date(value) }}</p>
                        </template>
                        <template #created_at="{ value }">
                            <p class="small">{{ format.date(value) }}</p>
                        </template>
                    </NewDatatable>
                </div>
            </tab>
        </tabs>
        <Dialog
            v-model:visible="showGroupModel"
            modal
            header="New Group"
            :style="{ marginTop: '4rem' }"
            :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
            :draggable="false"
            position="top"
        >
            <form class="row" @submit.prevent="saveNewGroup()">
                <div class="col">
                    <label for="group-create-name" class="form-label">Name:</label>
                    <InputText id="group-create-name" type="text" class="w-100" v-model="newGroup" />
                </div>
                <div class="d-flex justify-content-end mt-3">
                    <div class="btn btn-secondary me-2" @click="showGroupModel = false">Cancel</div>
                    <Button id="group-create-submit" class="btn btn-primary" type="submit"> Save </Button>
                </div>
            </form>
        </Dialog>
    </AuthenticatedLayout>
</template>
