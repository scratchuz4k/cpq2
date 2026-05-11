<script setup>
import { reactive, ref } from 'vue';
import { can, hasRole } from '@composables/useAuth';
import InputPrice from '@/Components/InputPrice.vue';
import { DataTable } from 'primevue';
import Column from 'primevue/column';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import format from '@/Composables/format.js';
import axios from 'axios';

const props = defineProps({
    productId: {
        type: Number,
        default: null
    }
});

const prices = defineModel('prices', { required: true });

const price = reactive({
    code: null,
    cost: 0,
    net: 0,
    product_id: props.productId
});

const showModal = ref(false);
const editingRows = ref([]);

const savePrice = async () => {
    if (props.productId) {
        try {
            let res = await axios.post(`/api/prices`, {
                ...price,
                net: parseFloat(price.net) ?? 0,
                cost: parseFloat(price.cost) ?? 0,
                product_id: props.productId
            });
            prices.value.push(res.data.data);
            showModal.value = false;
        } catch (ex) {
            notyf.error(ex.response.data.message);
        }
    } else {
        let temp = { ...price, net: parseFloat(price.net), cost: parseFloat(price.cost) };
        prices.value.push(temp);
        Object.assign(price, { code: null, cost: 0, net: 0 });
        showModal.value = false;
    }
};

const deletePrice = async (id) => {
    let result = await swal.fire({ title: 'Are you sure?' });
    if (['cancel', 'backdrop'].includes(result.dismiss)) return;

    if (props.productId) {
        try {
            let index = prices.value.findIndex((e) => e.id == id);
            await axios.delete(`/api/prices/${id}`);
            prices.value.splice(index, 1);
        } catch (ex) {
            notyf.error(ex.response.data.message);
        }
    } else {
        let index = prices.value.findIndex((e) => e === id || e.code == id);
        prices.value.splice(index, 1);
    }
};

const onRowEditSave = async (event) => {
    let { newData, index } = event;

    if (props.productId) {
        try {
            let res = await axios.put(`/api/prices/${newData.id}`, {
                cost: parseFloat(newData.cost),
                net: parseFloat(newData.net)
            });
            prices.value[index] = res.data.data;
            notyf.success('Updated with success');
        } catch (ex) {
            notyf.error(ex.response.data.message);
        }
    } else {
        prices.value[index] = { ...newData };
    }
};
</script>

<template>
    <DataTable
        v-model:editingRows="editingRows"
        :value="prices"
        editMode="row"
        dataKey="id"
        @row-edit-save="onRowEditSave"
        :pt="{
            column: {
                bodycell: ({ state }) => ({
                    style: state['d_editing'] && 'padding-top: 0.75rem; padding-bottom: 0.75rem',
                    class: 'small'
                }),
                headercell: {
                    style: 'color: var(--table-header-dark-grey); background: var(--table-header-light-grey)',
                    class: 'small'
                },
                columntitle: { class: 'custom-column-title' }
            }
        }"
    >
        <template #empty>
            <div class="d-flex justify-content-end">
                <Button
                    class="p-button p-component p-button-icon-only p-button-secondary p-button-rounded p-button-text"
                    id="add-price-btn"
                    @click="showModal = true"
                >
                    <font-awesome-icon class="text-primary" :icon="['fas', 'plus']" />
                </Button>
            </div>
        </template>

        <Column field="code" header="Code" />
        <Column field="cost" header="Cost">
            <template #body="{ data, field }">{{ format.GBP(data[field]) }}</template>
            <template #editor="{ data, field }"><InputPrice v-model="data[field]" /></template>
        </Column>
        <Column field="net" header="Net">
            <template #body="{ data, field }">{{ format.GBP(data[field]) }}</template>
            <template #editor="{ data, field }"><InputPrice v-model="data[field]" /></template>
        </Column>
        <Column field="net" header="Net SPA">
            <template #body="{ data, field }">{{ format.GBP(data[field]) }}</template>
        </Column>
        <Column field="active_spas" header="Active SPA">
            <template #body="slotProps">
                <p v-for="item in slotProps.data.active_spas" :key="item.id">
                    {{ item.operation.code }}:
                    {{ item.percentage ? format.decimalPlaces(item.percentage) + '%' : format.GBP(item.margin) }}
                </p>
            </template>
        </Column>
        <Column :rowEditor="true" v-if="hasRole('Super Admin') || can('update price')" style="width: 5%">
            <template #roweditoriniticon>
                <Button
                    class="p-button p-component p-button-icon-only p-button-secondary p-button-rounded p-button-text p-datatable-row-editor-init"
                >
                    <font-awesome-icon class="text-primary" :icon="['far', 'pen-to-square']" />
                </Button>
            </template>
        </Column>
        <Column bodyStyle="text-align:center" v-if="hasRole('Super Admin') || can('delete price')" style="width: 5%">
            <template #body="{ data }">
                <Button
                    @click="deletePrice(data.id)"
                    class="p-button p-component p-button-icon-only p-button-secondary p-button-rounded p-button-text"
                >
                    <font-awesome-icon :icon="['far', 'trash-can']" />
                </Button>
            </template>
        </Column>
    </DataTable>

    <!-- Add Price Dialog -->
    <Dialog
        v-model:visible="showModal"
        modal
        :style="{ width: '50rem', marginTop: '4rem' }"
        :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
        :draggable="false"
        position="top"
    >
        <template #header><h3>Add new Price</h3></template>

        <div class="mb-3">
            <label class="form-label">Code:</label>
            <input class="form-control" v-model="price.code" />
        </div>
        <div class="mb-3">
            <label class="form-label">Cost</label>
            <InputPrice id="price-cost" v-model="price.cost" />
        </div>
        <div class="mb-3">
            <label class="form-label">Price</label>
            <InputPrice id="price-price" v-model="price.net" />
        </div>

        <div class="d-flex justify-content-end">
            <button class="btn btn-secondary me-2" @click="showModal = false">Cancel</button>
            <button class="btn btn-primary" @click="savePrice()">Save</button>
        </div>
    </Dialog>
</template>
<style>
.custom-column-title {
    &.p-datatable-column-title {
        font-weight: 500;
        color: var(--table-header-dark-grey) !important;
    }
}
</style>
