<template>
    <div class="filters rounded bg-white p-3 mb-3">
        <div class="row mb-3">
            <div class="col-3">
                <div class="mt-2">
                    <p class="bold">Categories</p>
                    <ShopBlocksCategories v-model="proxyValue.categories"></ShopBlocksCategories>
                </div>
            </div>
            <div class="col-3">
                <div class="mt-2">
                    <p class="bold">Brands</p>
                    <ShopBlocksBrands v-model="proxyValue.brands" :category="currentCategory"></ShopBlocksBrands>
                </div>
            </div>
            <div class="col-3">
                <div class="mt-2">
                    <p class="bold">Models</p>
                    <ShopBlocksModels v-model="proxyValue.models" :category="currentCategory"></ShopBlocksModels>
                </div>
            </div>
            <div class="col-3">
                <div class="mt-2">
                    <p class="bold">Storage</p>
                    <ShopBlocksStorages v-model="proxyValue.storage" :category="currentCategory"></ShopBlocksStorages>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <ul class="d-flex list-unstyled p-0 m-0">
                    <li>
                        <Badge class="mb-1" v-for="(brand, index) in currentBrand" :value="brand.name">
                            <template #close-button>
                                <font-awesome-icon
                                    :icon="['fas', 'x']"
                                    role="button"
                                    class="me-1 fs-7"
                                    @click="() => currentBrand.splice(index, 1)"
                                />
                            </template>
                        </Badge>
                        <Badge class="mb-1" v-for="(model, index) in currentModel" :value="model.name">
                            <template #close-button>
                                <font-awesome-icon
                                    :icon="['fas', 'x']"
                                    role="button"
                                    class="me-1 fs-7"
                                    @click="() => currentModel.splice(index, 1)"
                                />
                            </template>
                        </Badge>
                        <Badge class="mb-1" v-for="(storage, index) in currentStorage" :value="storage.value">
                            <template #close-button>
                                <font-awesome-icon
                                    :icon="['fas', 'x']"
                                    role="button"
                                    class="me-1 fs-7"
                                    @click="() => currentStorage.splice(index, 1)"
                                />
                            </template>
                        </Badge>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
<script setup>
import { computed, reactive } from 'vue';

const props = defineProps({
    modelValue: Object
});

const proxyValue = computed({
    get: () => props.modelValue,
    set: (value) => emit('update:modelValue', value)
});

const filterList = reactive({
    models: [],
    storage: [
        { label: '1TB', value: '1TB' },
        { label: '512 GB', value: '512 GB' },
        { label: '256 GB', value: '256 GB' },
        { label: '128 GB', value: '128 GB' },
        { label: '64 GB', value: '64 GB' },
        { label: '32 GB', value: '32 GB' },
        { label: '16 GB', value: '16 GB' }
    ]
});
</script>
