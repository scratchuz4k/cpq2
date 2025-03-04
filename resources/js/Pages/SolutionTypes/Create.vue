<script setup>
import Select from 'primevue/select';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head, router, useForm, usePage} from "@inertiajs/vue3";
import {computed, reactive, ref} from 'vue';

const page = usePage();
const props = defineProps({
    solutions: Array,
    available_tables: Array,
});

const solution = reactive({
    tenant_id: page.props.tenant.id,
    solution_type_id: null,
    configurations: {
        component: null
    }
})

const saveSolution = () => {
    solution.configurations.component ??= 'DealMaker';
    router.post("/solution-types", solution, {
        onSuccess: (page) => {
            notyf.success("Created with Success")
        },
        onError: (errors) => {
            let string = ''
            Object.keys(errors).forEach((key) => {
                string += errors[key] + '<br>'
            })
            notyf.error(string)
        },
    })
}

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: 'Solution Types', url: '/solution-types' },
    { label: 'New Solution Type' },
]);

</script>
<template>
    <Head title="Solution Type" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="New Solution Type" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton id="btn-save-solution-type" @click="saveSolution()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon> Save
                    </PrimaryButton>
                </template>
            </Header>
            <div class="mt-3">
                <tabs>
                    <tab title="General" id="tab-general" :selected="true">
                        <div class="mt-3">
                            <div class="mb-5">
                                <label for="solution_type-name" class="form-label">Name:</label>
                                <Select
                                    required
                                    inputId="solution_type"
                                    v-model="solution.solution_type_id"
                                    :options="solutions"
                                    optionLabel="name"
                                    optionValue="id"
                                    placeholder="Select Solution type"
                                    class="w-100"
                                />
                            </div>
                        </div>
                    </tab>
                    <tab title="Quote" id="tab-quotes">
                        <p class="my-3">
                            This section contains all the customizations for the appearance of the
                            solution type to match specific preferences within the quote.
                        </p>
                        <div class="my-3">
                            <p for="solution_type-component" class="form-label">Choose the view:</p>
                            <Select
                                required
                                inputId="solution_type-component"
                                v-model="solution.configurations.component"
                                :options="available_tables"
                                placeholder="Select a View"
                                class="w-100"
                            />
                        </div>
                    </tab>
                </tabs>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
