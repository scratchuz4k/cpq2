<template>
    <Head title="Notifications" />
    <AuthenticatedLayout>
        <div class="container" style="border: 1px solid lightgray; border-radius: 5px; background-color: white">
            <h2 class="mt-3">Notifications HUB</h2>
            <div class="row mb-2 px-1 py-2">
                <div class="col-2">
                    <p class="bold">Group By:</p>
                    <Select
                        v-model="group"
                        :options="options"
                        fluid
                        size="small"
                        option-label="label"
                        option-value="value"
                    />
                </div>
            </div>
            <hr class="m-0 mb-3" />
            <ul class="m-0 p-0" style="list-style: none">
                <li v-for="notification in notifications" :key="notification.id" class="p-2 m-0">
                    <div class="d-flex align-items-center justify-content-between">
                        <p class="bold m-0 p-0">
                            {{ notification.name }}
                        </p>
                    </div>
                    <div class="ms-3 mt-1">
                        <div
                            v-for="item in notification.items"
                            class="w-100 px-2"
                            @click="followNotification(item)"
                            style="cursor: pointer; position: relative; border-bottom: 1px solid #4a3a6f"
                            :class="[item.is_read ? '' : 'unread']"
                        >
                            <div
                                v-if="!item.is_read"
                                class="unread-circle d-flex"
                                style="position: absolute; right: 5px; top: 5px"
                            ></div>
                            <component :is="template[item.type]" :notification="item" />
                            <small class="p-0" style="font-size: 10px; color: gray">
                                {{ format.date(item.created_at) }}
                            </small>
                        </div>
                    </div>
                </li>
                <li v-if="notifications.length === 0" class="list-group-item">No new notifications.</li>
            </ul>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import queryString from 'query-string';
import { onMounted, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Avatar from 'primevue/avatar';
import { template, followNotification } from '@composables/useNotification';
import Select from 'primevue/select';

const page = usePage();
const user = page.props.auth.user;

const group = ref('date');
const options = ref([
    {
        label: 'Sender',
        value: 'sender.name'
    },
    {
        label: 'Type',
        value: 'type'
    },
    {
        label: 'Day',
        value: 'date'
    }
]);

const showModal = ref(false);
const notifications = ref([]);
const total = ref(0);

const getNotifications = () => {
    let string = queryString.stringify({
        order: 'desc',
        orderBy: 'created_at',
        group: group.value,
        groupOrder: 'desc',
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
            // total.value = response.data.meta.total;
        })
        .catch((error) => {
            console.error('Error fetching notifications:', error);
        });
};

const toggleNotification = async (notification) => {
    try {
        await axios.put('/api/notifications/' + notification.id, {
            is_read: !notification.is_read
        });
        getNotifications();
    } catch (error) {
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

watch(group, () => {
    getNotifications();
});
</script>
<style scoped></style>
