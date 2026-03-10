import { ref } from 'vue';

export const selectedItems = ref([]);

export const addSelected = (item) => {
    if (selectedItems.value.length >= 4) {
        notyf.error('Product comparator is full');
        return;
    }

    if (item.value.sku && selectedItems.value.find((x) => x.sku == item.value.sku)) {
        notyf.error('Product is already in comparator');
        return;
    }

    if (item.value.id && selectedItems.value.find((x) => x.id == item.value.id)) {
        notyf.error('Product is already in comparator');
        return;
    }

    selectedItems.value.push(item.value);
};
