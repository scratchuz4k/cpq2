<script setup>
import axios from 'axios';
import { ref, reactive, watch } from 'vue'
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net';
import MultiSelect from 'primevue/multiselect';
import Slider from 'primevue/slider';
import { stringify } from 'postcss';


DataTable.use(DataTablesCore);

const props = defineProps({
    postCode: String,
    modelValue: Array
})

const showFilters = ref(false)
const isLoading = ref(true)
let data = []
const articles = ref([])
const filters = reactive({
    providerName: [],
    connectivitySupplierName: [],
    contractTermMonths: []
})

const filtersRange = reactive({
    bearer: [],
    speed: []
})

const filterList = reactive({
    providerName: [],
    connectivitySupplierName: [],
    contractTermMonths: [],
    bearer: [],
    speed: []
})

const subProviders = {
    "btw": "bt",
    "bt": "bt",
    "cityfibre": "cityfibre",
    "colt": "colt",
    "gamma": "gamma",
    "giacom": "giacom",
    "glide": "glide",
    "its": "its",
    "neos networks": "neos",
    "sky": "sky",
    "talktalk": "talktalk",
    "virgin": "virgin",
    "virtual1": "virtual1",
    "vodafone": "vodafone",
};

const GBP = new Intl.NumberFormat("en-GB", {
    style: "currency",
    currency: "GBP",
});

const getArticles = async () => {
    let query = {
        connectivityType: 3,
        postCode: props.postCode,
    };
    try {
        let res = await axios.post('/api/external/get-articles-from-shop-blocks', query)
        data = res.data
        processFilters(data)
        filterData(data)
    } catch (ex) {
        alert(ex.response.data.message)
    }
}

const filterData = (data) => {
    articles.value = data
    Object.keys(filters).forEach(key => {
        articles.value = articles.value.filter((item) => {
            if (!Object.keys(item).includes(key) || !filters[key] || !filters[key].length) {
                return true
            }
            return filters[key].reduce((acc, num) => {
                let op = true
                if (typeof item[key] == 'string') {
                    op = num ? item[key].toLowerCase().includes(num.toLowerCase()) : true
                }

                if (typeof item[key] == 'number') {
                    op = num ? item[key] == num : true
                }
                acc = acc || op
                return acc
            }, false)

        })
    });

    Object.keys(filtersRange).forEach(key => {
        articles.value = articles.value.filter((item) => {
            if (!Object.keys(item).includes(key) || !filtersRange[key] || !filtersRange[key].length) {
                return true
            }
            return item[key] >= filterList[key][filtersRange[key][0]] && item[key] <= filterList[key][filtersRange[key][1]]
        })

    })
    if (articles.value.length) {
        orderBy(sort.field, sort.order)
    }
    isLoading.value = false
}
const countFilters = (column, value) => {
    let temp = articles.value.filter((item) => {
        return item[column].toLowerCase() == value.toLowerCase()
    })
    return temp.length
}

const processFilters = (data) => {
    data.forEach((item) => {
        Object.keys(filterList).forEach((key) => {
            if (!Object.keys(item).includes(key) || !item[key]) {
                return
            }
            if (filterList[key].find((a) => a == item[key])) {
                return
            }
            filterList[key].push(item[key])
        })
    })
    filterList.bearer = filterList.bearer.sort((a, b) => a - b);
    filterList.speed = filterList.speed.sort((a, b) => a - b);
}

const setAltImg = (event) => {
    event.target.src = '/images/no_image.png'
}

const bytesFormatter = (value) => {
    let temp = Math.ceil(value)
    if ((temp / 1000) >= 1) {
        return Math.ceil((temp / 1000)) + " Gb"
    }
    return temp + " Mb"
}
const sort = reactive({
    field: "id",
    order: "asc"
})
const orderBy = (column, order = null) => {
    let temp = articles.value

    let changeSort = order ? order : sort.order == 'asc' ? 'desc' : 'asc'
    sort.order = sort.field === column ? changeSort : sort.order
    sort.field = column

    if (sort.order == 'asc') {
        temp.sort((a, b) => a[column] - b[column])
    } else {
        temp.sort((a, b) => b[column] - a[column])
    }

    articles.value = temp
}

const add = (article) => {
    props.modelValue.push(article)
}

const remove = (article) => {
    let index = props.modelValue.findIndex((item) => item.quoteId == article.quoteId)
    props.modelValue.splice(index, 1)
}


watch(filters, () => {
    isLoading.value = true
    filterData(data)
})

watch(filtersRange, () => {
    isLoading.value = true
    filterData(data)
})

getArticles()

</script>
<template>
    <div class="row mb-4" style="min-height: 50vh;">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Live prices</h2>

                <div>
                    <button class="btn btn-primary m-2" @click="() => showFilters = !showFilters">
                        <font-awesome-icon :icon="['fas', 'filter']" />
                    </button>
                </div>
            </div>
            <div id="filters" class="fitlers mb-5 pt-3 pb-5 px-3" v-if="showFilters">
                <Tabs>
                    <Tab title="Provider" :selected="true">
                        <div class="row mt-3">
                            <div class="col">
                                <label for="filter-subprovider" class="form-label">Provider:</label>
                                <MultiSelect v-model="filters.providerName" :options="filterList.providerName" filter
                                    placeholder="Select a provider" class="w-100">
                                    <template #option="slotProps">
                                        <div>{{ slotProps.option }} ({{ countFilters("providerName",
                                            slotProps.option) }})</div>
                                    </template>
                                </MultiSelect>
                            </div>
                            <div class="col">
                                <label for="filter-subprovider" class="form-label">Subprovider:</label>
                                <MultiSelect v-model="filters.connectivitySupplierName"
                                    :options="filterList.connectivitySupplierName" filter
                                    placeholder="Select a subprovider" class="w-100">
                                    <template #option="slotProps">
                                        <div>{{ slotProps.option }} ({{ countFilters("connectivitySupplierName",
                                            slotProps.option) }})</div>
                                    </template>
                                </MultiSelect>
                            </div>
                        </div>
                    </Tab>
                    <Tab title="Contract">
                        <div class="row mt-3">
                            <div class="col">
                                <label for="filter-contract" class="form-label">Contract Length:</label>
                                <MultiSelect v-model="filters.contractTermMonths"
                                    :options="filterList.contractTermMonths" filter placeholder="Select months"
                                    class="w-100">
                                    <template #option="slotProps">
                                        <div>{{ slotProps.option }} Months</div>
                                    </template>
                                </MultiSelect>
                            </div>
                            <div class="col px-5">
                                <label for="bearerSlider" class="form-label">Bearer:</label>
                                <Range v-model="filtersRange.bearer" :options="filterList.bearer" />
                            </div>
                            <div class="col px-5">
                                <label for="speedSlider" class="form-label">Speed:</label>
                                <Range v-model="filtersRange.speed" :options="filterList.speed" mode="range" />
                            </div>
                        </div>
                    </Tab>
                </Tabs>
            </div>
            <Loader v-if="isLoading"></Loader>
            <div v-else> ---- MOBILE TABLE ---- </div>          
        </div>
    </div>
</template>

<style scoped>
tr.active td {
    background-color: var(--light-teal);
}
</style>