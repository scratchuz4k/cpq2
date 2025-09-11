import { quote, getQuote } from '@/Composables/useQuote';
import { ref } from 'vue';

export const isLoading = ref(false);
export const addons = ref([]);

export const remove = (item) => {
    let items = quote.items.filter((x) => x.group == item.group);
    if (item.data?.type == 'Primary Product') {
        items = quote.items;
    }
    try {
        items.forEach(async (x) => {
            await axios.delete(`/api/quote-items/${x.id}`);
            let index = quote.items.findIndex((e) => e.id == x.id);
            quote.items.splice(index, 1);
        });
        getQuote(quote.id);
    } catch (ex) {
        alert(ex.response.data.message);
    }
};

export const add = (article) => {
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

        items.forEach(async (x) => {
            let res = await axios.post('/api/quote-items', x);
            quote.items.push(res.data);
        });
        getQuote(quote.id);
    } catch (ex) {
        alert(ex.response.data.message);
    }
};

export const addAddon = (article) => {
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

        items.forEach(async (x) => {
            let res = await axios.post('/api/quote-items', x);
        });
        getQuote(quote.id);
    } catch (ex) {
        alert(ex.response.data.message);
    }
};

export const removeAddon = (item) => {
    let items = quote.items.filter((x) => x.product_code == item.product_code);
    try {
        items.forEach(async (x) => {
            await axios.delete(`/api/quote-items/${x.id}`);
            let index = quote.items.findIndex((e) => e.id == x.id);
            quote.items.splice(index, 1);
        });
        getQuote(quote.id);
    } catch (ex) {
        alert(ex.response.data.message);
    }
};
