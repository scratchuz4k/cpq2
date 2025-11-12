<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import axios from 'axios';
import ToggleSwitch from 'primevue/toggleswitch';
import { InputText } from 'primevue';
import _ from 'lodash';

const props = defineProps({
    isSuperAdmin: Boolean,
    tenant: Object,
    quote_approval_flows: Array
});

const isLoading = ref(false);
const tenant = reactive({
    ...props.tenant,
    configurations: {
        orders_flow_is_active: props.tenant.configurations.orders_flow_is_active ?? '0',
        log_system_is_active: props.tenant.configurations.log_system_is_active ?? '0',
        restrict_account_management_is_active: props.tenant.configurations.restrict_account_management_is_active ?? '0',
        quote_expiration_days: props.tenant.configurations.quote_expiration_days ?? 30,
        quote_margin_rules_active: props.tenant.configurations.quote_margin_rules_active ?? '0',
        quote_margin_rules_value: props.tenant.configurations.quote_margin_rules_value
            ? Number(props.tenant.configurations.quote_margin_rules_value)
            : 0,
        quote_margin_rules_operation: props.tenant.configurations.quote_margin_rules_operation ?? 'percentage',
        has_quote_approval: props.tenant.configurations.has_quote_approval ?? '0',
        quote_approval_flow: props.tenant.configurations.quote_approval_flow ?? 'internal'
    }
});

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
        tenant.configurations.quote_margin_rules_value = tenant.configurations.quote_margin_rules_value
            ? Number(tenant.configurations.quote_margin_rules_value)
            : 1;
    } catch (err) {
        notyf.error(err?.response?.data?.message ?? 'Something went wrong');
    } finally {
        isLoading.value = false;
    }
};

const onQuoteDaysInput = _.debounce(() => {
    let value = Number(tenant.configurations.quote_expiration_days);

    if (value < 1) value = 1;
    if (value > 365) value = 365;

    tenant.configurations.quote_expiration_days = value;
    submitTenantSettings();
}, 1000);

const setMarginDefaults = () => {
    tenant.configurations.quote_margin_rules_value = 0;
    submitTenantSettings();
};

const items = ref([{ label: 'Home', url: '/' }, { label: 'My Account', url: '/myaccount' }, { label: 'Tenants' }]);
</script>

