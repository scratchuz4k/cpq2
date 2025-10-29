import { quote, getQuote } from '@/Composables/useQuote';
import { reactive, ref } from 'vue';

export const isLoading = ref(false);
export const addons = ref([]);
export const checks = reactive({
    primaryHardwareRequired: false,
    failoverHardwareRequired: false
});

export const remove = async (item) => {
    let items = quote.items.filter((x) => x.group == item.group);
    if (item.data?.type == 'Primary Product') {
        items = quote.items;
    }
    try {
        for (const x of items) {
            await axios.delete(`/api/quote-items/${x.id}`);
            let index = quote.items.findIndex((e) => e.id == x.id);
            quote.items.splice(index, 1);
        }
        await getQuote(quote.id);
    } catch (ex) {
        alert(ex.response.data.message);
    }
};

export const add = async (article) => {
    try {
        let items = [];

        let group = quote.items.reduce((max, item) => (item.group > max ? item.group : max), 0);

        let item = {
            group: group + 1,
            quote_id: quote.id,
            product_id: null,
            quantity: 1,
            product_code: article.code,
            data: article
        };

        if (article.priceMonthly_spa) {
            items.push({
                ...item,
                name: article.productName,
                price_plan_code: 'monthly',
                contract_length: article.contractTermMonths,
                raw_price: article.priceMonthly,
                price: article.priceMonthly_spa
            });
        }
        if (article.priceInstall_spa) {
            items.push({
                ...item,
                name: article.productName + ' (service install)',
                price_plan_code: 'single',
                raw_price: article.priceInstall,
                price: article.priceInstall_spa
            });
        }

        for (const x of items) {
            let res = await axios.post('/api/quote-items', x);
            quote.items.push(res.data);
        }

        await getQuote(quote.id);
    } catch (ex) {
        alert(ex.response.data.message);
    }
};

export const addAddon = async (article) => {
    try {
        let items = [];

        let item = {
            group: article.group,
            quote_id: quote.id,
            product_id: null,
            quantity: 1,
            product_code: article.code,
            data: article
        };
        if (article.priceMonthly_spa) {
            items.push({
                ...item,
                name: article.productName,
                price_plan_code: 'monthly',
                contract_length: article.contractTermMonths,
                raw_price: article.priceMonthly,
                price: article.priceMonthly_spa
            });
        } else {
            items.push({
                ...item,
                name: article.productName,
                price_plan_code: 'single',
                raw_price: article.priceInstall,
                price: article.priceInstall_spa
            });
        }

        for (const x of items) {
            let res = await axios.post('/api/quote-items', x);
            quote.items.push(res.data);
        }
        await getQuote(quote.id);
    } catch (ex) {
        alert(ex.response.data.message);
    }
};

export const removeAddon = async (item) => {
    let items = quote.items.filter((x) => x.product_code == item.product_code);
    try {
        for (const x of items) {
            await axios.delete(`/api/quote-items/${x.id}`);
            let index = quote.items.findIndex((e) => e.id == x.id);
            quote.items.splice(index, 1);
        }
        await getQuote(quote.id);
    } catch (ex) {
        alert(ex.response.data.message);
    }
};
