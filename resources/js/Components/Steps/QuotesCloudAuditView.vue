<script setup>
import { quote, setQuote, getQuote } from '@composables/useQuote';
import axios from 'axios';
import { usePage } from '@inertiajs/vue3';
import { computed, reactive, ref, watch } from 'vue';
import queryString from 'query-string';
import format from '@/Composables/format.js';
import { Checkbox } from 'primevue';
import { updateQuoteItem, createQuoteItem, removeQuoteItem } from '@composables/useQuoteItem';

const props = defineProps({
    modelValue: Object
});

const emit = defineEmits(['update:modelValue']);
const proxyValue = computed({
    get() {
        return props.modelValue;
    },
    set(val) {
        emit('update:modelValue', val);
    }
});

const page = usePage();
const tenant = page.props.tenant;
const solution_type = props.modelValue.solution_type;
const isLoading = ref(false);
const reviewProduct = ref(null);
setQuote(props.modelValue);

emitter.on('save-quote', () => {
    saveQuote();
});
const organization = ref(quote.data?.organization ?? null);
const report = ref(organization.value?.data?.report ?? null);
const items = ref(report.value ? report.value.data?.items : []);
const totalMonthlyCost = ref(0);

const configs = reactive({
    unassigned_licenses: quote.configs?.has_unassigned_licenses ?? 0,
    under_5_utilized_licenses: 0,
    turnoff_1_in_1_month: 0,
    turnoff_4_in_3_month: 0,
    duplicate_licenses: 0,
    wrong_product_plan: 0,
    optional_saving_inefficient_bundle: 0,
    optional_saving_under_utilized_bundles: 0,
    cloud_selected_option: quote.configs?.cloud_selected_option ?? 'none'
});

function handleUnder5UtilizedClick(event) {
    event.preventDefault();

    if (configs.under_5_utilized_licenses == '0') {
        configs.turnoff_1_in_1_month = '1';
        configs.turnoff_4_in_3_month = '1';
        configs.under_5_utilized_licenses = '1';
    } else {
        configs.turnoff_1_in_1_month = '0';
        configs.turnoff_4_in_3_month = '0';
        configs.under_5_utilized_licenses = '0';
    }
}

const loadItem = async (itemsList) => {
    if (!itemsList || !itemsList.length) {
        return;
    }
    const skuIds = itemsList.flatMap((item) => [item.skuId, item.skuPartNumber]);
    if (!skuIds.length) {
        return;
    }
    const string = queryString.stringify({
        filters: JSON.stringify([
            {
                column: 'sku',
                operator: 'in',
                value: skuIds
            },
            {
                column: 'status',
                operator: 'equals',
                value: 'Active'
            }
        ])
    });
    const res = await axios.get('/api/products?' + string);
    (res.data.data || []).forEach((p) => {
        itemsList
            .filter((item) => [item.skuId, item.skuPartNumber].includes(p.sku))
            .forEach((item) => {
                Object.assign(item, {
                    ...p,
                    status_license: item.status,
                    subscribedUnits: item.subscribedUnits,
                    assignedUnits: item.assignedUnits,
                    unassignedUnits: item.unassignedUnits
                });
                totalMonthlyCost.value += p.price?.net_spa * item.subscribedUnits;
            });
    });
};

