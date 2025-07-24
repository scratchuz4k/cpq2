import { reactive } from 'vue';

export const quote = reactive({});

export const getQuote = async (id) => {
    try {
        let res = await axios.get(`/api/quotes/${id}`);
        setQuote(res.data);
    } catch (ex) {
        notyf.error(ex.response.data.message);
    }
};

export const setQuote = (temp) => {
    Object.assign(quote, temp);
};
