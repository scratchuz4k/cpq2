<script setup>
import { reactive, ref } from "vue";
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from "@inertiajs/vue3";

const props = defineProps({
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

const spa = reactive({
    entity_1: null,
    entity_1_value: null,
    entity_2: null,
    entity_2_value: null,
    operation_id: 1,
    name: null,
    value: 0,
    percentage: 0
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

    router.post(`/spas`, item,
        {
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
        }
    )
}

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: 'SPAs', url: '/spas' },
    { label: 'New SPA' },
]);

</script>
<template>

    <Head title="New SPA" />
    <AuthenticatedLayout>
        <Header title="New SPA" :breadcrumbs="items">
            <template #buttons>
                <PrimaryButton id="save-spa" @click="saveSpa()">
                    <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon> Save
                </PrimaryButton>
            </template>
        </Header>
        <SpaForm v-model="spa" :rules="rules" :operations="props.operations"></SpaForm>

    </AuthenticatedLayout>
</template>
<style scoped>
.rule {
    cursor: pointer;
    margin-right: 5px;
    border: 1px solid;
    border-radius: 20px;
    text-align: center;
}

.rule:hover {
    border: 1px solid var(--primary-light);
    color: var(--primary-light);
    background: whitesmoke;
}

.rule.active {
    border: 1px solid var(--primary-light);
    color: var(--primary-light)
}
</style>
