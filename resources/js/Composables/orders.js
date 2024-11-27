import axios from "axios";
import {usePage} from "@inertiajs/vue3";

const page = usePage()
export const createOrder = async (id, getData) => {

    let result = await swal.fire({
        title: 'Do you want to create an Order from this Quote?'
    })

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
    }).then((result) => {
        if (result.isConfirmed) {
            swal.fire({
                title: 'Enter a rejection Note',
                input: 'textarea',
                preConfirm: (note) => {
                    if (!note) {
                        swal.showValidationMessage('Please enter a note!');
                        return false;
                    }
                    return note;
                }
            }).then( async (noteResult) => {
                if (noteResult.isConfirmed) {
                    data.note = noteResult.value
                    try {
                        let res = await axios.put('/api/orders/' + order.id, data)
                        notyf.success('Order rejected with success');
                        return
                    } catch (err) {
                        notyf.error(err?.response?.data?.message ?? 'Something went wrong')
                    }
                }
            });
        }
    });

}

export const proceedOrder = async (order) => {
    let message = 'Are you sure you want to proceed with this Order?'
    if (!order.user_id) {
        message = 'This action will assign this order to you. Do you wan\'t to continue'
    }

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
            return
        } catch (err) {
            notyf.error(err?.response?.data?.message ?? 'Something went wrong')
        }
    })
}
