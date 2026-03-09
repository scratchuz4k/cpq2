import { ref } from 'vue';

export const selectedItems = ref([]);

export const addSelected = (item) => {
    if (selectedItems.value.length >= 4) {
        notyf.error('Comparator is full');
        return;
    }
    if (selectedItems.value.find((x) => x.sku == item.value.sku)) {
        notyf.error('Already added');
        return;
    }

    selectedItems.value.push(item.value);
};
