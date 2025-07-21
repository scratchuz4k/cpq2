<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Editor from 'primevue/editor';
import { reactive, ref } from 'vue';

const props = defineProps({
    email: Object
});

const email = reactive(props.email);

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: 'Email', url: '/email-templates' },
    { label: 'Show Email' }
]);
</script>
<template>
    <Head :title="'Show Email #' + email.id" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header :title="'Show Email #' + email.id" :breadcrumbs="items"></Header>
            <div class="mb-3">
                <label for="email-code" class="form-label">Code:</label>
                <input id="email-code" class="form-control" v-model="email.email.code" disabled />
            </div>
            <div class="mb-3">
                <label for="email-notes" class="form-label">Notes:</label>
                <input id="email-notes" class="form-control" v-model="email.email.notes" disabled />
            </div>
            <hr />
            <div class="mb-3">
                <label for="email-from" class="form-label">From:</label>
                <input id="email-from" class="form-control" v-model="email.from" />
            </div>
            <div class="row mb-3">
                <div class="col">
                    <label for="email-to" class="form-label">To:</label>
                    <input id="email-to" class="form-control" v-model="email.to" />
                </div>
                <div class="col">
                    <label for="email-cc" class="form-label">Cc:</label>
                    <input id="email-cc" class="form-control" v-model="email.cc" />
                </div>
                <div class="col">
                    <label for="email-bcc" class="form-label">Bbc:</label>
                    <input id="email-bcc" class="form-control" v-model="email.bcc" />
                </div>
            </div>
            <div class="mb-3">
                <label for="email-subject" class="form-label">Subject:</label>
                <input id="email-subject" class="form-control" v-model="email.subject" />
            </div>
            <div class="mb-3">
                <label for="email-body" class="form-label">Body:</label>
                <Editor v-model="email.body" editorStyle="height: 320px" readonly>
                    <template v-slot:toolbar>
                        <span class="ql-formats"> </span>
                    </template>
                </Editor>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<style scoped>
.token-card:hover {
    box-shadow:
        rgba(0, 0, 0, 0.16) 0px 1px 4px,
        rgb(51, 51, 51) 0px 0px 0px 1px;
    cursor: pointer;
}
</style>
