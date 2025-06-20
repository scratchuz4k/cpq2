<script setup>
import { MultiSelect } from "primevue";
import _ from "lodash";
import { reactive, ref, watch } from "vue";
import format from "@/Composables/format";
import RadioCard from '@/Components/RadioCard.vue';

const GOOGLE_MAPS_API_KEY = import.meta.env.VITE_GOOGLE_MAPS_API_KEY;

const props = defineProps({
  solution_types: Object,
  modelValue: Object,
  is_updating_quote: Boolean,
});

const configurations = reactive({
  speed: [0, 10000],
  bearer: [0, 10000],
  contract_length: [],
  managed: 0,
});

props.modelValue.configurations = configurations;

const solutionChange = (form) => {
  if (props.is_updating_quote) {
    const selected_solution = props.modelValue.solution_type.id;
    props.modelValue.solution_type = props.solution_types.find(
      (item) => item.id === selected_solution
    );
    return;
  }

  addressPostCode.value = "";
  form.postCode = "";
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

const addressPostCode = ref(props.modelValue.postCode ?? "");
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
      postCode: form.postCode,
    });
    form.isLoading = false;
    if (!res.data) {
      notyf.error("Addresses not found!");
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
        lon: form.address.longitude,
      };
    } else {
      let res = await axios.post(`/api/external/get-position-by-address`, {
        fullAddress: form.address.label,
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
  if (ev.target.value < 0) {
    field[index] = 0;
    return;
  }

  field[index] = format.simpleSpeed(ev.target.value);
};

solutionChange(props.modelValue);
addressSearch({
  postCode: props.modelValue.postCode ?? "",
});
watch(addressPostCode, () => {
  props.modelValue.postCode = addressPostCode.value;
  addressSearch(props.modelValue);
});
</script>
<template>
  <div class="row">
    <div class="col-6">
      <div class="panel">
        <h5 class="font-bold mb-3">Now, select an solution type</h5>
        <div class="mb-3">
          <template v-if="solution_types.length">
            <label class="form-label"
              >Solution Type <span class="text-primary fw-bold">*</span></label
            >
            <select
              :disabled="is_updating_quote"
              id="solution_id"
              class="form-select"
              @change="solutionChange(modelValue)"
              v-model="modelValue.solution_type"
            >
              <option :value="null">Select an option</option>
              <option v-for="(solution, key) in solution_types" :value="solution">
                {{ solution.name }}
              </option>
            </select>
          </template>
          <div v-else class="text-primary fst-italic">
            <font-awesome-icon
              class="me-2"
              :icon="['fas', 'circle-exclamation']"
            ></font-awesome-icon>
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
                ><font-awesome-icon
                  class="text-muted"
                  :icon="['fas', 'search']"
                ></font-awesome-icon
              ></span>
            </div>
          </div>

          <div class="mb-3" id="address" v-if="addresses.length">
            <label class="form-label" for="select_address"
              >Select address <span class="text-primary fw-bold">*</span></label
            >
            <select
              class="form-select"
              id="select_address"
              @change="addressSelect(modelValue)"
              v-model="modelValue.address"
            >
              <option :value="null">Select address</option>
              <option v-for="(address, key) in addresses" :value="address">
                {{ address.label }}
              </option>
            </select>
          </div>
        </template>
      </div>
    </div>
  </div>
  <div class="row mb-3" v-if="modelValue.address && addresses.length">
    <div class="col-6">
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
  <div
    class="row"
    v-if="modelValue.solution_type?.code == 3 && modelValue.address != null"
  >
    <div class="col-6">
      <div class="panel">
        <h5 class="font-bold mb-3">Optionally, select some default filters</h5>
        <div class="mb-5 pb-2">
          <div class="d-flex justify-content-between mb-2">
            <p class="form-label">Speed</p>
            <span>
              <span class="badge text-bg-secondary">Min</span>
				{{ format.speed(configurations.speed[0]) }}
				<span class="ms-2 badge text-bg-secondary">Max</span>
				{{ format.speed(configurations.speed[1]) }}
              </span>
          </div>
          <div class="d-flex align-items-end">
            <div class="w-20">
              <input
                type="number"
                min="0"
                max="10000"
                class="form-control w-100"
                @change="(ev) => formatInput(ev, configurations.speed, 0)"
                v-model="configurations.speed[0]"
              />
            </div>
            <div class="px-4 mb-3" style="width: 80%">
              <SpeedRange
                v-model="configurations.speed"
                :options="[0, 10, 100, 1000, 10000]"
              ></SpeedRange>
            </div>
            <div class="w-20">
              <input
                type="number"
                :min="0"
                :max="10000"
                @change="(ev) => formatInput(ev, configurations.speed, 1)"
                class="form-control w-100"
                v-model="configurations.speed[1]"
              />
            </div>
          </div>
        </div>
		<hr class="mb-4" />
        <div class="mb-5 pb-2">
          <div class="d-flex justify-content-between mb-2">
            <p class="form-label">Bearer</p>
            <span>
              <span class="badge text-bg-secondary">Min</span>
				{{ format.speed(configurations.bearer[0]) }}
				<span class="ms-2 badge text-bg-secondary">Max</span>
				{{ format.speed(configurations.bearer[1]) }}
            </span>
          </div>
          <div class="d-flex align-items-end">
            <div class="w-20">
              <input
                type="number"
                min="0"
                max="10000"
                class="form-control w-100"
                @change="(ev) => formatInput(ev, configurations.bearer, 0)"
                v-model="configurations.bearer[0]"
              />
            </div>
            <div class="px-4 mb-3" style="width: 80%">
              <BearerRange
                v-model="configurations.bearer"
                :options="[0, 10, 100, 1000, 10000]"
              ></BearerRange>
            </div>
            <div class="w-20">
              <input
                type="number"
                :min="0"
                :max="10000"
                @change="(ev) => formatInput(ev, configurations.bearer, 1)"
                class="form-control w-100"
                v-model="configurations.bearer[1]"
              />
            </div>
          </div>
        </div>
        <div class="mb-4 pb-2">
          <div class="d-flex justify-content-between">
            <p class="form-label">Contract Length</p>
          </div>
          <MultiSelect
            v-model="configurations.contract_length"
            style="width: 100%"
            :options="['12', '24', '36', '60']"
            placeholder="All"
            class="me-3 custom-multiselect"
          >
            <template #option="slotProps">
              <div style="font-size: 14px; line-height: 1.5rem">
                {{ slotProps.option }} Months
              </div>
            </template>
          </MultiSelect>
        </div>
        <div class="mb-3">
		  <p class="form-label mb-2">Managed Products:</p>
		  <div class="row row-cols-1 row-cols-md-3 g-3">
		    <div class="col">
		      <RadioCard v-model="configurations.managed" :value="0" name="managed-products">
		        <font-awesome-icon
		        	class="py-3 opacity-25"
		        	:icon="['fa', 'layer-group']"
		        	size="2x"
		        ></font-awesome-icon>
		        <span class="d-block h6">Show All</span>
		      </RadioCard>
		    </div>
		    <div class="col">
		      <RadioCard v-model="configurations.managed" :value="1" name="managed-products">
		        <font-awesome-icon
		        	class="py-3 opacity-25"
		        	:icon="['fa', 'lock']"
		        	size="2x"
		        ></font-awesome-icon>
		        <span class="d-block h6">Show Managed Only</span>
		      </RadioCard>
		    </div>
		    <div class="col">
		      <RadioCard v-model="configurations.managed" :value="2" name="managed-products">
		        <font-awesome-icon
		        	class="py-3 opacity-25"
		        	:icon="['fa', 'lock-open']"
		        	size="2x"
		        ></font-awesome-icon>
		        <span class="d-block h6">Show Unmanaged Only</span>
		      </RadioCard>
		    </div>
		  </div>
        </div>
      </div>
    </div>
  </div>
</template>
<style scoped>
.form-label{
	font-size:14px;
}
</style>
