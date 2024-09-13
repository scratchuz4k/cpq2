<script setup>
import { ref } from "vue"
defineProps({
    solution_types: Object,
    modelValue: Object
})

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
            alert("Addresses not found!")
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
    <h2 class="mb-3">Select a solution type and address to get started</h2>
    <div class="row">
        <div class="col">
            <div class="mb-3">
                <label class="form-label">Solution Type</label>
                <select class="form-select" @change="solutionChange(modelValue)" v-model="modelValue.solutionType">
                    <option :value="null">Select an option</option>
                    <option v-for="(solution, key) in solution_types" :value="{ code: key, name: solution }">
                        {{ solution }}
                    </option>
                </select>
            </div>

            <template v-if="modelValue.solutionType?.code == 3">
                <div class="mb-3 " id="postcode">
                    <label for="input_address" class="form-label">Enter postcode</label>
                    <input class="form-control" id="input_address" placeholder="B11 1SH" name="postcode"
                        v-model="modelValue.postCode">
                    <button type="button" class="btn btn-secondary mt-3"
                        @click="addressSearch(modelValue)">Search</button>
                </div>

                <div class="mb-3" id="address" v-if="addresses.length">
                    <label for="select_address">Select address</label>
                    <select class="form-control" id="select_address" @change="addressSelect(modelValue)"
                        v-model="modelValue.address">
                        <option :value="null">Select address</option>
                        <option v-for="(address, key) in addresses" :value="address">{{ address.label }}
                        </option>
                    </select>
                </div>
                <div v-if="modelValue.address" class="p-3" id="location">
                    <h2>Location summary</h2>
                    <div class="d-flex flex-column flex-lg-row justify-content-between mb-3">
                        <div>
                            <p id="location_company" class="m-0">
                                <b>{{ modelValue.address.addressparts.PremisesName }}</b>
                            </p>
                            <p id="location_address">
                                {{ modelValue.address.addressparts.ThoroughfareName }},
                                {{ modelValue.address.addressparts.ThoroughfareNumber }},
                                {{ modelValue.address.addressparts.SubPremises }}
                                <br>
                                {{ modelValue.address.addressparts.PostCode }}
                                {{ modelValue.address.addressparts.PostTown }}
                                <br>
                                {{ modelValue.address.addressparts.Country }}
                            </p>
                            <p><i class="fa fa-location-dot"></i> <span id="location_geo"></span></p>
                        </div>
                        <div id="map">
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>