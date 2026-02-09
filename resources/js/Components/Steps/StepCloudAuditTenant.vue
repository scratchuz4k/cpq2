<script setup>
import _ from 'lodash';
import { IconField, InputText, InputIcon, Dialog } from 'primevue';
import { watch, ref, reactive, computed, onMounted } from 'vue';
import queryString from 'query-string';
import { currentStep } from '@composables/useQuoteSteps';
import Paginator from 'primevue/paginator';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import StepActionBar from './StepActionBar.vue';
import { isDisabled } from '@/Composables/useQuoteSteps.js';

const props = defineProps({
    solutionType: Array,
    modelValue: Object
});

const emit = defineEmits(['update:modelValue', 'nextStep']);
const proxyValue = computed({
    get() {
        return props.modelValue;
    },
    set(val) {
        emit('update:modelValue', val);
    }
});
const isLoading = ref(false);
const form = reactive({
    email: null,
    password: null
});

const organisation = ref(props.modelValue?.organisation || null);
const organisations = ref([]);
const showModal = ref(false);
const showLodingLogin = ref(false);
const tenant_review_active = ref(false);
const meta = reactive({
    limit: 12,
    page: 1,
    total: 0,
    first: 1,
    search: null
});

const activateTiming = async () => {
    try {
        await router.post('/quotes', {
            account_id: proxyValue.value.account?.id,
            address_position: proxyValue.value.position,
            address_city: proxyValue.value.address?.postTown,
            address_data: proxyValue.value.address,
            address_line1: proxyValue.value.address?.label,
            address_postcode: proxyValue.value.address?.postCode,
            solution_type_id: proxyValue.value.solution_type?.id,
            organisation: organisation.value
        });
    } catch (ex) {
        notyf.error(ex.response.data.message);
    }
};
const getOrganisations = async (email) => {
    let string = queryString.stringify({
        order: 'desc',
        orderBy: 'updated_at',
        page: meta.page,
        limit: meta.limit,
        search: meta.search
    });
    let res = await axios.get('/api/cloudaudit/organisations?' + string);
    organisations.value = res.data.data;
    meta.total = res.data.meta?.total ?? res.data.total;
    meta.first = (meta.page - 1) * meta.limit;

    if (organisation.value?.id) {
        const match = organisations.value.find((o) => o.id === organisation.value.id);
        if (match) {
            organisation.value = match;
        }
    }
};

const submit = async () => {
    showLodingLogin.value = true;

    try {
        let res = await axios.post('/api/cloudaudit/organisations', form);

        organisation.value = res.data;
        showLodingLogin.value = false;
        showModal.value = false;
        getOrganisations();
        notyf.success('Organisation added with Success');
    } catch (ex) {
        showLodingLogin.value = false;
        notyf.error(ex.response?.data?.message ?? ex.response?.data);
    }
};

const syncCloudaudit = async () => {
    isLoading.value = true;
    let res = await axios.get('/api/cloudaudit/organisations/sync');
    setTimeout(() => {
        isLoading.value = false;
        getOrganisations();
    }, 200);
};

const handlePaginator = (ev) => {
    form.limit = ev.rows;
    meta.page = ev.page + 1;
    getOrganisations();
};

watch(
    () => [meta.search],
    _.debounce(function () {
        meta.page = 1;
        getOrganisations();
    }, 200)
);

onMounted(() => {
    isDisabled.value = proxyValue.value.account === null;
});

watch(
    () => [proxyValue.value.account],
    () => {
        if (proxyValue.value.account === null) {
            isDisabled.value = true;
        } else {
            isDisabled.value = false;
        }
    }
);

syncCloudaudit();

