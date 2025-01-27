import axios from "axios";
import {usePage} from "@inertiajs/vue3";
import { get } from "lodash";

const page = usePage()
export const createOrder = async (id) => {
    let result = await swal.fire({
        title: 'Do you want to create an Order from this Quote?'
    })

    if (['cancel', 'backdrop'].includes(result.dismiss)) {
        return false
    }
    try {
        let res = await axios.put('/api/quotes/' + id,
            { status: 'REGISTRY'}
        )
        notyf.success('Order created with success');
        return true
    } catch (err) {
        notyf.error(err?.response?.data?.message ?? 'Something went wrong')
        return false
    }
}

export const assignOrder = async (id) => {
    let result = await swal.fire({
        title: 'Do you want to assign this Order to you?'
    })

    if (['cancel', 'backdrop'].includes(result.dismiss)) {
        return false
    }

    try {
        let res = await axios.put('/api/orders/' + id,
            { user_id: page.props.auth.user.id }
        )
        notyf.success('Order assigned to you with success');
        return true
    } catch (err) {
        notyf.error(err?.response?.data?.message ?? 'Something went wrong')
        return false
    }
}

export const rejectOrder = async (order) => {
    let message = 'Are you sure?'

    const data = {
        status: 'REJECTED',
        user_id: order.user_id ?? page.props.auth.user.id
    };

    if (!order.user_id) {
        message = 'This action will assign this order to you. Do you wan\'t to continue'
    }

    swal.fire({
        title: message,
    }).then( async (result) => {
        if (result.isConfirmed) {
            if (!order.user_id) {
                try {
                    let res = await axios.put('/api/orders/' + order.id, data)
                    notyf.success('Order assigned to you with success');
                } catch (err) {
                    notyf.error(err?.response?.data?.message ?? 'Something went wrong')
                }
            }

            swal.fire({
                title: 'Please specify a reason for the rejection',
                input: 'textarea',
                width: 640,
                preConfirm: (note) => {
                    if (!note) {
                        swal.showValidationMessage('You must provide a reason for the rejection');
                        return false;
                    }
                    return note;
                }
            }).then( async (noteResult) => {
                if (noteResult.isConfirmed) {
                    data.note = noteResult.value
                    try {
                        let res = await axios.put('/api/orders/' + order.id, data)
                        notyf.success('Order rejected');
                        window.location = '/dashboard'
                    } catch (err) {
                        notyf.error(err?.response?.data?.message ?? 'Something went wrong')
                    }
                }
            });
        }
    });

}

export const proceedOrder = async (order) => {
    let message = 'I want to proceed with this Order?'

    const data = {
        status: 'ORDERED',
        user_id: order.user_id ?? page.props.auth.user.id
    };

    swal.fire({
        title: message
    }).then(async (result) => {
        if (['cancel', 'backdrop'].includes(result.dismiss)) {
            return
        }
        try {
            let res = await axios.put('/api/orders/' + order.id, data)
            notyf.success('Order submited success');
            window.location = '/dashboard'
        } catch (err) {
            notyf.error(err?.response?.data?.message ?? 'Something went wrong')
        }
    })
}
