<script setup>
import MultiSelect from 'primevue/multiselect';
import axios from 'axios';
import { router } from '@inertiajs/vue3';
import { reactive, ref, watch } from 'vue'
import queryString from 'query-string';

const props = defineProps({
    modelValue: Object,
})

let data = []
const modelsLimit = ref(15)

const urlParams = new URLSearchParams(window.location.search)
const params = new Proxy(urlParams, {
    get: (searchParams, prop) => searchParams.get(prop),
})

const step = ref(params["step"] ?? 1)

const products = reactive({
    plan: [],
    bolt_on: [],
})
const productsHardware = ref([])

const getProducts = () => {
    Object.keys(products).forEach(async (key) => {
        let query = queryString.stringify({
            limit: 100,
            filters: JSON.stringify([{
                column: 'categories.code',
                operator: 'equals',
                value: key
            }]),
        })
        let res = await axios.get("/api/products" + '?' + query)
        res.data.data.forEach((e) => {
            let temp = props.modelValue.items.find(x => x.data?.name == e.name)
            e.quantity = temp ? temp.quantity : 0
        });
        products[key] = res.data.data
    })
}


const checkPlansHardwareQuantity = () => {
    let plansQty = products.plan.reduce((acc, x) => acc += x.quantity, 0)
    let boltOnQty = products.bolt_on.reduce((acc, x) => acc += x.quantity, 0)
    let hardwareQty = productsHardware.value.reduce((acc, x) => acc += x.quantity, 0)

    return (plansQty + boltOnQty) - hardwareQty
}

const checkValue = (product) => {
    if (product.quantity < 0) {
        product.quantity = 0
    }
}
const activeFilters = reactive({
    models: [],
    storage: [],
})

const filters = reactive({
    models: [],
    storage: ['1TB', '512 GB', '256 GB', '128 GB', '64 GB', '32 GB', '16 GB'],
})

const getProductsShopBlocks = async () => {
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
    filterData(data)
}

const filterData = (data) => {
    productsHardware.value = data.filter((item) => {
        let l = activeFilters.models.length
        return l ? activeFilters.models.reduce((acc, num) => {
            acc = acc || item.model.toLowerCase().includes(num.toLowerCase())
            return acc
        }, false) : true
    })
    productsHardware.value = productsHardware.value.filter((item) => {
        let l = activeFilters.storage.length
        return l ? activeFilters.storage.reduce((acc, num) => {
            acc = acc || item.name.toLowerCase().includes(num.toLowerCase())
            return acc
        }, false) : true
    })
}

const addQuantity = (product, quantity) => {
    if (!Object.keys(product).includes('quantity')) {
        product.quantity = 0
    }
    let oldQty = product.quantity
    let temp = product.quantity + quantity
    if (product.stock <= temp) {
        temp = product.stock
    }

    product.quantity = temp

    if (checkPlansHardwareQuantity() < 0) {
        swal.fire({
            title: 'Quantity exceeds plans limit',
            subtitle: 'Your hardware quantity exceeds plans limit'
        }).then(async (result) => {
            if (['cancel', 'backdrop'].includes(result.dismiss)) {
                product.quantity = oldQty
                return
            }
            product.quantity += checkPlansHardwareQuantity()
        })
    }
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
    items.push(...products.plan.filter(x => x.quantity))
    items.push(...products.bolt_on.filter(x => x.quantity))
    items.push(...productsHardware.value.filter(x => x.quantity))
    try {
        items.forEach(async (article) => {
            let temp = props.modelValue.items.find(x => x.name == article.name)
            let url = temp ? `/api/order-items/${temp.id}` : '/api/order-items';
            let method = temp ? !article.quantity ? 'delete' : 'put' : 'post';
            await axios[method](url,
                {
                    order_id: props.modelValue.id,
                    quantity: article.quantity,
                    name: article.name,
                    data: article,
                    raw_price: article.price ?? article.prices[0].net,
                    raw_price_monthly: article.priceMonthly ?? !article.prices ? 0 : article.prices[0].net_monthly,
                    price: article.price ?? article.prices[0].net_spa,
                    price_monthly: article.priceMonthly ?? !article.prices ? 0 : article.prices[0].net_monthly_spa
                })
        })
        router.get(`/orders/${props.modelValue.id}`)
    } catch (ex) {
        alert(ex.response.data.message)
    }
}

getProductsShopBlocks()
getProducts()

watch(activeFilters, () => {
    filterData(data)
})