const tenant_reviewed = ref(false);
</script>
<template>
    <div class="row mb-3" v-if="!tenant_review_active">
        <div class="col-lg-12">
            <div class="panel mb-3">
                <div class="row">
                    <div class="col-12">
                        <div class="d-flex justify-content-between mt-3">
                            <h5 class="font-bold mb-2">Select an organisation to review</h5>
                            <div>
                                <div class="btn me-2" v-if="isLoading">
                                    <div class="spinner-border spinner-border-sm" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                                <button
                                    v-else
                                    id="link-to-refresh-permission"
                                    type="button"
                                    class="btn me-2"
                                    @click="syncCloudaudit()"
                                    style="border-style: dashed"
                                >
                                    <font-awesome-icon :icon="['fas', 'sync']" />
                                </button>

                                <button
                                    id="link-to-request-permission"
                                    type="button"
                                    class="btn btn-primary"
                                    @click="showModal = true"
                                    style="border-style: dashed"
                                >
                                    <font-awesome-icon :icon="['fas', 'plus']" />
                                    Add new
                                </button>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <IconField>
                                <InputIcon>
                                    <font-awesome-icon :icon="['fas', 'search']" />
                                </InputIcon>
                                <InputText
                                    id="datatable-search"
                                    v-model="meta.search"
                                    type="search"
                                    placeholder="Keyword Search"
                                />
                            </IconField>
                        </div>
                        <div
                            v-if="organisations.length"
                            class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-3 mt-1 mb-3"
                        >
                            <div class="col" v-for="(org, index) in organisations">
                                <RadioCard
                                    v-model="organisation"
                                    :value="org"
                                    :allow-deselect="true"
                                    name="cloud-selected-option"
                                >
                                    <div class="d-flex justify-content-between">
                                        <p class="h5 mb-0 font-bold">{{ org.name }}</p>
                                        <div v-if="Object.keys(org.data).includes('errors')" class="org-tooltip">
                                            <font-awesome-icon
                                                icon="fas fa-info"
                                                class="badge bg-danger"
                                            ></font-awesome-icon>
                                            <div class="org-tooltip-info rounded border p-2 px-4">
                                                <p>Some errors have occurred trying to sync this organisation</p>
                                            </div>
                                        </div>
                                    </div>

                                    <p class="small mb-2">{{ org.email }}</p>
                                    <p class="badge w-100 bg-secondary text-white mt-2">
                                        {{ format.capitalizeFirstLetter(org.status) }}
                                    </p>
                                </RadioCard>
                            </div>
                        </div>
                        <div v-else class="text-center my-1">No records found</div>

                        <Paginator
                            v-model:first="meta.first"
                            :rows="meta.limit"
                            :totalRecords="meta.total"
                            :rowsPerPageOptions="[12, 20, 28]"
                            @page="handlePaginator"
                        >
                        </Paginator>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-3" v-if="tenant_review_active">
        <div class="col">
            <div class="row">
                <div class="text-center py-4">
                    <h1><strong>Reviewing tenant</strong></h1>
                    <Spinner class="mb-4 d-flex justify-content-center" />
                    <h4 style="font-weight: normal">
                        We are reviewing the tenant and calculating the potential saving <br />
                        (won't take long!)
                    </h4>
                    <div class="cpq-bar-bg">
                        <div class="cpq-bar"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <Dialog
        v-model:visible="showModal"
        modal
        :style="{ marginTop: '4rem', minWidth: '25vw' }"
        :draggable="false"
        position="top"
    >
        <template #header>
            <h2><font-awesome-icon :icon="['fas', 'globe']"></font-awesome-icon> New Organisation</h2>
        </template>
        <div class="container position-relative">
            <div v-if="showLodingLogin" class="login-loading-container">
                <Loader class="h-100"></Loader>
            </div>
            <form @submit.prevent="submit">
                <div class="mb-2">
                    <label for="name" class="form-label"> Name <span class="text-primary fw-bold">*</span></label>
                    <IconField>
                        <InputIcon>
                            <font-awesome-icon :icon="['fas', 'envelope']"></font-awesome-icon>
                        </InputIcon>
                        <InputText
                            id="name"
                            class="w-100"
                            v-model="form.name"
                            placeholder="Name"
                            required
                            type="name"
                        />
                    </IconField>
                </div>
                <div class="mb-5">
                    <label for="email" class="form-label"> Email <span class="text-primary fw-bold">*</span></label>
                    <IconField>
                        <InputIcon>
                            <font-awesome-icon :icon="['fas', 'envelope']"></font-awesome-icon>
                        </InputIcon>
                        <InputText
                            id="email"
                            class="w-100"
                            v-model="form.email"
                            placeholder="Email"
                            required
                            type="email"
                        />
                    </IconField>
                </div>
                <div class="mb-4">
                    <button class="btn btn-primary w-100">Create</button>
                </div>
            </form>
        </div>
    </Dialog>
</template>

<style scoped>
.login-loading-container {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1;
    background: rgba(255, 255, 255, 0.75);
}

.org-tooltip {
    position: relative;
}

.org-tooltip-info {
    position: absolute;
    z-index: 100;
    background: white;
    width: 300px;
    visibility: hidden;
    pointer-events: none;
    opacity: 0;
    transition: all 0.3s ease-in-out;
}
.org-tooltip:hover > .org-tooltip-info {
    visibility: visible;
    pointer-events: auto;
    opacity: 1;
}
</style>
