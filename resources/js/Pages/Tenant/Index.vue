<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import {Head} from "@inertiajs/vue3";
import {reactive, ref} from 'vue';
import Button from "primevue/button";
import axios from "axios";

const props = defineProps({
    isSuperAdmin: Boolean,
    tenant: Object
})

const isLoading = ref(false)
const event = ref(null)
const tenant = reactive(props.tenant)
const item = ref(null)

const submitFileForm = async () => {
    isLoading.value = true

    let data = {
        sso_id: tenant.sso_id
    }
    try {
        let res = await axios.put(`/api/tenant/` + tenant.id, data)
        Object.assign(tenant, res.data);
        notyf.success("File uploaded with Success")
    } catch (err) {
        notyf.error(err?.response?.data?.message ?? 'Something went wrong')
    }
};

const submitLogoForm = async () => {
    isLoading.value = true
    let data = {
        logo_id: tenant.logo_id
    }
    try {
        let res = await axios.put(`/api/tenant/` + tenant.id, data)
        Object.assign(tenant, res.data);
        notyf.success("Logo uploaded with Success")
    } catch (err) {
        notyf.error(err?.response?.data?.message ?? 'Something went wrong')
    }

    isLoading.value = false
};

const submitTextForm = async () => {
    let data = {
        sso_text_area: tenant.file ? tenant.file?.file_content :tenant.sso_text
    }

    try {
        let res = await axios.put(`/api/tenant/` + tenant.id, data)
        Object.assign(tenant, res.data);
        notyf.success("Content updated successfully")
    } catch (err) {
        notyf.error(err?.response?.data?.message ?? 'Something went wrong')
    }
    isLoading.value = false
}

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'My Account', url: '/myaccount' },
    { label: 'Tenants' },
]);

</script>

<template>
    <Head title="Tenants" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Tenants" :breadcrumbs="items"></Header>
            <div class="containter">
                <div class="row">
                    <div class="d-flex align-items-start">
                        <div class="nav flex-column nav-pills me-3" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                            <button class="nav-link active" id="v-pills-sso-tab" data-bs-toggle="pill" data-bs-target="#v-pills-sso" type="button" role="tab" aria-controls="v-pills-sso" aria-selected="true">SSO</button>
                            <button class="nav-link" id="v-pills-logo-tab" data-bs-toggle="pill" data-bs-target="#v-pills-logo" type="button" role="tab" aria-controls="v-pills-logo" aria-selected="false">Logo</button>
                        </div>
                        <div class="tab-content ps-3 w-100" id="v-pills-tabContent">
                            <div class="tab-pane fade show active" id="v-pills-sso" role="tabpanel" aria-labelledby="v-pills-sso-tab">
                                <div class="col xml-in-tabs">
                                    <h4>Import XML file for {{ tenant.name }}</h4>
                                    <p>The file to upload must be a .xml</p>
                                    <form @submit.prevent="submitFileForm" enctype="multipart/form-data">
                                        <InputResource v-model="tenant.sso_id" :resource="tenant.file" :acceptedFiles="['text/xml']" @change="submitFileForm"></InputResource>
                                    </form>
                                    <div class="seperator"> OR</div>
                                    <div class="mt-4">
                                        <h4>Input SSO configuration for {{ tenant.name }}</h4>
                                        <p>Please provide a valid xml syntax</p>
                                        <form @submit.prevent="submitTextForm" enctype="multipart/form-data">
                                            <textarea v-if="tenant.file" v-model="tenant.file.file_content" class="w-100 p-4" rows="10"></textarea>
                                            <textarea v-else v-model="tenant.sso_text" class="w-100 p-4" rows="10"></textarea>
                                            <p class="small">* This will update the file content</p>
                                            <button id="submit-sso-textarea" type="submit" class="btn btn-primary mt-2">
                                                <font-awesome-icon class="me-2" :icon="['fas', 'save']"/>
                                                Save
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="v-pills-logo" role="tabpanel" aria-labelledby="v-pills-logo-tab">
                                <div class="col xml-in-tabs">
                                    <div class="d-flex mt-4">
                                        <div class="logo-form">
                                            <h4>Import Logo for {{ tenant.name }}</h4>
                                            <p>The file to upload must be a .png</p>
                                            <form @submit.prevent="submitLogoForm" enctype="multipart/form-data">
                                                <InputResource v-model="tenant.logo_id" :resource="tenant.logo" :showLikeLine="false" :acceptedFiles="['image/jpeg', 'image/png', 'image/gif', ]" @change="submitLogoForm"></InputResource>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.seperator {
    position: relative;
    text-align: center;
    margin: 2rem auto;
}
.seperator:before {
    content: '';
    width: 45%;
    height:1px;
    background-color:var(--light-grey);
    position: absolute;
    left:0;
    top: 50%;
}
.seperator:after {
    content: '';
    width: 45%;
    height:1px;
    background-color:var(--light-grey);
    position: absolute;
    right: 0;
    top: 50%;
}
.tab-content {
    border-left: 1px solid var(--light-grey);
}
.nav-link {
    color: var(--color-purple);
    font-weight: 600;
}
.nav-link.active {
    background-color: var(--color-purple);
}
</style>
