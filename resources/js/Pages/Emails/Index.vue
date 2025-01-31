<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { ref } from "vue";
const items = ref([
  { label: "Home", url: "/" },
  { label: "My Account", url: "/myaccount" },
  { label: "Email's" },
]);
</script>
<template>
  <Head title="Email's" />
  <AuthenticatedLayout>
    <div class="panel">
      <Header title="Email's" :breadcrumbs="items"> </Header>
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <Datatable
          :allow="{
            update: true,
            delete: true,
            link: false,
          }"
          :columns="[
            { data: 'id', title: '#', style: 'width:5%' },
            { data: 'code', title: 'Code', style: 'width:25%' },
            { data: 'description', title: 'Description', style: 'width:30%' },
            { data: 'notes', title: 'Notes' },
          ]"
          route="/email-templates"
        >
          <template #updated_at="{ value }">
            {{ format.date(value) }}
          </template>
          <template #created_at="{ value }">
            {{ format.date(value) }}
          </template>
        </Datatable>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
