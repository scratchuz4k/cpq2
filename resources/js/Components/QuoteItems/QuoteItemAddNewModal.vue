<template>
    <button id="btn-shareable-open" class="btn px-2 py-1 d-flex justify-content-center w-100" @click="openNewIUtem()">
        <div>
            <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
            Add new
        </div>
    </button>
    <Dialog v-model:visible="showModalNewItem" modal :style="{ width: '40rem' }" :draggable="false">
        <template #header>
            <h2>Create new quote item</h2>
        </template>
        <form @submit.prevent="submitForm">
            <div class="row mb-3">
                <div class="col-9">
                    <label>Description:</label>
                    <InputText class="w-100" v-model="newLine.name" />
                </div>
                <div class="col-3">
                    <label>Group:</label>
                    <InputText class="w-100" v-model="newLine.group" />
                </div>
            </div>
            <div class="mb-3">
                <label>Price Plan:</label>
                <InputSelect @change="() => (newLine.contract_length = 1)" id="order-item-new-item" url="/price-plans"
                    :fields="['name']" v-model="newLine.price_plan" placeholder="Select an Price Plan">
                </InputSelect>
            </div>
            <div class="row mb-3">
                <div class="col" v-if="newLine.price_plan?.code == 'MONTHLY'">
                    <label>Contract Length:</label>
                    <InputText class="w-100" type="number" v-model="newLine.contract_length" />
                </div>
                <div class="col">
                    <label>Quantity:</label>
                    <InputText class="w-100" type="number" v-model="newLine.quantity" />
                </div>
                <div class="col">
                    <label>Price:</label>
                    <InputText class="w-100" type="number" v-model="newLine.price" />
                </div>
                <div class="col">
                    <label>Discount:</label>
                    <InputText class="w-100" type="number" v-model="newLine.discount" />
                </div>
            </div>
            <div class="mb-3">
                <button type="submit" id="btn-shareable-create" class="btn btn-primary">
                    Create
                </button>
                <button type="button" class="btn" @click="() => (showModalNewItem = !showModalNewItem)">
                    Cancel
                </button>
            </div>
        </form>
    </Dialog>
</template>
<script setup>
import { Dialog, InputText } from "primevue";
import { quote } from "@composables/useQuote";
import { createQuoteItem } from "@/Composables/useQuoteItem";
import { ref, reactive } from "vue";

const showModalNewItem = ref(false);

const openNewIUtem = () => {
    showModalNewItem.value = true;
};

const newLine = reactive({
    name: null,
    discount: 0,
    quantity: 1,
    price: 0,
    contract_length: 1,
    price_plan: null,
    price_plan_id: null,
    group: null,
});

const submitForm = async () => {
    newLine.price_plan_id = newLine.price_plan?.id;
    if (await createQuoteItem({ quote_id: quote.id, ...newLine })) {
        showModalNewItem.value = false;
        emitter.emit("quote-items:updated");
        Object.assign(newLine, {
            name: null,
            margin: 0,
            quantity: 0,
            price: 0,
            discount: 0,
            group: null,
        });
    }
};
</script>
