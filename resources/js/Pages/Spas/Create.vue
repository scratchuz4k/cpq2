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
    entity_2: null,
    supplier_id: null,
    user_id: null,
    name: null
})

const saveSpa = () => {
    router.post(`/spas`, spa,
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

const icons = {
    'Product': ['far', 'rectangle-list'],
    'Account': ['far', 'address-card'],
    'Supplier': ['far', 'handshake'],
    null: ['fas', 'fa-slash']
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
                <PrimaryButton @click="saveSpa()">
                    <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon> Save
                </PrimaryButton>
            </template>
        </Header>
        <div class="col-6">
            <div class="panel">
                <h5>Choose the first Entity</h5>
                <ul class="d-flex flex-wrap p-0 m-0" style="list-style: none;">
                    <li v-for="(rule, index) in rules">
                        <div :class="{ 'active': spa.entity_1 === index }" class="rule d-flex flex-column p-2"
                            @click="() => { spa.entity_1 = index; spa.entity_2 = null }">
                            <font-awesome-icon class="p-2 " :icon="icons[index]" size="2x"></font-awesome-icon>
                            <p>{{ index }}</p>
                        </div>
                    </li>
                </ul>
                <template v-if="spa.entity_1">
                    <h5 class="mt-3">Choose the Second Entity (if needed)</h5>
                    <ul class="d-flex flex-wrap p-0 m-0" style="list-style: none;">
                        <li v-for="rule in rules[spa.entity_1]">
                            <div :class="{ 'active': spa.entity_2 === rule }" class="rule d-flex flex-column p-2"
                                @click="() => spa.entity_2 = rule">
                                <font-awesome-icon class="p-2 " :icon="icons[rule]" size="2x"></font-awesome-icon>
                                <p>{{ rule ?? 'None' }}</p>
                            </div>
                        </li>
                    </ul>
                    <div class="row my-3">
                        <label class="form-label">Operation:</label>
                        <select class="form-select" v-model="spa.rule_id">
                            <option v-for="operation in props.operations" :value="operation.id">{{ operation.code }}
                            </option>
                        </select>
                    </div>
                </template>
            </div>
        </div>

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