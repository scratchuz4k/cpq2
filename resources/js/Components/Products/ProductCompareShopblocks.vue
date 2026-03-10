<template>
    <div
        :style="{ height: show ? '100vh' : '80px', bottom: show ? '0' : '70px' }"
        style="
            position: sticky;
            left: 0;
            right: 0;
            background: white;
            z-index: 500;
            transition: height 0.5s ease;
            border-top: 2px solid var(--color-purple);
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
        "
    >
        <div class="container" style="height: 80px; cursor: pointer">
            <div class="row h-100">
                <div class="w-20" @click="() => (show = !show)">
                    <div class="d-flex align-items-center justify-content-start h-100">
                        <font-awesome-icon
                            v-if="!show"
                            style="font-size: 24px; color: var(--color-purple)"
                            :icon="['fas', 'chevron-up']"
                        />
                        <font-awesome-icon
                            v-else
                            style="font-size: 24px; color: var(--color-purple)"
                            :icon="['fas', 'chevron-down']"
                        />
                        <p class="big bold ms-2" style="color: var(--color-purple)">
                            {{ show ? 'Close' : 'Open' }}
                        </p>
                    </div>
                </div>
                <div v-for="(item, index) in selectedItems" class="w-20">
                    <div class="d-flex align-items-center justify-content-between h-100">
                        <div class="ms-2">
                            <img
                                :src="item.primary_image ?? '/images/no_image.png'"
                                style="object-fit: contain"
                                :style="{
                                    width: '40px',
                                    height: '40px'
                                }"
                            />
                        </div>
                        <div class="w-100">
                            <p class="bold truncate-1-line">
                                {{ item.name }}
                            </p>
                            <p class="truncate-1-line">{{ item.sku }}</p>
                        </div>
                        <div class="me-2">
                            <button @click="() => removeCompareItem(index)" class="btn">
                                <font-awesome-icon :icon="['fas', 'trash-alt']" @click="" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div
            v-if="show"
            class="container product-info"
            style="overflow-y: scroll; overflow-x: hidden; height: calc(100% - 80px)"
        >
            <div class="panel h-100">
                <h3 class="mb-3">General</h3>
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th>Sku</th>
                            <td>
                                <template v-if="selectedItems.length >= 1">
                                    {{ selectedItems[0].sku }}
                                </template>
                            </td>
                            <td>
                                <template v-if="selectedItems.length >= 2">
                                    {{ selectedItems[1].sku }}
                                </template>
                            </td>
                            <td>
                                <template v-if="selectedItems.length >= 3">
                                    {{ selectedItems[2].sku }}
                                </template>
                            </td>
                            <td>
                                <template v-if="selectedItems.length >= 4">
                                    {{ selectedItems[3].sku }}
                                </template>
                            </td>
                        </tr>
                        <tr>
                            <th>Name</th>
                            <td>
                                <template v-if="selectedItems.length >= 1">
                                    {{ selectedItems[0].name }}
                                </template>
                            </td>
                            <td>
                                <template v-if="selectedItems.length >= 2">
                                    {{ selectedItems[1].name }}
                                </template>
                            </td>
                            <td>
                                <template v-if="selectedItems.length >= 3">
                                    {{ selectedItems[2].name }}
                                </template>
                            </td>
                            <td>
                                <template v-if="selectedItems.length >= 4">
                                    {{ selectedItems[3].name }}
                                </template>
                            </td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td>
                                <div
                                    class="truncate-3-lines"
                                    v-if="selectedItems.length >= 1"
                                    v-html="selectedItems[0].description"
                                ></div>
                            </td>
                            <td>
                                <div
                                    class="truncate-3-lines"
                                    v-if="selectedItems.length >= 2"
                                    v-html="selectedItems[1].description"
                                ></div>
                            </td>
                            <td>
                                <div
                                    class="truncate-3-lines"
                                    v-if="selectedItems.length >= 3"
                                    v-html="selectedItems[2].description"
                                ></div>
                            </td>
                            <td>
                                <div
                                    class="truncate-3-lines"
                                    v-if="selectedItems.length >= 4"
                                    v-html="selectedItems[3].description"
                                ></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div class="d-flex justify-content-between align-items-end mt-5 py-3">
                    <h3>Features</h3>
                    <div v-if="temp.length">
                        <IconField>
                            <InputIcon>
                                <font-awesome-icon icon="fas fa-search" />
                            </InputIcon>
                            <InputText
                                id="'select-compare-search'"
                                v-model="searchInput"
                                placeholder="Search"
                                type="search"
                            />
                        </IconField>
                    </div>
                </div>
                <table v-if="temp.length" class="table table-bordered">
                    <tbody>
                        <template v-for="group in temp">
                            <tr>
                                <th colspan="5" style="background: whitesmoke">
                                    <div @click="() => (group.show = !group.show)" style="cursor: pointer">
                                        <div class="d-flex justify-content-between w-100">
                                            <p>{{ group.name }}</p>
                                            <div class="px-2">
                                                <font-awesome-icon
                                                    class="w-100"
                                                    v-if="group.show"
                                                    style="color: var(--color-purple)"
                                                    :icon="['fas', 'chevron-down']"
                                                />
                                                <font-awesome-icon
                                                    class="w-100"
                                                    v-else
                                                    style="color: var(--color-purple)"
                                                    :icon="['fas', 'chevron-up']"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </th>
                            </tr>
                            <tr v-if="group.show" v-for="row in group.features">
                                <th>{{ row.label }}</th>
                                <td>{{ row.items[0] }}</td>
                                <td>{{ row.items[1] }}</td>
                                <td>{{ row.items[2] }}</td>
                                <td>{{ row.items[3] }}</td>
                            </tr>
                        </template>
                    </tbody>
                </table>
                <div v-else>No Features Available</div>
                <h3 class="mb-3 mt-5">Prices</h3>
                <table class="table table-bordered">
                    <tbody>
                        <tr>
                            <th>Stock</th>
                            <td>
                                <template v-if="selectedItems.length >= 1">
                                    {{ selectedItems[0].stock }}
                                </template>
                            </td>
                            <td>
                                <template v-if="selectedItems.length >= 2">
                                    {{ selectedItems[1].stock }}
                                </template>
                            </td>
                            <td>
                                <template v-if="selectedItems.length >= 3">
                                    {{ selectedItems[2].stock }}
                                </template>
                            </td>
                            <td>
                                <template v-if="selectedItems.length >= 4">
                                    {{ selectedItems[3].stock }}
                                </template>
                            </td>
                        </tr>
                        <tr>
                            <th>Net</th>
                            <td>
                                <template v-if="selectedItems.length >= 1">
                                    {{ format.GBP(selectedItems[0].price.net_spa) }}</template
                                >
                            </td>
                            <td>
                                <template v-if="selectedItems.length >= 2">
                                    {{ format.GBP(selectedItems[1].price.net_spa) }}
                                </template>
                            </td>
                            <td>
                                <template v-if="selectedItems.length >= 3">
                                    {{ format.GBP(selectedItems[2].price.net_spa) }}</template
                                >
                            </td>
                            <td>
                                <template v-if="selectedItems.length >= 4">
                                    {{ format.GBP(selectedItems[3].price.net_spa) }}</template
                                >
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
<script setup>
import queryString from 'qs';
import { computed, ref, watch } from 'vue';
import _ from 'lodash';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import { selectedItems, addSelected } from '@/Composables/useComparator';

