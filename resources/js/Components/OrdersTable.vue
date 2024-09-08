<script setup>
import axios from 'axios';
import { ref } from 'vue'
const props = defineProps({
    modelValue: Object
})
const showFilters = ref(false)
const isLoading = ref(true)

const articles = ref([])
const getArticles = async () => {
    let query = {
        connectivityType: props.modelValue.solution_type,
        postCode: props.modelValue.postCode,
        latitude: props.modelValue.position?.lat,
        longitude: props.modelValue.position?.lon,
    };
    try {
        let res = await axios.post('/api/external/get-articles-from-shop-blocks', query)
        articles.value = res.data
        isLoading.value = false
    } catch (ex) {
        alert(ex.response.data.message)
    }
}

getArticles()

</script>
<template>
    <div class="row mb-4" style="min-height: 50vh;">
        <div class="col">
            <div class="d-flex justify-content-between align-items-center">
                <h2>Live prices</h2>

                <button class="btn btn-primary m-2" @click="() => showFilters = !showFilters">
                    <font-awesome-icon :icon="['fas', 'filter']" />
                </button>
            </div>
            <Loader v-if="isLoading"></Loader>
            <table v-else id="table_products" class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Product <font-awesome-icon :icon="['fas', 'sort']" /></th>
                        <th>Provider <font-awesome-icon :icon="['fas', 'sort']" /></th>
                        <th>Subprovider <font-awesome-icon :icon="['fas', 'sort']" /></th>
                        <th>Bearer <font-awesome-icon :icon="['fas', 'sort']" /> </th>
                        <th>Speed <font-awesome-icon :icon="['fas', 'sort']" /> </th>
                        <th>Set Up <font-awesome-icon :icon="['fas', 'sort']" /> </th>
                        <th>Monthly <font-awesome-icon :icon="['fas', 'sort']" /></th>
                        <th>ECC <font-awesome-icon :icon="['fas', 'sort']" /> </th>
                        <th>Contract Term <font-awesome-icon :icon="['fas', 'sort']" /> </th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="article in articles">
                        <td>{{ article.productName }} </td>
                        <td>{{ article.providerName }} </td>
                        <td>{{ article.connectivitySupplierName }} </td>
                        <td>{{ article.bearer }} </td>
                        <td>{{ article.speed }} </td>
                        <td>{{ article.priceInstall }} </td>
                        <td>{{ article.priceAnnual }} </td>
                        <td>{{ article.priceECC }} </td>
                        <td>{{ article.contractTermMonths }} </td>
                        <td>Add</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <Modal v-model="showFilters">
        <template v-slot:header>
            <p class="m-0">
                <font-awesome-icon :icon="['fas', 'filter']" style="color:#EC008C" /> FILTERS<span
                    style="color:#EC008C">.</span>
            </p>
        </template>
        <div class="container">
            <div class="row">
                <div class="col">
                    <Tabs>
                        <Tab title="Provider" :selected="true">
                            <div class="my-3">
                                <label for="filter-provider" class="form-label">Provider:</label>
                                <select id="filter-provider" class="form-select" multiple>
                                    <option value="its">ITS (0)</option>
                                    <option value="gamma">Gamma (0)</option>
                                    <option value="giacom">Giacom (0)</option>
                                    <option value="virtual1">Virtual1 (0)</option>
                                </select>
                            </div>
                            <div class="my-3">
                                <label for="filter-subprovider" class="form-label">Subprovider:</label>
                                <select id="filter-subprovider" class="form-select" multiple>
                                    <option value='bt'>BT (0)</option>
                                    <option value='cityfibre'>CityFibre (0)</option>
                                    <option value='colt'>Colt (0)</option>
                                    <option value='gamma'>Gamma (0)</option>
                                    <option value='giacom'>Giacom (0)</option>
                                    <option value='glide'>Glide (0)</option>
                                    <option value='its'>ITS (0)</option>
                                    <option value='neos'>Neos Networks (0)</option>
                                    <option value='sky'>Sky (0)</option>
                                    <option value='talktalk'>TalkTalk (0)</option>
                                    <option value='virgin'>Virgin (0)</option>
                                    <option value='virtual1'>Virtual1 (0)</option>
                                    <option value='vodafone'>Vodafone (0)</option>
                                </select>
                            </div>
                        </Tab>
                        <Tab title="Contract">
                            <div class="my-3">
                                <label for="filter-contract" class="form-label">Contract Length:</label>
                                <select id="filter-contract" class="form-select" multiple>
                                    <option value="12">12 months</option>
                                    <option value="24">24 months</option>
                                    <option value="36">36 months</option>
                                    <option value="48">48 months</option>
                                    <option value="60">60 months</option>
                                </select>
                            </div>
                            <div class="my-3">
                                <label for="bearerSlider" class="form-label">Bearer:</label>
                                <input class="form-control" id="bearerSlider" type="text">
                            </div>
                            <div class="my-3">
                                <label for="speedSlider" class="form-label">Speed:</label>
                                <input class="form-control" id="speedSlider" type="text">
                                <div class="mt-2 slider" id=""></div>
                            </div>
                        </Tab>
                    </Tabs>

                    <div class="text-end">
                        <a class="btn btn-secondary">Reset</a>
                    </div>
                </div>
            </div>
        </div>
    </Modal>
</template>