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
