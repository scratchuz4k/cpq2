<script setup>
import Panel from "primevue/panel";
import Select from "primevue/select";
import draggable from "vuedraggable";
import InputText from "primevue/inputtext";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import { reactive, ref } from "vue";
import Checkbox from "primevue/checkbox";

const props = defineProps({
  solution_type: Object,
  available_tables: Array,
  available_columns: Array,
});

const table = reactive(
  props.solution_type.table
    ? { ...props.solution_type.table }
    : {
        code: "custom",
        group: null,
        group_name: null,
        columns: [],
      }
);

const dragOptions = {
  animation: 200,
  group: "description",
  disabled: false,
  ghostClass: "ghost",
};

const saveSolutionType = () => {
  table.columns = table.columns.filter((x) => !!x.value);
  table.columns.forEach((x) => {
    let temp = props.available_columns.find((e) => e.value == x.value);
    x.has_order = !!temp?.has_order;
  });

  table.code = !table.code ? "custom" : table.code;
  router.put(
    `/solution-types/${props.solution_type.id}`,
    { ...solutiontype, tables: [table] },
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
  configurations:
    len && props.solution_type.configurations.component
      ? props.solution_type.configurations
      : { component: "DealMaker" },
});

const addColumn = (table) => {
  if (!Object.keys(table).includes("columns")) {
    table.columns = [];
  }
  table.columns.push({ value: null, name: null });
};

const deleteColumn = (element) => {
  let index = table.columns.findIndex((x) => x.value == element.value);
  if (index != -1) {
    table.columns.splice(index, 1);
  }
};

const clearAllColumns = async (table) => {
  let result = await swal.fire({
    title: "Are you sure?",
    text: "This action removes all columns",
  });

  if (["cancel", "backdrop"].includes(result.dismiss)) {
    return;
  }

  table.columns = [];
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
            <Panel class="mt-3" header="Summary Layout" :toggleable="true">
              <p class="mb-3">
                This section contains all the customizations for the appearance of the
                solution type to match specific preferences within the quote.
              </p>
              <div>
                <p for="solution_type-component" class="form-label">Choose the view:</p>
                <Select
                  inputId="solution_type-component"
                  v-model="solutiontype.configurations.component"
                  :options="available_tables"
                  placeholder="Select a View"
                  class="w-100"
                />
              </div>
            </Panel>

            <Panel class="mt-3" header="Summary Table" :toggleable="true">
              <p class="mb-3">
                In this section you can enable or disable a custom a table shown in the
                solution type view.
              </p>
              <div class="mb-3">
                <div class="d-flex align-items-center gap-2">
                  <Checkbox
                    inputId="has_custom_table"
                    v-model="solutiontype.configurations.has_custom_table"
                    binary
                    trueValue="1"
                    falseValue="0"
                  />
                  <label for="has_custom_table"> Enable Custom Table </label>
                </div>
              </div>
              <template v-if="solutiontype.configurations.has_custom_table == '1'">
                <div class="mb-3">
                  <label class="form-label">Choose the group field (optional):</label>
                  <Select
                    v-model="table.group"
                    :options="available_columns.filter((x) => x.is_group)"
                    id="solution-type-table-group"
                    placeholder="Select a group"
                    class="w-100"
                    optionLabel="name"
                    optionValue="value"
                    showClear
                  />
                </div>
                <div class="mb-3">
                  <label class="form-label">Pick a name (optional):</label>

                  <InputText
                    :id="`table-column-` + index + `-name`"
                    type="text"
                    v-model="table.group_name"
                    class="w-100"
                    size="small"
                  />
                </div>
                <hr />

                <div class="mb-3">
                  <label class="form-label">Choose and order the table columns:</label>
                  <table class="table">
                    <thead>
                      <tr>
                        <th></th>
                        <th style="width: 50%">Field</th>
                        <th style="width: 50%">Name</th>
                        <th>
                          <button
                            id="solution-type-table-remove-columns"
                            class="btn p-2 mx-1 border-0"
                            @click="clearAllColumns(table)"
                          >
                            <font-awesome-icon
                              :icon="['fas', 'trash-alt']"
                            ></font-awesome-icon>
                          </button>
                        </th>
                      </tr>
                    </thead>
                    <draggable
                      v-model="table.columns"
                      group="columns"
                      item-key="id"
                      tag="tbody"
                      v-bind="dragOptions"
                      handle=".handle"
                    >
                      <template #item="{ element, index }">
                        <tr>
                          <td>
                            <button class="btn p-1 border-0 handle">
                              <font-awesome-icon icon="fas fa-bars" />
                            </button>
                          </td>
                          <td>
                            <Select
                              v-model="element.value"
                              :options="available_columns"
                              :id="`table-column-` + index + `-value`"
                              optionLabel="name"
                              optionValue="value"
                              placeholder="Select a Field"
                              class="w-100"
                            ></Select>
                          </td>
                          <td>
                            <InputText
                              :id="`table-column-` + index + `-name`"
                              type="text"
                              v-model="element.name"
                              class="w-100"
                              size="small"
                            />
                          </td>
                          <td>
                            <button
                              class="btn p-2 mx-1 border-0"
                              @click="deleteColumn(element)"
                            >
                              <font-awesome-icon icon="fas fa-trash-alt" />
                            </button>
                          </td>
                        </tr>
                      </template>
                    </draggable>
                    <tfoot>
                      <tr>
                        <th colspan="4">
                          <div class="d-flex justify-content-end">
                            <button
                              id="solution-type-table-add-column"
                              class="btn btn-sm"
                              @click="addColumn(table)"
                            >
                              <font-awesome-icon
                                :icon="['fas', 'plus']"
                              ></font-awesome-icon>
                            </button>
                          </div>
                        </th>
                      </tr>
                    </tfoot>
                  </table></div
              ></template>
            </Panel>
          </tab>
        </tabs>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
