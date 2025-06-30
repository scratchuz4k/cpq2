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
const showLodingLogin = ref(false);
const tenant = ref(null);
const tenant_review_active = ref(false);
const activateTiming = () => {
    tenant_review_active.value = !tenant_review_active.value;
    setTimeout(() => {
        currentStep.value++;
    }, 50000);
};
const extractDomain = (email) => {
    // Split by '@' and take the last part
    const parts = email.split('@');
    if (parts.length < 2) return null;
    return parts[parts.length - 1];
};
const submit = () => {
    showLodingLogin.value = true;
    setTimeout(() => {
        const newTenant = { name: extractDomain(form.email), id: `${uuidv4()}` };
        tenants.value.push(newTenant);
        tenant.value = newTenant.id;
        showLodingLogin.value = false;
        showModal.value = false;
        notyf.success('Connected with Success');
    }, 3000);
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
                <div class="text-center py-4">
                    <h1><strong>Reviewing tenant</strong></h1>
                    <Spinner class="mb-4 d-flex justify-content-center" />
                    <h4 style="font-weight: normal">
                        We are reviewing the tenant and calculating the potential savings <br />
                        (wont take long!)
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
            <h2><font-awesome-icon :icon="['fas', 'globe']"></font-awesome-icon> Login External Service</h2>
        </template>
        <div class="container position-relative">
            <div v-if="showLodingLogin" class="login-loading-container">
                <Loader class="h-100"></Loader>
            </div>
            <form @submit.prevent="submit">
                <div class="mb-2">
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
                <div class="mb-5">
                    <label for="password" class="form-label"
                        >Password <span class="text-primary fw-bold">*</span></label
                    >
                    <IconField>
                        <InputIcon>
                            <font-awesome-icon :icon="['fas', 'lock']"></font-awesome-icon>
                        </InputIcon>
                        <InputText
                            id="password"
                            class="w-100"
                            v-model="form.password"
                            placeholder="Password"
                            required
                            type="password"
                        />
                    </IconField>
                </div>
                <div class="mb-4">
                    <button class="btn btn-primary w-100">Login</button>
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
</style>
