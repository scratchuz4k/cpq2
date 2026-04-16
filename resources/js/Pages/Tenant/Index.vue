<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { reactive, ref } from 'vue';
import axios from 'axios';
import ToggleSwitch from 'primevue/toggleswitch';
import { InputText } from 'primevue';
import _ from 'lodash';
import InputColor from '@/Components/InputColor.vue';

const props = defineProps({
    isSuperAdmin: Boolean,
    tenant: Object,
    quote_approval_flows: Array,
    social_links: Array
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
        quote_approval_flow: props.tenant.configurations.quote_approval_flow ?? 'Internal review',
        has_client_approval: props.tenant.configurations.has_client_approval ?? '0',
        customization_header: JSON.parse(props.tenant.configurations.customization_header ?? '[]'),
        customization_footer: JSON.parse(props.tenant.configurations.customization_footer ?? '[]'),
        customization_social: JSON.parse(props.tenant.configurations.customization_social ?? '[]'),
        customization_colors: props.tenant.configurations.customization_colors
            ? JSON.parse(props.tenant.configurations.customization_colors)
            : {
                  app: {
                      header: 'AAAAAA',
                      navbar: 'EEEEEE',
                      footer: 'AAAAAA'
                  },
                  customer_approval: {
                      header: 'AAAAAA',
                      footer: 'AAAAAA'
                  },
                  shareable: {
                      header: 'AAAAAA',
                      footer: 'AAAAAA'
                  }
              }
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

    let data = {
        logo_id: tenant.logo_id,
        logo_alt_id: tenant.logo_alt_id,
        logo_docs_id: tenant.logo_docs_id
    };

    try {
        let res = await axios.put(`/api/tenants/` + tenant.id, data);
        Object.assign(tenant, res.data);
        emitter.emit('logo:updated');
        notyf.success('Updated with Success');
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
        let configs = {};
        Object.assign(configs, tenant.configurations);
        configs.customization_header = JSON.stringify(configs.customization_header);
        configs.customization_footer = JSON.stringify(configs.customization_footer);
        configs.customization_social = JSON.stringify(configs.customization_social);
        configs.customization_colors = JSON.stringify(configs.customization_colors);

        let res = await axios.put(`/api/tenants/` + tenant.id, { ...tenant, configurations: configs });

        let temp = res.data;
        temp.configurations.customization_header = JSON.parse(temp.configurations.customization_header);
        temp.configurations.customization_footer = JSON.parse(temp.configurations.customization_footer);
        temp.configurations.customization_social = JSON.parse(temp.configurations.customization_social);
        temp.configurations.customization_colors = JSON.parse(temp.configurations.customization_colors);
        Object.assign(tenant, temp);

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

const items = ref([{ label: 'Home', url: '/' }, { label: 'Settings', url: '/settings' }, { label: 'Tenant' }]);
</script>

<template>
    <Head title="Tenant" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Tenant" :breadcrumbs="items"> </Header>

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
                                            id="sso"
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
                                                                {{ approval.value }}
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <div
                                                        class="mb-3"
                                                        v-if="
                                                            tenant.configurations.quote_approval_flow ===
                                                            'Margin review'
                                                        "
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
                                                    <div class="d-flex align-items-center">
                                                        <label class="form-label m-0 me-2"
                                                            >Send to Client Approval:</label
                                                        >
                                                        <ToggleSwitch
                                                            id="active-client_approval-rules"
                                                            v-model="tenant.configurations.has_client_approval"
                                                            @change="submitTenantSettings"
                                                            trueValue="1"
                                                            falseValue="0"
                                                            binary
                                                        >
                                                            <template #handle>
                                                                <font-awesome-icon
                                                                    :icon="
                                                                        tenant.configurations.has_client_approval ===
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
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </tab>
                <tab title="Layout">
                    <div class="d-flex align-items-start mt-5">
                        <div
                            class="nav flex-column nav-pills me-3"
                            id="v-pills-tab"
                            role="tablist"
                            aria-orientation="vertical"
                        >
                            <button
                                class="nav-link active"
                                id="v-pills-logo-tab"
                                data-bs-toggle="pill"
                                data-bs-target="#v-pills-logo"
                                type="button"
                                role="tab"
                                aria-controls="v-pills-logo"
                                aria-selected="false"
                            >
                                Logos
                            </button>
                            <button
                                class="nav-link"
                                id="v-pills-header-tab"
                                data-bs-toggle="pill"
                                data-bs-target="#v-pills-header"
                                type="button"
                                role="tab"
                                aria-controls="v-pills-header"
                                aria-selected="false"
                            >
                                Header
                            </button>
                            <button
                                class="nav-link"
                                id="v-pills-footer-tab"
                                data-bs-toggle="pill"
                                data-bs-target="#v-pills-footer"
                                type="button"
                                role="tab"
                                aria-controls="v-pills-footer"
                                aria-selected="false"
                            >
                                Footer
                            </button>
                            <button
                                class="nav-link"
                                id="v-pills-social-tab"
                                data-bs-toggle="pill"
                                data-bs-target="#v-pills-social"
                                type="button"
                                role="tab"
                                aria-controls="v-pills-social"
                                aria-selected="false"
                            >
                                Social
                            </button>
                            <button
                                class="nav-link"
                                id="v-pills-colors-tab"
                                data-bs-toggle="pill"
                                data-bs-target="#v-pills-colors"
                                type="button"
                                role="tab"
                                aria-controls="v-pills-colors"
                                aria-selected="false"
                            >
                                Colors
                            </button>
                        </div>
                        <div class="tab-content ps-3 w-100" id="v-pills-tabContent">
                            <div
                                class="tab-pane fade show active"
                                id="v-pills-logo"
                                role="tabpanel"
                                aria-labelledby="v-pills-logo-tab"
                            >
                                <div class="col xml-in-tabs">
                                    <div class="d-flex">
                                        <div class="logo-form">
                                            <h4>Import Logos for {{ tenant.name }}</h4>
                                            <p>The files to upload must be a .jpeg, .jpg, .png, .gif</p>
                                        </div>
                                    </div>
                                    <div class="row row-cols-3 mt-3">
                                        <div>
                                            <label>Header</label>
                                            <InputResource
                                                id="logo"
                                                v-model="tenant.logo_id"
                                                :resource="tenant.logo"
                                                :showLikeLine="false"
                                                :acceptedFiles="['image/jpeg', 'image/jpg', 'image/png', 'image/gif']"
                                                @change="submitLogoForm"
                                            ></InputResource>
                                        </div>
                                        <div>
                                            <label>Footer</label>
                                            <InputResource
                                                id="logo-alt"
                                                v-model="tenant.logo_alt_id"
                                                :resource="tenant.logo_alt"
                                                :showLikeLine="false"
                                                :acceptedFiles="['image/jpeg', 'image/jpg', 'image/png', 'image/gif']"
                                                @change="submitLogoForm"
                                            ></InputResource>
                                        </div>
                                        <div>
                                            <label>PDF documents</label>
                                            <InputResource
                                                id="logo-docs"
                                                v-model="tenant.logo_docs_id"
                                                :resource="tenant.logo_docs"
                                                :showLikeLine="false"
                                                :acceptedFiles="['image/jpeg', 'image/jpg', 'image/png', 'image/gif']"
                                                @change="submitLogoForm"
                                            ></InputResource>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="tab-pane fade"
                                id="v-pills-header"
                                role="tabpanel"
                                aria-labelledby="v-pills-header-tab"
                            >
                                <h4>Header Links for {{ tenant.name }}</h4>
                                <p class="mb-3">Manage the header navigation links</p>

                                <TenantLinks
                                    v-model="tenant.configurations.customization_header"
                                    id="header"
                                    :linkLimit="4"
                                    @change="submitTenantSettings()"
                                />
                            </div>
                            <div
                                class="tab-pane fade"
                                id="v-pills-footer"
                                role="tabpanel"
                                aria-labelledby="v-pills-footer-tab"
                            >
                                <h4>Footer Links for {{ tenant.name }}</h4>
                                <p class="mb-3">Manage the footer navigation links</p>

                                <TenantLinks
                                    v-model="tenant.configurations.customization_footer"
                                    id="footer"
                                    :linkLimit="4"
                                    @change="submitTenantSettings()"
                                />
                            </div>
                            <div
                                class="tab-pane fade"
                                id="v-pills-social"
                                role="tabpanel"
                                aria-labelledby="v-pills-social-tab"
                            >
                                <h4>Social Links for {{ tenant.name }}</h4>
                                <p class="mb-3">Manage the social navigation links</p>

                                <TenantLinks
                                    v-model="tenant.configurations.customization_social"
                                    :socialLinks="props.social_links"
                                    id="social"
                                    :linkLimit="5"
                                    @change="submitTenantSettings()"
                                />
                            </div>
                            <div
                                class="tab-pane fade"
                                id="v-pills-colors"
                                role="tabpanel"
                                aria-labelledby="v-pills-colors-tab"
                            >
                                <div class="colors-form">
                                    <h4>Colors for tenant {{ tenant.name }}</h4>
                                    <p class="mb-3">Manage tenant colors</p>
                                </div>
                                <div class="col">
                                    <h2 class="seperator">App</h2>
                                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
                                        <div class="col">
                                            <div class="card bg-light border-0 settings-block h-100">
                                                <div class="card-body">
                                                    <h6 class="fw-bold pe-5 mb-3">Header</h6>
                                                    <div class="color-app-header">
                                                        <InputColor
                                                            v-model="
                                                                tenant.configurations.customization_colors.app.header
                                                            "
                                                            :id="'color-app-header'"
                                                            @change="submitTenantSettings"
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="card bg-light border-0 settings-block h-100">
                                                <div class="card-body">
                                                    <h6 class="fw-bold pe-5 mb-3">Navbar</h6>
                                                    <div class="vcolor-app-navbar">
                                                        <InputColor
                                                            v-model="
                                                                tenant.configurations.customization_colors.app.navbar
                                                            "
                                                            :id="'color-app-navbar'"
                                                            @change="submitTenantSettings"
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="card bg-light border-0 settings-block h-100">
                                                <div class="card-body">
                                                    <h6 class="fw-bold pe-5 mb-3">Footer</h6>
                                                    <div class="color-app-footer">
                                                        <InputColor
                                                            v-model="
                                                                tenant.configurations.customization_colors.app.footer
                                                            "
                                                            :id="'color-app-footer'"
                                                            @change="submitTenantSettings"
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <h2 class="seperator">Customer Approval</h2>
                                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
                                        <div class="col">
                                            <div class="card bg-light border-0 settings-block h-100">
                                                <div class="card-body">
                                                    <h6 class="fw-bold pe-5 mb-3">Header</h6>
                                                    <div class="color-app-header">
                                                        <InputColor
                                                            v-model="
                                                                tenant.configurations.customization_colors
                                                                    .customer_approval.header
                                                            "
                                                            :id="'color-app-header'"
                                                            @change="submitTenantSettings"
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="card bg-light border-0 settings-block h-100">
                                                <div class="card-body">
                                                    <h6 class="fw-bold pe-5 mb-3">Footer</h6>
                                                    <div class="color-app-footer">
                                                        <InputColor
                                                            v-model="
                                                                tenant.configurations.customization_colors
                                                                    .customer_approval.footer
                                                            "
                                                            :id="'color-app-footer'"
                                                            @change="submitTenantSettings"
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col">
                                    <h2 class="seperator">Shareable Link</h2>
                                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
                                        <div class="col">
                                            <div class="card bg-light border-0 settings-block h-100">
                                                <div class="card-body">
                                                    <h6 class="fw-bold pe-5 mb-3">Header</h6>
                                                    <div class="color-app-header">
                                                        <InputColor
                                                            v-model="
                                                                tenant.configurations.customization_colors.shareable
                                                                    .header
                                                            "
                                                            :id="'color-app-header'"
                                                            @change="submitTenantSettings"
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col">
                                            <div class="card bg-light border-0 settings-block h-100">
                                                <div class="card-body">
                                                    <h6 class="fw-bold pe-5 mb-3">Footer</h6>
                                                    <div class="color-app-footer">
                                                        <InputColor
                                                            v-model="
                                                                tenant.configurations.customization_colors.shareable
                                                                    .footer
                                                            "
                                                            :id="'color-app-footer'"
                                                            @change="submitTenantSettings"
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
