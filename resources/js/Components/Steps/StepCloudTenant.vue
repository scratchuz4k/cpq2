<script setup>
import Select from 'primevue/select';
import { computed, ref } from 'vue';
import { currentStep, initSteps, steps } from '@composables/useQuoteSteps';

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
const tenants = [
    { id: 1, name: 'tenant_1' },
    { id: 2, name: 'tenant_2' },
    { id: 3, name: 'tenant_3' }
];
const tenant = ref(null);
const tenant_review_active = ref(false);
const activateTiming = () => {
    tenant_review_active.value = !tenant_review_active.value;
    setTimeout(() => {
        currentStep.value++;
    }, 3000);
};
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
                            />
                        </div>
                        or
                        <a id="link-to-create-account" href="#" class="text-primary">
                            request a permission to review an account
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mb-3" v-if="!tenant_review_active">
        <div class="col-lg-6 d-flex justify-content-end">
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
</template>
