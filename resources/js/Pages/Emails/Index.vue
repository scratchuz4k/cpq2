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
          <div class="mt-3 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <Datatable
              :allow="{
                update: true,
                delete: false,
                link: false,
              }"
              :columns="[
                { data: 'id', title: '#', style: 'width:5%' },
                { data: 'code', title: 'Code', style: 'width:20%' },
                { data: 'description', title: 'Description', style: 'width:30%' },
                { data: 'notes', title: 'Notes', style: 'width:5%' },
                { data: 'created_at', title: 'Created At', style: 'width:15%' },
                { data: 'updated_at', title: 'Updated At', style: 'width:15%' },
              ]"
              route="/email-templates"
            >
              <template #code="{ value }">
                <span class="badge text-bg-secondary">{{ value }}</span>
              </template>
              <template #notes="{ value }">
                <span class="cpq-tooltip border-0">
                    <font-awesome-icon :icon="['fas', 'circle-info']" />
                    <span class="cpq-tooltip-text"><small>{{ value }}</small></span>
                </span>
              </template>
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
          <div class="mt-3 max-w-7xl mx-auto sm:px-6 lg:px-8">
            <Datatable
              :allow="{
                update: false,
                delete: false,
                link: true,
              }"
              :columns="[
                { data: 'id', title: '#', style: 'width:5%' },
                { data: 'email.code', title: 'Code', style: 'width:18%' },
                { data: 'user.name', title: 'User', style: 'width:16%' },
                { data: 'from', title: 'From', style: 'width:18%' },
                { data: 'to', title: 'To', style: 'width:18%' },
                { data: 'updated_at', title: 'Updated At', style: 'width:15%' },
              ]"
              route="/email-history"
            >
              <template #email.code="{ value }">
                <span class="badge text-bg-secondary">{{ value }}</span>
              </template>
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
