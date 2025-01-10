<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Tabs from "@/Components/Tabs.vue";
import Tab from "@/Components/Tab.vue";
import {Head, router} from "@inertiajs/vue3";
import {ref} from 'vue';

defineProps({
    isSuperAdmin: Boolean,
    tenants: Array
})

const isLoading =ref(false)
const onFileChange = (event, tenant) => {
    tenant.temp_file = event.target.files[0];
};

const submitFileForm = (tenant) => {
    isLoading.value = true
    if (tenant.temp_file === null) {
        isLoading.value = false
        return
    }
    router.post(`/tenant/${tenant.id}`,
        {
            _method: "PUT",
            file: tenant.temp_file
        },{
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
            onFinish: () => {
                // tenant.temp_file_name = null
                isLoading.value = false
            }
        })
};

const submitLogoForm = (tenant) => {
    isLoading.value = true
    if (tenant.temp_file === null) {
        isLoading.value = false
        return
    }
    router.post(`/tenant/${tenant.id}`,
        {
            _method: "PUT",
            logo: tenant.temp_file
        },{
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
            onFinish: () => {
                // tenant.temp_file_name = null
                isLoading.value = false
            }
        })
};

const submitTextForm = (tenant) => {
    router.post(`/tenant/${tenant.id}`,
        {
            _method: "PUT",
            sso_text_area: tenant.file ? tenant.file?.file_content :tenant.sso_text
        },{
            onSuccess: () => {
                notyf.success(`Content updated successfully`)
                isLoading.value = false

            },
            onError: (errors) => {
                let string = ''
                Object.keys(errors).forEach((key) => {
                    string += errors[key] + '<br>'
                })
                notyf.error(string)
                isLoading.value = false
            }
        }
    )
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
                            <button class="nav-link active" id="v-pills-sso-tab" data-bs-toggle="pill" data-bs-target="#v-pills-sso" type="button" role="tab" aria-controls="v-pills-sso" aria-selected="true">Home</button>
                            <button class="nav-link" id="v-pills-logo-tab" data-bs-toggle="pill" data-bs-target="#v-pills-logo" type="button" role="tab" aria-controls="v-pills-logo" aria-selected="false">Profile</button>
                        </div>
                        <div class="tab-content ps-3 w-100" id="v-pills-tabContent">
                            <div class="tab-pane fade show active" id="v-pills-sso" role="tabpanel" aria-labelledby="v-pills-sso-tab">
                                <div class="col xml-in-tabs">
                                    <tabs>
                                        <tab v-for="(tenant, index) in tenants" :title="tenant.name" :selected="index == 0"
                                             :key="tenant.id">
                                            <br>
                                            <h4>Import XML file for {{ tenant.name }}</h4>
                                            <p>The file to upload must be a .xml</p>
                                            <form @submit.prevent="submitFileForm(tenant)" enctype="multipart/form-data">
                                                <input type="file" accept=".xml" required @input="(ev) => onFileChange(ev, tenant)" :value="tenant.temp_file_name"/>
                                                <button type="submit" class="btn btn-success ms-2" :disabled="isLoading">
                                                    <span v-if="isLoading">
                                                        <font-awesome-icon :icon="['fa', 'spinner']" class="fa-spin"></font-awesome-icon>
                                                        Uploading...
                                                    </span>
                                                    <span v-else>Upload</span>
                                                </button>
                                            </form>
                                            <div class="seperator"> OR</div>
                                            {{ tenant}}
                                            <div class="mt-4">
                                                <h4>Input SSO configuration for {{ tenant.name }}</h4>
                                                <p>Please provide a valid xml syntax</p>
                                                <form @submit.prevent="submitTextForm(tenant)" enctype="multipart/form-data">
                                                    <textarea v-if="tenant.file" v-model="tenant.file.file_content" class="w-100 p-4" rows="10"></textarea>
                                                    <textarea v-else v-model="tenant.sso_text" class="w-100 p-4" rows="10"></textarea>
                                                    <button type="submit" class="btn btn-success">Save</button>
                                                </form>
                                            </div>
                                        </tab>
                                    </tabs>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="v-pills-logo" role="tabpanel" aria-labelledby="v-pills-logo-tab">
                                <div class="col xml-in-tabs">
                                    <tabs>
                                        <tab v-for="(tenant, index) in tenants" :title="tenant.name" :selected="index == 0"
                                             :key="tenant.id">
                                            <div class="d-flex mt-4">
                                                <div class="logo-form">
                                                    <h4>Import Logo for {{ tenant.name }}</h4>
                                                    <p>The file to upload must be a .png</p>
                                                    <form @submit.prevent="submitLogoForm(tenant)" enctype="multipart/form-data">
                                                        <!--                                                        <InputImage v-model="tenant.id" ></InputImage>-->
                                                        <input type="file" accept=".png" required @input="(ev) => onFileChange(ev, tenant)" :value="tenant.temp_file_name"/>
                                                        <button type="submit" class="btn btn-success ms-2" :disabled="isLoading">
                                                    <span v-if="isLoading">
                                                        <font-awesome-icon :icon="['fa', 'spinner']" class="fa-spin"></font-awesome-icon>
                                                        Uploading...
                                                    </span>
                                                            <span v-else>Upload</span>
                                                        </button>
                                                    </form>
                                                </div>
                                                <div class="logo-image ms-4">
                                                    <p class="h6">
                                                        Current Logo:
                                                    </p>
                                                    <img class="tenant-logo w-100"
                                                         @error="setAltImg"
                                                         :src="tenant.logo.full_route"
                                                         :alt="tenant.logo.alt_text" :title="tenant.logo.alt_text" />
                                                </div>
                                            </div>
                                        </tab>
                                    </tabs>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">

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
</style>
