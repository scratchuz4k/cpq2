import { reactive } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();

export const temp = reactive({
    contract_length: 1,
    discount: 0,
    quantity: 0
});
export const getMarginThreshHoldWarning = () => {
    let tenant = page.props.tenant;
    let value = tenant.configurations.quote_margin_rules_value;
    let operation = tenant.configurations.quote_margin_rules_operation == 'percentage' ? '%' : '£';
    return `Minimum Margin Threshold of ${value}${operation}  has not been reached.`;
};

export const createQuoteItem = async (data) => {
    try {
        let res = await axios.post('/api/quote-items/', data);
        if (!res.data?.is_margin_valid) {
            window.notyf.error(getMarginThreshHoldWarning());
        }
        return true;
    } catch (err) {
        notyf.error(err?.response?.data?.message ?? 'Something went wrong');
        return false;
    }
};
export const updateQuoteItem = async (id, data) => {
    try {
        let res = await axios.put('/api/quote-items/' + id, data);
        if (!res.data?.is_margin_valid) {
            window.notyf.error(getMarginThreshHoldWarning());
        }
        return true;
    } catch (err) {
        notyf.error(err?.response?.data?.message ?? 'Something went wrong');
        return false;
    }
};

export const removeQuoteItem = async (id) => {
    try {
        let res = await axios.delete('/api/quote-items/' + id);
        return true;
    } catch (err) {
        notyf.error(err?.response?.data?.message ?? 'Something went wrong');
        return false;
    }
};