const items = ref([
    { label: 'Home', url: '/' },
    { label: 'Create a quote' },
]);


</script>
<template>
    <div class="panel panel-mobile">
        <Header title="Create a Quote" :breadcrumbs="items"></Header>
        <div class="row justify-content-between">
            <div class="col-lg-7">
                <div v-if="step == 1" id="mobile_step1" class="step">
                    <div class="d-flex justify-content-between mb-3">
                        <h2>Select some products to get started</h2>
                        <p class="bigger bold text-primary">STEP 1/3</p>
                    </div>

                    <div v-for="(product, index) in products.plan" class="mobile-item mobile-product mb-3">
                        <div class="d-flex justify-content-between">
                            <div class="d-flex align-items-center mb-2">
                                <h4 class="m-0">{{ product.name }}</h4>
                                <p class="m-0 ms-2">
                                    {{ product.description }}
                                </p>
                            </div>
                            <font-awesome-icon :icon="['fa', 'circle-info']" />
                        </div>
                        <div class="mobile-product-quantity">
                            <label>Quantity</label>
                            <div class="d-flex align-items-center pb-3">
                                <button :id="'btn-plan-' + index + '-remove-1'" class="btn btn-secondary btn-sm me-1"
                                    type="button" @click="() => product.quantity--" :disabled="product.quantity <= 0">
                                    <font-awesome-icon :icon="['fas', 'minus']" />
                                </button>
                                <input :id="'input-plan-' + index + '-quantity'" type="number"
                                    class="form-control form-control-sm text-center w-25 me-1"
                                    v-model="product.quantity" min="0" @input="checkValue(product)" />
                                <input type="hidden" />
                                <button :id="'btn-plan-' + index + '-add-1'" class=" btn btn-secondary btn-sm me-3"
                                    type="button" @click="() => product.quantity++">
                                    <font-awesome-icon :icon="['fas', 'plus']" />
                                </button>
                                <button :id="'btn-plan-' + index + '-add-25'" type=" button"
                                    class="badge border-0 text-bg-secondary"
                                    @click="() => product.quantity += 25">+25</button>
                                <button :id="'btn-plan-' + index + '-add-50'" type=" button"
                                    class="badge border-0 text-bg-secondary mx-2"
                                    @click="() => product.quantity += 50">+50</button>
                                <button :id="'btn-plan-' + index + '-add-100'" type=" button"
                                    class="badge border-0 text-bg-secondary"
                                    @click="() => product.quantity += 100">+100</button>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end align-items-center">
                        <span id="total_mobile_users">
                            Add {{ products.plan.reduce((acc, item) => acc += item.quantity, 0) }}
                            mobile users
                        </span>
                        <button :id="'btn-step-1-next'" class="ms-3 btn btn-primary step-nav" type="button"
                            @click="() => step++">Next
                            <i class="fa fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
                <div v-if="step == 2" id="mobile_step2" class="step">
                    <div class="d-flex justify-content-between mb-3">
                        <h2>Next, choose your bolt-ons</h2>
                        <p class="m-0 mb-2 text-primary bold">STEP 2/3</p>
                    </div>
                    <div v-for="(product, index) in products.bolt_on" class="mobile-item mobile-product mb-3">
                        <div class="d-flex justify-content-between">
                            <div class="d-flex align-items-center mb-2">
                                <h4 class="m-0 ">{{ product.name }}</h4>
                                <p class="m-0 ms-2">
                                    {{ product.priceMonthly }} £
                                </p>
                            </div>
                            <font-awesome-icon :icon="['fa', 'circle-info']" />
                        </div>
                        <div class="mobile-product-quantity">
                            <label>Quantity</label>
                            <div class="d-flex align-items-center  pb-3">
                                <button :id="'btn-bolt-' + index + '-remove-1'" class="btn btn-secondary btn-sm me-1"
                                    type="button" @click="() => product.quantity--" :disabled="product.quantity <= 0">
                                    <font-awesome-icon :icon="['fas', 'minus']" />
                                </button>
                                <input :id="'input-bolt-' + index + '-quantity'" type="number"
                                    class="form-control form-control-sm text-center w-25 me-1"
                                    v-model="product.quantity" min="0" @input="checkValue(product)" />
                                <input type="hidden" />
                                <button :id="'btn-bolt-' + index + '-add-1'" class="btn btn-secondary btn-sm me-3"
                                    type="button" @click="() => product.quantity++">
                                    <font-awesome-icon :icon="['fas', 'plus']" />
                                </button>
                                <button :id="'btn-bolt-' + index + '-add-25'" type="button"
                                    class="badge border-0 text-bg-secondary"
                                    @click="() => product.quantity += 25">+25</button>
                                <button :id="'btn-bolt-' + index + '-add-50'" type="button"
                                    class="badge border-0 text-bg-secondary mx-2"
                                    @click="() => product.quantity += 50">+50</button>
                                <button :id="'btn-bolt-' + index + '-add-100'" type="button"
                                    class="badge border-0 text-bg-secondary"
                                    @click="() => product.quantity += 100">+100</button>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <button :id="'btn-step-2-previous'" class="btn btn-light step-nav btn-sm me-3" type="button"
                            @click="() => step--">
                            <font-awesome-icon :icon="['fas', 'arrow-left']" /> Back
                        </button>
                        <div>
                            <span id="total_mobile_users">
                                Add {{ products.bolt_on.reduce((acc, item) => acc += item.quantity, 0) }}
                                bolt-ons
                            </span>
                            <button :id="'btn-step-2-next'" class="ms-3 btn btn-primary step-nav" type="button"
                                @click="() => step++">Next
                                <i class="fa fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div v-if="step == 3" id="mobile_step3" class="step">
                    <div class="d-flex align-items-end mb-3">
                        <h2 class="m-0">Finally, choose your add-ons</h2>
                        <p class="m-0 ms-2 text-primary bold">STEP 3/3</p>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <h3>Handsets</h3>
                    </div>
                    <div class="panel mb-3">
                        <div class="d-flex justify-content-between mb-3">
                            <h4>Filters</h4>
                            <button class="btn border-0 p-0 text-primary" type="button">
                                Clear filters
                            </button>
                        </div>
                        <div class="row">
                            <div class="col">
                                <label>Models</label>
                                <MultiSelect v-model="activeFilters.models" :options="filters.models" placeholder="All"
                                    filter class="w-100">
                                </MultiSelect>
                            </div>
                            <div class="col">
                                <label>Storage</label>
                                <MultiSelect v-model="activeFilters.storage" :options="filters.storage"
                                    placeholder="All" filter class="w-100">
                                </MultiSelect>
                            </div>
                            <div class="col">
                                <label>Sort by</label>
                                <MultiSelect v-model="activeFilters.models" :options="filters.models" placeholder="All"
                                    filter class="w-100">
                                </MultiSelect>
                            </div>
                        </div>
                    </div>

                    <div v-if="!productsHardware.length"> No Hardware Found</div>
                    <div v-for="(product, index) in productsHardware" class="panel p-4 mb-3">
                        <div class="row">
                            <div class="col-2">
                                <a :href="product.url" target="_blank">
                                    <img :src="product.primary_image" style="width: 100%;object-fit: contain;">
                                </a>
                            </div>
                            <div class="col-6">
                                <p class="big bold">{{ product.model }}</p>
                                <p style="white-space: nowrap;overflow:hidden;text-overflow: ellipsis;">
                                    {{ product.sku }} - {{ product.name }}</p>
                                <div class="pt-3">
                                    <p style="font-weight: 500;">Quantity</p>
                                    <div class="d-flex align-items-center mb-2">
                                        <button :id="'btn-hardware-' + index + '-remove-1'"
                                            class="btn-quantity btn-minus-quantity" type="button"
                                            @click="removeQuantity(product, 1)" :disabled="!product.quantity">
                                            <font-awesome-icon :icon="['fas', 'minus']" />
                                        </button>
                                        <input :id="'input-hardware-' + index + '-quantity'" type="number"
                                            class="input-quantity" v-model="product.quantity">
                                        <button :id="'btn-hardware-' + index + '-add-1'"
                                            class="btn-quantity btn-add-quantity" type="button"
                                            @click="addQuantity(product, 1)"
                                            :disabled="checkPlansHardwareQuantity() <= 0">
                                            <font-awesome-icon :icon="['fas', 'plus']" />
                                        </button>

                                        <button :id="'btn-hardware-' + index + '-add-25'"
                                            class="btn btn-sm ms-4 btn-add-custom-quantity"
                                            @click="addQuantity(product, 25)"
                                            :disabled="checkPlansHardwareQuantity() <= 0">+25</button>
                                        <button :id="'btn-hardware-' + index + '-add-50'"
                                            class="btn btn-sm ms-2 btn-add-custom-quantity"
                                            @click="addQuantity(product, 50)"
                                            :disabled="checkPlansHardwareQuantity() <= 0">+50</button>
                                        <button :id="'btn-hardware-' + index + '-add-100'"
                                            class="btn btn-sm ms-2 btn-add-custom-quantity"
                                            @click="addQuantity(product, 100)"
                                            :disabled="checkPlansHardwareQuantity() <= 0">+100</button>
                                    </div>
                                </div>
                            </div>

                            <div class="col d-flex justify-content-end">
                                <div class="me-3">
                                    <p v-if="product.stock > 0" class="badge text-bg-warning"
                                        style="--bs-bg-opacity: .5;">
                                        <span :id="'hardware-' + index + '-stock'">
                                            {{ product.stock }}
                                        </span> in stock
                                    </p>
                                    <p v-else class="badge text-bg-danger" style="--bs-bg-opacity: .5;">
                                        <font-awesome-icon :icon="['fa', 'warning']" />
                                        OUT OF STOCK
                                    </p>
                                </div>
                                <div>
                                    <p class="bold big mb-3"> £{{ product.price }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="label my-2" v-if="productsHardware.length">
                        Showing {{ productsHardware.length }} products
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <button id="btn-step-3-previous" class="btn btn-light step-nav btn-sm me-3" type="button"
                            @click="() => step--">
                            <font-awesome-icon :icon="['fas', 'arrow-left']" /> Back
                        </button>
                        <div>
                            <span id="total_add_ons">
                                Add {{ productsHardware.reduce((acc, item) => acc += item.quantity, 0) }}
                                add-ons
                            </span>
                            <button id="btn-step-3-next" class="ms-3 btn btn-primary step-nav" type="button"
                                @click="saveQuote()">Next
                                <i class="fa fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>

            </div>
            <div class="col-lg-3 order-first order-lg-last mb-5">
                <InfoBox>
                    <div style="font-size: 14px;">
                        <b style="font-size: 16px;">Quote {{ modelValue.id }} for {{ modelValue.account.name }}</b>
                        <br>
                        Contact Number: 0124567890<br>
                        Email: firstname.lastname@gmail.com
                    </div>
                </InfoBox>
                <div class="configure-mobile-sidebar px-4 py-4" style="position: sticky; top: 25px">
                    <h4 id="sidebar_products" data-total="0">1. Products</h4>
                    <div class="sidebar_items mb-3">
                        <div v-for="product in products.plan" class="item mb-2">
                            <span>{{ product.name }}</span>
                            <span class="badge bg-light-grey dark">
                                {{ product.quantity }}
                            </span>
                        </div>
                    </div>
                    <h4>2. Bolt-ons</h4>
                    <div class="sidebar_bolt_ons mb-3">
                        <div v-for="bolt in products.bolt_on" class="item mb-2">
                            <span>{{ bolt.name }}</span>
                            <span class="badge bg-light-grey dark">
                                {{ bolt.quantity }}
                            </span>
                        </div>
                    </div>
                    <h4>3. Add-ons</h4>
                    <div class="sidebar_add_ons mb-3">
                        <div class="item mb-2">
                            <span>Hardware</span><span class="badge bg-light-grey dark">
                                {{ productsHardware.reduce((acc, x) => acc += !x.quantity ? 0 : x.quantity, 0) }}
                            </span>
                        </div>
                    </div>
                    <button class="btn btn-primary mt-2" @click="saveQuote()"
                        :disabled="checkPlansHardwareQuantity() < 0">
                        Save Quote
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.input-quantity {
    width: 101px;
    height: 32px;
    padding: 8px 12px 8px 12px;
    gap: 8px;
    border-width: 1px;
    border-style: solid;
    border-color: #BDBDBD;
}

.btn-quantity {
    outline: none;
    border-style: solid;
    border-color: #BDBDBD;
}

.btn-minus-quantity {
    width: 36px;
    height: 32px;
    gap: 0px;
    border-radius: 6px 0px 0px 6px;
    background: #FFFFFF;
    border-width: 1px 0px 1px 1px;
    border-style: solid;
    border-color: #BDBDBD;
}

.btn-add-quantity {
    width: 36px;
    height: 32px;
    gap: 0px;
    border-radius: 0px 6px 6px 0px;
    background: #FFFFFF;
    border-width: 1px 1px 1px 0px;
}

.btn-add-custom-quantity {
    font-size: 12px;
    font-weight: 600;
    border-radius: 20px;
    text-decoration: none;
    background: #F5E5F3;
    color: #A34996;
}
</style>
