<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head } from "@inertiajs/vue3";
import { ref } from "vue";
import { assignOrder, createOrder } from "@/Composables/useOrders.js";
import Button from "primevue/button";
const props = defineProps({
  users: {
    type: Array,
    default: [],
  },
  solutions: {
    type: Array,
    default: [],
  },
  accounts: {
    type: Array,
    default: [],
  },
  status: {
    type: Array,
    default: [],
  },
});

const assignOrderAndRefresh = async (id) => {
  const res = await assignOrder(id);
  if (res) {
    emitter.emit("datatable-refresh");
  }
};
const items = ref([{ label: "Home", url: "/" }, { label: "Orders" }]);
</script>

<template>
  <Head title="Orders" />
  <AuthenticatedLayout>
    <div class="panel">
      <Header title="Orders" :breadcrumbs="items"></Header>
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <Datatable
          :tableProps="{ id: 'dashboard-table' }"
          :allow="{
            link: true,
          }"
          :columns="[
            { data: 'id', title: '#', style: 'width:5%' },
            { data: 'account.name', title: 'Account', style: 'width:13%' },
            { data: 'solution_type.name', title: 'Solution', style: 'width:18%' },
            { data: 'address_postcode', title: 'Postcode', style: 'width:8%' },
            { data: 'user.name', title: 'Owner', style: 'width:18%' },
            { data: 'status', title: 'Status', style: 'width:10%' },
            { data: 'updated_at', title: 'Updated At', style: 'width:15%' },
            { data: 'actions', title: '', style: 'width:13%' },
            { data: 'items.data', title: 'Data', hide: true },
            { data: 'items.name', title: 'Name', hide: true },
            { data: 'address_data', title: 'Address', hide: true },
            { data: 'account.contacts.email', title: 'Email', hide: true },
          ]"
          :filters="{
            'user.name': { type: 'options', options: users.map((x) => x.name) },
            'solution_type.name': {
              type: 'options',
              options: solutions.map((x) => x.name),
            },
            status: { type: 'options', options: status },
          }"
          route="/orders"
        >
          <template #status="{ value }">
            <span class="badge text-bg-secondary">{{ value }}</span>
          </template>
          <template #updated_at="{ value }">
            {{ format.date(value) }}
          </template>
          <template #actions="{ data, value, index, getData }">
            <div class="d-flex justify-content-end">
              <button
                v-if="data.user_id === null"
                :id="'datatable-btn-' + index + '-assign-order'"
                @click="assignOrderAndRefresh(data.id)"
                class="btn px-0 py-0 border-0"
              >
                <font-awesome-icon :icon="['fas', 'user-plus']" />
              </button>
            </div>
          </template>
        </Datatable>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
