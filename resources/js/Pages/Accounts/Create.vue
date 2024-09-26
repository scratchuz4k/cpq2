<script setup>
import Button from 'primevue/button';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from "@inertiajs/vue3";
import Accordion from 'primevue/accordion';
import AccordionPanel from 'primevue/accordionpanel';
import AccordionHeader from 'primevue/accordionheader';
import AccordionContent from 'primevue/accordioncontent';

const account = useForm({
    name: null,
    reference: null,
    contacts: []
})
const addContact = () => {
    account.contacts.push({})
}

const saveAccount = () => {
    account.post("/accounts", account)
}


</script>

<template>

    <Head title="Accounts" />
    <AuthenticatedLayout>
        <div class="panel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="m-0">New Account</h1>
                <div>
                    <Button @click="saveAccount()">
                        <font-awesome-icon :icon="['fas', 'save']"></font-awesome-icon> Save
                    </Button>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col">
                    <label for="account-name" class="form-label">Name:</label>
                    <input id="account-name" class="form-control" v-model="account.name">
                    <div class="text-danger"  v-if="account.errors.name">{{ account.errors.name }}</div>
                </div>
                <div class="col">
                    <label for="account-name" class="form-label">Reference:</label>
                    <input id="account-name" class="form-control" v-model="account.reference">
                    <div class="text-danger"  v-if="account.errors.reference">{{ account.errors.reference }}</div>
                </div>
            </div>
            <h2>Contacts</h2>
            <Accordion value="0">
                <AccordionPanel v-for="(contact, key) in account.contacts" :key="contact.email" :value="key">
                    <AccordionHeader>Contact #{{ contact.id }} {{ contact.primary_contact_name ? ' - ' +
                        contact.primary_contact_name : '' }}</AccordionHeader>
                    <AccordionContent>
                        <div class="row">
                            <div class="col">
                                <div class=" mb-3">
                                    <label :for="'contact-name-' + key" class="form-label">
                                        Primary Contact Name:
                                    </label>
                                    <input type="text" :id="'contact-name-' + key" class="form-control"
                                        v-model="contact.primary_contact_name">
                                </div>
                                <div class=" mb-3">
                                    <label :for="'contact-address-' + key" class="form-label">Address:</label>
                                    <input :id="'contact-address-' + key" class="form-control"
                                        v-model="contact.address_1">
                                </div>
                                <div class=" mb-3">
                                    <label :for="'contact-post-code-' + key" class="form-label">Post Code:</label>
                                    <input :id="'contact-post-code-' + key" class="form-control"
                                        v-model="contact.postcode">
                                </div>
                            </div>
                            <div class="col">
                                <div class=" mb-3">
                                    <label :for="'contact-email-' + key" class="form-label">Email:</label>
                                    <input type="email" :id="'contact-email-' + key" class="form-control"
                                        v-model="contact.email">
                                </div>
                                <div class=" mb-3">
                                    <label :for="'contact-city-' + key" class="form-label">City:</label>
                                    <input :id="'contact-city-' + key" class="form-control" v-model="contact.city">
                                </div>
                            </div>
                        </div>
                    </AccordionContent>
                </AccordionPanel>
            </Accordion>
            <button class="add-contact" @click="addContact()">
                Add new <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
            </button>
        </div>
    </AuthenticatedLayout>
</template>
<style>
.add-contact {
    width: 100%;
    background-color: #f6f6f6;
    text-align: center;
    border-radius: 0 0 5px 5px;
    border: 0;
    padding: 10px 0
}
</style>
