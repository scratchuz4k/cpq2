<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { ref } from "vue";
const items = ref([
  { label: "Home", url: "/" },
  { label: "My Account", url: "/myaccount" },
  { label: "SPAs" },
]);
</script>
<template>
  <Head title="SPAs" />
  <AuthenticatedLayout>
    <div class="panel">
      <Header title="SPAs" :breadcrumbs="items">
        <template #buttons>
          <Link id="create-spa" href="/spas/create" class="btn btn-primary me-2">
            <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon> New SPA
          </Link>
        </template>
      </Header>
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <Datatable
          :allow="{
            update: true,
            delete: true,
            link: false,
          }"
          :columns="[
            { data: 'id', title: '#', style: 'width:5%' },
            { data: 'rule.code', title: 'Rule', style: 'width:15%' },
            { data: 'field_1.name', title: 'Field 1', style: 'width:15%' },
            { data: 'field_2.name', title: 'Field 2', style: 'width:15%' },
            { data: 'price_plan.name', title: 'Price Plan', style: 'width:15%' },
            { data: 'operation.code', title: 'Operation', style: 'width:15%' },
            { data: 'value', title: 'Value', style: 'width:15%' },
            { data: 'percentage', title: 'Percentage', style: 'width:15%' },
          ]"
          route="/spas"
        >
        </Datatable>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
