<script setup>
import { quote, setQuote } from '@composables/useQuote';
import axios from 'axios';
import { router, usePage } from '@inertiajs/vue3';
import { computed, reactive, ref, watch } from 'vue';
import queryString from 'query-string';
import format from '@/Composables/format.js';
import { Checkbox } from 'primevue';

const props = defineProps({
    modelValue: Object
});

const emit = defineEmits(['update:modelValue', 'nextStep']);
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
const loading = ref(true);
setQuote(props.modelValue);
const saveQuote = async () => {
    if (step.value == active_categories.value.length - 1) {
        router.get(`/quotes/${props.modelValue.id}`);
        return;
    }
    step.value++;
};
emitter.on('save-quote', () => {
    saveQuote();
});
const organization = ref(quote.data?.organization ?? null);
const report = ref(organization.value.data?.report ?? null);
const items = ref(report.value ? report.value.data?.items : []);
const itemPrices = ref({});

const configs = reactive({
    unassigned_licenses: 0,
    under_5_utilized_licenses: 0,
    turnoff_1_in_1_month: 0,
    turnoff_4_in_3_month: 0,
    duplicate_licenses: 0,
    wrong_product_plan: 0,
    optional_saving_inefficient_bundle: 0,
    optional_saving_under_utilized_bundles: 0,
    cloud_selected_option: 'none'
});

const checkedCount = computed(() => {
    return Object.entries(configs)
        .filter(([key, value]) => key !== 'cloud_selected_option' && key !== 'under_5_utilized_licenses')
        .reduce((sum, [key, value]) => sum + (value == 1 ? 1 : 0), 0);
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
    const skuIds = itemsList.map((i) => i.skuId).filter(Boolean);
    if (!skuIds.length) {
        return;
    }
    const string = queryString.stringify({
        filters: JSON.stringify([
            {
                column: 'sku',
                operator: 'in',
                value: skuIds
            }
        ])
    });
    const res = await axios.get('/api/products?' + string);
    (res.data.data || []).forEach((p) => {
        itemsList
            .filter((item) => item.skuId === p.sku)
            .forEach((item) => {
                Object.assign(item, {
                    ...p,
                    status_license: item.status,
                    subscribedUnits: item.subscribedUnits,
                    assignedUnits: item.assignedUnits,
                    unassignedUnits: item.unassignedUnits
                });
            });
    });
};

watch(
    () => [configs.turnoff_1_in_1_month, configs.turnoff_4_in_3_month],
    ([val1, val4]) => {
        if (val1 == '1' || val4 == '1') {
            configs.under_5_utilized_licenses = '1';
        } else {
            configs.under_5_utilized_licenses = '0';
        }
    }
);

watch(
    items,
    async (newItems) => {
        loadItem(newItems);
    },
    { immediate: true }
);

const dynamicSavings = computed(() => {
    return 1746 * 0.1 * checkedCount.value;
});
</script>
<template>
    <div class="row justify-content-between mb-3">
        <div class="col-lg-8">
            <div class="mt-3 d-flex justify-content-between align-items-center">
                <h3>Total Monthly Cost</h3>
                <h3>£1,746.00</h3>
            </div>
            <hr class="m-0" />
            <table id="table-simple" class="table table-dealmaker table-bordered table-hover mt-3">
                <!-- Headers -->
                <thead>
                    <tr>
                        <th style="width: 65%">Products</th>
                        <th style="width: 15%">Qty</th>
                        <th style="width: 15%">Price</th>
                        <th style="width: 15%">Total Price</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in items">
                        <td>{{ item.name ?? item.skuPartNumber }}</td>
                        <td>x{{ item.subscribedUnits }}</td>
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

            <div class="d-flex align-items-center gap-2 mt-3 py-2">
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
            </ul>

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
                        <p>
                            One-off fee of £356.00<br />
                            Keep licenses where they are
                        </p>
                    </RadioCard>
                </div>
            </div>

            <ButtonStep v-model="proxyValue" :disabled="true" btnName="Continue with selected option">
                <a id="btn-save-quote" class="btn btn-outline-primary me-2" type="submit" href="/">Save for later</a>
            </ButtonStep>
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
                        <p>Your total monthly cost is <strong>£1,746.00</strong></p>
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
