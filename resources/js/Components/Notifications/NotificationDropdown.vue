<template>
    <div id="header-notification-button">
        <a class="nav-link" href="#" @click="openNotification">
            <font-awesome-icon :icon="['far', 'bell']" />

            <div v-if="total && total > 0" class="notification-badge">
                {{ total }}
            </div>
        </a>
        <div id="header-notification-dropdown" :class="[showModal ? 'show' : '']">
            <div
                id="header-notification-content"
                class="p-2"
                style="border: 1px solid lightgray; border-radius: 5px; background-color: white"
            >
                <div class="d-flex justify-content-between align-items-center mb-2 px-1 py-2">
                    <p class="bold">Notifications</p>
                    <a href="/notifications" class="text-decoration-none">View All</a>
                </div>
                <hr class="m-0" />
                <ul class="m-0 p-0" style="list-style: none">
                    <li
                        v-for="notification in notifications"
                        :key="notification.id"
                        class="p-1 px-2"
                        :class="[notification.is_read ? '' : 'unread']"
                        style="border-bottom: 1px solid #4a3a6f"
                    >
                        <div class="d-flex align-items-center w-100">
                            <div class="w-100" @click="followNotification(notification)" style="cursor: pointer">
                                <div class="d-flex align-items-center justify-content-between">
                                    <p class="bold m-0 p-0">
                                        {{ notification.sender.name }}
                                        <small class="ms-2 p-0" style="font-size: 10px; color: gray">
                                            {{ format.date(notification.created_at) }}
                                        </small>
                                    </p>
                                    <div v-if="!notification.is_read" class="unread-circle d-flex me-2"></div>
                                </div>
                                <component :is="template[notification.type]" :notification="notification" />
                            </div>
                        </div>
                    </li>
                    <li v-if="notifications.length === 0" class="list-group-item">No new notifications.</li>
                </ul>
            </div>
        </div>
    </div>
</template>
<script setup>
import queryString from 'query-string';
import { onMounted, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { template, followNotification } from '@composables/useNotification';

const page = usePage();
const user = page.props.auth.user;

const showModal = ref(false);
const notifications = ref([]);
const total = ref(0);

const openNotification = () => {
    showModal.value = !showModal.value;
    if (showModal.value) {
        getNotifications();
        getTotalUnread();
    }
};

const getNotifications = () => {
    let string = queryString.stringify({
        order: 'desc',
        orderBy: 'created_at',
        filters: JSON.stringify([
            {
                column: 'user_id',
                operator: 'equals',
                value: user.id
            }
        ])
    });

    axios
        .get('/api/notifications?' + string)
        .then((response) => {
            notifications.value = response.data.data;
        })
        .catch((error) => {
            console.error('Error fetching notifications:', error);
        });
};

const getTotalUnread = () => {
    let string = queryString.stringify({
        order: 'desc',
        orderBy: 'created_at',
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
            total.value = response.data.meta.total;
        })
        .catch((error) => {
            console.error('Error fetching notifications:', error);
        });
};

onMounted(() => {
    getNotifications();
    getTotalUnread();
    var select = document.getElementById('header-notification-button');
    document.addEventListener('click', (ev) => {
        if (!select.contains(ev.target)) {
            showModal.value = false;
        }
    });
});
</script>
<style scoped>
#header-notification-button {
    position: relative;
}
.notification-badge {
    position: absolute;
    top: 0;
    right: 0;
    background-color: red;
    color: white;
    border-radius: 50%;
    width: 16px;
    height: 16px;
    font-size: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
}

#header-notification-dropdown {
    position: absolute;
    top: 120%;
    right: 0;
    width: 500px;
    max-height: 0;
    overflow-y: auto;
    z-index: 1000;
    transition:
        max-height 0.4s ease-in-out,
        opacity 0.2s ease-in-out;
    pointer-events: none;
    padding: 0;
    border: none;
    opacity: 0;
}

#header-notification-dropdown.show {
    max-height: 400px;
    pointer-events: auto;
    opacity: 1;
}
</style>
