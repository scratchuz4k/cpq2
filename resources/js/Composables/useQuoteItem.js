

import { reactive } from "vue";

export const temp = reactive({
    contract_length: 1,
    discount: 0,
    quantity: 0
})
export const createQuoteItem = async (data) => {
    try {
        let res = await axios.post('/api/quote-items/', data)
        return true
    } catch (err) {
        notyf.error(err?.response?.data?.message ?? 'Something went wrong')
        return false
    }
}
export const updateQuoteItem = async (id, data) => {
    try {
        let res = await axios.put('/api/quote-items/' + id, data)
        return true
    } catch (err) {
        notyf.error(err?.response?.data?.message ?? 'Something went wrong')
        return false
    }
}

export const removeQuoteItem = async (id) => {
    try {
        let res = await axios.delete('/api/quote-items/' + id)
        return true
    } catch (err) {
        notyf.error(err?.response?.data?.message ?? 'Something went wrong')
        return false
    }
}
