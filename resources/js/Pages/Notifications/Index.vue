<template>
    <Head title="Notifications" />
    <AuthenticatedLayout>
        <div id="header-notification-dropdown">
            <div
                id="header-notification-content"
                class="p-2"
                style="border: 1px solid lightgray; border-radius: 5px; background-color: white"
            >
                <p class="bold">Notifications</p>
                <ul class="m-0 p-0" style="list-style: none">
                    <li v-for="notification in notifications" :key="notification.id" class="p-2 m-0">
                        <div class="d-flex justify-content-between mb-1">
                            <div class="d-flex align-items-center">
                                <Avatar
                                    :label="notification.sender.name.charAt(0).toUpperCase()"
                                    class="mr-2"
                                    style="background-color: #dee9fc; color: #1a2551"
                                    shape="circle"
                                />
                                <p class="ms-2">{{ notification.sender.name }}</p>
                            </div>
                            <small>{{ format.date(notification.created_at) }}</small>
                        </div>
                        <div class="ms-3 ps-3" style="border-left: 1px solid gray">
                            <div
                                style="background-color: whitesmoke; border-radius: 5px; border: 1px solid lightgray"
                                class="px-2 mb-2"
                            >
                                <small>{{ notification.description }}</small>
                            </div>
                            <small class="d-flex justify-content-between mb-1">
                                <a href="#" class="text-decoration-none">View more...</a>
                                <a v-if="!notification.is_read" href="#" class="text-decoration-none">Mark as read</a>
                                <a v-else href="#" class="text-decoration-none">Mark as unread</a>
                            </small>
                        </div>
                    </li>
                    <li v-if="notifications.length === 0" class="list-group-item">No new notifications.</li>
                </ul>
            </div>
        </div></AuthenticatedLayout
    >
</template>
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import queryString from 'query-string';
import { onMounted, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Avatar from 'primevue/avatar';

const page = usePage();
const user = page.props.auth.user;

const showModal = ref(false);
const notifications = ref([]);
const total = ref(0);

const openNotification = () => {
    showModal.value = !showModal.value;
};
const getNotifications = () => {
    let string = queryString.stringify({
        filters: JSON.stringify([
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
        ])
    });

    axios
        .get('/api/notifications?' + string)
        .then((response) => {
            notifications.value = response.data.data;
            total.value = response.data.meta.total;
        })
        .catch((error) => {
            console.error('Error fetching notifications:', error);
        });
};

const followNotification = async (notification) => {
    try {
        isLoading.value = true;
        await axios.get('/api/cloudmarket/products/save-quote?quote_id=' + props.modelValue.id);
        window.location.href = route('quotes.show', { quote: props.modelValue.id });
    } catch (error) {
        isLoading.value = false;
        notyf.error(error.response.data.message);
    }
};

onMounted(() => {
    getNotifications();
    var select = document.getElementById('header-notification-button');
    document.addEventListener('click', (ev) => {
        if (!select.contains(ev.target)) {
            showModal.value = false;
        }
    });
});
</script>
<style scoped></style>
