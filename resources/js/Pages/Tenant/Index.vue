<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import SooXmlForm from "@/Components/SooXmlForm.vue";
import Tabs from "@/Components/Tabs.vue";
import Tab from "@/Components/Tab.vue";

defineProps({
    isSuperAdmin: String,
    tenants: Array
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
                            <tabs>
                                <tab v-for="(tenant, index) in tenants" :title="tenant.name" :selected="index == 0" :key="tenant.id">
                                    <SooXmlForm
                                        v-model="tenant.file"
                                        :tenant="tenant"
                                        :tenant-name="tenant.name"
                                        />
                                </tab>
                            </tabs>
                        </div>
                        <div v-else>
                            <SooXmlForm
                                v-model="tenants.file"
                                :tenant="tenants"
                                :tenant-name="tenants.name"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
