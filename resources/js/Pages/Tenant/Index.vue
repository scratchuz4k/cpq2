<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import Button from 'primevue/button';
import axios from 'axios';
import ToggleSwitch from 'primevue/toggleswitch';
import InputNumber from 'primevue/inputnumber';
import _ from 'lodash';

const props = defineProps({
    isSuperAdmin: Boolean,
    tenant: Object
});

const isLoading = ref(false);
const event = ref(null);
const tenant = reactive({
    ...props.tenant,
    configurations: {
        orders_flow_is_active: props.tenant.configurations.orders_flow_is_active ?? '0',
        log_system_is_active: props.tenant.configurations.log_system_is_active ?? '0',
        restrict_account_management_is_active: props.tenant.configurations.restrict_account_management_is_active ?? '0',
        quote_expiration_days: props.tenant.configurations.quote_expiration_days ?? 14
    }
});

const item = ref(null);

const submitFileForm = async () => {
    isLoading.value = true;

    let success_message = tenant.sso_id ? 'File uploaded with Success' : 'File deleted with Success';

    let data = {
        sso_id: tenant.sso_id
    };
    try {
        let res = await axios.put(`/api/tenants/` + tenant.id, data);

        Object.assign(tenant, res.data);
        if (!tenant.file) tenant.sso_text = null;
        notyf.success(success_message);
    } catch (err) {
        notyf.error(err?.response?.data?.message ?? 'Something went wrong');
    }
};

const submitLogoForm = async () => {
    isLoading.value = true;

    let success_message = tenant.logo_id ? 'Logo uploaded with Success' : 'Logo deleted with Success';
    let data = {
        logo_id: tenant.logo_id
    };
    try {
        let res = await axios.put(`/api/tenants/` + tenant.id, data);
        Object.assign(tenant, res.data);
        notyf.success(success_message);
    } catch (err) {
        notyf.error(err?.response?.data?.message ?? 'Something went wrong');
    }

    isLoading.value = false;
};

const submitTextForm = async () => {
    let data = {
        sso_text_area: tenant.file ? tenant.file?.file_content : tenant.sso_text
    };

    try {
        let res = await axios.put(`/api/tenants/` + tenant.id, data);
        Object.assign(tenant, res.data);
        notyf.success('Content updated successfully');
    } catch (err) {
        notyf.error(err?.response?.data?.message ?? 'Something went wrong');
    }
    isLoading.value = false;
};

const submitTenantSettings = async () => {
    try {
        let res = await axios.put(`/api/tenants/` + tenant.id, tenant);
        Object.assign(tenant, res.data);
        notyf.success('Content updated successfully');
    } catch (err) {
        notyf.error(err?.response?.data?.message ?? 'Something went wrong');
    } finally {
        isLoading.value = false;
    }
};

const submitTenantExprire = _.debounce(submitTenantSettings, 1000);

// const onQuoteDaysInput = (value) => {
//     const min = 1;
//     const max = 365;
//
//     const clamped = Math.min(Math.max(value.value, min), max);
//     tenant.configurations.quote_expiration_days = value.value;
//
//     if (!isNaN(clamped)) {
//         submitTenantExprire();
//     }
// };

const onQuoteDaysInput = () => {
    let value = tenant.configurations.quote_expiration_days;
    value = Number(value);

    if (value < 1) value = 1;
    if (value > 365) value = 365;

    if (value !== tenant.configurations.quote_expiration_days) {
        tenant.configurations.quote_expiration_days = value;
    }
    submitTenantExprire();
};

const items = ref([{ label: 'Home', url: '/' }, { label: 'My Account', url: '/myaccount' }, { label: 'Tenants' }]);
</script>

