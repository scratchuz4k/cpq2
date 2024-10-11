<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Tabs from "@/Components/Tabs.vue";
import Tab from "@/Components/Tab.vue";
import {router} from "@inertiajs/vue3";
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
</script>

<template>
    <AuthenticatedLayout>
        <div class="panel">
            <div class="containter">
                <h1>Admin</h1>
                <div class="row">
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
                                <div class="separator pt-4"> OR</div>
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
            </div>
        </div>
    </AuthenticatedLayout>
</template>
