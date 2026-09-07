<script setup>
import { reactive, ref, computed } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import Permissions from '@/Components/Permissions.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ToggleSwitch from 'primevue/toggleswitch';
import { integerOnly, checkValue } from '@/Composables/input.js';
import InputSelect from '@/Components/InputSelect.vue';
import InputSelectMultiple from '@/Components/InputSelectMultiple.vue';

const props = defineProps({
    user: Object,
    roles: Object,
    permissions: Array
});

const emit = defineEmits(['update:modelValue']);
const proxyValue = computed({
    get() {
        return props.user;
    },
    set(val) {
        emit('update:modelValue', val);
    }
});

const page = usePage();
const form = reactive({
    role: props.user.roles.length ? props.user.roles[0] : null,
    role_id: props.user.roles.length ? props.user.roles[0].id : null,
    groups: props.user.groups.length ? [...props.user.groups] : []
});

const authUser = page.props.auth.user;
const isSelf = authUser.id === props.user.id;
const authGroupIds = (authUser.groups ?? []).map((g) => g.id);
const committedGroupIds = ref(props.user.groups.map((g) => g.id));
let lastPromptSignature = '';

// The /api/groups endpoint scopes results to the acting user's own groups plus
// (via `for_user_id`) the groups of the user being edited, so no client filter
// is needed here.
const groupParams = { for_user_id: props.user.id };

// Groups the target user is in but the acting user is not: selected but locked.
const lockedGroupIds = computed(() =>
    isSelf || !authGroupIds.length ? [] : props.user.groups.filter((g) => !authGroupIds.includes(g.id)).map((g) => g.id)
);

const onGroupsClose = async () => {
    if (!isSelf || !authGroupIds.length) {
        return;
    }

    const currentIds = form.groups.map((g) => g.id);
    const removed = committedGroupIds.value.filter((id) => !currentIds.includes(id));

    if (!removed.length) {
        return;
    }

    if (!currentIds.length) {
        notyf.error('You cannot remove all groups from your account. Please contact a company administrator');
        return;
    }

    const signature = [...removed].sort().join(',');
    if (signature === lastPromptSignature) {
        return;
    }
    lastPromptSignature = signature;

    await swal.fire({
        title: 'Are you sure?',
        text: 'Any groups removed from your own user can only be re-added by another authorized user'
    });
    // Informational only: the selection is intentionally left as-is.
};

const saveUser = () => {
    if (authUser.groups?.length && !form.groups.length) {
        notyf.error(
            isSelf
                ? 'You cannot remove all groups from your account. Please contact a company administrator'
                : 'You must keep at least one of your groups assigned to this user.'
        );
        return;
    }

    proxyValue.value.role_id = form.role ? [form.role.id] : [];
    proxyValue.value.groups = form.groups.map((item) => item.id);

    router.put(`/users/${proxyValue.value.id}`, proxyValue.value, {
        onSuccess: (page) => {
            committedGroupIds.value = form.groups.map((g) => g.id);
            lastPromptSignature = '';
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
const items = ref([
    { label: 'Home', url: '/' },
    { label: 'Settings', url: '/settings' },
    { label: 'Users', url: '/users' },
    { label: 'Edit User' }
]);
</script>
<template>
    <Head :title="'Edit User #' + user.id" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header :title="'Edit User #' + user.id" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton id="btn-save-user" @click="saveUser()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon>
                        Save
                    </PrimaryButton>
                </template>
            </Header>
            <div class="row mb-3">
                <div class="col-6">
                    <div class="mb-3 d-flex flex-column">
                        <label class="form-label" for="active-filter"> Active: </label>
                        <ToggleSwitch id="active-filter" v-model="proxyValue.is_active">
                            <template #handle>
                                <font-awesome-icon
                                    :icon="proxyValue.is_active ? ['fas', 'circle-check'] : ['far', 'circle-xmark']"
                                    class="text-primary"
                                />
                            </template>
                        </ToggleSwitch>
                    </div>
                    <div class="mb-3">
                        <label for="user-name" class="form-label">Name:</label>
                        <input id="user-name" class="form-control" v-model="proxyValue.name" required />
                    </div>
                    <div class="mb-3">
                        <label for="user-email" class="form-label">Email:</label>
                        <input id="user-email" type="email" class="form-control" v-model="proxyValue.email" required />
                    </div>
                    <div class="mb-3">
                        <label for="user-password" class="form-label">Password:</label>
                        <input
                            id="user-password"
                            type="password"
                            autocomplete="off"
                            class="form-control"
                            v-model="proxyValue.password"
                            required
                        />
                    </div>

                    <div class="mb-3">
                        <label for="user-role" class="form-label">Role:</label>
                        <InputSelect
                            id="account_id"
                            v-model="form.role"
                            url="/roles"
                            :fields="['name']"
                            :placeholder="'Select an Role'"
                        >
                        </InputSelect>
                    </div>
                    <div class="mb-3">
                        <label for="user-group" class="form-label">Groups:</label>
                        <GroupFieldHelp subject="user" />
                        <InputSelectMultiple
                            id="user-group"
                            v-model="form.groups"
                            url="/groups"
                            :fields="['name']"
                            :placeholder="'Select a Group'"
                            :params="groupParams"
                            :locked-ids="lockedGroupIds"
                            @close="onGroupsClose"
                        >
                        </InputSelectMultiple>
                    </div>
                    <div class="mb-3">
                        <label for="user-commission-rate" class="form-label">Commission Rate:</label>
                        <div class="input-group p-0">
                            <input
                                id="user-commission-rate"
                                type="number"
                                max="100"
                                @keydown="integerOnly"
                                @input="checkValue"
                                class="form-control"
                                v-model="user.commission_rate"
                            />
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="form-check p-0">
                            <input id="user-sso" type="checkbox" class="form-check-input" v-model="user.sso" />
                            <label class="form-check-label ms-2" for="user-sso">
                                Single sign-on (SSO) login only
                            </label>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <h2>User Permissions</h2>
                    <Permissions :permissions="permissions" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
