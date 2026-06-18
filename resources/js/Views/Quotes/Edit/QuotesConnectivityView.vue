<template>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <p class="fs-4 me-3 mb-0">
                <b>{{ getTitle() }}</b>
            </p>
            <p class="mb-0 text-primary bold fs-5 pt-1">STEP {{ currentStep + 1 }}/{{ connectivitySteps.length }}</p>
        </div>
    </div>

    <div class="row mb-4" v-if="result.length">
        <div :class="[showConditionalContent ? 'col-3' : 'col-12']">
            <div class="row">
                <div :class="[showConditionalContent ? 'col-12' : 'col-3']" v-for="(article, index) in result">
                    <div :id="'connectivity-product-' + (index + 1)" class="card">
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
                            <div class="text-end">
                                <button
                                    type="button "
                                    class="btn btn-primary btn-sm btn-secondary mt-2"
                                    data-mdb-ripple-init
                                    @click="removeItem(article)"
                                >
                                    Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div v-if="showConditionalContent" class="col-8 offset-1">
            <div class="d-flex">
                <font-awesome-icon :icon="['fas', 'circle-check']" size="lg" class="me-2 text-primary my-auto" />
                <h5 class="m-0">RESILIENCE (Broadband Backup)</h5>
            </div>
            <div>
                <small>
                    A fully managed and automated backup service for your primary FibreBright or Leased Line connection
                    over a Broadband connection (SoGEA or FTTP).
                    <i class="bold text-primary">
                        Prior to order, availability of the broadband service must be checked by
                        <a href="https://www.zen.co.uk/business" target="_blank"> clicking here </a>
                        or
                        <a href="https://www.zen.co.uk/business" target="_blank"> https://www.zen.co.uk/business </a>
                    </i>
                    <br />
                    If Business Pro, Business Essentials, Unlimited Fibre 1 or Unlimited Fibre 2 are the options
                    returned, then SoGEA Broadband Backup is available for ordering. If the following "Full Fibre, 100,
                    300, 500 and 900" are shown as available on the Zen portal then ITS Broadband FTTP Back up service
                    will be available (All FTTP speeds/bandwidths will be available).
                </small>
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
import { computed, ref, watch, watchEffect } from 'vue';
import { getQuote, quote } from '@/Composables/useQuote';
import { remove, addons, isLoading, checks } from '@/Composables/useConnectivityItem';
import axios from 'axios';
import { actionBarButtons } from '@/Composables/useQuoteSteps.js';

const props = defineProps({
    modelValue: Object
});

const needsAddons = ref(false);
const result = ref([]);
const currentStep = ref(0);
const connectivitySteps = ref(['QuotesConnectivityTable']);

const showConditionalContent = computed(() => {
    const primary = getPrimary();
    const hasFailover = quote.items?.some((x) => x.data?.type === 'Failover Product');

    return (
        primary?.data?.providerKey === 'its' &&
        connectivitySteps.value[currentStep.value] === 'QuoteConnectivityFailOverTable' &&
        !hasFailover
    );
});

const setAltImg = (event) => {
    event.target.src = '/images/no_image.png';
};

const removeItem = (article) => {
    remove(article);
    if (article.data?.type == 'Primary Product') {
        emitter.emit('primary-product:removed');
        currentStep.value = 0;
    } else if (article.data?.type == 'Failover Product') {
        emitter.emit('failover-product:removed');
        currentStep.value = 1;
    }
    getQuote(props.modelValue.id);
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
    return quote.items.find((x) => x.data?.type == 'Primary Product');
};

const getTitle = () => {
    let primary = getPrimary();
    if (Object.keys(steps).includes(primary?.data?.providerKey)) {
        return steps[getPrimary().data?.providerKey][currentStep.value].title;
    } else {
        return 'Select Product';
    }
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
    let check = currentStep.value == 0 ? checks.primaryHardwareRequired : checks.failoverHardwareRequired;
    let productType = currentStep.value == 0 ? 'Primary Hardware Product' : 'Failover Hardware Product';
    checkAddons();
    if (check && !quote.items.find((x) => x.data.type == productType) && addons.value.length != 0) {
        scrollToDiv();
    } else {
        currentStep.value++;
    }
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

const saveQuote = async () => {
    try {
        isLoading.value = true;
        await axios.get('/api/cloudmarket/products/save-quote?quote_id=' + props.modelValue.id);
        window.location.href = route('quotes.show', { quote: props.modelValue.id });
    } catch (error) {
        isLoading.value = false;
        notyf.error(error.response.data.message);
    }
};

watch(
    () => [quote.items],
    () => {
        getComponent();
        checkAddons();
        getbyGroup();
    }
);

watchEffect(() => {
    actionBarButtons.value = [
        currentStep.value !== 0
            ? {
                  btn: 'back',
                  label: 'Previous Step',
                  disabled: isLoading.value,
                  onClick: checkGoPrevious
              }
            : null,

        connectivitySteps.value.length === currentStep.value + 1
            ? {
                  btn: 'next',
                  id: 'btn-continue',
                  disabled: isLoading.value,
                  label: 'Continue',
                  onClick: saveQuote
              }
            : {
                  btn: 'next',
                  disabled: isLoading.value,
                  label: 'Next Step',
                  onClick: checkGoNext
              }
    ].filter(Boolean);
});

getComponent();
checkAddons();
getbyGroup();
</script>
