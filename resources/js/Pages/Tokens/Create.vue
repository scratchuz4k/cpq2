<script setup>
import { reactive, ref } from "vue";
import Button from 'primevue/button';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from "@inertiajs/vue3";

const props = defineProps({
    users: Object,
})

const token = reactive({
    user_id: null,
    name: null,
})

const saveToken = () => {
    router.post(`/tokens`, token,
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
    { label: 'My Account', url: '/myaccount' },
    { label: 'Tokens', url: '/tokens' },
]);

</script>
<template>

    <Head title="Tokens" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="New Token" :breadcrumbs="items">
                <template #buttons>
                    <Button @click="saveToken()" size="small">
                        <font-awesome-icon :icon="['fas', 'save']"></font-awesome-icon> Save
                    </Button>
                </template>
            </Header>
            <div class="col-6">
                <div class="row mb-3">
                    <label for="token-name" class="form-label">Name:</label>
                    <input id="token-name" class="form-control" v-model="token.name">
                </div>
                <div class="row mb-3">
                    <label class="form-label">User:</label>
                    <select class="form-select" v-model="token.user_id">
                        <option v-for="user in users" :value="user.id">
                            {{ user.name }}
                            ({{ user.roles.map(x => x.name).join(',') }})
                        </option>
                    </select>
                </div>
            </div>

        </div>
    </AuthenticatedLayout>
</template>
