<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, Link } from "@inertiajs/vue3";
import { Button } from "primevue";
import { reactive, ref, watch } from "vue";
import queryString from "query-string";
import { usePage } from "@inertiajs/vue3";
import Paginator from "primevue/paginator";
import _ from "lodash";

const page = usePage();

const props = defineProps({
  rules: Array,
  price_plans: Array,
});

const visible = ref(false);

const form = reactive({
  rule: null,
  rule_id: null,
  field_1: null,
  field_2: null,
  price_plan_id: null,
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

const rules = {};
props.rules.forEach((element) => {
  let str = element.code;
  const splitResult = str.split(/(?=[A-Z])/);

  rules[element.code] = { entity_1: splitResult[0], entity_2: splitResult[1] };
});

const changeRule = (rule) => {
  form.rule_id = rule?.id;
  form.field_1 = rule?.name == "Global" ? page.props.tenant : null;
  form.field_2 = null;
};

const getData = async () => {
  let temp = [];
  if (form.rule_id) {
    temp.push({ column: "rule_id", operator: "equals", value: form.rule_id });
  }
  if (form.field_1) {
    temp.push({ column: "field_1", operator: "equals", value: form.field_1.id });
  }
  if (form.field_2) {
    temp.push({ column: "field_2", operator: "equals", value: form.field_2.id });
  }

  if (form.price_plan_id) {
    temp.push({ column: "price_plan_id", operator: "equals", value: form.price_plan_id });
  }

  let string = queryString.stringify({
    orderBy: meta.orderBy,
    order: meta.order,
    page: meta.page,
    filters: JSON.stringify(temp),
    limit: meta.limit,
    page: meta.page,
  });

  let res = await axios.get("/api/spas?" + string);
  data.value = res.data.data;
  meta.total = res.data.meta?.total ?? res.data.total;
  meta.first = (meta.page - 1) * meta.limit;
};

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
  Object.keys(form).forEach((x) => (form[x] = null));
};

const sortBy = (column) => {
  let changeSort = meta.order == "asc" ? "desc" : "asc";
  meta.order = meta.orderBy === column ? changeSort : meta.order;
  meta.orderBy = column;
  getData();
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

watch(
  form,
  _.debounce(() => {
    getData();
  })
);
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
            :class="{'p-button-secondary': !visible, 'p-button-primary': visible}"
            @click="visible = !visible"
            class="p-button p-component me-2"
            ><font-awesome-icon :icon="['fas', 'filter']"
          /></Button>

          <Button
            v-if="visible"
            type="button"
            :severity="form.rule ? 'primary' : 'secondary'"
            @click="clearFilters()"
          >
            <font-awesome-icon :icon="['fas', 'eraser']" />
          </Button>
          <div v-if="visible" class="d-flex mt-3">
            <div class="d-flex border rounded p-2 me-2">
              <div class="me-2">
                <label class="form-label">Selec Rule</label>
                <select
                  class="form-select"
                  id="product-supplier"
                  v-model="form.rule"
                  @change="changeRule(form.rule)"
                >
                  <option :value="null">Select Rule</option>
                  <option v-for="(rule, index) in props.rules" :value="rule">
                    {{ rule.name }}
                  </option>
                </select>
              </div>
              <div class="me-2">
                <template
                  v-if="form.rule?.code && rules[form.rule?.code]?.entity_1 == 'Account'"
                >
                  <label class="form-label">Account</label>
                  <InputSelect
                    id="Account1"
                    url="/accounts"
                    field="name"
                    v-model="form.field_1"
                    :is-multiple="false"
                  >
                  </InputSelect>
                </template>
                <template
                  v-if="form.rule?.code && rules[form.rule?.code]?.entity_1 == 'Supplier'"
                >
                  <label class="form-label">Supplier</label>
                  <InputSelect
                    id="Supplier1"
                    url="/suppliers"
                    field="name"
                    v-model="form.field_1"
                    :is-multiple="false"
                  >
                  </InputSelect>
                </template>
                <template
                  v-if="form.rule?.code && rules[form.rule?.code]?.entity_1 == 'Product'"
                >
                  <label class="form-label">Product</label>
                  <InputSelect
                    id="Product1"
                    url="/products"
                    field="name"
                    v-model="form.field_1"
                    :is-multiple="false"
                  >
                  </InputSelect>
                </template>
              </div>
              <div class="me-2" v-if="form.rule?.code">
                <template
                  v-if="form.rule?.code && rules[form.rule?.code]?.entity_2 == 'Account'"
                >
                  <label class="form-label">Account</label>
                  <InputSelect
                    id="Account2"
                    url="/accounts"
                    field="name"
                    v-model="form.field_2"
                    :is-multiple="false"
                  >
                  </InputSelect>
                </template>
                <template
                  v-if="form.rule?.code && rules[form.rule?.code]?.entity_2 == 'Supplier'"
                >
                  <label class="form-label">Supplier</label>
                  <InputSelect
                    id="Supplier2"
                    url="/suppliers"
                    field="name"
                    v-model="form.field_2"
                    :is-multiple="false"
                  >
                  </InputSelect>
                </template>
                <template
                  v-if="form.rule?.code && rules[form.rule?.code]?.entity_2 == 'Product'"
                >
                  <label class="form-label">Product</label>
                  <InputSelect
                    id="Product2"
                    url="/products"
                    field="name"
                    v-model="form.field_2"
                    :is-multiple="false"
                  >
                  </InputSelect>
                </template>
              </div>
            </div>
            <div class="d-flex border rounded p-2 me-2">
              <div>
                <label class="form-label">Select a Price Plan</label>
                <select
                  class="form-select"
                  v-model="form.price_plan_id"
                  :is-multiple="false"
                >
                  <option :value="null">Select an option</option>
                  <option v-for="price_plan in props.price_plans" :value="price_plan.id">
                    {{ price_plan.name }}
                  </option>
                </select>
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
.table thead th, .table tbody td {
    color:#334155;
}
</style>