const handleQuoteItems = async () => {
    isLoading.value = true;
    try {
        let res = await axios.put(`/api/quotes/${quote.id}`, {
            configurations: {
                ...quote.configs,
                has_unassigned_licenses: parseInt(configs.unassigned_licenses),
                cloud_selected_option: configs.cloud_selected_option
            }
        });

        setQuote(res.data);
    } catch (ex) {
        alert(ex.response.data.message);
    }

    const quoteHasItems = quote.items.length;
    if (!quoteHasItems) {
        for (const item of items.value.filter((x) => x.id)) {
            await createQuoteItem({
                quote_id: quote.id,
                product_code: item.sku,
                product_id: item.id,
                quantity: item.subscribedUnits,
                contract_length: item.contract_length,
                name: item.name,
                raw_price: item.price ? item.price.net : 0,
                price: item.price ? item.price.net_spa : 0,
                price_plan_id: item.price_plan_id
            });
        }
    } else if (quoteHasItems) {
        for (const item of items.value.filter((x) => x.id)) {
            const quoteItem = quote.items.find((x) => x.product_code === item.sku);
            if (item.quantity !== item.assignedUnits) {
                await updateQuoteItem(quoteItem.id, {
                    quantity: parseInt(configs.unassigned_licenses) === 1 ? item.assignedUnits : item.subscribedUnits
                });
            }
        }
    }

    const quoteItemReviewProduct = quote.items.find((x) => x.product_code === 'Paid-PSA-Review');

    if (configs.cloud_selected_option === 'review' && !quoteItemReviewProduct) {
        await createQuoteItem({
            quote_id: quote.id,
            product_code: reviewProduct.value.product_code,
            product_id: reviewProduct.value.id,
            quantity: 1,
            contract_length: reviewProduct.value.contract_length,
            name: reviewProduct.value.name,
            raw_price: reviewProduct.value.price ? reviewProduct.value.price?.net : 0,
            price: reviewProduct.value.price ? reviewProduct.value.price?.net_spa : 0,
            price_plan_id: reviewProduct.value.price_plan_id
        });
    } else if (configs.cloud_selected_option !== 'review' && quoteItemReviewProduct) {
        await removeQuoteItem(quoteItemReviewProduct.id);
    }
    saveQuote();
};

const saveQuote = async () => {
    try {
        await axios.get('/api/cloudmarket/products/save-quote?quote_id=' + props.modelValue.id);
        window.location.href = route('quotes.show', { quote: props.modelValue.id });
    } catch (error) {
        notyf.error(error.response.data.message);
    }
};

const closeQuote = async () => {
    isLoading.value = true;
    try {
        let res = await axios.delete('/api/quotes/' + quote.id);
        if (res) {
            window.location.href = route('quotes.create');
        }
    } catch (error) {
        notyf.error(error.response.data.message);
    }
};

const getReviewProduct = async () => {
    try {
        let filters = [
            {
                column: 'sku',
                operator: 'equals',
                value: 'Paid-PSA-Review'
            }
        ];

        let query = queryString.stringify({
            page: 1,
            perPage: 1,
            filters: JSON.stringify(filters)
        });

        let res = await axios.get('/api/products' + '?' + query);

        reviewProduct.value = res.data?.data?.[0] ?? {
            product_code: 'Paid-PSA-Review',
            quantity: 1,
            contract_length: 1,
            name: 'Paid PSA review',
            raw_price: 356,
            price: 356,
            price_plan_id: 2
        };
    } catch (error) {
        notyf.error(error.response.data.message);
    }
};

