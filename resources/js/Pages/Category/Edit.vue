<script setup>
import { reactive, ref } from "vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, router } from "@inertiajs/vue3";
import { Button } from "primevue";

const props = defineProps({
  category: Object,
});

const product = ref(null);
const category = reactive(props.category);

const saveCate = () => {
  router.put(`/categories/${category.id}`, category, {
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

const setProductCategory = async (product_id, category_id, action) => {
  if (!product_id) {
    return;
  }
  try {
    await axios.put("/api/categories/" + category_id, { product_id: product_id });
    product.value = null;

    if (product_id) {
      notyf.success(action === 'remove' ? "Product removed with success" : "Product added with Success");
    }
    emitter.emit("datatable-refresh:group-products");
  } catch (ex) {
    product.value = null;
    notyf.error(ex.response?.data?.message ?? ex.response?.data);
  }
};

const items = ref([
  { label: "Home", url: "/" },
  { label: "My Account", url: "/myaccount" },
  { label: "Categories", url: "/categories" },
  { label: "Edit Category" },
]);
</script>
<template>
  <Head title="New Product" />
  <AuthenticatedLayout>
    <div class="panel">
      <Header title="New Product" :breadcrumbs="items">
        <template #buttons>
          <PrimaryButton id="btn-save-product" @click="saveCate()">
            <font-awesome-icon class="me-2" :icon="['fas', 'save']"></font-awesome-icon>
            Save
          </PrimaryButton>
        </template>
      </Header>
      <tabs>
        <tab :selected="true" title="General">
          <div class="col-6">
            <div class="mb-3">
              <label class="form-label" for="product-code">Code:</label>
              <input id="product-code" v-model="category.code" class="form-control" />
            </div>
            <div class="mb-3">
              <label class="form-label" for="product-name">Name:</label>
              <input id="product-name" v-model="category.name" class="form-control" />
            </div>
          </div>
        </tab>
        <tab title="Products">
          <form
            class="mt-3 d-flex"
            @submit.prevent="setProductCategory(product?.id, category.id, 'add')"
          >
            <div class="me-3" style="width: 100%">
              <label class="form-label">Add Products:</label>
              <InputSelect
                id="product"
                v-model="product"
                :fields="['name']"
                placeholder="Select a Product"
                :query="[
                  { column: 'categories.id', operator: 'is_null', value: category.id },
                ]"
                url="/products"
              >
              </InputSelect>
            </div>
            <div class="d-flex align-items-end justify-content-end">
              <Button
                id="button-add-product-to-category"
                class="btn btn-secondary"
                type="submit"
              >
                <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
              </Button>
            </div>
          </form>
          <div class="row mt-3">
            <div class="col">
              <label class="form-label">Products:</label>
              <Datatable
                id="group-products"
                :columns="[
                  { data: 'id', title: '#', style: 'width:5%' },
                  { data: 'code', title: 'Code', style: 'width:20%' },
                  { data: 'name', title: 'Name', style: 'width:30%' },
                  { data: 'supplier.name', title: 'Supplier', style: 'width:12%' },
                  { data: 'brand.name', title: 'Brand', style: 'width:12%' },
                  { data: 'updated_at', title: 'Updated At', style: 'width:15%' },
                  { data: 'actions', title: ' At', style: 'width:5%' },
                ]"
                :query="[
                  { column: 'categories.id', operator: 'equals', value: category.id },
                ]"
                route="/products"
              >
                <template #updated_at="{ value }">
                  {{ format.date(value) }}
                </template>
                <template #created_at="{ value }">
                  {{ format.date(value) }}
                </template>
                <template #actions="{ data, value, index }">
                  <button
                    class="btn px-2 py-0 mx-1 border-0"
                    :id="'datatable-btn-' + index + '-delete'"
                    @click="setProductCategory(data.id, category.id, 'remove')"
                  >
                    <font-awesome-icon :icon="['fas', 'trash-alt']"></font-awesome-icon>
                  </button>
                </template>
              </Datatable>
            </div>
          </div>
        </tab>
      </tabs>
    </div>
  </AuthenticatedLayout>
</template>
