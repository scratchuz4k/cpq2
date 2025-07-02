<script setup>
import format from "@composables/format";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { Button, Paginator } from "primevue";
import { reactive, ref, watch } from "vue";
import queryString from "query-string";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";
import {exportData} from "@/Composables/export.js";
import {can, hasRole} from "@/Composables/useAuth.js";

const page = usePage();
const props = defineProps({
  rules: Array,
  price_plans: Array,
});

const visible = ref(false);
const showGlobal = ref(false);
const selectedRules = ref([]);
const emptyData = ref(true);
const has_export_permission = can(`export spa`) || hasRole('Super Admin');

const form = reactive({
  rule: null,
  rule_id: [],
  field_1: null,
  field_2: null,
});

const fields = reactive({
  account: null,
  supplier: null,
  product: null,
  group: null,
});

const data = ref([]);
const meta = reactive({
  page: 1,
  total: 0,
  first: 1,
  limit: 20,
  orderBy: "id",
  order: "asc",
});

const globalRules = {};
props.rules.forEach((element) => {
  let str = element.code;
  const splitResult = str.split(/(?=[A-Z])/);
  if (!Object.keys(globalRules).includes(splitResult[0])) {
    globalRules[splitResult[0]] = [];
  }

  if (!globalRules[splitResult[0]].find((x) => x === splitResult[1])) {
    globalRules[splitResult[0]].push(splitResult[1] ?? null);
  }
});

const handleGlobal = () => {
  let keys = Object.keys(fields).filter((x) => fields[x] != null);
  showGlobal.value = keys.length ? true : !showGlobal.value;
  Object.keys(fields).forEach((x) => (fields[x] = null));

  form.rule_id = [];
  form.field_1 = null;
  form.field_2 = null;
  if (!showGlobal.value) {
    selectedRules.value = [];
    getData();
    return;
  }

  selectedRules.value = props.rules;
  selectedRules.value = selectedRules.value.filter((x) => x.code.includes("Global"));

  getData();
};

const handleFields = () => {
  showGlobal.value = false;
  let keys = Object.keys(fields).filter((x) => fields[x] != null);

  if (keys.length > 2) {
    data.value = [];
    selectedRules.value = [];
    notyf.error("Only two entities may be selected at a time");
    return;
  } else if (keys.length == 0) {
    selectedRules.value = [];
  } else {
    selectedRules.value = props.rules;

    keys.forEach((e) => {
      selectedRules.value = selectedRules.value.filter((x) => {
        return x.code.includes(format.capitalizeFirstLetter(e));
      });
    });
  }
  getData();
};

const getData = async () => {
  let string = queryString.stringify({
    orderBy: meta.orderBy,
    order: meta.order,
    page: meta.page,
    filters: JSON.stringify(getFilters()),
    limit: meta.limit,
  });

  let res = await axios.get("/api/spas?" + string);
  data.value = res.data.data;
  emptyData.value = !data.value.length;
  meta.total = res.data.meta?.total ?? res.data.total;
  meta.first = (meta.page - 1) * meta.limit;
};

const getFilters = () => {
    let temp = [];
    if (!showGlobal.value) {
        let keys = Object.keys(fields).filter((x) => fields[x] != null);
        selectedRules.value.forEach((x, ruleIndex) => {
            const splitResult = x.code.split(/(?=[A-Z])/);
            splitResult.forEach((e, index) => {
                if (!keys.includes(e.toLowerCase())) {
                    return;
                }

                temp.push({
                    column: "rule_id",
                    operator: "equals",
                    value: x.id,
                    function: ruleIndex != 0 ? "orWhere" : "where",
                });

                if (index == 0) {
                    temp.push({
                        column: "field_1",
                        operator: "equals",
                        value: fields[e.toLowerCase()]?.id,
                    });
                }

                if (index == 1) {
                    temp.push({
                        column: "field_2",
                        operator: "equals",
                        value: fields[e.toLowerCase()]?.id,
                    });
                }
            });
        });
    } else {
        temp.push({
            column: "rule_id",
            operator: "equals",
            value: selectedRules.value[0].id,
        });

        temp.push({
            column: "field_1",
            operator: "equals",
            value: page.props.tenant.id,
        });
    }

    return temp;
}
const deleteRecord = async (id) => {
  swal
    .fire({
      title: "Are you sure?",
    })
    .then(async (result) => {
      if (["cancel", "backdrop"].includes(result.dismiss)) {
        return;
      }
      try {
        let res = await axios.delete("/api/spas/" + id);
        getData();
        notyf.success("Removed with success");
        return;
      } catch (err) {
        notyf.error(err?.response?.data?.message ?? "Something went wrong");
      }
    });
};

