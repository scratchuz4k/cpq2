<script setup>
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import { reactive, ref, watch } from 'vue'
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import InputText from "primevue/inputtext";

const props = defineProps({
    modelValue: Object
})

let data = []
const modelsLimit = ref(15)
const hardwareProducts = ref([])
const searchValue = ref('')

const activeFilters = reactive({
    models: [],
    storage: [],
})

const filters = {
    models: [],
    storage: []
}

const getProducts = async () => {
    let res = await axios.post("/api/external/get-articles-from-shop-blocks")
    data = res.data
    data.forEach(element => {
        let orderItem = props.modelValue.items.find(x => x.data?.name == element.name)
        element.quantity = orderItem ? orderItem.quantity : 0
        element.id = orderItem ? orderItem.id : null
        if (!filters.models.find(x => x == element.model)) {
            filters.models.push(element.model)
        }
    });
    // filterData(data)
    hardwareProducts.value = data
}

const addFilter = (items, item) => {
    let index = items.findIndex(x => x == item)
    if (index == -1) {
        items.push(item)
        return
    }
    items.splice(index, 1)
}

const filterData = (data) => {
    hardwareProducts.value = data.filter((item) => {
        let l = activeFilters.models.length
        return l ? activeFilters.models.reduce((acc, num) => {
            acc = acc || item.model.toLowerCase().includes(num.toLowerCase())
            return acc
        }, false) : true
    })

    hardwareProducts.value = hardwareProducts.value.filter((item) => {
        let l = activeFilters.storage.length
        return l ? activeFilters.storage.reduce((acc, num) => {
            acc = acc || item.name.toLowerCase().includes(num.toLowerCase())
            return acc
        }, false) : true
    })

    hardwareProducts.value = hardwareProducts.value.filter((item) => {
        return item.name.toLowerCase().includes(searchValue.value.toLowerCase())
    })
}

const addQuantity = (product, quantity) => {
    if (!Object.keys(product).includes('quantity')) {
        product.quantity = 0
    }
    let temp = product.quantity + quantity
    if (product.stock <= temp) {
        temp = product.stock
    }
    product.quantity = temp

}

const removeQuantity = (product, quantity) => {
    if (!Object.keys(product).includes('quantity')) {
        product.quantity = 0
        return
    }

    if (!product.stock) {
        return
    }
    product.quantity -= quantity

}

const saveQuote = () => {
    let items = []
    items.push(...hardwareProducts.value.filter(x => x.quantity || x.id))
    try {
        items.forEach(async (article) => {
            let url = article.id ? `/api/order-items/${article.id}` : '/api/order-items';
            let method = article.id ? !article.quantity ? 'delete' : 'put' : 'post';
            await axios[method](url,
                {
                    order_id: props.modelValue.id,
                    quantity: article.quantity,
                    name: article.name,
                    data: article,
                })
        })
        router.get(`/orders/${props.modelValue.id}`)
    } catch (ex) {
        alert(ex.response.data.message)
    }
}


getProducts()

watch(activeFilters, () => {
    filterData(data)
})

watch(searchValue, () => {
    filterData(data)
})

