import { ref } from 'vue';
import queryString from 'qs';
import { usePage } from '@inertiajs/vue3';
const page = usePage();

export const totalUnread = ref(0);

export const template = {
    quote_submitted: 'notification-default',
    quote_approved: 'notification-default',
    quote_rejected: 'notification-default',
    quote_expired: 'notification-default'
};

export const followNotification = async (notification) => {
    try {
        await axios.put('/api/notifications/' + notification.id, {
            is_read: true
        });
        window.location.href = notification.redirect_url;
    } catch (error) {
        notyf.error(error.response.data.message);
    }
};

export const getTotalUnread = async () => {
    const user = page.props.auth.user;
    let string = queryString.stringify({
        order: 'desc',
        orderBy: 'created_at',
        filters: [
            {
                column: 'user_id',
                operator: 'equals',
                value: user.id
            },
            {
                column: 'is_read',
                operator: 'equals',
                value: false
            }
        ]
    });

    let res = await axios.get('/api/notifications?' + string);
    totalUnread.value = res.data.meta.total;
};
