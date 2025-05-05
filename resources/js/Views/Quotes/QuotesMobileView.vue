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



const solution_type = props.modelValue.solution_type
const active_categories = ref([]);
const loading = ref(true);
const urlParams = new URLSearchParams(window.location.search);
const params = new Proxy(urlParams, {
    get: (searchParams, prop) => searchParams.get(prop),
});

const last_step = ref(0)
const step = ref(params["step"] === 'last' ? last_step : 0);


const getCategoriesWithProducts = async () => {
    loading.value = true;
    let query = queryString.stringify({
        limit: 200,
        filters: JSON.stringify([
            {
                column: "id",
                operator: "in",
                value: solution_type.categories.map((x) => x.id).join(",")
            },
            {
                column: "products.id",
                operator: "not_equals",
                value: "",
            },
            {
                column: "is_active",
                operator: "equals",
                value: 1
            },
        ]),
    });
    let res = await axios.get("/api/categories" + "?" + query);

    const categories_order = solution_type.categories.map((x) => x.id);

    active_categories.value = res.data.data.sort((a, b) => {
        return categories_order.indexOf(a.id) - categories_order.indexOf(b.id);
    });

    last_step.value = active_categories.value.length;
    loading.value = false;
};

setQuote(props.modelValue);

const saveQuote = async () => {
    if (step.value == active_categories.value.length) {
        router.get(`/quotes/${props.modelValue.id}`);
        return;
    }
    step.value++;
};

const getHardwareProductsCount = () => {
    let items = [...quote.items];
    return items
        .filter((x) => !x.product_id)
        .reduce((acc, i) => (acc += i.quantity), 0);
};

getCategoriesWithProducts();

</script>
<template>
    <div class="panel panel-mobile">
        <div class="row justify-content-between">
            <div class="col-lg-7">
                <div v-if="!loading">
                    <div v-for="(category, index) in active_categories">
                        <CategoryProductsView :category="category" :account="props.modelValue.account" v-model="step"
                            :index="index" :total_categories="active_categories.length + 1">
                        </CategoryProductsView>
                    </div>


                    <HandsetProductsView
                        v-if="(step == active_categories.length && step > 0 || active_categories.length == 0 && step == 0)"
                        :step="step" :quote="quote" v-model="step" @save="saveQuote">
                    </HandsetProductsView>
                </div>
            </div>
            <div class="col-lg-4 order-first order-lg-last mb-5">
                <div style="
            position: sticky;
            top: 25px;
            max-width: 82%;
            margin-right: 0;
            margin-left: auto;
          ">
                    <InfoBox>
                        <div style="font-size: 14px">
                            <b style="font-size: 16px">Quote {{ modelValue.id }} for {{ modelValue.account.name }}</b>
                            <br />
                            Contact Number: 0124567890<br />
                            Email: firstname.lastname@gmail.com
                        </div>
                    </InfoBox>
                    <div class="configure-mobile-sidebar px-4 py-4">
                        <div v-for="(category, index) in active_categories">
                            <h4 id="sidebar_products" data-total="0">{{ index + 1 }}. {{ category.name }}</h4>
                            <div class="sidebar_items mb-3">
                                <div v-for="product in category.products" class="item mb-2">
                                    <span>{{ product.name }}</span>
                                    <span class="badge bg-light-grey dark">
                                        {{quote.items.find((x) => x.product_id == product.id)?.quantity ?? 0}}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <h4>{{ active_categories.length + 1 }}. Add-ons</h4>
                        <div class="sidebar_add_ons mb-3">
                            <div class="item mb-2">
                                <span>Hardware</span><span class="badge bg-light-grey dark">
                                    {{ getHardwareProductsCount() }}
                                </span>
                            </div>
                        </div>
                        <button class="btn btn-primary mt-2" @click="saveQuote()">Next</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style></style>
