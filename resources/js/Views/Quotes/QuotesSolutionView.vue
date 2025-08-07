<script setup>
import Select from 'primevue/select';
import { MultiSelect } from 'primevue';
import _ from 'lodash';
import { reactive, ref, watch, computed } from 'vue';
import format from '@/Composables/format';
import RadioCard from '@/Components/RadioCard.vue';

const GOOGLE_MAPS_API_KEY = import.meta.env.VITE_GOOGLE_MAPS_API_KEY;

const props = defineProps({
    solution_types: Object,
    modelValue: Object,
    is_updating_quote: Boolean
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

const configurations = reactive({
    speed: props.modelValue.configs?.speed ? props.modelValue.configs.speed.split(',') : [10, 10000],
    bearer: props.modelValue.configs?.bearer ? props.modelValue.configs.bearer.split(',') : [100, 10000],
    contract_length: props.modelValue.configs?.contract_length
        ? props.modelValue.configs.contract_length.split(',')
        : [],
    managed: props.modelValue.configs?.managed ? props.modelValue.configs.managed : '0'
});

proxyValue.value.configs = configurations;

const solutionChange = (form) => {
    if (props.is_updating_quote) {
        const selected_solution = props.modelValue.solution_type.id;
        props.modelValue.solution_type = props.solution_types.find((item) => item.id === selected_solution);
        return;
    }

    addressPostCode.value = '';
    form.postCode = '';
    form.address = null;
    form.position = null;
    form.articles = [];
    addresses.value = [];

    if (!form.account) {
        form.solutionCheck = true;
        return;
    }

    if (!form.solution_type) {
        form.solutionCheck = true;
        return;
    }

    if (form.solution_type?.code == 3) {
        form.solutionCheck = true;
        return;
    }

    form.solutionCheck = false;
};

const addressPostCode = ref(props.modelValue.postCode ?? '');
const addresses = ref([]);
const addressSearch = _.debounce(async (form) => {
    addresses.value = [];
    form.address = null;
    form.solutionCheck = true;
    form.isLoading = true;

    if (!form.postCode) {
        form.isLoading = false;
        return;
    }

    try {
        let res = await axios.post(`/api/external/get-address-by-postcode`, {
            postCode: form.postCode
        });
        form.isLoading = false;
        if (!res.data) {
            notyf.error('Addresses not found!');
            return;
        }
        addresses.value = res.data;
    } catch (ex) {
        form.isLoading = false;
        notyf.error(ex.response.data.message);
    }
}, 1000);

const addressSelect = async (form) => {
    form.isLoading = true;
    form.solutionCheck = true;

    if (!form.address) {
        form.isLoading = false;
        return;
    }

    try {
        if (form.address.latitude && form.address.longitude) {
            form.position = {
                lat: form.address.latitude,
                lon: form.address.longitude
            };
        } else {
            let res = await axios.post(`/api/external/get-position-by-address`, {
                fullAddress: form.address.label
            });
            form.position = res.data;
        }
        form.isLoading = false;
        form.solutionCheck = false;
    } catch (ex) {
        alert(ex.response.data.message);
    }
};

const formatInput = (ev, field, index) => {
    if (ev.target.value > 10000) {
        field[index] = 10000;
        return;
    }

    if (field == configurations.speed && ev.target.value < 10) {
        field[index] = 10;
        return;
    }

    if (field == configurations.bearer && ev.target.value < 100) {
        field[index] = 100;
        return;
    }

    field[index] = format.simpleSpeed(ev.target.value);

    if (Array.isArray(field) && field.length === 2) {
        if (field[0] > field[1]) {
            if (index === 0) {
                field[0] = field[1];
            } else {
                field[1] = field[0];
            }
        }
    }
};

solutionChange(props.modelValue);
addressSearch({
    postCode: props.modelValue.postCode ?? ''
});
watch(addressPostCode, () => {
    props.modelValue.postCode = addressPostCode.value;
    addressSearch(props.modelValue);
});
</script>
<template>
    <div class="row">
        <div class="col-lg-6">
            <div class="panel">
                <h5 class="font-bold mb-3">Now, select an solution type</h5>
                <div class="mb-3">
                    <template v-if="solution_types.length">
                        <label class="form-label">Solution Type <span class="text-primary fw-bold">*</span></label>
                        <Select
                            v-model="modelValue.solution_type"
                            :inputId="'solution_id'"
                            :options="solution_types"
                            class="w-100"
                            optionLabel="name"
                            placeholder="Select an option"
                            @change="solutionChange(modelValue)"
                        />
                    </template>
                    <div v-else class="text-primary fst-italic">
                        <font-awesome-icon class="me-2" :icon="['fas', 'circle-exclamation']"></font-awesome-icon>
                        No solution types available
                    </div>
                </div>
                <template v-if="modelValue.solution_type?.code == 3">
                    <div class="mb-3" id="postcode" style="position: relative">
                        <label for="input_address" class="form-label"
                            >Enter postcode
                            <span class="text-primary fw-bold">*</span>
                            <font-awesome-icon
                                v-if="modelValue.isLoading"
                                :icon="['fas', 'refresh']"
                                class="fa-spin ms-2"
                            ></font-awesome-icon>
                        </label>
                        <div class="d-flex">
                            <input
                                class="form-control d-flex"
                                id="input_address"
                                placeholder="Enter postcode"
                                name="postcode"
                                v-model="addressPostCode"
                            />
                            <span class="form-control-icon"
                                ><font-awesome-icon class="text-muted" :icon="['fas', 'search']"></font-awesome-icon
                            ></span>
                        </div>
                    </div>

                    <div class="mb-3" id="address" v-if="addresses.length">
                        <label class="form-label" for="select_address"
                            >Select address <span class="text-primary fw-bold">*</span></label
                        >
                        <Select
                            v-model="modelValue.address"
                            :inputId="'select_address'"
                            :options="addresses"
                            filter
                            class="w-100"
                            optionLabel="label"
                            placeholder="Select Address"
                            @change="addressSelect(modelValue)"
                        />
                    </div>
                </template>
            </div>
        </div>
    </div>
    <div class="row mb-3" v-if="modelValue.address && addresses.length">
        <div class="col-lg-6">
            <InfoBox>
                <p id="location_company" class="mb-2">
                    {{ modelValue.account?.name }}
                </p>
                <p class="mb-0" id="location_address">
                    {{ modelValue.address.label }}
                    <br />
                    <font-awesome-icon :icon="['fas', 'map-pin']" class="me-2"></font-awesome-icon
                    >{{ modelValue.position?.lat }}, {{ modelValue.position?.lon }}
                </p>
                <p><i class="fa fa-location-dot"></i> <span id="location_geo"></span></p>
                <iframe
                    v-if="modelValue.position"
                    class="map"
                    width="100%"
                    height="230"
                    frameborder="0"
                    style="border: 0"
                    referrerpolicy="no-referrer-when-downgrade"
                    :src="`https://www.google.com/maps/embed/v1/place?key=${GOOGLE_MAPS_API_KEY}&q=${modelValue.position.lat}, ${modelValue.position.lon}`"
                    allowfullscreen
                >
                </iframe>
            </InfoBox>
        </div>
    </div>
    <StepOptionalFilters
        v-model="proxyValue"
        :showButton="false"
        v-if="modelValue.solution_type?.code == 3 && modelValue.address != null"
    ></StepOptionalFilters>
</template>
<style scoped>
.form-label {
    font-size: 14px;
}
</style>
