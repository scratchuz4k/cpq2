<script setup>
import _ from "lodash";
import { ref, watch } from "vue"

const GOOGLE_MAPS_API_KEY = import.meta.env.VITE_GOOGLE_MAPS_API_KEY

const props = defineProps({
    solution_types: Object,
    modelValue: Object
})

const solutionChange = (form) => {
    addressPostCode.value = ''
    form.postCode = ''
    form.address = null
    form.position = null
    form.articles = []
    addresses.value = []

    if (!form.account) {
        form.solutionCheck = true
        return
    }

    if (!form.solution_type) {
        form.solutionCheck = true
        return
    }

    if (form.solution_type?.code == 3) {
        form.solutionCheck = true
        return
    }

    form.solutionCheck = false
}

const addressPostCode = ref('')
const addresses = ref([])
const addressSearch = _.debounce(async (form) => {
    addresses.value = []
    form.address = null
    form.solutionCheck = true
    form.isLoading = true

    if (!form.postCode) {
        form.isLoading = false
        return
    }

    try {
        let res = await axios.post(`/api/external/get-address-by-postcode`, { postCode: form.postCode })
        form.isLoading = false
        if (!res.data?.body?.addresses) {
            notyf.error("Addresses not found!")
            return
        }
        addresses.value = res.data.body.addresses

    } catch (ex) {
        form.isLoading = false
        notyf.error(ex.response.data.message)
    }
}, 1000)

const addressSelect = async (form) => {
    form.isLoading = true
    form.solutionCheck = true

    if (!form.address) {
        form.isLoading = false
        return
    }

    try {
        let res = await axios.post(`/api/external/get-position-by-address`, { fullAddress: form.address.label })
        form.isLoading = false
        form.position = res.data
        form.solutionCheck = false
    } catch (ex) {
        alert(ex.response.data.message)
    }
}

watch(addressPostCode, () => {
    props.modelValue.postCode = addressPostCode.value
    addressSearch(props.modelValue)
})
</script>
<template>
    <div class="row mb-3 ">
        <div class="col-6">
            <div class="panel">
                <div class="row ">
                    <div class="col-8">
                        <h5 class="font-bold mb-3">Now, select an solution type</h5>
                        <div class="mb-3">
                            <label class="form-label">Solution Type <span class="text-primary fw-bold">*</span></label>
                            <select class="form-select" @change="solutionChange(modelValue)"
                                v-model="modelValue.solution_type">
                                <option :value="null">Select an option</option>
                                <option v-for="(solution, key) in solution_types" :value="solution">
                                    {{ solution.name }}
                                </option>
                            </select>
                        </div>
                        <template v-if="modelValue.solution_type?.code == 3">
                            <div class="mb-3" id="postcode" style="position: relative">
                                <label for="input_address" class="form-label">Enter postcode
                                    <span class="text-primary fw-bold">*</span>
                                    <font-awesome-icon v-if="modelValue.isLoading" :icon="['fas', 'refresh']"
                                        class="fa-spin ms-2"></font-awesome-icon>
                                </label>
                                <div class="d-flex">
                                    <input class="form-control d-flex" id="input_address" placeholder="Enter postcode" name="postcode"
                                    v-model="addressPostCode">
                                    <span class="form-control-icon"><font-awesome-icon class="text-muted" :icon="['fas', 'search']"></font-awesome-icon></span>
                                </div>
                            </div>

                            <div class="mb-3" id="address" v-if="addresses.length">
                                <label class="form-label" for="select_address">Select address <span
                                        class="text-primary fw-bold">*</span></label>
                                <select class="form-select" id="select_address" @change="addressSelect(modelValue)"
                                    v-model="modelValue.address">
                                    <option :value="null">Select address</option>
                                    <option v-for="(address, key) in addresses" :value="address">{{ address.label }}
                                    </option>
                                </select>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            <div class="row">
                    <div class="col-12">
                        <InfoBox v-if="modelValue.address && addresses.length">
                            <p id="location_company" class="mb-2">
                                {{ modelValue.account?.name }}
                            </p>
                            <p class="mb-0" id="location_address">
                                {{ modelValue.address.addressparts.PremisesName }}
                                {{ modelValue.address.addressparts.ThoroughfareName }},
                                {{ modelValue.address.addressparts.ThoroughfareNumber }},
                                {{ modelValue.address.addressparts.SubPremises }}
                                {{ modelValue.address.addressparts.PostCode }}
                                {{ modelValue.address.addressparts.PostTown }}
                                {{ modelValue.address.addressparts.Country }}
                                <br>
                                <font-awesome-icon :icon="['fas', 'map-pin']" class="me-2"></font-awesome-icon>{{
                                    modelValue.position?.lat }}, {{ modelValue.position?.lon }}
                            </p>
                            <p><i class="fa fa-location-dot"></i> <span id="location_geo"></span></p>
                            <iframe v-if="modelValue.position" class="map" width="100%" height="230" frameborder="0"
                                style="border:0" referrerpolicy="no-referrer-when-downgrade"
                                :src="`https://www.google.com/maps/embed/v1/place?key=${GOOGLE_MAPS_API_KEY}&q=${modelValue.position.lat}, ${modelValue.position.lon}`"
                                allowfullscreen>
                            </iframe>
                        </InfoBox>
                    </div>
                </div>
        </div>
    </div>


</template>
