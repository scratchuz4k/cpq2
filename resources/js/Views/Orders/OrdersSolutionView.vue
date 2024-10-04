<script setup>
import { ref } from "vue"


defineProps({
    solution_types: Object,
    modelValue: Object
})

const GOOGLE_MAPS_API_KEY = import.meta.env.VITE_GOOGLE_MAPS_API_KEY
const solutionChange = (form) => {
    form.postCode = ''
    form.address = null
    form.position = null
    form.articles = []
    addresses.value = []

    if (!form.solutionType) {
        form.solutionCheck = true
        return
    }

    if (form.solutionType?.code == 3) {
        form.solutionCheck = true
        return
    }

    form.solutionCheck = false
}

const addresses = ref([])
const addressSearch = async (form) => {
    if (!form.postCode) {
        alert("Post Code is required")
        return
    }

    addresses.value = []
    form.address = null
    form.solutionCheck = true
    form.isLoading = true

    try {
        let res = await axios.post(`/api/external/get-address-by-postcode`, { postCode: form.postCode })
        form.isLoading = false
        if (!res.data?.body?.addresses) {
            notyf.error("Addresses not found!")
            return
        }
        addresses.value = res.data.body.addresses

    } catch (ex) {
        alert(ex.response.data.message)
    }
}

const addressSelect = async (form) => {
    form.isLoading = true
    try {
        let res = await axios.post(`/api/external/get-position-by-address`, { fullAddress: form.address.label })
        form.isLoading = false
        form.position = res.data
        form.solutionCheck = false
    } catch (ex) {
        alert(ex.response.data.message)
    }
}

</script>
<template>
    <p style="font-weight: bold;">Select a solution type and address to get started</p>
    <div class="row">
        <div class="col-6">
            <div class="mb-3">
                <label class="form-label">Solution Type</label>
                <select class="form-select" @change="solutionChange(modelValue)" v-model="modelValue.solutionType">
                    <option :value="null">Select an option</option>
                    <option v-for="(solution, key) in solution_types" :value="solution">
                        {{ solution.name }}
                    </option>
                </select>
            </div>

            <template v-if="modelValue.solutionType?.code == 3">
                <div class="mb-3 " id="postcode">
                    <label for="input_address" class="form-label">Enter postcode</label>
                    <div class="d-flex">
                        <input class="form-control me-3" id="input_address" placeholder="B11 1SH" name="postcode"
                            v-model="modelValue.postCode">
                        <button type="button" class="btn btn-secondary"
                            @click="addressSearch(modelValue)">Search</button>
                    </div>
                </div>

                <div class="mb-3" id="address" v-if="addresses.length">
                    <label for="select_address">Select address</label>
                    <select class="form-select" id="select_address" @change="addressSelect(modelValue)"
                        v-model="modelValue.address">
                        <option :value="null">Select address</option>
                        <option v-for="(address, key) in addresses" :value="address">{{ address.label }}
                        </option>
                    </select>
                </div>
            </template>
        </div>
        <div class="col-6">
            <div v-if="modelValue.address" class="row p-3" id="location">
                <div class="col">
                    <p style="font-weight: bold;">Location summary</p>
                    <div class="d-flex flex-column flex-lg-row justify-content-between mb-3">
                        <div>
                            <p id="location_company" class="mb-2">
                                <b>{{ modelValue.account?.name }}</b>
                            </p>
                            <p id="location_address">
                                <b>{{ modelValue.address.addressparts.PremisesName }}</b>
                                {{ modelValue.address.addressparts.ThoroughfareName }},
                                {{ modelValue.address.addressparts.ThoroughfareNumber }},
                                {{ modelValue.address.addressparts.SubPremises }}
                                <br>
                                {{ modelValue.address.addressparts.PostCode }}
                                {{ modelValue.address.addressparts.PostTown }}
                                <br>
                                <br>
                                {{ modelValue.address.addressparts.Country }}
                                <br>
                                <br>
                                <font-awesome-icon :icon="['fas','map-pin']" class="me-2"></font-awesome-icon>{{ modelValue.position?.lat }}, {{ modelValue.position?.lon }}
                            </p>
                            <p><i class="fa fa-location-dot"></i> <span id="location_geo"></span></p>
                        </div>
                    </div>
                </div>
                <div class="col">
                    <iframe v-if="modelValue.position" class="map" width="250" height="250" frameborder="0" style="border:0"
                        referrerpolicy="no-referrer-when-downgrade"
                        :src="`https://www.google.com/maps/embed/v1/place?key=${GOOGLE_MAPS_API_KEY}&q=${modelValue.position.lat}, ${modelValue.position.lon}`" allowfullscreen>
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</template>
