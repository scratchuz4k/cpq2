import { reactive } from "vue"

export const quote = reactive({})

export const getQuote = async (id) => {
    try {
        let res = await axios.get(`/api/quotes/${id}`)
        setQuote(res.data);
    } catch (ex) {
        notyf.error(ex.response.data.message)
    }
}

export const setQuote = (temp) => {
    Object.assign(quote, temp);

    if (quote.solution_type.configurations?.component == 'Simple') {
        return
    }

    Object.assign(quote.items, getLeafNodesWithChildren(quote.items));
}

export const getLeafNodesWithChildren = (objects) => {
    let leaves = [];
    const findLeaves = (items) => {
        for (const item of items) {
            if (item.children && item.children.length > 0) {
                findLeaves(item.children);
            } else {
                leaves.push(item);
            }
        }
    };

    findLeaves(objects);
    return leaves;
};
