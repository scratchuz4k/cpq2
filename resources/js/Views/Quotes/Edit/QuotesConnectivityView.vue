<template>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <p class="fs-4 me-3 mb-0">
                <b>{{ currentStepConfig.title }}</b>
            </p>
            <p class="mb-0 text-primary bold fs-5 pt-1">STEP {{ currentStep + 1 }}/{{ activeSteps.length }}</p>
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
                    over a Broadband connection (SoGEA or FTTP).<br />
                    <i class="bold text-primary">
                        Prior to order, availability of the broadband service must be checked on
                        <a href="https://www.zen.co.uk/business" target="_blank"> https://www.zen.co.uk/business </a>
                    </i>
                    <br />
                    <template v-if="showFullResilienceText">
                        If Business Pro, Business Essentials, Unlimited Fibre 1 or Unlimited Fibre 2 are the options
                        returned, then SoGEA Broadband Backup is available for ordering. If the following "Full Fibre,
                        100, 300, 500 and 900" are shown as available on the Zen portal then ITS Broadband FTTP Back up
                        service will be available (All FTTP speeds/bandwidths will be available).
                    </template>
                    <a
                        v-else
                        href="#"
                        class="text-muted text-decoration-underline d-inline-block"
                        @click.prevent="toggleResilienceText"
                    >
                        Read more
                    </a>
                </small>
            </div>
        </div>
    </div>
    <component
        :is="currentStepConfig.component"
        :key="currentStep"
        v-bind="{
            modelValue: props.modelValue,
            needsAddons: needsAddons,
            currentStep: currentStep,
            ...(currentStepConfig.props ?? {})
        }"
    ></component>
</template>
<script setup>
import { computed, ref, watchEffect } from 'vue';
import { getQuote, quote } from '@/Composables/useQuote';
import { remove, addons, isLoading, checks } from '@/Composables/useConnectivityItem';
import axios from 'axios';
import { actionBarButtons } from '@/Composables/useQuoteSteps.js';

const props = defineProps({
    modelValue: Object
});

const currentStep = ref(0);

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

const primaryStepProps = {
    productType: 'Primary Product',
    route: 'cloudmarket/products',
    hardwareProductType: 'Primary Hardware Product'
};

const failoverStepProps = {
    productType: 'Failover Product',
    route: 'cloudmarket/products/failover',
    hardwareProductType: 'Failover Hardware Product'
};

// pxc and its currently follow the exact same step flow; kept as separate provider keys
// so a provider-specific flow can diverge later without reshaping this structure.
const defaultSteps = [
    {
        title: 'Select a Primary Product',
        component: 'QuotesConnectivityTable',
        needAddons: true,
        props: primaryStepProps
    },
    {
        title: 'Failover Service',
        component: 'QuotesConnectivityTable',
        needAddons: true,
        props: failoverStepProps
    },
    {
        title: 'Addons',
        component: 'QuoteConnectivityExtrasTable',
        needAddons: false
    }
];

const fallbackSteps = [
    { title: 'Select Product', component: 'QuotesConnectivityTable', needAddons: false, props: primaryStepProps }
];

const steps = {
    pxc: defaultSteps,
    its: defaultSteps
};

const getPrimary = () => {
    if (!quote.items) {
        return null;
    }
    return quote.items.find((x) => x.data?.type == 'Primary Product');
};

const activeSteps = computed(() => {
    const primary = getPrimary();
    if (primary && Object.keys(steps).includes(primary.data?.providerKey)) {
        return steps[primary.data.providerKey];
    }
    return fallbackSteps;
});

const currentStepConfig = computed(() => activeSteps.value[currentStep.value] ?? activeSteps.value[0]);
const needsAddons = computed(() => currentStepConfig.value.needAddons ?? false);

const showConditionalContent = computed(() => {
    const primary = getPrimary();
    const hasFailover = quote.items?.some((x) => x.data?.type === 'Failover Product');

    return (
        primary?.data?.providerKey === 'its' &&
        currentStepConfig.value.props?.productType === 'Failover Product' &&
        !hasFailover
    );
});

const result = computed(() => {
    if (!quote.items) {
        return [];
    }
    const seenGroups = new Set();
    const grouped = [];
    quote.items
        .filter((x) => ['Primary Product', 'Failover Product'].includes(x.data?.type))
        .forEach((item) => {
            if (!seenGroups.has(item.group)) {
                seenGroups.add(item.group);
                grouped.push(item);
            }
        });
    return grouped;
});

const checkGoPrevious = () => {
    currentStep.value--;
};

const checkGoNext = () => {
    let check = currentStep.value == 0 ? checks.primaryHardwareRequired : checks.failoverHardwareRequired;
    let productType = currentStep.value == 0 ? 'Primary Hardware Product' : 'Failover Hardware Product';
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

        activeSteps.value.length === currentStep.value + 1
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
</script>
