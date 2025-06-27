<script setup>
import { Select, IconField, InputText, InputIcon, Dialog } from 'primevue';
import { computed, ref, reactive } from 'vue';
import { currentStep } from '@composables/useQuoteSteps';
import { v4 as uuidv4 } from 'uuid';

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
const form = reactive({
    email: null,
    password: null
});
const tenants = ref([]);
const showModal = ref(false);
const tenant = ref(null);
const tenant_review_active = ref(false);
const activateTiming = () => {
    tenant_review_active.value = !tenant_review_active.value;
    setTimeout(() => {
        currentStep.value++;
    }, 3000);
};
const extractDomain = (email) => {
    // Split by '@' and take the last part
    const parts = email.split('@');
    if (parts.length < 2) return null;
    return parts[parts.length - 1];
};
const submit = () => {
    tenants.value.push({ name: extractDomain(form.email), id: `${uuidv4()}` });
    showModal.value = false;
};

const tenant_reviewed = ref(false);
</script>
<template>
    <div class="row mb-3" v-if="!tenant_review_active">
        <div class="col-lg-6">
            <div class="panel mb-3">
                <div class="row">
                    <div class="col-12">
                        <h5 class="font-bold mb-3">Select a tenant to review</h5>
                        <div class="mb-3">
                            <label for="account_id" class="form-label"
                                >Tenant <span class="text-primary fw-bold">*</span></label
                            >

                            <Select
                                :inputId="'quote-tenants'"
                                v-model="tenant"
                                editable
                                :options="tenants"
                                optionLabel="name"
                                optionValue="id"
                                placeholder="Select a tenant"
                                class="w-100"
                                :disabled="tenant_reviewed"
                            />
                        </div>
                        or
                        <a id="link-to-request-permission" href="#" class="text-primary" @click="showModal = true">
                            request a permission to review an account
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-3" v-if="!tenant_review_active && !tenant_reviewed">
        <div class="col-lg-6 d-flex justify-content-between">
            <button
                id="btn-previous-step"
                class="btn"
                type="button"
                v-if="currentStep != 0"
                @click="() => currentStep--"
                :disabled="props.disabled"
            >
                <font-awesome-icon :icon="['fas', 'arrow-left']" class="ms-2"></font-awesome-icon>
                Previous Step
            </button>
            <button
                id="btn-next-step"
                class="btn btn-primary"
                type="button"
                @click="activateTiming()"
                :disabled="tenant == null"
            >
                Review Tenant
            </button>
        </div>
    </div>
    <div class="row mb-3" v-if="tenant_review_active">
        <div class="col">
            <div class="row">
                <div class="text-center">
                    <Spinner class="mt-5 mb-4 d-flex justify-content-center" />
                    <h4><strong>Reviewing tenant</strong></h4>
                    <h6>
                        We are reviewing the tenant and calculating the potential savings <br />
                        (wont take long!)
                    </h6>
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
        :style="{ marginTop: '4rem', minWidth: '60vw' }"
        :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
        :draggable="false"
        position="top"
    >
        <template #header>
            <h2>Login External Service</h2>
        </template>
        <div class="container m-0 p-0">
            <form @submit.prevent="submit">
                <div class="mb-3">
                    <label for="email" class="form-label"> Email <span class="text-primary fw-bold">*</span></label>
                    <IconField>
                        <InputIcon>
                            <font-awesome-icon icon="fas fa-search" />
                        </InputIcon>
                        <InputText id="email" v-model="form.email" placeholder="Search" type="email" />
                    </IconField>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label"
                        >Password <span class="text-primary fw-bold">*</span></label
                    >
                    <IconField>
                        <InputIcon>
                            <font-awesome-icon icon="fas fa-search" />
                        </InputIcon>
                        <InputText id="password" v-model="form.password" placeholder="Search" type="password" />
                    </IconField>
                </div>
                <button class="btn btn-primary">Login</button>
            </form>
        </div>
    </Dialog>
</template>
