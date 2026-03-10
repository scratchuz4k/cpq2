<template>
    <div id="header-notification-button" class="h-100">
        <a
            class="nav-link d-flex align-items-center h-100"
            role="button"
            @click="openNotification"
            v-tooltip
            data-bs-toggle="tooltip"
            data-bs-placement="top"
            title="Notifications"
        >
            <font-awesome-icon :icon="['far', 'bell']" />

            <div v-if="totalUnread && totalUnread > 0" class="notification-badge">
                {{ totalUnread }}
            </div>
        </a>
        <div id="header-notification-dropdown" :class="[showModal ? 'show' : '']">
            <div id="header-notification-title" class="d-flex justify-content-between align-items-center p-3">
                <p class="bold">Notifications</p>
                <a
                    href="/notifications"
                    class="text-decoration-none badge border border-primary text-primary ms-1 lh-sm"
                    >View All</a
                >
            </div>
            <div id="header-notification-content">
                <ul class="m-0 p-0" style="list-style: none">
                    <li
                        v-for="notification in notifications"
                        :key="notification.id"
                        :class="[notification.is_read ? '' : 'unread']"
                        @click="followNotification(notification)"
                        style="cursor: pointer"
                    >
                        <div class="d-flex align-items-center w-100 px-3 py-2">
                            <div class="w-100">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="d-flex justify-content-between align-items-center w-100">
                                        <div class="bold m-0 p-0">
                                            {{ notification.sender.name }}
                                        </div>
                                        <small class="p-0 text-muted">
                                            {{ format.date(notification.created_at) }}
                                        </small>
                                    </div>
                                </div>
                                <component :is="template[notification.type]" :notification="notification" />
                            </div>
                        </div>
                    </li>
                    <li v-if="notifications.length === 0" class="list-group-item text-center py-4">
                        No new notifications
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
<script setup>
import queryString from 'qs';
import { onMounted, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { template, followNotification, getTotalUnread, totalUnread } from '@composables/useNotification';

const page = usePage();
const user = page.props.auth.user;

const showModal = ref(false);
const notifications = ref([]);

const openNotification = async () => {
    showModal.value = !showModal.value;
    if (showModal.value) {
        getNotifications();
    }
};

const getNotifications = () => {
    let string = queryString.stringify({
        order: 'desc',
        orderBy: 'created_at',
        filters: [
            {
                column: 'user_id',
                operator: 'equals',
                value: user.id
            }
        ]
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

onMounted(async () => {
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
    top: -4px;
    right: -2px;
    background-color: var(--color-primary);
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
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
    z-index: 1000;
    transition:
        visibility 0.2s ease-in-out,
        opacity 0.2s ease-in-out;
    visibility: hidden;
    padding: 0;
    opacity: 0;
    border: none;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    background-color: white;
    border-radius: 10px;
    overflow: hidden;
}

#header-notification-dropdown.show {
    visibility: visible;
    opacity: 1;
}

#header-notification-title {
    position: relative;
    border-bottom: 2px solid #e1e3e4;
}

#header-notification-content {
    position: relative;
    max-height: 400px;
    overflow-y: auto;
}

#header-notification-content ul li {
    position: relative;
    font-size: 14px;
    border-top: 1px solid #e1e3e4;
    transition: background-color 0.1s ease-in-out;
    background-color: #fff;
}

#header-notification-content ul li:hover {
    background-color: var(--lighter-grey);
}

#header-notification-content ul li.unread {
    background-color: #e1d3e1;
}
#header-notification-content ul li.unread:before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    width: 4px;
    height: 100%;
    background-color: var(--color-primary);
}
</style>
