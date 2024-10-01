<script setup>
import Button from 'primevue/button';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from "@inertiajs/vue3";
import Accordion from 'primevue/accordion';
import AccordionPanel from 'primevue/accordionpanel';
import AccordionHeader from 'primevue/accordionheader';
import AccordionContent from 'primevue/accordioncontent';
import Swal from 'sweetalert2';
import { reactive, ref } from 'vue';

const account = reactive({
    name: null,
    reference: null,
    contacts: []
})
const addContact = () => {
    account.contacts.push({})
}

const saveAccount = () => {
    router.post("/accounts", account, {
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
    })
}

const removeContact = (key) => {
    Swal.fire({
        title: 'Are you sure?',
        icon: 'warning',
        showCancelButton: true,
        cancelButtonText: 'No, cancel.',
        confirmButtonColor: '#2d72d9',
        cancelButtonColor: '#E53A40',
        confirmButtonText: 'Yes, delete!'
    }).then(async (result) => {
        if (['cancel', 'backdrop'].includes(result.dismiss)) {
            bulkOperation.value = ''
            return
        }
        account.contacts.splice(key, 1)
    })
}

const items = ref([
    { label: 'My', url: '/myaccount' },
    { label: 'Accounts', url: '/accounts' },
]);


</script>

<template>

    <Head title="Accounts" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="New Account" :breadcrumbs="items">
                <template #buttons>
                    <Button @click="saveAccount()" size="small">
                        <font-awesome-icon :icon="['fas', 'save']"></font-awesome-icon> Save
                    </Button>
                </template>
            </Header>
            <div class="row mb-3">
                <div class="col">
                    <label for="account-name" class="form-label">Name:</label>
                    <input id="account-name" class="form-control" v-model="account.name">
                </div>
                <div class="col">
                    <label for="account-name" class="form-label">Reference:</label>
                    <input id="account-name" class="form-control" v-model="account.reference">
                </div>
            </div>
            <h2>Contacts</h2>
            <Accordion value="0">
                <AccordionPanel v-for="(contact, key) in account.contacts" :key="key" :value="key">
                    <AccordionHeader>
                        <div class="d-flex justify-content-between align-items-center w-100 pe-3">
                            <p class="m-0">
                                Contact #{{ contact.id }}
                                {{ contact.primary_contact_name ? ' - ' + contact.primary_contact_name : '' }}

                            </p>
                            <Button size="small" @click="removeContact(key)">Delete</Button>
                        </div>
                    </AccordionHeader>
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