<template>
    <Head title="Tenants" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Tenants" :breadcrumbs="items"></Header>

            <tabs>
                <tab :selected="true" title="General">
                    <div class="d-flex align-items-start mt-5">
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
                        </div>
                    </div>
                </tab>
                <tab title="Settings">
                    <div class="col xml-in-tabs">
                        <div class="mt-5">
                            Here you can configure the core settings for your tenant. Any changes will be applied across
                            the platform and reflected in all related features. Please review your settings carefully
                            before saving:
                        </div>
                        <h2 class="seperator">Misc</h2>
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-3">
                            <div class="col">
                                <div class="card bg-light border-0 settings-block h-100">
                                    <div class="card-body">
                                        <h6 class="fw-bold pe-5 mb-3">Log System</h6>
                                        <div class="log_system_is_active">
                                            <div>
                                                <label for="log-system-is-active" class="form-label lh-sm small pb-2">
                                                    <i> If this setting is active, the activate the log system </i>
                                                </label>
                                            </div>
                                            <div class="sidenote">
                                                <ToggleSwitch
                                                    id="log-system-is-active"
                                                    v-model="tenant.configurations.log_system_is_active"
                                                    @change="submitTenantSettings"
                                                    trueValue="1"
                                                    falseValue="0"
                                                    binary
                                                >
                                                    <template #handle>
                                                        <font-awesome-icon
                                                            :icon="
                                                                tenant.configurations.log_system_is_active === '1'
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
                            </div>
                            <div class="col">
                                <div class="card bg-light border-0 settings-block h-100">
                                    <div class="card-body">
                                        <h6 class="fw-bold pe-5 mb-3">Account Management</h6>
                                        <div class="restrict_account_management_is_active">
                                            <div>
                                                <label
                                                    for="active-account-management"
                                                    class="form-label lh-sm small pb-2"
                                                >
                                                    <i>
                                                        If this setting is active, users will be restricted from
                                                        managing accounts
                                                    </i>
                                                </label>
                                            </div>
                                            <div class="sidenote">
                                                <ToggleSwitch
                                                    id="active-account-management"
                                                    v-model="
                                                        tenant.configurations.restrict_account_management_is_active
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
                                                                    .restrict_account_management_is_active === '1'
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
                            </div>
                        </div>
                        <h2 class="seperator">Quotes</h2>
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-3">
                            <div class="col">
                                <div class="card bg-light border-0 settings-block h-100">
                                    <div class="card-body">
                                        <h6 class="fw-bold pe-5 mb-3">Expire days</h6>
                                        <div class="quote-expiration-days">
                                            <div>
                                                <label for="quote-expiration-days" class="form-label lh-sm small pb-2">
                                                    <i>
                                                        Set the number of days a quote takes to expire. (Should be a
                                                        number between 1 and 365)
                                                    </i>
                                                </label>
                                            </div>
                                            <div>
                                                <InputText
                                                    type="number"
                                                    class="w-100"
                                                    id="quote-expiration-days"
                                                    v-model="tenant.configurations.quote_expiration_days"
                                                    :min="1"
                                                    :max="365"
                                                    @input="onQuoteDaysInput"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col">
                                <div class="card bg-light border-0 settings-block h-100">
                                    <div class="card-body">
                                        <h6 class="fw-bold pe-5 mb-3">Approvals</h6>
                                        <div class="quote-expiration-days">
                                            <div>
                                                <label for="quote-expiration-days" class="form-label lh-sm small pb-2">
                                                    <i> Activate this setting add approvals in quotes </i>
                                                </label>
                                            </div>
                                            <div class="sidenote">
                                                <ToggleSwitch
                                                    id="active-margin-rules"
                                                    v-model="tenant.configurations.has_quote_approval"
                                                    @change="submitTenantSettings"
                                                    trueValue="1"
                                                    falseValue="0"
                                                    binary
                                                >
                                                    <template #handle>
                                                        <font-awesome-icon
                                                            :icon="
                                                                tenant.configurations.has_quote_approval === '1'
                                                                    ? ['fas', 'circle-check']
                                                                    : ['far', 'circle-xmark']
                                                            "
                                                            class="text-primary"
                                                        />
                                                    </template>
                                                </ToggleSwitch>
                                            </div>
                                            <div class="row g-3 mb-3">
                                                <div class="col">
                                                    <div class="mb-3">
                                                        <label>Approvals Flow:</label>
                                                        <select
                                                            id="quote-approval-flow"
                                                            class="form-select"
                                                            v-model="tenant.configurations.quote_approval_flow"
                                                            @change="submitTenantSettings()"
                                                        >
                                                            <option
                                                                :value="approval.value"
                                                                v-for="approval in quote_approval_flows"
                                                            >
                                                                {{ approval.name }}
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <div
                                                        class="mb-3"
                                                        v-if="tenant.configurations.quote_approval_flow === 'margin'"
                                                    >
                                                        <label>Margin value:</label>
                                                        <div class="row g-3 mb-3">
                                                            <div class="col">
                                                                <input
                                                                    id="margin-value"
                                                                    type="number"
                                                                    class="form-control"
                                                                    v-model="
                                                                        tenant.configurations.quote_margin_rules_value
                                                                    "
                                                                    @input="onQuoteDaysInput"
                                                                    min="0"
                                                                    max="100"
                                                                />
                                                            </div>
                                                            <div class="col">
                                                                <select
                                                                    id="margin-operation"
                                                                    class="form-select"
                                                                    v-model="
                                                                        tenant.configurations
                                                                            .quote_margin_rules_operation
                                                                    "
                                                                    @change="setMarginDefaults()"
                                                                >
                                                                    <option value="percentage">%</option>
                                                                    <option value="value">£</option>
                                                                </select>
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
                </tab>
            </tabs>
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
    width: 40%;
    height: 1px;
    background-color: var(--light-grey);
    position: absolute;
    left: 0;
    top: 50%;
}

.seperator:after {
    content: '';
    width: 40%;
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

.sidenote {
    position: absolute;
    right: 1rem;
    top: 0.875rem;
}
</style>