const dynamicSavings = computed(() => {
    if (parseInt(configs.unassigned_licenses) !== 0) {
        return items.value.reduce((acc, item) => acc + (item.unassignedUnits || 0) * (item.price?.net_spa || 0), 0);
    }
    return 0;
});
watch(
    items,
    async (newItems) => {
        await loadItem(newItems);
    },
    { immediate: true }
);
getReviewProduct();
</script>
<template>
    <div class="row justify-content-between mb-3">
        <div class="col-lg-8">
            <div class="mt-3 d-flex justify-content-between align-items-center">
                <h3>Total Monthly Cost</h3>
                <h3>{{ format.GBP(totalMonthlyCost) }}</h3>
            </div>
            <hr class="m-0" />
            <table id="table-simple" class="table table-dealmaker table-bordered table-hover mt-3">
                <!-- Headers -->
                <thead>
                    <tr>
                        <th>Products</th>
                        <th>Subscribed</th>
                        <th>Assigned</th>
                        <th>Unassigned</th>
                        <th>Price</th>
                        <th>Total Price</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in items">
                        <td>{{ item.name ?? item.skuPartNumber }}</td>
                        <td>x{{ item.subscribedUnits }}</td>
                        <td>x{{ item.assignedUnits }}</td>
                        <td>x{{ item.unassignedUnits }}</td>
                        <td>{{ item.price ? format.GBP(item.price.net_spa) : 'N/A' }}</td>
                        <td>{{ item.price ? format.GBP(item.price.net_spa * item.subscribedUnits) : 'N/A' }}</td>
                    </tr>
                </tbody>
            </table>
            <div class="mt-3 d-flex justify-content-between align-items-center">
                <h3>Recommendations</h3>
            </div>
            <hr class="m-0" />

            <InfoBox severity="info" class="mt-3">
                <small
                    >Potential savings require a transfer of the tenant or a Professional Services engagement to assess
                    the licenses in more detail.</small
                >
            </InfoBox>

            <div class="d-flex align-items-center gap-2 mt-3 py-2">
                <Checkbox
                    inputId="unassigned_licenses"
                    v-model="configs.unassigned_licenses"
                    binary
                    trueValue="1"
                    falseValue="0"
                />
                <label for="unassigned_licenses" class="font-bold"> Unassigned Licenses </label>
            </div>
            <ul class="ms-3">
                <li>Licenses available in the tenant but not assigned</li>
            </ul>

            <!--            <div class="d-flex align-items-center gap-2 mt-3 py-2">
                <Checkbox
                    inputId="under_5_utilized_licenses"
                    v-model="configs.under_5_utilized_licenses"
                    binary
                    trueValue="1"
                    falseValue="0"
                    @click="handleUnder5UtilizedClick"
                />
                <label for="under_5_utilized_licenses" class="font-bold"> 5 under-utilised licenses</label>
            </div>
            <ul class="ms-3">
                <li>
                    Visio license assigned, but low or no utilisation in 1 month (1), 3 months(4)<br />

                    <div class="d-flex align-items-center gap-2 py-1 mt-2">
                        <Checkbox
                            inputId="turnoff_1_in_1_month"
                            v-model="configs.turnoff_1_in_1_month"
                            binary
                            trueValue="1"
                            falseValue="0"
                        />
                        <label for="turnoff_1_in_1_month">Turn off 1x licences in 1 month</label>
                    </div>
                    <div class="d-flex align-items-center gap-2 py-1">
                        <Checkbox
                            inputId="turnoff_4_in_3_month"
                            v-model="configs.turnoff_4_in_3_month"
                            binary
                            trueValue="1"
                            falseValue="0"
                        />
                        <label for="turnoff_4_in_3_month">Turn off 4x licences in 3 month</label>
                    </div>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2 mt-3 py-2">
                <Checkbox
                    inputId="duplicate_licenses"
                    v-model="configs.duplicate_licenses"
                    binary
                    trueValue="1"
                    falseValue="0"
                />
                <label for="duplicate_licenses" class="font-bold"> Duplicate licenses assigned to same user </label>
            </div>
            <ul class="ms-3">
                <li>User has an E3 license (which includes Teams) but is also assigned a Teams standalone license</li>
            </ul>

            <div class="d-flex align-items-center gap-2 mt-3 py-2">
                <Checkbox
                    inputId="wrong_product_plan"
                    v-model="configs.wrong_product_plan"
                    binary
                    trueValue="1"
                    falseValue="0"
                />
                <label for="wrong_product_plan" class="font-bold">Wrong product plan assigned </label>
            </div>
            <ul class="ms-3">
                <li>
                    User has Visio Plan 2 assigned (which includes desktop app) but is using web client (which could
                    potentially be met by cheaper Visio Plan 1)
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2 mt-3 py-2">
                <Checkbox
                    inputId="optional_saving_inefficient_bundle"
                    v-model="configs.optional_saving_inefficient_bundle"
                    binary
                    trueValue="1"
                    falseValue="0"
                />
                <label for="optional_saving_inefficient_bundle" class="font-bold"
                    >Optional saving: Inefficient bundle assignment
                </label>
            </div>
            <ul class="ms-3">
                <li>
                    User 1 is assigned an E3 license and uses component product A and B, but User 2 has products C and D
                    assigned, which are also part of E3 and have a higher cost than A+B
                </li>
            </ul>

            <div class="d-flex align-items-center gap-2 mt-3 py-2">
                <Checkbox
                    inputId="optional_saving_under_utilized_bundles"
                    v-model="configs.optional_saving_under_utilized_bundles"
                    binary
                    trueValue="1"
                    falseValue="0"
                />
                <label for="optional_saving_under_utilized_bundles" class="font-bold"
                    >Optional saving: Under-utilised bundles
                </label>
            </div>
            <ul class="ms-3">
                <li>
                    User has E5 license but only uses features available in cheaper E3, or the components which are used
                    would be cheaper to buy separately
                </li>
            </ul>-->

            <div class="mt-3 d-flex justify-content-between align-items-center">
                <h3>Options</h3>
            </div>
            <hr class="m-0" />
            <div class="row row-cols-1 row-cols-md-3 g-3 mt-3 mb-5">
                <div class="col">
                    <RadioCard v-model="configs.cloud_selected_option" :value="'none'" name="cloud-selected-option">
                        <p class="font-bold mb-2">No Change</p>
                        <p>Do nothing to the tenant</p>
                    </RadioCard>
                </div>
                <div class="col">
                    <RadioCard v-model="configs.cloud_selected_option" :value="'move'" name="cloud-selected-option">
                        <p class="font-bold mb-2">Move to us</p>
                        <p>
                            No-risk action<br />
                            FreePSA review up to {{ format.GBP(dynamicSavings) }} saving
                        </p>
                    </RadioCard>
                </div>
                <div class="col">
                    <RadioCard v-model="configs.cloud_selected_option" :value="'review'" name="cloud-selected-option">
                        <p class="font-bold mb-2">Paid PSA review</p>
                        <p v-if="reviewProduct">
                            One-off fee of {{ format.GBP(reviewProduct.price?.net_spa ?? reviewProduct.price) }}<br />
                            Keep licenses where they are
                        </p>
                    </RadioCard>
                </div>
            </div>
            <div class="d-flex justify-content-between flex-row-reverse mt-3">
                <button
                    v-if="configs.cloud_selected_option === 'none'"
                    id="btn-close-quote"
                    class="btn btn-primary"
                    @click="closeQuote()"
                >
                    Close
                    <font-awesome-icon v-if="isLoading" :icon="['fa', 'spinner']" class="fa-spin"></font-awesome-icon>
                    <font-awesome-icon :icon="['fas', 'xmark']" class="ms-2"></font-awesome-icon>
                </button>

                <button
                    v-else
                    id="btn-next-step"
                    class="btn btn-primary"
                    :disabled="isLoading"
                    @click="handleQuoteItems()"
                >
                    Next
                    <font-awesome-icon v-if="isLoading" :icon="['fa', 'spinner']" class="fa-spin"></font-awesome-icon>
                    <font-awesome-icon :icon="['fas', 'arrow-right']" class="ms-2"></font-awesome-icon>
                </button>
            </div>
        </div>
        <div class="col-lg-3">
            <InfoBox>
                <div style="font-size: 14px">
                    <b style="font-size: 16px"> {{ proxyValue.account?.name }}</b>
                    <br />
                    Contact Number: 0124567890<br />
                    Email: firstname.lastname@gmail.com
                </div>
            </InfoBox>
            <InfoBox v-if="['QUOTE', 'PRICE', 'CONFIGURE'].includes(proxyValue.status)">
                <div style="font-size: 14px">
                    This quote will expire on
                    <b style="font-size: 16px">
                        {{ format.expireDate(proxyValue.created_at, tenant.configurations?.quote_expiration_days) }}
                    </b>
                </div>
            </InfoBox>
            <div class="panel mt-3">
                <div class="row">
                    <div class="col-12">
                        <h5 class="font-bold mb-3">Potential Saving</h5>
                        <p>
                            Your total monthly cost is <strong>{{ format.GBP(totalMonthlyCost) }}</strong>
                        </p>
                        <br />
                        <p>
                            You have a potential saving of up to
                            <strong class="total-savings">{{ format.GBP(dynamicSavings) }}</strong>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
