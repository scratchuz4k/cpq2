<script setup>
import _ from "lodash";
import { quote, setQuote } from "@composables/useQuote";
import axios from "axios";
import { router } from "@inertiajs/vue3";
import { ref } from "vue";
import queryString from "query-string";

const props = defineProps({
  modelValue: Object,
});

const solution_type = props.modelValue.solution_type;
const active_categories = ref([]);
const loading = ref(true);

const getCategoriesWithProducts = async () => {
  loading.value = true;
  let query = queryString.stringify({
    limit: 200,
    filters: JSON.stringify([
      {
        column: "id",
        operator: "in",
        value: solution_type.categories.map((x) => x.id).join(","),
      },
      {
        column: "products.id",
        operator: "not_equals",
        value: "",
      },
      {
        column: "is_active",
        operator: "equals",
        value: 1,
      },
    ]),
  });
  let res = await axios.get("/api/categories?" + query);

  const categories_order = solution_type.categories.map((x) => x.id);

  active_categories.value = res.data.data.sort((a, b) => {
    return categories_order.indexOf(a.id) - categories_order.indexOf(b.id);
  });
  loading.value = false;
};

const urlParams = new URLSearchParams(window.location.search);
const params = new Proxy(urlParams, {
  get: (searchParams, prop) => searchParams.get(prop),
});
const step = ref(params["step"] ?? 0);

setQuote(props.modelValue);

const saveQuote = async () => {
  if (step.value == active_categories.value.length) {
    router.get(`/quotes/${props.modelValue.id}`);
    return;
  }
  step.value++;
};

getCategoriesWithProducts();
</script>
<template>
  <div class="panel panel-mobile">
    <div class="row justify-content-between">
      <div class="col">
        <template v-if="!loading" v-for="(category, index) in active_categories">
          <CategoryProductsView
            :category="category"
            :account="props.modelValue.account"
            v-model="step"
            :index="index"
            :total_categories="active_categories.length + 1"
          >
          </CategoryProductsView>
        </template>

        <ShopBlocksProductsView
          v-if="
            !loading &&
            ((step == active_categories.length && step > 0) ||
              (active_categories.length == 0 && step == 0))
          "
          :step="step"
          :quote="quote"
          v-model="step"
          @save="saveQuote"
        >
        </ShopBlocksProductsView>
      </div>
    </div>
  </div>
</template>
