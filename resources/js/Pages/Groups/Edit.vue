<script setup>
import { Button, InputText, DataTable, Column, Panel } from 'primevue';
import { reactive, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';

const props = defineProps({
    group: Object
});

const user = ref(null);
const account = ref(null);
const group = reactive(props.group);

const saveGroup = () => {
    router.put(`/groups/${props.group.id}`, group, {
        onSuccess: (page) => {
            notyf.success('Updated with Success');
        },
        onError: (errors) => {
            let string = '';
            Object.keys(errors).forEach((key) => {
                string += errors[key] + '<br>';
            });
            notyf.error(string);
        }
    });
};

const setUserGroup = async (user, group_id) => {
    if (!user.id) {
        return;
    }

    let userGroups = user.groups.map((group) => group.id);

    if (group_id) {
        userGroups.push(group_id);
    } else {
        userGroups = userGroups.filter((x) => x !== group.id);
    }

    try {
        await axios.put('/api/users/' + user.id, {
            groups: userGroups
        });
        user.value = null;

        if (group_id) {
            notyf.success('Created with Success');
        }
        emitter.emit('datatable-refresh:group-users');
        emitter.emit('input-select-refresh:select-user');
    } catch (ex) {
        user.value = null;
        notyf.error(ex.response?.data?.message ?? ex.response?.data);
    }
};

const addAccount = async () => {
    if (!account.value) {
        return;
    }
    try {
        await axios.post('/api/group-accounts/', {
            account_id: account.value.id,
            group_id: group.id
        });
        account.value = null;
        emitter.emit('datatable-refresh:group-accounts');
        emitter.emit('input-select-refresh:select-account');
        notyf.success('Created with Success');
    } catch (ex) {
        account.value = null;
        notyf.error(ex.response?.data?.message ?? ex.response?.data);
    }
};

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'Settings', url: '/settings' },
    { label: 'Groups', url: '/users' },
    { label: 'Edit Group' }
]);
</script>
<template>
    <Head :title="'Edit Group #' + group.id" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header :title="'Edit Group #' + group.id" :breadcrumbs="items"></Header>
            <tabs>
                <tab title="General" :selected="true">
                    <div class="row mt-3">
                        <div class="col-3">
                            <label for="user-group-owner" class="form-label">Owner:</label>
                            <InputText
                                id="user-group-owner"
                                type="text"
                                class="w-100"
                                :value="group?.owner?.name"
                                :disabled="true"
                            />
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col">
                            <label for="user-group-name" class="form-label">Name:</label>
                            <InputText id="user-group-name" type="text" class="w-100" v-model="group.name" />
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col d-flex justify-content-end">
                            <Button class="btn btn-primary" @click="saveGroup()">
                                <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon>
                                Save
                            </Button>
                        </div>
                    </div>
                </tab>
                <tab title="Users">
                    <form @submit.prevent="setUserGroup(user, group.id)" class="mt-3 d-flex">
                        <div class="me-3" style="width: 100%">
                            <label class="form-label">Add Users:</label>
                            <InputSelect
                                id="select-user"
                                url="/users"
                                :fields="['name']"
                                v-model="user"
                                placeholder="Select a User"
                                :query="[{ column: 'is_active', operator: 'equals', value: 1 }]"
                            >
                            </InputSelect>
                        </div>
                        <div class="d-flex align-items-end justify-content-end">
                            <Button id="button-add-user-to-group" type="submit" class="btn btn-secondary">
                                <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                                Add
                            </Button>
                        </div>
                    </form>
                    <div class="row mt-3">
                        <div class="col">
                            <label class="form-label">Users:</label>
                            <Datatable
                                id="group-users"
                                :query="[{ column: 'groups.id', operator: 'equals', value: group.id }]"
                                :columns="[
                                    { data: 'id', title: '#', style: 'width:5%' },
                                    { data: 'name', title: 'Name', style: 'width:50%' },
                                    { data: 'created_at', title: 'Created At', style: 'width:15%' },
                                    { data: 'updated_at', title: 'Updated At', style: 'width:15%' },
                                    { data: 'actions', title: ' At', style: 'width:5%' }
                                ]"
                                route="/users"
                            >
                                <template #updated_at="{ value }">
                                    {{ format.date(value) }}
                                </template>
                                <template #created_at="{ value }">
                                    {{ format.date(value) }}
                                </template>
                                <template #actions="{ data, value, index }">
                                    <button class="btn px-2 py-0 mx-1 border-0" @click="setUserGroup(data, null)">
                                        <font-awesome-icon :icon="['fas', 'trash-alt']"></font-awesome-icon>
                                    </button>
                                </template>
                            </Datatable>
                        </div>
                    </div>
                </tab>
                <tab title="Accounts">
                    <form @submit.prevent="addAccount()" class="mt-3 d-flex">
                        <div class="me-3" style="width: 100%">
                            <label class="form-label">Add Accounts:</label>
                            <InputSelect
                                id="select-account"
                                url="/accounts"
                                :fields="['name']"
                                v-model="account"
                                placeholder="Select an Account"
                                :query="[
                                    { column: 'groups.id', operator: 'is_null', value: '' },
                                    { column: 'is_active', operator: 'equals', value: 1 }
                                ]"
                            >
                            </InputSelect>
                        </div>
                        <div class="d-flex align-items-end justify-content-end">
                            <Button id="button-add-account-to-group" type="submit" class="btn btn-secondary">
                                <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                                Add
                            </Button>
                        </div>
                    </form>
                    <div class="row mt-3">
                        <div class="col">
                            <label class="form-label">Accounts:</label>
                            <Datatable
                                id="group-accounts"
                                :allow="{
                                    update: false,
                                    delete: true,
                                    link: false
                                }"
                                :query="[{ column: 'group_id', operator: 'equals', value: group.id }]"
                                :columns="[
                                    { data: 'id', title: '#', style: 'width:5%' },
                                    { data: 'account.name', title: 'Name', style: 'width:55%' },
                                    { data: 'created_at', title: 'Created At', style: 'width:15%' },
                                    { data: 'updated_at', title: 'Updated At', style: 'width:15%' }
                                ]"
                                route="/group-accounts"
                            >
                                <template #updated_at="{ value }">
                                    {{ format.date(value) }}
                                </template>
                                <template #created_at="{ value }">
                                    {{ format.date(value) }}
                                </template>
                            </Datatable>
                        </div>
                    </div>
                </tab>
            </tabs>
        </div>
    </AuthenticatedLayout>
</template>
