<script setup>
import Panel from "primevue/panel";
import draggable from "vuedraggable";
import Select from "primevue/select";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import { reactive, ref } from "vue";

const props = defineProps({
  solution_type: Object,
  available_columns: Array,
  price_plans: Array,
});

const saveSolutionType = () => {
  router.put(
    `/solution-types/${props.solution_type.id}`,
    { ...solutiontype, tables: tables[solutiontype.configurations.component].tables },
    {
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
    }
  );
};

let len = Object.keys(props.solution_type.configurations).length;
const solutiontype = reactive({
  configurations: len ? props.solution_type.configurations : { component: "DealMaker" },
  tables: [],
});

const tables = reactive({
  DealMaker: {
    tables: [],
  },
  Simple: {
    tables: [],
  },
});

props.price_plans.forEach((e) => {
  let table = props.solution_type.tables.find((table) => table.code == e);
  tables.DealMaker.tables.push({
    columns: table && table.columns.length ? table.columns : props.available_columns,
    code: e,
  });
});

let table = props.solution_type.tables.find((table) => table.code == "Simple");
tables.Simple.tables.push({
  columns: table && table.columns.length ? table.columns : props.available_columns,
  code: "Simple",
});

const selectAll = (ev, table) => {
  table.columns.forEach((e) => (e.is_active = false));
  if (ev.target.checked) {
    table.columns.forEach((e) => (e.is_active = true));
  }
};

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
                :options="['DealMaker', 'Simple']"
                placeholder="Select a View"
                class="w-100"
              />
            </div>
            <p>
              Select columns to show in the tables and drag and drop items to sort the
              columns
            </p>
            <div class="row my-3">
              <div
                v-for="table in tables[solutiontype.configurations.component].tables"
                class="col-4"
              >
                <Panel :header="table.code" :toggleable="true">
                  <table class="table">
                    <thead>
                      <tr>
                        <th style="width: 60%">Column</th>
                        <th class="text-end" v-if="table.code == 'Simple'">Group</th>
                        <th class="text-end">Show</th>
                      </tr>
                      <tr>
                        <th>
                          <p class="form-check-label" :for="'select-option-all'">All</p>
                        </th>
                        <th v-if="table.code == 'Simple'"></th>
                        <th class="text-end">
                          <input
                            class="form-check-input"
                            type="checkbox"
                            :id="'select-option-all'"
                            @click="(ev) => selectAll(ev, table)"
                          />
                        </th>
                      </tr>
                    </thead>
                    <draggable
                      :id="'drag' + table.code"
                      v-model="table.columns"
                      itemKey="id"
                      tag="tbody"
                    >
                      <template #item="{ element }">
                        <tr>
                          <td>
                            <label
                              class="form-check-label"
                              :for="table.code + '-' + element.value + '-option'"
                              style="cursor: pointer"
                            >
                              <font-awesome-icon
                                :icon="['fas', 'sort']"
                                class="me-2"
                              ></font-awesome-icon
                              >{{ element.name }}
                            </label>
                          </td>
                          <td class="text-end" v-if="table.code == 'Simple'">
                            <input
                              class="form-check-input"
                              type="checkbox"
                              :id="table.code + '-' + element.value + '-is_group'"
                              v-model="element.is_group"
                            />
                          </td>
                          <td class="text-end">
                            <input
                              class="form-check-input"
                              type="checkbox"
                              :id="table.code + '-' + element.value + '-is_active'"
                              v-model="element.is_active"
                            />
                          </td>
                        </tr>
                      </template>
                    </draggable>
                  </table>
                </Panel>
              </div>
            </div>
          </tab>
        </tabs>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