</script>
<template>
    <div class="panel panel-mobile">
        <div class="row">
            <div class="col-lg-9">
                <h2 class="mb-4">Create a quote</h2>
                <InfoBox>
                        <h4>Customer Details</h4>
                        <p class="mb-0">
                            <b>Name:</b> {{ modelValue.account?.name }}<br>

                            <template v-if="modelValue?.address_line1">
                                <b>Address:</b> {{ modelValue?.address_line1 }}<br>
                            </template>
                            <template v-if="modelValue.account?.primary_contact_name">
                                <b>Primary Contact:</b> {{ modelValue.account?.primary_contact_name }}<br>
                            </template>
                            <template v-if="modelValue.account?.email">
                                <b>Email:</b> {{ modelValue.account?.email }}<br>
                            </template>
                        </p>
                </InfoBox>
                <div id="hardware_step" class="step">
                    <div class="mobile-item mobile-add-on mb-3">
                        <div class="d-flex justify-content-between">
                            <h4>Handsets</h4>
                            <div class="input-group w-auto">
                                <input v-model="searchValue" type="text" class="form-control" placeholder="Type to search">
                                <button class="btn btn-primary btn-search" type="button" id="button-addon2">
                                    <font-awesome-icon :icon="['fas', 'magnifying-glass']" />
                                </button>
                            </div>
                            <button class="btn btn-secondary btn-sm btn-clear-filters" type="button" disabled>Clear
                                filters</button>
                        </div>
                        <div class="hardware-labels mb-3">
                            <p class="mb-0">Models</p>
                            <div v-for="i in modelsLimit" class="badge badge-label me-1 mb-1"
                                 :class="[activeFilters.models.find(x => x == filters.models[i]) ? 'active' : 'text-bg-secondary']"
                                 @click="addFilter(activeFilters.models, filters.models[i])">
                                {{ filters.models[i] }}
                            </div>
                            <div class="d-flex mb-1">
                                <div>
                                    <a v-if="modelsLimit > 0" class="me-1" style="font-size: 12px;cursor:pointer"
                                       @click="() => modelsLimit -= 10">Show fewer...</a>
                                    <a v-if="modelsLimit + 1 < filters.models.length" class="me-1"
                                       style="font-size: 12px;cursor:pointer" @click="() => modelsLimit += 10">Show
                                        more...</a>
                                </div>
                            </div>
                        </div>

                        <div class="hardware-filters mb-3">
                            <p class="mb-1">Filters</p>
                            <div class="mobile-item-filter">
                                <label>Storage</label>
                                <div class="badge badge-filter text-bg-dark m-1"
                                     :class="[activeFilters.storage.find(x => x == '1TB') ? 'active' : 'text-bg-secondary']"
                                     @click="addFilter(activeFilters.storage, '1TB')">
                                    1 TB
                                </div>
                                <div class="badge badge-filter text-bg-dark m-1"
                                     :class="[activeFilters.storage.find(x => x == '512GB') ? 'active' : 'text-bg-secondary']"
                                     @click="addFilter(activeFilters.storage, '512GB')">
                                    512 GB
                                </div>
                                <div class="badge badge-filter text-bg-dark m-1"
                                     :class="[activeFilters.storage.find(x => x == '256GB') ? 'active' : 'text-bg-secondary']"
                                     @click="addFilter(activeFilters.storage, '256GB')">
                                    256 GB
                                </div>
                                <div class="badge badge-filter text-bg-dark m-1"
                                     :class="[activeFilters.storage.find(x => x == '128GB') ? 'active' : 'text-bg-secondary']"
                                     @click="addFilter(activeFilters.storage, '128GB')">
                                    128 GB
                                </div>
                                <div class="badge badge-filter text-bg-dark m-1"
                                     :class="[activeFilters.storage.find(x => x == '64GB') ? 'active' : 'text-bg-secondary']"
                                     @click="addFilter(activeFilters.storage, '64GB')">
                                    64 GB
                                </div>
                                <div class="badge badge-filter text-bg-dark m-1"
                                     :class="[activeFilters.storage.find(x => x == '32GB') ? 'active' : 'text-bg-secondary']"
                                     @click="addFilter(activeFilters.storage, '32GB')">
                                    32 GB
                                </div>
                                <div class="badge badge-filter text-bg-dark m-1"
                                     :class="[activeFilters.storage.find(x => x == '16GB') ? 'active' : 'text-bg-secondary']"
                                     @click="addFilter(activeFilters.storage, '16GB')">
                                    16 GB
                                </div>
                            </div>
                        </div>

                        <div class="hardware-products pb-3">
                            <div v-if="!hardwareProducts.length"> No Hardware Found</div>
                            <div v-for="product in hardwareProducts" class="hardware-product">
                                <div class="text-center">
                                    <a :href="product.url" target="_blank">
                                        <img :src="product.primary_image" style="height: 200px;object-fit: contain;">
                                    </a>
                                </div>
                                <b class="label">{{ product.model }}</b>
                                <span>{{ product.sku }}</span>
                                <span class="name mb-1">{{ product.name }}</span>
                                <span class="price mb-2"> £{{ product.price }}</span>
                                <span v-if="product.stock > 0" class="badge text-bg-warning">
                                    {{ product.stock }} in stock
                                </span>
                                <span v-else class="out-of-stock badge text-bg-danger">
                                    <font-awesome-icon :icon="['fa', 'warning']" />
                                    OUT OF STOCK
                                </span>

                                <div class="mobile-product-quantity pt-2">
                                    <label>Quantity</label>
                                    <div class="d-flex align-items-center mb-2">
                                        <button class="btn btn-secondary btn-sm me-1" type="button"
                                                @click="removeQuantity(product, 1)" :disabled="!product.quantity">
                                            <font-awesome-icon :icon="['fas', 'minus']" />
                                        </button>
                                        <input type="number" class="form-control text-center input-qty me-1"
                                               v-model="product.quantity">
                                        <button class="btn btn-secondary btn-sm" type="button"
                                                @click="addQuantity(product, 1)">
                                            <font-awesome-icon :icon="['fas', 'plus']" />
                                        </button>
                                    </div>

                                    <a class="badge text-bg-secondary badge-qty-add" style="text-decoration: none;"
                                       @click="addQuantity(product, 25)">+25</a>
                                    <a class="badge text-bg-secondary badge-qty-add mx-2" style="text-decoration: none;"
                                       @click="addQuantity(product, 50)">+50</a>
                                    <a class="badge text-bg-secondary badge-qty-add" style="text-decoration: none;"
                                       @click="addQuantity(product, 100)">+100</a>
                                </div>
                            </div>
                        </div>
                        <div class="label my-2" v-if="hardwareProducts.length"> Showing {{ hardwareProducts.length }}
                            products</div>
                    </div>

                    <div class="text-end">
                        <div>
                            <span id="total_add_ons">
                                Add <span>{{ hardwareProducts.reduce((acc, x) => acc += !x.quantity ? 0 : x.quantity, 0) }}</span> Hardware
                            </span>
                            <button class="btn btn-primary ms-3" @click="saveQuote()">
                                Next <font-awesome-icon :icon="['fas', 'arrow-right']" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 order-first order-lg-last mb-5">
                <div class="configure-mobile-sidebar px-4 py-4" style="position: sticky; top: 25px">
                    <h4>Add-ons</h4>
                    <div class="sidebar_add_ons mb-3">
                        <div class="item mb-2">
                            <span>Hardware</span><span class="badge bg-light-grey dark">
                                {{ hardwareProducts.reduce((acc, x) => acc += !x.quantity ? 0 : x.quantity, 0) }}
                            </span>
                        </div>
                    </div>

                    <button class="btn btn-primary mt-2" @click="saveQuote()">
                        Save Quote
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
tr.active td {
    background-color: var(--light-teal);
}

.active {
    background-color: var(--highlight-darker) !important
}
</style>
