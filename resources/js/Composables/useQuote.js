export const getQuote = async (id) => {
    try {
        let res = await axios.get(`/api/quotes/${id}`)
        return res.data
    } catch (ex) {
        notyf.error(ex.response.data.message)
    }
}
