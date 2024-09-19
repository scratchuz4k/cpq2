<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SooXmlForm from "@/Components/SooXmlForm.vue";

const titleImport = 'Import XML file for '
const subTitleImport = 'The file to upload must be a .xml'
const titleTextArea = 'Input SSO configuration for '
const subTitleTextArea = 'Please provide a valid xml syntax'

defineProps({
    isSuperAdmin: String,
    tenants: Array,
    files: Array
})

</script>

<template>
    <AuthenticatedLayout>
        <div class="panel">
            <div class="containter">
                <h1>Admin</h1>
                <div class="row">
                    <div class="col">
                        <div v-if="isSuperAdmin" class="xml-in-tabs">
                            <ul class="nav nav-tabs" id="myTab" role="tablist">
                                <li v-for="(tenant, index) in tenants" class="nav-item" role="presentation">
                                    <button
                                        class="nav-link"
                                        v-bind:class="{ 'active': index === 0 }"
                                        id="home-tab"
                                        data-bs-toggle="tab"
                                        v-bind:data-bs-target="'#tab-' + tenant.id"
                                        type="button"
                                        role="tab"
                                        v-bind:aria-controls="'tab-' + tenant.id"
                                        aria-selected="true"
                                    >
                                        {{ tenant.name }}
                                    </button>
                                </li>
                            </ul>
                            <div class="tab-content p-3" id="myTabContent">
                                <div v-for="(tenant, index) in tenants"
                                     class="tab-pane fade"
                                     :class="{ 'active show': index === 0 }"
                                     v-bind:id="'tab-' + tenant.id"
                                     role="tabpanel"
                                     aria-labelledby="home-tab"
                                     tabindex="0"
                                >
                                    <SooXmlForm
                                        :tenant="tenant.id"
                                        :titleImport="titleImport + tenant.name"
                                        :subTitleImport="subTitleImport"
                                        v-bind:titleTextArea="titleTextArea + tenant.name"
                                        :subTitleTextArea="subTitleTextArea"
                                        :files="files"
                                    />
                                </div>
                            </div>
                        </div>
                        <div v-else>
                            <SooXmlForm />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