const cloneSpa = async (item) => {
  let result = await swal.fire({
    title: "Are you sure?",
    text: "This action will clone the selected SPA",
  });
  if (["cancel", "backdrop"].includes(result.dismiss)) {
    return;
  }
  try {
    let res = await axios.post("/api/spas", {
      rule_id: item.rule_id ?? 0,
      field_1: item.field_1?.id ?? 0,
      field_2: item.field_2?.id ?? 0,
      operation_id: item.operation_id ?? 0,
      price_plan_id: item.price_plan_id ?? null,
      value: item.value ?? 0,
      percentage: item.percentage ?? 0,
    });
    notyf.success("Clone with success");
    getData();
  } catch (err) {
    notyf.error(err?.response?.data?.message ?? "Something went wrong");
  }
};

const handlePaginator = (ev) => {
  meta.limit = ev.rows;
  meta.page = ev.page + 1;
  getData();
};

const clearFilters = () => {
  showGlobal.value = false;
  Object.keys(fields).forEach((x) => (fields[x] = null));
  selectedRules.value = [];
  getData();
};

const sortBy = (column) => {
  let changeSort = meta.order == "asc" ? "desc" : "asc";
  meta.order = meta.orderBy === column ? changeSort : meta.order;
  meta.orderBy = column;
  getData();
};

const getExportData = async () => {
    // let filters = {
    //     filters: ,
    //     search: global.value,
    // };

    exportData([], 'spas');
};

const items = ref([
  { label: "Home", url: "/" },
  { label: "My Account", url: "/myaccount" },
  { label: "SPAs" },
]);

const columns = [
  { name: "#", data: "id" },
  { name: "Rule", data: "rule_id" },
  { name: "Level 1", data: "field_1" },
  { name: "Level 2", data: "field_2" },
  { name: "Price Plan", data: "price_plan_id" },
  { name: "Operation", data: "operation_id" },
  { name: "Value", data: "value" },
  { name: "Percentage", data: "percentage" },
];

