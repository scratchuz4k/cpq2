<script setup>
import { Button, InputText } from 'primevue';
import { DatatableOptions } from '@/Components/Datatables/DatatableOptions.js';
import { FilterOperators } from '@composables/useApi';
import { can, hasRole } from '@composables/useAuth';
import { computed, reactive, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';

const props = defineProps({
    group: Object
});

const user = ref(null);
const account = ref(null);
const group = reactive(props.group);

const authUser = usePage().props.auth.user;
const authGroupIds = (authUser.groups ?? []).map((g) => g.id);
const authIsGrouped = authGroupIds.length > 0;

// CPQ-530: a user in exactly one group has nothing to add (they can only see
// users/accounts that are already in their group). Ungrouped users, and users in
// more than one group, can add.
const canAddMembers = computed(() => !authIsGrouped || authGroupIds.length > 1);

// Users/accounts offered must not already be in this group, and — for grouped
// editors — must sit in one of the editor's other groups.
const memberPickerQuery = (extra = []) => {
    const query = [{ column: 'groups.id', operator: 'not_in', value: [group.id] }, ...extra];

    if (authIsGrouped) {
        query.push({ column: 'groups.id', operator: 'in', value: authGroupIds });
    }

    return query;
};

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

const userOptions = new DatatableOptions('group-users', 'group-users');
userOptions.addColumn('id', '#');
userOptions.addColumn('user.name', 'Name');
userOptions.addColumn('created_at', 'Created At');
userOptions.addColumn('updated_at', 'Updated At');
userOptions.addQuery('group_id', FilterOperators.EQUALS, group.id);
userOptions.setAllowedActions({
    allowExport: can('export group-user') || hasRole('Super Admin')
});

const accountOptions = new DatatableOptions('group-accounts', 'group-accounts');
accountOptions.addColumn('id', '#');
accountOptions.addColumn('account.name', 'Name');
accountOptions.addColumn('created_at', 'Created At');
accountOptions.addColumn('updated_at', 'Updated At');
accountOptions.addQuery('group_id', FilterOperators.EQUALS, group.id);
accountOptions.setAllowedActions({
    destroy: true,
    allowExport: can('export group-account') || hasRole('Super Admin')
});

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
                    <form v-if="canAddMembers" @submit.prevent="setUserGroup(user, group.id)" class="mt-3 d-flex">
                        <div class="me-3" style="width: 100%">
                            <label class="form-label">Add Users:</label>
                            <InputSelect
                                id="select-user"
                                url="/users"
                                :fields="['name']"
                                v-model="user"
                                placeholder="Select a User"
                                :query="memberPickerQuery([{ column: 'is_active', operator: 'equals', value: 1 }])"
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
                            <NewDatatable :options="userOptions">
                                <template #updated_at="{ value }">
                                    <p class="small">{{ format.date(value) }}</p>
                                </template>
                                <template #created_at="{ value }">
                                    <p class="small">{{ format.date(value) }}</p>
                                </template>
                                <template #row-actions="{ data, index, close }">
                                    <button
                                        type="button"
                                        :id="'datatable-btn-' + index + '-delete'"
                                        class="datatable-actions-item datatable-actions-item-danger"
                                        @click="
                                            () => {
                                                setUserGroup(data.user, null);
                                                close();
                                            }
                                        "
                                    >
                                        <font-awesome-icon :icon="['fas', 'trash']" class="me-2" />
                                        Remove
                                    </button>
                                </template>
                            </NewDatatable>
                        </div>
                    </div>
                </tab>
                <tab title="Accounts">
                    <form v-if="canAddMembers" @submit.prevent="addAccount()" class="mt-3 d-flex">
                        <div class="me-3" style="width: 100%">
                            <label class="form-label">Add Accounts:</label>
                            <InputSelect
                                id="select-account"
                                url="/accounts"
                                :fields="['name']"
                                v-model="account"
                                placeholder="Select an Account"
                                :query="memberPickerQuery([{ column: 'is_active', operator: 'equals', value: 1 }])"
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
                            <NewDatatable :options="accountOptions">
                                <template #updated_at="{ value }">
                                    <p class="small">{{ format.date(value) }}</p>
                                </template>
                                <template #created_at="{ value }">
                                    <p class="small">{{ format.date(value) }}</p>
                                </template>
                            </NewDatatable>
                        </div>
                    </div>
                </tab>
            </tabs>
        </div>
    </AuthenticatedLayout>
</template>
