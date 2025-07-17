<script setup>
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import Editor from 'primevue/editor';
import { reactive, ref } from 'vue';
import Card from 'primevue/card';
import ToggleSwitch from 'primevue/toggleswitch';

const props = defineProps({
    email: Object
});

const email = reactive(props.email);

const saveEmail = () => {
    router.put(`/email-templates/${email.id}`, email, {
        onSuccess: (page) => {
            notyf.success('Updated with Success');
        },
        onError: (errors) => {
            let string = '';
            Object.keys(errors).forEach((key) => {
                string += errors[key] + '<br>';
            });
            notyf.error(string);
        }
    });
};
const copyToClipboard = async (msg) => {
    try {
        await navigator.clipboard.writeText(msg);
        notyf.success('Copied to clipboard!');
    } catch (err) {
        notyf.error('Failed to copy text: ' + err);
    }
};

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: "Email's", url: '/email-templates' },
    { label: 'Edit Email' }
]);
</script>
<template>
    <Head :title="'Edit Email #'" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header :title="'Edit Email #'" :breadcrumbs="items">
                <template #buttons>
                    <PrimaryButton id="btn-save-email" @click="saveEmail()">
                        <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon>
                        Save
                    </PrimaryButton>
                </template>
            </Header>
            <div class="mb-3 d-flex flex-column">
                <label class="form-label" for="active-filter"> Active: </label>
                <ToggleSwitch id="active-filter" v-model="email.is_active">
                    <template #handle>
                        <font-awesome-icon
                            :icon="email.is_active ? ['fas', 'circle-check'] : ['far', 'circle-xmark']"
                            class="text-primary"
                        />
                    </template>
                </ToggleSwitch>
            </div>
            <div class="mb-3">
                <label for="email-code" class="form-label">Code:</label>
                <input id="email-code" class="form-control" v-model="email.code" disabled />
            </div>
            <div class="mb-3">
                <label for="email-notes" class="form-label">Notes:</label>
                <input id="email-notes" class="form-control" v-model="email.notes" disabled />
            </div>
            <hr />
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
                <Editor v-model="email.body" editorStyle="height: 320px" />
            </div>
            <label for="email-tokens" class="form-label">Tokens:</label>
            <div class="row row-cols-5 mb-3">
                <div class="col" v-for="token in email.tokens">
                    <Card class="token-card" @click="copyToClipboard(`[${token.field}]`)">
                        <template #title>
                            <p>{{ token.description }}</p>
                        </template>
                        <template #content>
                            {{ token.field }}
                        </template>
                    </Card>
                </div>
            </div>
            <small>Use this tokens in the email subject or body</small>
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
