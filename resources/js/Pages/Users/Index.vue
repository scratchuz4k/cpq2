<script setup>
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

const showModal = ref(false);
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

const commission = ref(0);
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

const saveCommission = async (data, commission, toggleModal = null) => {
    try {
        let res = await axios.put(`/api/users/${data.id}`, { commission_rate: commission });
        data.commission_rate = commission;
        if (toggleModal !== null) toggleModal();
    } catch (e) {
        notyf.error(e.message);
        if (toggleModal !== null) toggleModal();
    }
};

const saveCommissionBulk = (items, toggleModal = null) => {
    items.forEach((item) => {
        saveCommission(item, commission.value, toggleModal);
    });
    showModal.value = false;
    commission.value = 0;

    notyf.success('Updated with Success');
};

const saveNewGroup = async () => {
    try {
        await axios.post('/api/groups', { name: newGroup.value });
        showGroupModel.value = false;
        emitter.emit('datatable-refresh');
        notyf.success('Created with Success');
    } catch (ex) {
        notyf.error(ex.response?.data?.message ?? 'Something went wrong!');
        showGroupModel.value = false;
    }
};

const items = ref([{ label: 'Home', url: '/' }, { label: 'My Account', url: '/myaccount' }, { label: 'Users' }]);
</script>
<template>
    <Head title="Users" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Users" :breadcrumbs="items">
                <template #buttons></template>
            </Header>
            <tabs>
                <tab title="Users" :selected="true">
                    <div class="d-flex mt-3 justify-content-end">
                        <Button
                            class="btn btn-secondary me-2"
                            @click="showModal = true"
                            :disabled="!(selectedItems.length > 0)"
                            >Add Commission
                        </Button>
                        <Link href="/imports?model=User" id="product-import-btn" class="btn btn-secondary me-2">
                            <font-awesome-icon :icon="['fas', 'file-import']"></font-awesome-icon>
                            Import Users
                        </Link>
                        <Link id="link-to-create-user" :href="route('users.create')" class="btn btn-primary me-2">
                            <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                            New User
                        </Link>
                    </div>
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
                                    Your CSV must contain the following columns: name*, email*, password, sso,
                                    managername. * must not be blank
                                </p>
                                <input type="file" accept=".csv" required @input="onFileChange" />
                                <button type="submit" class="btn btn-primary ms-2" :disabled="isLoading">
                                    <span v-if="isLoading">
                                        <font-awesome-icon
                                            :icon="['fa', 'spinner']"
                                            class="fa-spin"
                                        ></font-awesome-icon>
                                        Uploading...
                                    </span>
                                    <span v-else>Upload</span>
                                </button>
                            </form>
                        </div>
                    </Dialog>
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-3">
                        <Datatable
                            :tableProps="{ id: 'users-table' }"
                            id="users-table"
                            :allow="{
                                update: true,
                                delete: true,
                                link: false,
                                commission: true,
                                create: true
                            }"
                            :columns="[
                                { data: 'id', title: '#' },
                                { data: 'is_active', title: 'Active', style: 'width:5%' },
                                { data: 'sso', title: 'SSO', style: 'width:4%' },
                                { data: 'name', title: 'Name' },
                                { data: 'email', title: 'Email' },
                                { data: 'commission_rate', title: 'Commission' },
                                { data: 'roles.name', title: 'Role' },
                                { data: 'group.name', title: 'Group' }
                            ]"
                            route="/users"
                        >
                            <template #dialog_content="{ data, toggleModal }">
                                <div class="panel">
                                    <div class="row">
                                        <label for="user-commission-rate" class="form-label">Commission Rate:</label>
                                        <div class="input-group">
                                            <input
                                                id="user-commission-rate"
                                                type="number"
                                                class="form-control"
                                                v-model="commission"
                                            />
                                            <span class="input-group-text">%</span>
                                        </div>
                                        <div class="d-flex justify-content-end mt-3">
                                            <div class="btn btn-secondary me-2" @click="toggleModal()">Cancel</div>
                                            <Button
                                                class="btn btn-primary"
                                                @click="saveCommissionBulk([data], toggleModal)"
                                            >
                                                Save
                                            </Button>
                                        </div>
                                    </div>
                                </div>
                            </template>
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
                                <ToggleActive
                                    :data="data"
                                    :value="value"
                                    route="users"
                                    :eventName="'treetable-refresh'"
                                />
                            </template>
                            <template #roles.name="{ value }">
                                <span class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm">
                                    {{ value }}
                                </span>
                            </template>
                            <template #group.name="{ value }">
                                <span class="badge text-bg-secondary">{{ value }}</span>
                            </template>
                            <template #commission_rate="{ value }"> {{ value }}%</template>
                        </Datatable>
                    </div>
                    <Dialog
                        v-model:visible="showModal"
                        modal
                        :style="{ marginTop: '4rem' }"
                        :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
                        :draggable="false"
                        position="top"
                    >
                        <div class="panel">
                            <div class="row">
                                <label for="user-bulk-commission-rate" class="form-label">Commission Rate:</label>
                                <div class="input-group">
                                    <input
                                        id="user-bulk-commission-rate"
                                        type="number"
                                        class="form-control"
                                        v-model="commission"
                                    />
                                    <span class="input-group-text">%</span>
                                </div>
                                <div class="d-flex justify-content-end mt-3">
                                    <div class="btn btn-secondary me-2" @click="showModal = false">Cancel</div>
                                    <Button
                                        class="btn btn-primary"
                                        @click="saveCommissionBulk(selectedItems)"
                                        :disabled="!(selectedItems.length > 0)"
                                    >
                                        Save
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </Dialog>
                </tab>
                <tab title="Groups" v-if="hasRole('Super Admin') || can('viewAny group')">
                    <div class="d-flex mt-3 justify-content-end">
                        <Button
                            id="link-to-create-group"
                            class="btn btn-primary me-2"
                            @click="showGroupModel = !showGroupModel"
                        >
                            <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                            New Group
                        </Button>
                    </div>
                    <Datatable
                        :tableProps="{ id: 'roles-table' }"
                        :allow="{
                            update: true,
                            delete: true,
                            link: false
                        }"
                        :columns="[
                            { data: 'id', title: '#', style: 'width:5%' },
                            { data: 'name', title: 'Name', style: 'width:55%' },
                            { data: 'created_at', title: 'Created At', style: 'width:15%' },
                            { data: 'updated_at', title: 'Updated At', style: 'width:15%' }
                        ]"
                        route="/groups"
                    >
                        <template #updated_at="{ value }">
                            {{ format.date(value) }}
                        </template>
                        <template #created_at="{ value }">
                            {{ format.date(value) }}
                        </template>
                    </Datatable>

                    <Dialog
                        v-model:visible="showGroupModel"
                        modal
                        header="New Group"
                        :style="{ marginTop: '4rem' }"
                        :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
                        :draggable="false"
                        position="top"
                    >
                        <div class="panel">
                            <form class="row" @submit.prevent="saveNewGroup()">
                                <div class="col">
                                    <label for="group-create-name" class="form-label">Name:</label>
                                    <InputText id="group-create-name" type="text" class="w-100" v-model="newGroup" />
                                </div>
                                <div class="d-flex justify-content-end mt-3">
                                    <div class="btn btn-secondary me-2" @click="showGroupModel = false">Cancel</div>
                                    <Button id="group-create-submit" class="btn btn-primary" type="submit">
                                        Save
                                    </Button>
                                </div>
                            </form>
                        </div>
                    </Dialog>
                </tab>
            </tabs>
        </div>
    </AuthenticatedLayout>
</template>
