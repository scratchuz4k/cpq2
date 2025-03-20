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

const handlePaginator = (ev) => {
  meta.limit = ev.rows;
  meta.page = ev.page + 1;
  getData();
};
const clearFilters = () => {
  Object.keys(form).forEach((x) => (form[x] = null));
};

const items = ref([
  { label: "Home", url: "/" },
  { label: "My Account", url: "/myaccount" },
  { label: "SPAs" },
]);

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
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="my-3">
          <Button @click="visible = !visible" class="me-2"
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
                  <p>{{ rule.name }}</p>
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
        <div>
          <table class="table">
            <thead>
              <tr>
                <th>Rule Name</th>
                <th>Level 1</th>
                <th>Level 2</th>
                <th>Price Plan</th>
                <th>Operation</th>
                <th>Value</th>
                <th>Percentage</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in data">
                <td>{{ item.rule.name }}</td>
                <td>{{ item.field_1?.name }}</td>
                <td>{{ item.field_2?.name }}</td>
                <td>{{ item.price_plan?.name }}</td>
                <td>{{ item.operation.code }}</td>
                <td>{{ item.value }}</td>
                <td>{{ item.percentage }}</td>
                <td>
                  <div class="d-flex justify-content-center">
                    <Link
                      :id="'datatable-btn-' + index + '-edit'"
                      :href="'/spas/' + item.id + '/edit'"
                      class="btn p-2 mx-1 border-0"
                    >
                      <font-awesome-icon :icon="['fas', 'edit']" />
                    </Link>

                    <button
                      :id="'datatable-btn-' + index + '-delete'"
                      class="btn p-2 mx-1 border-0"
                      @click="deleteRecord(item.id)"
                    >
                      <font-awesome-icon icon="fas fa-trash-alt" />
                    </button>
                  </div>
                </td>
              </tr>
              <tr v-if="!data.length" class="text-center">
                <td colspan="8">No Results Found</td>
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
