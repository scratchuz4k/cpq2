<template>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <p v-if="currentStep == 0" class="fs-4 me-3 mb-0"><b>Select a Product</b></p>
            <p v-if="currentStep == 1" class="fs-4 me-3 mb-0"><b>Failover Service</b></p>
            <p v-if="currentStep == 2" class="fs-4 me-3 mb-0"><b>Addons</b></p>
            <p class="mb-0 text-primary bold fs-5 pt-1">STEP {{ currentStep + 1 }}/{{ connectivitySteps.length }}</p>
        </div>
        <div class="d-flex justify-content-end align-items-center">
            <div>
                <button v-if="currentStep != 0" class="btn continue-btn" type="button" @click="currentStep--">
                    <font-awesome-icon :icon="['fas', 'arrow-left']" class="ms-2"></font-awesome-icon>
                    Previous Step
                </button>
            </div>
            <div>
                <a
                    v-if="connectivitySteps.length == currentStep + 1"
                    class="btn btn-primary continue-btn"
                    type="submit"
                    :href="route('quotes.show', { quote: modelValue.id })"
                >
                    Continue
                    <font-awesome-icon :icon="['fas', 'arrow-right']" class="ms-2"></font-awesome-icon>
                </a>
                <button v-else class="btn btn-primary continue-btn" type="button" @click="currentStep++">
                    Next Step
                    <font-awesome-icon :icon="['fas', 'arrow-right']" class="ms-2"></font-awesome-icon>
                </button>
            </div>
        </div>
    </div>

    <div class="row mb-4" v-if="result.length">
        <div class="col-3" v-for="article in result">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ article.data.type }}</h5>
                    <p class="card-text">
                        {{ article.name }}
                    </p>
                    <div class="d-flex my-2 items-center">
                        <img
                            class="img-provider me-3"
                            @error="setAltImg"
                            :src="`/images/providers/${format.toLowerCase(article.data.providerKey)}.png`"
                            :alt="article.data.providerName"
                            :title="article.data.providerName"
                            :data-bs-original-title="article.data.providerName"
                            v-tooltip
                        /><img
                            class="img-subprovider"
                            @error="setAltImg"
                            :src="`/images/providers/${format.toLowerCase(article.data.connectivitySupplierGroup)}.png`"
                            :alt="article.data.connectivitySupplierName"
                            :title="article.data.connectivitySupplierName"
                            :data-bs-original-title="article.data.connectivitySupplierName"
                            v-tooltip
                        />
                    </div>
                    <button type="button " class="btn p-0 mt-2" data-mdb-ripple-init @click="remove(article)">
                        Remove
                    </button>
                </div>
            </div>
        </div>
    </div>
    <component
        :is="connectivitySteps[currentStep]"
        v-bind="{
            modelValue: props.modelValue
        }"
    ></component>
</template>
<script setup>
import { ref, watch } from 'vue';
import { quote } from '@/Composables/useQuote';
import { remove, add } from '@/Composables/useConnectivityItem';

const props = defineProps({
    modelValue: Object
});

const result = ref([]);
const currentStep = ref(0);
const connectivitySteps = ref(['QuotesConnectivityTable']);

const setAltImg = (event) => {
    event.target.src = '/images/no_image.png';
};

const calculateSteps = () => {
    if (!quote.items) {
        return;
    }
    if (quote.items.some((x) => ['pxc', 'its'].includes(x.data?.providerKey))) {
        connectivitySteps.value = [
            'QuotesConnectivityTable',
            'QuoteConnectivityFailOverTable',
            'QuoteConnectivityAddonsTable'
        ];
    } else {
        connectivitySteps.value = ['QuotesConnectivityTable'];
    }
};

const getbyGroup = () => {
    if (!quote.items) {
        return;
    }
    const seenGroups = new Set();
    result.value = [];
    quote.items.forEach((item) => {
        if (!seenGroups.has(item.group)) {
            seenGroups.add(item.group);
            result.value.push(item);
        }
    });
};

watch(
    () => [quote.items],
    () => {
        calculateSteps();
        getbyGroup();
    }
);

calculateSteps();
getbyGroup();
</script>
