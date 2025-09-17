<template>
    <InfoConnectivity></InfoConnectivity>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <p class="fs-4 me-3 mb-0">
                <b>{{ getPrimary() ? steps[getPrimary().data?.providerKey][currentStep].title : 'Select Product' }}</b>
            </p>
            <p class="mb-0 text-primary bold fs-5 pt-1">STEP {{ currentStep + 1 }}/{{ connectivitySteps.length }}</p>
        </div>
        <div class="d-flex justify-content-end align-items-center">
            <div>
                <button v-if="currentStep != 0" class="btn continue-btn" type="button" @click="checkGoPrevious()">
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
                <button
                    v-else
                    class="btn btn-primary continue-btn"
                    type="button"
                    @click="checkGoNext()"
                    :disabled="isLoading"
                >
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
                    <p>{{ article.name }}</p>
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
                    <button type="button " class="btn p-0 mt-2" data-mdb-ripple-init @click="removeItem(article)">
                        Remove
                    </button>
                </div>
            </div>
        </div>
    </div>
    <component
        :is="connectivitySteps[currentStep]"
        v-bind="{
            modelValue: props.modelValue,
            needsAddons: needsAddons,
            currentStep: currentStep
        }"
    ></component>
</template>
<script setup>
import { ref, watch } from 'vue';
import { quote } from '@/Composables/useQuote';
import { remove, addons, isLoading, checks } from '@/Composables/useConnectivityItem';

const props = defineProps({
    modelValue: Object
});

const needsAddons = ref(false);
const result = ref([]);
const currentStep = ref(0);
const connectivitySteps = ref(['QuotesConnectivityTable']);

const setAltImg = (event) => {
    event.target.src = '/images/no_image.png';
};

const removeItem = (article) => {
    remove(article);
    if (article.data?.type == 'Primary Product') {
        currentStep.value = 0;
    }
};

const steps = {
    pxc: [
        {
            title: 'Select a Primary Product',
            component: 'QuotesConnectivityTable',
            needAddons: true
        },
        {
            title: 'Failover Service',
            component: 'QuoteConnectivityFailOverTable',
            needAddons: true
        },
        {
            title: 'Addons',
            component: 'QuoteConnectivityExtrasTable',
            needAddons: false
        }
    ],
    its: [
        {
            title: 'Select a Primary Product',
            component: 'QuotesConnectivityTable',
            needAddons: true
        },
        {
            title: 'Failover Service',
            component: 'QuoteConnectivityFailOverTable',
            needAddons: true
        },
        {
            title: 'Addons',
            component: 'QuoteConnectivityExtrasTable',
            needAddons: false
        }
    ]
};

const getPrimary = () => {
    if (!quote.items) {
        return null;
    }
    return quote.items.find((x) => x.data.type == 'Primary Product');
};

const getComponent = () => {
    if (!quote.items) {
        connectivitySteps.value = ['QuotesConnectivityTable'];
    }
    let item = getPrimary();

    if (item && Object.keys(steps).includes(item.data?.providerKey)) {
        connectivitySteps.value = steps[item.data?.providerKey].map((x) => x.component);
    } else {
        connectivitySteps.value = ['QuotesConnectivityTable'];
    }
};

const checkAddons = () => {
    if (!quote.items) {
        return;
    }
    let item = getPrimary();

    if (item && Object.keys(steps).includes(item.data?.providerKey)) {
        needsAddons.value = steps[item.data?.providerKey][currentStep.value].needAddons;
    } else {
        needsAddons.value = false;
    }
};

const getbyGroup = () => {
    if (!quote.items) {
        return;
    }
    const seenGroups = new Set();
    result.value = [];
    quote.items
        .filter((x) => ['Primary Product', 'Failover Product'].includes(x.data?.type))
        .forEach((item) => {
            if (!seenGroups.has(item.group)) {
                seenGroups.add(item.group);
                result.value.push(item);
            }
        });
};

const checkGoPrevious = () => {
    currentStep.value--;
    checkAddons();
};

const checkGoNext = () => {
    let items = quote.items.filter((x) => x.group == 1);
    let check = currentStep.value == 0 ? checks.primaryHardwareRequired : checks.failoverHardwareRequired;
    if (check && !items.find((x) => x.data.type == 'Primary Hardware Product') && addons.value.length != 0) {
        scrollToDiv();
    } else {
        currentStep.value++;
    }
    checkAddons();
};

const scrollToDiv = () => {
    const el = document.getElementById('pcx-its-hardware');
    el.scrollIntoView({ behavior: 'smooth' });
    el.classList.add('blink');

    // Stop blinking after 'duration' ms
    setTimeout(() => {
        el.classList.remove('blink');
    }, 1500);
};

watch(
    () => [quote.items],
    () => {
        getComponent();
        checkAddons();
        getbyGroup();
    }
);

getComponent();
checkAddons();
getbyGroup();
</script>