const props = defineProps({
    index: Number
});

const loadingFeatures = ref(false);
const searchInput = ref('');
const show = ref(false);

const temp = ref([]);
const getFeaturesTable = () => {
    temp.value = [];
    selectedItems.value.forEach((x, i) => {
        if (!x.features) {
            return;
        }
        let lowerSearch = searchInput.value.toLowerCase();
        let features = x.features.filter((obj) => {
            return (
                obj.dimension.value.toLowerCase().includes(lowerSearch) ||
                obj.dimension_type.label.toLowerCase().includes(lowerSearch)
            );
        });

        features.forEach((e) => {
            let gIndex = temp.value.findIndex((t) => t.name == e.dimension_type.group);
            if (gIndex == -1) {
                temp.value.push({ name: e.dimension_type.group, show: true, features: [] });
                gIndex = temp.value.length - 1;
            }

            let index = temp.value[gIndex].features.findIndex((t) => t.key == e.dimension_type.key);
            if (index == -1) {
                temp.value[gIndex].features.push({
                    key: e.dimension_type.key,
                    label: e.dimension_type.label,
                    items: {}
                });
                index = temp.value[gIndex].features.length - 1;
            }
            temp.value[gIndex].features[index].items[i] = e.dimension.value;
        });
    });
};

const getFeatures = () => {
    loadingFeatures.value = true;
    if (!selectedItems.value.length) {
        loadingFeatures.value = false;
        return;
    }
    selectedItems.value.forEach(async (x) => {
        if (x.features) {
            return;
        }
        let query = queryString.stringify({
            filters: [
                {
                    column: 'product_id',
                    operator: 'equals',
                    value: x.id
                }
            ],
            limit: 1000
        });

        let res = await axios.get('/api/shop-blocks/product-dimensions?' + query);
        x.features = res.data?.data;
    });
    loadingFeatures.value = false;
    getFeaturesTable();
};

const removeCompareItem = (index) => {
    selectedItems.value.splice(index, 1);
    if (selectedItems.value.length === 0) {
        document.body.style.overflow = 'auto';
    }
};

watch(searchInput, () => {
    getFeaturesTable();
});

watch(selectedItems, () => {
    if (!selectedItems.length) {
        return;
    }
    document.body.style.overflow = show.value ? 'hidden' : 'auto';
    getFeatures();
});

watch(show, () => {
    document.body.style.overflow = show.value ? 'hidden' : 'auto';
});
getFeatures();
</script>
<style scoped>
.table tbody th {
    width: 20%;
}
.table tbody td {
    width: 20%;
}

.w-20 {
    flex: 0 0 auto;
    width: 20%;
}

.truncate-1-line {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.5em; /* or a fixed px value, e.g. 24px */
    height: calc(1.5em * 1); /* 3 lines × line-height */
}
</style>
