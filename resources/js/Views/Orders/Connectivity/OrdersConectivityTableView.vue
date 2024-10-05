<script setup>
import axios from 'axios';
import Paginator from 'primevue/paginator';
import { ref, reactive, watch } from 'vue'
import MultiSelect from 'primevue/multiselect';
import Swal from 'sweetalert2';

const props = defineProps({
    modelValue: Object
})

const showQuoted = ref(false)
const showFilters = ref(true)
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
const meta = reactive({
    page: 1,
    total: 0,
    first: 1,
    limit: 20,
    isUpdating: false
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

const getArticles = async () => {
    let query = {
        connectivityType: 3,
        postCode: props.modelValue.address_postcode,
        latitude: props.modelValue.address_position?.lat,
        longitude: props.modelValue.address_position?.lon,
        uprn: props.modelValue.address_data?.uprn,
        street: props.modelValue.address_data?.addressparts?.ThoroughfareName,
        town: props.modelValue.address_city,
        addressKey: props.modelValue.address_data?.value,
        buildingNumber: props.modelValue.address_data?.addressparts?.ThoroughfareNumber,
        page: meta.page,
        perPage: meta.limit
    };
    let res = await axios.post('/api/external/get-articles-from-cloud-market', query)
    if (res.data.wating_task) {
        let timerInterval;
        Swal.fire({
            title: "We're fetching results!",
            html: "I will close in <b></b> milliseconds.",
            timer: 10000,
            timerProgressBar: true,
            didOpen: () => {
                Swal.showLoading();
                const timer = Swal.getPopup().querySelector("b");
                timerInterval = setInterval(() => {
                    timer.textContent = `${Swal.getTimerLeft()}`;
                }, 100);
            },
            willClose: () => {
                clearInterval(timerInterval);
            }
        }).then((result) => {
            /* Read more about handling dismissals below */
            if (result.dismiss === Swal.DismissReason.timer) {
                console.log("I was closed by the timer");
            }
            getArticles()
        });
    }
    data = res.data.data
    meta.first = (meta.page - 1) * meta.limit
    meta.total = res.data.total
    meta.isUpdating = !!res.data.finished_at
    processFilters(data)
    filterData(data)
}

const filterData = (data) => {
    articles.value = data
    articles.value = articles.value.filter(x => x.connectivitySupplierName != null)
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
            return item[key] >= filtersRange[key][0] && item[key] <= filtersRange[key][1]
        })

    })

    if (showQuoted.value) {
        articles.value = articles.value.filter((article) => {
            return props.modelValue.items.find((item) => item.data.quoteId == article.quoteId)
        })
    }

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

const add = async (article) => {
    try {
        let res = await axios.post('/api/order-items',
            {
                order_id: props.modelValue.id,
                product_id: null,
                quantity: 1,
                name: article.productName,
                data: article,
            })
        props.modelValue.items.push(res.data)
    } catch (ex) {
        alert(ex.response.data.message)
    }
}

const remove = async (article) => {
    let item = props.modelValue.items.find((item) => item.data.quoteId == article.quoteId)
    let index = props.modelValue.items.findIndex((item) => item.data.quoteId == article.quoteId)
    try {
        await axios.delete(`/api/order-items/${item.id}`)
        props.modelValue.items.splice(index, 1)
    } catch (ex) {
        alert(ex.response.data.message)
    }
}

const getQuoted = () => {
    showQuoted.value = !showQuoted.value
    filterData(data)
}

