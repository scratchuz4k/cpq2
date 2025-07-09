<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { reactive, ref, watch } from 'vue';
import { currentStep, initSteps, steps } from '@composables/useQuoteSteps';

const props = defineProps({
    accounts: Array,
    solution_types: Object,
    steps: Object
});

// const configurations = reactive({
//     speed: props.modelValue.configs?.speed ? props.modelValue.configs.speed.split(',') : [0, 10000],
//     bearer: props.modelValue.configs?.bearer ? props.modelValue.configs.bearer.split(',') : [0, 10000],
//     contract_length: props.modelValue.configs?.contract_length
//         ? props.modelValue.configs.contract_length.split(',')
//         : [],
//     managed: props.modelValue.configs?.managed ? props.modelValue.configs.managed : '0'
// });

const quote = reactive({
    isLoading: false,
    account: null,
    solution_type: null,
    address: null,
    configs: {
        speed: null,
        bearer: null,
        contract_length: [],
        managed: '0'
    },
    postCode: null,
    position: null,
    solutionCheck: true
});

const submit = () => {
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
        router.post('/quotes', {
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
    () => [quote.solution_type],
    () => {
        if (quote.solution_type) {
            let temp = props.steps;
            steps.value = [...initSteps, ...temp[quote.solution_type.name]];
        } else {
            steps.value = [...initSteps];
        }
    }
);
const items = ref([{ label: 'Home', url: '/dashboard' }, { label: 'Create a quote' }]);
</script>

<template>
    <Head title="Create a quote" />

    <AuthenticatedLayout>
        <Header title="Create a quote" :breadcrumbs="items"></Header>

        <form @submit.prevent="submit">
            <component
                :is="steps[currentStep]"
                @nextStep="() => currentStep++"
                v-bind="{
                    solutionType: props.solution_types,
                    modelValue: quote
                }"
            ></component>
        </form>
    </AuthenticatedLayout>
</template>