getData();
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
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 pt-2">
        <div class="mb-2 mt-1">
          <Button
            :class="{ 'p-button-secondary': !visible, 'p-button-primary': visible }"
            @click="visible = !visible"
            class="p-button p-component me-2"
            ><font-awesome-icon :icon="['fas', 'filter']"
          /></Button>
            <Button v-if="has_export_permission"
                    type="button"
                    :severity="'secondary'"
                    @click="getExportData()"
                    :disabled="emptyData"
            >
                <font-awesome-icon :icon="['fas', 'file-export']"/>
            </Button>
          <Button
            v-if="visible"
            type="button"
            :severity="form.rule ? 'primary' : 'secondary'"
            @click="clearFilters()"
          >
            <font-awesome-icon :icon="['fas', 'eraser']" />
          </Button>
          <div v-if="visible" class="mt-3 mb-3">
            <div class="row row-cols-4 border rounded p-2">
              <div class="col">
                <label class="form-label">Global</label>
                <Button
                  id="Global"
                  class="w-100 py-2 border-2 lh-1"
                  :severity="showGlobal ? 'primary' : 'secondary'"
                  @click="handleGlobal()"
                >
                  {{ showGlobal ? "Hide" : "Show" }}
                </Button>
              </div>
              <div class="col">
                <label class="form-label">Account</label>
                <InputSelect
                  id="filter-account"
                  url="/accounts"
                  :fields="['name', 'reference']"
                  v-model="fields.account"
                  @change="handleFields()"
                  placeholder="Select an Account"
                >
                </InputSelect>
              </div>
              <div class="col">
                <label class="form-label">Supplier</label>
                <InputSelect
                  id="filter-supplier"
                  url="/suppliers"
                  :fields="['name']"
                  v-model="fields.supplier"
                  @change="handleFields()"
                  placeholder="Select an Supplier"
                >
                </InputSelect>
              </div>
              <div class="col">
                <label class="form-label">Product</label>
                <InputSelect
                  id="filter-product"
                  url="/products"
                  :fields="['name']"
                  v-model="fields.product"
                  @change="handleFields()"
                  placeholder="Select a Product"
                >
                </InputSelect>
              </div>
              <div class="col">
                <label class="form-label">Group</label>
                <InputSelect
                  id="filter-group"
                  url="/groups"
                  :fields="['name']"
                  v-model="fields.group"
                  @change="handleFields()"
                  placeholder="Select a Group"
                >
                </InputSelect>
              </div>
            </div>
            <div v-if="selectedRules.length" class="row mt-3">
              <div class="col">
                <p>
                  <b class="me-2">Selected Rules:</b>
                  <template v-for="(rule, index) in selectedRules">
                    <span
                      class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm"
                    >
                      {{ rule.name }}
                    </span>
                  </template>
                </p>
              </div>
            </div>
          </div>
        </div>
        <div>
          <table class="table">
            <thead>
              <tr>
                <th v-for="column in columns">
                  <span
                    class="column-title sortable d-inline-flex align-items-center"
                    @click="sortBy(column.data)"
                    :style="{
                      textDecoration: column.data == meta.orderBy ? 'underline' : 'none',
                    }"
                  >
                    {{ column.name }}
                    <span class="text-center" style="width: 20px">
                      <template v-if="column.data == meta.orderBy">
                        <font-awesome-icon
                          v-if="meta.order == 'desc'"
                          :icon="['fas', 'sort-down']"
                        />
                        <font-awesome-icon v-else :icon="['fas', 'sort-up']" />
                      </template>
                    </span>
                  </span>
                </th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in data">
                <td>{{ item.id }}</td>
                <td>{{ item.rule.name }}</td>
                <td>{{ item.field_1?.name }}</td>
                <td>{{ item.field_2?.name }}</td>
                <td>{{ item.price_plan?.name }}</td>
                <td>{{ item.operation.code }}</td>
                <td>{{ item.value }}</td>
                <td>{{ item.percentage }}</td>
                <td>
                  <div class="d-flex justify-content-center">
                    <button
                      :id="'datatable-btn-' + index + '-clone'"
                      class="btn p-2 py-0 mx-1 border-0"
                      @click="cloneSpa(item)"
                    >
                      <font-awesome-icon :icon="['fas', 'copy']" />
                    </button>
                    <Link
                      :id="'datatable-btn-' + index + '-edit'"
                      :href="'/spas/' + item.id + '/edit'"
                      class="btn p-2 py-0 mx-1 border-0"
                    >
                      <font-awesome-icon :icon="['fas', 'edit']" />
                    </Link>

                    <button
                      :id="'datatable-btn-' + index + '-delete'"
                      class="btn p-2 py-0 mx-1 border-0"
                      @click="deleteRecord(item.id)"
                    >
                      <font-awesome-icon icon="fas fa-trash-alt" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!data.length" class="text-center">
                <td colspan="9">No Results Found</td>
              </tr>
            </tbody>
          </table>
          <Paginator
            v-model:first="meta.first"
            :rows="meta.limit"
            :totalRecords="meta.total"
            :rowsPerPageOptions="[20, 30, 50]"
            @page="handlePaginator"
          >
          </Paginator>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
<style scoped>
.table thead th,
.table tbody td {
  color: #334155;
}
</style>
