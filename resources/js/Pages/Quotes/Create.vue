<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref, watch, onMounted } from 'vue';
import { currentStep, initSteps, steps, actionBarButtons } from '@composables/useQuoteSteps';

const props = defineProps({
    accounts: Array,
    solution_types: Object,
    steps: Object
});

onMounted(() => {
    actionBarButtons.value = [];
    currentStep.value = 0;
    steps.value = [...initSteps];
});

const quote = reactive({
    isLoading: false,
    account: null,
    solution_type: null,
    address: null,
    configs: {
        speed: null,
        bearer: null,
        contract_length: []
    },
    postCode: null,
    position: null,
    solutionCheck: true
});

const submit = async () => {
    if (!quote.account || !quote.solution_type) {
        return;
    }
    let configs = {};
    if (quote.solution_type?.code == 3) {
        Object.keys(quote.configs).forEach((key) => {
            configs[key] = Array.isArray(quote.configs[key]) ? quote.configs[key].join(',') : quote.configs[key];
        });
    }

    try {
        await router.post('/quotes', {
            account_id: quote.account?.id,
            address_position: quote.position,
            address_city: quote.address?.postTown,
            address_data: quote.address,
            address_line1: quote.address?.label,
            address_postcode: quote.address?.postCode,
            solution_type_id: quote.solution_type.id,
            configurations: configs
        });
    } catch (ex) {
        alert(ex.response.data.message);
    }
};

watch(
    () => [quote.solution_type, quote.account],
    () => {
        let temp = props.steps;
        steps.value = [...initSteps];

        if (props.solution_types.length === 1) {
            steps.value = steps.value.filter((step) => step !== 'StepSolutionType');
            quote.solution_type = props.solution_types[0];
        }

        if (quote.solution_type && temp[quote.solution_type.name]) {
            steps.value = [...steps.value, ...temp[quote.solution_type.name]];
        }
    }
);
const items = ref([{ label: 'Home', url: '/dashboard' }, { label: 'Create a quote' }]);
</script>

<template>
    <Head title="Create a quote" />

    <AuthenticatedLayout :show-step-action-bar="true">
        <Header title="Create a quote" :breadcrumbs="items"></Header>

        <form @keydown.enter.prevent @submit.prevent="submit" id="create-quote-form">
            <component
                :is="steps[currentStep]"
                @nextStep="
                    () => {
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                        currentStep++;
                    }
                "
                v-bind="{
                    solutionType: props.solution_types,
                    modelValue: quote
                }"
            ></component>
        </form>
        <template #actionBar>
            <template v-if="actionBarButtons.length" v-for="btn in actionBarButtons" :key="btn.label">
                <button
                    v-if="btn.btn === 'back'"
                    id="btn-previous-step"
                    class="btn btn-outline-dark"
                    type="button"
                    @click="btn.onClick"
                    :disabled="btn.disabled"
                >
                    <font-awesome-icon :icon="['fas', 'arrow-left']" class="ms-2"></font-awesome-icon>
                    Previous Step
                </button>
                <button
                    v-else-if="btn.btn === 'next'"
                    id="btn-next-step"
                    class="btn btn-primary"
                    type="button"
                    @click="btn.onClick"
                    :disabled="btn.disabled"
                >
                    Review Tenant
                </button>
            </template>

            <ButtonStep v-else v-model="quote"></ButtonStep>
        </template>
    </AuthenticatedLayout>
</template>
