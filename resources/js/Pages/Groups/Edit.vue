<script setup>
import { Button, InputText } from "primevue";
import { reactive, ref } from "vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router, usePage } from "@inertiajs/vue3";
const props = defineProps({
  group: Object,
});

const group = reactive(props.group);

const saveGroup = () => {
  router.put(`/users/${props.group.id}`, group, {
    onSuccess: (page) => {
      notyf.success("Updated with Success");
    },
    onError: (errors) => {
      let string = "";
      Object.keys(errors).forEach((key) => {
        string += errors[key] + "<br>";
      });
      notyf.error(string);
    },
  });
};
const items = ref([
  { label: "Home", url: "/" },
  { label: "My Account", url: "/myaccount" },
  { label: "Groups", url: "/users" },
  { label: "Edit Group" },
]);
</script>
<template>
  <Head :title="'Edit User #' + group.id" />
  <AuthenticatedLayout>
    <div class="panel">
      <form @submit.prevent="saveGroup()">
        <Header :title="'Edit User #' + group.id" :breadcrumbs="items">
          <template #buttons>
            <Button class="btn btn-primary">
              <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon>
              Save
            </Button>
          </template>
        </Header>
        <div class="row">
          <div class="col">
            <label for="user-bulk-commission-rate" class="form-label">Name:</label>
            <InputText
              id="user-bulk-commission-rate"
              type="text"
              class="w-100"
              v-model="group.name"
            />
          </div>
        </div>
        <div class="d-flex justify-content-end mt-3">
          <div class="btn btn-secondary me-2" @click="showModal = false">Cancel</div>
          <Button class="btn btn-primary" type="submit"> Save </Button>
        </div>
      </form>
    </div>
  </AuthenticatedLayout>
</template>
