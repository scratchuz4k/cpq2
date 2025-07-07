export const toggle_is_active = async (url, data) => {

    let result = await swal.fire({
        title: 'Do you want to change the active status?'
    });

    if (['cancel', 'backdrop'].includes(result.dismiss)) {
        return false;
    }

    try {
        let res = await axios.put(`/api/${url}/` + data.id, {
            ...data,
            is_active: !data.is_active
        });
        if (res) {
            emitter.emit('datatable-refresh');
        }
        notyf.success('Updated with Success');
    } catch (err) {
        notyf.error(err?.response?.data?.message ?? 'Something went wrong');
    }
};
