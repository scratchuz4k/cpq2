import { ref } from 'vue';
import queryString from 'qs';

export const selectedItems = ref([]);
export const loadingFeatures = ref(false);

/**
 *
 * @param {object} item
 * @param {boolean} isExternal
 * @returns
 */

export const addSelected = async (item, isExternal = false) => {
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

    if (isExternal && !item.value.features) {
        item.value.features = await getFeatures(item.value);
    }

    selectedItems.value.push(item.value);
};

const getFeatures = async (item) => {
    if (item.features) {
        return;
    }

    let query = await queryString.stringify({
        filters: [
            {
                column: 'product_id',
                operator: 'equals',
                value: item.id
            }
        ],
        limit: 1000
    });

    let res = await axios.get('/api/shop-blocks/product-dimensions?' + query);
    return res.data?.data;
};