const handlePaginator = (ev) => {
    meta.limit = ev.rows
    meta.page = ev.page + 1
    getArticles()
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
    <div class="panel">
        <InfoBox :order="modelValue"></InfoBox>
        <div class="row mb-4" style="min-height: 50vh;">
            <div class="col">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex  align-items-center">
                        <h2 class="me-3">Live prices </h2>
                        <p v-if="!meta.isUpdating" class="badge badge-filter text-bg-danger m-0"> Prices Updating</p>
                        <p v-else class="badge badge-filter text-bg-success m-0"> Prices Updated</p>
                    </div>

                    <div>
                        <button class="btn m-2" :class="[showQuoted ? ' btn-light' : 'btn-secondary']"
                            @click="getQuoted()">
                            Show Quoted
                        </button>
                        <button class="btn m-2" :class="[showFilters ? ' btn-light' : 'btn-primary']"
                            @click="() => showFilters = !showFilters">
                            <font-awesome-icon :icon="['fas', 'filter']" />
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="col">
                        <div id="filters" v-if="showFilters">
                            <p class="me-3">
                                <b>FILTERS</b><br>
                                <span class="prices-total">
                                    ({{ articles.length }})</span>
                            </p>
                            <div>
                                <label for="filter-subprovider" class="form-label">Provider:</label>
                                <MultiSelect v-model="filters.providerName" :options="filterList.providerName"
                                    placeholder="Select a provider" class="me-3 " style="width:150px;" size="small">
                                    <template #option="slotProps">
                                        <div style="font-size:14px;line-height: 1.5rem;">{{ slotProps.option }} ({{
                                            countFilters("providerName",
                                                slotProps.option) }})</div>
                                    </template>
                                </MultiSelect>
                            </div>
                            <div>
                                <label for="filter-subprovider" class="form-label">Subprovider:</label>
                                <MultiSelect v-model="filters.connectivitySupplierName"
                                    :options="filterList.connectivitySupplierName" placeholder="Select a subprovider"
                                    class="me-3 " style="width:150px;">
                                    <template #option="slotProps">
                                        <div style="font-size:14px;line-height: 1.5rem;">{{ slotProps.option }} ({{
                                            countFilters("connectivitySupplierName",
                                                slotProps.option) }})</div>
                                    </template>
                                </MultiSelect>
                            </div>
                            <div>
                                <label for="filter-contract" class="form-label">Contract Length:</label>
                                <MultiSelect v-model="filters.contractTermMonths"
                                    :options="filterList.contractTermMonths" placeholder="Select months" class="me-3 "
                                    style="width:150px;">
                                    <template #option="slotProps">
                                        <div style="font-size:14px;line-height: 1.5rem;">{{ slotProps.option }} Months
                                        </div>
                                    </template>
                                </MultiSelect>
                            </div>
                            <div class="d-flex slider-wrapper me-5">
                                <label for="bearerSlider" class="form-label">Bearer:</label>
                                <BearerRange v-model="filtersRange.bearer" :options="filterList.bearer" />
                            </div>
                            <div class="d-flex slider-wrapper me-5">
                                <label for="speedSlider" class="form-label">Speed:</label>
                                <SpeedRange v-model="filtersRange.speed" :options="filterList.speed" />
                            </div>
                        </div>
                    </div>
                </div>
                <Loader v-if="isLoading"></Loader>
                <table v-else id="table_products" class="table  table-bordered table-hover">
                    <thead>
                        <tr>
                            <th></th>
                            <th style="white-space: nowrap;" :style="{
                                textDecoration: sort.field == 'productName' ? 'underline' : 'none',
                                width: '30%'
                            }">
                                Product
                                <font-awesome-icon :icon="['fas', 'sort']" />
                            </th>
                            <th style="white-space: nowrap;">
                                Provider
                                <font-awesome-icon :icon="['fas', 'sort']" />
                            </th>
                            <th style="white-space: nowrap;">
                                Subprovider
                                <font-awesome-icon :icon="['fas', 'sort']"></font-awesome-icon>
                            </th>
                            <th style="white-space: nowrap;" :style="{
                                textDecoration: sort.field == 'bearer' ? 'underline' : 'none'
                            }">
                                Bearer
                                <font-awesome-icon :icon="['fas', 'sort']" />
                            </th>
                            <th style="white-space: nowrap;" :style="{
                                textDecoration: sort.field == 'speed' ? 'underline' : 'none'
                            }">
                                Speed
                                <font-awesome-icon :icon="['fas', 'sort']" />
                            </th>
                            <th style="white-space: nowrap;" :style="{
                                textDecoration: sort.field == 'priceInstall' ? 'underline' : 'none'
                            }">
                                Set Up
                                <font-awesome-icon :icon="['fas', 'sort']" />
                            </th>
                            <th style="white-space: nowrap;" :style="{
                                textDecoration: sort.field == 'priceMonthly' ? 'underline' : 'none'
                            }">
                                Monthly
                                <font-awesome-icon :icon="['fas', 'sort']" />
                            </th>
                            <th style="white-space: nowrap;">
                                ECC
                                <font-awesome-icon :icon="['fas', 'sort']" />
                            </th>
                            <th style="white-space: nowrap;">
                                Contract Term
                                <font-awesome-icon :icon="['fas', 'sort']" />
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="article in articles"
                            :class="{ 'active': props.modelValue.items.find((item) => item.data.quoteId == article.quoteId) }"
                            style="text-align: center;">
                            <td>
                                <button
                                    v-if="!props.modelValue.items.find((item) => item.data.quoteId == article.quoteId)"
                                    type="button" class="btn btn-primary btn-sm" @click="add(article)">
                                    <font-awesome-icon :icon="['fas', 'add']" />
                                </button>
                                <button v-else type="button" class="btn btn-secondary btn-sm" @click="remove(article)">
                                    <font-awesome-icon :icon="['fas', 'minus']" />
                                </button>
                            </td>
                            <td style="text-align: left;">{{ article.productName }} </td>
                            <td><img class="img-provider" @error="setAltImg"
                                    :src="`/images/providers/${article.providerKey}.png`" :alt="article.providerName"
                                    :title="article.providerName" /> </td>
                            <td>
                                <img class="img-subprovider" @error="setAltImg"
                                    :src="`/images/providers/${subProviders[article.connectivitySupplierName?.toLowerCase()]}.png`"
                                    :alt="article.connectivitySupplierName" :title="article.connectivitySupplierName" />
                            </td>
                            <td>{{ bytesFormatter(article.bearer) }} </td>
                            <td>{{ bytesFormatter(article.speed) }} </td>
                            <td>{{ GBP.format(article.priceInstall) }} </td>
                            <td>{{ GBP.format(article.priceMonthly) }} </td>
                            <td>{{ GBP.format(article.priceECC) }} </td>
                            <td>{{ article.contractTermMonths }} Months </td>
                        </tr>
                    </tbody>
                </table>
                <Paginator v-model:first="meta.first" :rows="meta.limit" :totalRecords="meta.total"
                    :rowsPerPageOptions="[20, 30, 50]" @page="handlePaginator">
                </Paginator>
            </div>
        </div>
    </div>
</template>

<style scoped>
tr.active td {
    background-color: var(--light-teal);
}
</style>
