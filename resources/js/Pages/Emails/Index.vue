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
      <tabs class="mt-3">
        <tab title="Templates" :selected="true">
          <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <Datatable
              :allow="{
                update: true,
                delete: false,
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
        </tab>
        <tab title="History">
          <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <Datatable
              :allow="{
                update: false,
                delete: false,
                link: true,
              }"
              :columns="[
                { data: 'id', title: '#', style: 'width:5%' },
                { data: 'email.code', title: 'Code', style: 'width:25%' },
                { data: 'user.name', title: 'User', style: 'width:25%' },
                { data: 'from', title: 'From', style: 'width:25%' },
                { data: 'to', title: 'To', style: 'width:30%' },
              ]"
              route="/email-history"
            >
              <template #updated_at="{ value }">
                {{ format.date(value) }}
              </template>
              <template #created_at="{ value }">
                {{ format.date(value) }}
              </template>
            </Datatable>
          </div>
        </tab>
      </tabs>
    </div>
  </AuthenticatedLayout>
</template>
