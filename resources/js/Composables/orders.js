import axios from "axios";
import {usePage} from "@inertiajs/vue3";

const page = usePage()
export const createOrder = async (id) => {
    swal.fire({
        title: 'Do you want to create an Order from this Quote?'
    }).then(async (result) => {
        if (['cancel', 'backdrop'].includes(result.dismiss)) {
            return
        }
        try {
            let res = await axios.put('/api/quotes/' + id,
                { status: 'REGISTRY'}
            )
            notyf.success('Order created with success');
            return
        } catch (err) {
            notyf.error(err?.response?.data?.message ?? 'Something went wrong')
        }
    })
}

export const assignOrder = async (id) => {
    swal.fire({
        title: 'Do you want to assign this Order to you?'
    }).then(async (result) => {
        if (['cancel', 'backdrop'].includes(result.dismiss)) {
            return
        }
        try {
            let res = await axios.put('/api/orders/' + id,
                { user_id: page.props.auth.user.id }
            )
            notyf.success('Order assigned to you with success');
            return
        } catch (err) {
            notyf.error(err?.response?.data?.message ?? 'Something went wrong')
        }
    })
}
