<script setup>
import Select from "primevue/select";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import { reactive, ref } from "vue";

const props = defineProps({
  solution_type: Object,
  available_tables: Array,
});

const saveSolutionType = () => {
  router.put(`/solution-types/${props.solution_type.id}`, solutiontype, {
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

let len = Object.keys(props.solution_type.configurations).length;
const solutiontype = reactive({
  configurations: len ? props.solution_type.configurations : { component: "DealMaker" },
});

const items = ref([
  { label: "Home", url: "/" },
  { label: "My Account", url: "/myaccount" },
  { label: "Solution Types", url: "/solution-types" },
  { label: "Edit Solution Type" },
]);
</script>
<template>
  <Head title="Solution Types" />
  <AuthenticatedLayout>
    <div class="panel">
      <Header
        :title="'Edit Solution Types #' + props.solution_type.id"
        :breadcrumbs="items"
      >
        <template #buttons>
          <PrimaryButton id="btn-save-solution-type" @click="saveSolutionType()">
            <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon>
            Save
          </PrimaryButton>
        </template>
      </Header>
      <div class="mt-3">
        <tabs>
          <tab title="General" :selected="true">
            <div class="mt-3">
              <div class="mb-5">
                <label for="solution_type-name" class="form-label">Name:</label>
                <input
                  id="solution_type-name"
                  class="form-control"
                  v-model="solution_type.name"
                  disabled
                />
              </div>
            </div>
          </tab>
          <tab title="Quote">
            <p class="my-3">
              This section contains all the customizations for the appearance of the
              solution type to match specific preferences within the quote.
            </p>
            <div class="my-3">
              <p for="solution_type-component" class="form-label">Choose the view:</p>
              <Select
                inputId="solution_type-component"
                v-model="solutiontype.configurations.component"
                :options="available_tables"
                placeholder="Select a View"
                class="w-100"
              />
            </div>
          </tab>
        </tabs>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
