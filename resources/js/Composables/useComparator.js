import { ref } from 'vue';
import queryString from 'qs';

export const selectedItems = ref([]);
export const comparatorGroups = ref([]);
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
    getFeaturesTable();
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

export const getFeaturesTable = (search = '') => {
    comparatorGroups.value = [];
    selectedItems.value.forEach((x, i) => {
        if (!x.features) {
            return;
        }
        let lowerSearch = !!search ? search.toLowerCase() : null;
        let features = !lowerSearch
            ? x.features
            : x.features.filter((obj) => {
                  return (
                      obj.dimension.value.toLowerCase().includes(lowerSearch) ||
                      obj.dimension_type.label.toLowerCase().includes(lowerSearch)
                  );
              });

        features.forEach((e) => {
            let gIndex = comparatorGroups.value.findIndex((t) => t.name == e.dimension_type.group);
            if (gIndex == -1) {
                comparatorGroups.value.push({ name: e.dimension_type.group, show: true, features: [] });
                gIndex = comparatorGroups.value.length - 1;
            }

            let index = comparatorGroups.value[gIndex].features.findIndex((t) => t.key == e.dimension_type.key);
            if (index == -1) {
                comparatorGroups.value[gIndex].features.push({
                    key: e.dimension_type.key,
                    label: e.dimension_type.label,
                    items: {}
                });
                index = comparatorGroups.value[gIndex].features.length - 1;
            }
            comparatorGroups.value[gIndex].features[index].items[i] = e.dimension.value;
        });
    });
};