<template>
    <Head title="Tenants" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Tenants" :breadcrumbs="items"></Header>
            <div class="containter">
                <div class="row">
                    <div class="d-flex align-items-start">
                        <div
                            class="nav flex-column nav-pills me-3"
                            id="v-pills-tab"
                            role="tablist"
                            aria-orientation="vertical"
                        >
                            <button
                                class="nav-link active"
                                id="v-pills-sso-tab"
                                data-bs-toggle="pill"
                                data-bs-target="#v-pills-sso"
                                type="button"
                                role="tab"
                                aria-controls="v-pills-sso"
                                aria-selected="true"
                            >
                                SSO
                            </button>
                            <button
                                class="nav-link"
                                id="v-pills-logo-tab"
                                data-bs-toggle="pill"
                                data-bs-target="#v-pills-logo"
                                type="button"
                                role="tab"
                                aria-controls="v-pills-logo"
                                aria-selected="false"
                            >
                                Logo
                            </button>
                            <button
                                class="nav-link"
                                id="v-pills-orders-tab"
                                data-bs-toggle="pill"
                                data-bs-target="#v-pills-orders"
                                type="button"
                                role="tab"
                                aria-controls="v-pills-orders"
                                aria-selected="false"
                            >
                                Settings
                            </button>
                        </div>
                        <div class="tab-content ps-3 w-100" id="v-pills-tabContent">
                            <div
                                class="tab-pane fade show active"
                                id="v-pills-sso"
                                role="tabpanel"
                                aria-labelledby="v-pills-sso-tab"
                            >
                                <div class="col xml-in-tabs">
                                    <h4>Import XML file for {{ tenant.name }}</h4>
                                    <p>The file to upload must be a .xml</p>
                                    <form @submit.prevent="submitFileForm" enctype="multipart/form-data">
                                        <InputResource
                                            v-model="tenant.sso_id"
                                            :resource="tenant.file"
                                            :acceptedFiles="['text/xml']"
                                            @change="submitFileForm"
                                        ></InputResource>
                                    </form>
                                    <div class="seperator">OR</div>
                                    <div class="mt-4">
                                        <h4>Input SSO configuration for {{ tenant.name }}</h4>
                                        <p>Please provide a valid xml syntax</p>
                                        <form @submit.prevent="submitTextForm" enctype="multipart/form-data">
                                            <textarea
                                                v-if="tenant.file"
                                                v-model="tenant.file.file_content"
                                                class="w-100 p-4"
                                                rows="10"
                                            ></textarea>
                                            <textarea
                                                v-else
                                                v-model="tenant.sso_text"
                                                class="w-100 p-4"
                                                rows="10"
                                            ></textarea>
                                            <p class="small">* This will update the file content</p>
                                            <button id="submit-sso-textarea" type="submit" class="btn btn-primary mt-2">
                                                <font-awesome-icon class="me-2" :icon="['fas', 'save']" />
                                                Save
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="tab-pane fade"
                                id="v-pills-logo"
                                role="tabpanel"
                                aria-labelledby="v-pills-logo-tab"
                            >
                                <div class="col xml-in-tabs">
                                    <div class="d-flex">
                                        <div class="logo-form">
                                            <h4>Import Logo for {{ tenant.name }}</h4>
                                            <p>The file to upload must be a .jpeg, .jpg, .png, .gif</p>
                                            <form @submit.prevent="submitLogoForm" enctype="multipart/form-data">
                                                <InputResource
                                                    v-model="tenant.logo_id"
                                                    :resource="tenant.logo"
                                                    :showLikeLine="false"
                                                    :acceptedFiles="[
                                                        'image/jpeg',
                                                        'image/jpg',
                                                        'image/png',
                                                        'image/gif'
                                                    ]"
                                                    @change="submitLogoForm"
                                                ></InputResource>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="tab-pane fade"
                                id="v-pills-orders"
                                role="tabpanel"
                                aria-labelledby="v-pills-orders-tab"
                            >
                                <div class="col xml-in-tabs">
                                    <div class="d-flex">
                                        <div class="orders-form w-100">
                                            <h4>Settings</h4>
                                            <div class="row row-cols-4 mt-4">
                                                <div class="card bg-light border-0 settings-block m-2">
                                                    <div class="card-body">
                                                        <h6 class="fw-bold">Orders Flow</h6>
                                                        <div class="orders_flow_is_active">
                                                            <div>
                                                                <label
                                                                    for="active-orders-flow"
                                                                    class="form-label lh-sm small pb-2"
                                                                >
                                                                    <i
                                                                        >If this setting is active, the order management
                                                                        will be activated.</i
                                                                    >
                                                                </label>
                                                            </div>
                                                            <div>
                                                                <ToggleSwitch
                                                                    id="active-orders-flow"
                                                                    v-model="
                                                                        tenant.configurations.orders_flow_is_active
                                                                    "
                                                                    @change="submitTenantSettings"
                                                                    trueValue="1"
                                                                    falseValue="0"
                                                                    binary
                                                                >
                                                                    <template #handle>
                                                                        <font-awesome-icon
                                                                            :icon="
                                                                                tenant.configurations
                                                                                    .orders_flow_is_active === '1'
                                                                                    ? ['fas', 'circle-check']
                                                                                    : ['far', 'circle-xmark']
                                                                            "
                                                                            class="text-primary"
                                                                        />
                                                                    </template>
                                                                </ToggleSwitch>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card bg-light border-0 settings-block m-2">
                                                    <div class="card-body">
                                                        <h6 class="fw-bold">Log System</h6>
                                                        <div class="log_system_is_active">
                                                            <div>
                                                                <label
                                                                    for="active-orders-flow"
                                                                    class="form-label lh-sm small pb-2"
                                                                >
                                                                    <i
                                                                        >If this setting is active, the activate the log
                                                                        system</i
                                                                    >
                                                                </label>
                                                            </div>
                                                            <div>
                                                                <ToggleSwitch
                                                                    id="active-orders-flow"
                                                                    v-model="tenant.configurations.log_system_is_active"
                                                                    @change="submitTenantSettings"
                                                                    trueValue="1"
                                                                    falseValue="0"
                                                                    binary
                                                                >
                                                                    <template #handle>
                                                                        <font-awesome-icon
                                                                            :icon="
                                                                                tenant.configurations
                                                                                    .log_system_is_active === '1'
                                                                                    ? ['fas', 'circle-check']
                                                                                    : ['far', 'circle-xmark']
                                                                            "
                                                                            class="text-primary"
                                                                        />
                                                                    </template>
                                                                </ToggleSwitch>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card bg-light border-0 settings-block m-2">
                                                    <div class="card-body">
                                                        <h6 class="fw-bold">Account Management</h6>
                                                        <div class="restrict_account_management_is_active">
                                                            <div>
                                                                <label
                                                                    for="active-orders-flow"
                                                                    class="form-label lh-sm small pb-2"
                                                                >
                                                                    <i>
                                                                        If this setting is active, users will be
                                                                        restricted from managing accounts
                                                                    </i>
                                                                </label>
                                                            </div>
                                                            <div>
                                                                <ToggleSwitch
                                                                    id="active-orders-flow"
                                                                    v-model="
                                                                        tenant.configurations
                                                                            .restrict_account_management_is_active
                                                                    "
                                                                    @change="submitTenantSettings"
                                                                    trueValue="1"
                                                                    falseValue="0"
                                                                    binary
                                                                >
                                                                    <template #handle>
                                                                        <font-awesome-icon
                                                                            :icon="
                                                                                tenant.configurations
                                                                                    .restrict_account_management_is_active ===
                                                                                '1'
                                                                                    ? ['fas', 'circle-check']
                                                                                    : ['far', 'circle-xmark']
                                                                            "
                                                                            class="text-primary"
                                                                        />
                                                                    </template>
                                                                </ToggleSwitch>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="card bg-light border-0 settings-block m-2">
                                                    <div class="card-body">
                                                        <h6 class="fw-bold">Quote expire days</h6>
                                                        <div class="quote-expiration-days">
                                                            <div>
                                                                <label
                                                                    for="quote-expiration-days"
                                                                    class="form-label lh-sm small pb-2"
                                                                >
                                                                    <i>
                                                                        Set the number of days a quote takes to expire.
                                                                        (Should be a number between 1 and 365)
                                                                    </i>
                                                                </label>
                                                            </div>
                                                            <div>
                                                                <InputNumber
                                                                    id="quote-expiration-days"
                                                                    v-model="
                                                                        tenant.configurations.quote_expiration_days
                                                                    "
                                                                    @input="onQuoteDaysInput"
                                                                    :min="1"
                                                                    :max="365"
                                                                    fluid
                                                                />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.seperator {
    position: relative;
    text-align: center;
    margin: 2rem auto;
}

.seperator:before {
    content: '';
    width: 45%;
    height: 1px;
    background-color: var(--light-grey);
    position: absolute;
    left: 0;
    top: 50%;
}

.seperator:after {
    content: '';
    width: 45%;
    height: 1px;
    background-color: var(--light-grey);
    position: absolute;
    right: 0;
    top: 50%;
}

.tab-content {
    border-left: 1px solid var(--light-grey);
}

.nav-link {
    color: var(--color-purple);
    font-weight: 600;
}

.nav-link.active {
    background-color: var(--color-purple);
}
</style>
