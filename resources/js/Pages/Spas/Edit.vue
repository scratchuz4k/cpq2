<script setup>
import { reactive, ref } from "vue";
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from "@inertiajs/vue3";
import SpaForm from "@/Views/Spas/SpaForm.vue";

const props = defineProps({
    spa: Object,
    suppliers: Object,
    rules: Array,
    operations: Array,
})

const rules = {}
props.rules.forEach(element => {
    let str = element.code;
    const splitResult = str.split(/(?=[A-Z])/);
    if (!Object.keys(rules).includes(splitResult[0])) {
        rules[splitResult[0]] = []
    }

    if (!rules[splitResult[0]].find(x => x === splitResult[1])) {
        rules[splitResult[0]].push(splitResult[1] ?? null)
    }
});

const splitResult = props.spa.rule.code.split(/(?=[A-Z])/);

const spa = reactive({
    entity_1: splitResult.length ? splitResult[0] : null,
    entity_1_value: props.spa.field_1,
    entity_2: splitResult.length == 2 ? splitResult[1] : null,
    entity_2_value: props.spa.field_2,
    operation_id: props.spa.operation_id,
    value: props.spa.value,
    percentage: props.spa.percentage
})

const saveSpa = () => {
    let rule = props.rules.find(x => x.code == spa.entity_1 + '' + (spa.entity_2 ?? ''))

    let item = {
        rule_id: rule.id,
        field_1: spa.entity_1_value?.id,
        field_2: spa.entity_2_value?.id,
        operation_id: spa.operation_id,
        value: spa.value,
        percentage: spa.percentage,
    }

    router.put(`/spas/${props.spa.id}`, item,
        {
            onSuccess: (page) => {
                notyf.success("Updated with Success")
            },
            onError: (errors) => {
                let string = ''
                Object.keys(errors).forEach((key) => {
                    string += errors[key] + '<br>'
                })
                notyf.error(string)
            },
        }
    )
}


const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: 'SPAs', url: '/spas' },
    { label: 'Edit SPA' },
]);

</script>
<template>

    <Head title="New SPA" />
    <AuthenticatedLayout>
        <Header title="Edit SPA" :breadcrumbs="items">
            <template #buttons>
                <PrimaryButton @click="saveSpa()">
                    <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon> Save
                </PrimaryButton>
            </template>
        </Header>
        <SpaForm v-model="spa" :rules="rules" :operations="props.operations"></SpaForm>
    </AuthenticatedLayout>
</template>
