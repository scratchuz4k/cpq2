<template>
    <Head title="Notifications" />
    <AuthenticatedLayout>
        <div class="container panel">
            <Header title="Notifications" :breadcrumbs="items">
                <template #buttons>
                    <button
                        id="notifications-mark-all-read"
                        class="btn btn-secondary"
                        @click="markAllasRead()"
                        :disabled="totalUnread == 0"
                    >
                        <font-awesome-icon :icon="['fas', 'eye']" />
                        Mark All as Read
                    </button>
                </template>
            </Header>
            <div class="row px-1 py-2 justify-content-between align-items-center">
                <div class="col-2">
                    <Select v-model="group" :options="options" fluid option-label="label" option-value="value" />
                </div>
                <div class="col-3">
                    <IconField>
                        <InputIcon>
                            <font-awesome-icon :icon="['fas', 'search']" />
                        </InputIcon>
                        <InputText id="datatable-search" v-model="global" type="search" placeholder="Keyword Search" />
                    </IconField>
                </div>
            </div>
            <ul class="m-0 p-0" style="list-style: none">
                <li v-for="notification in notifications" :key="notification.id" class="p-2 m-0">
                    <div class="d-flex align-items-center justify-content-between py-2">
                        <p class="bold m-0 p-0">
                            {{ resolveFriendlyLabel(notification.name) }}
                        </p>
                    </div>
                    <div>
                        <div
                            v-for="(item, index) in notification.items"
                            :id="`notification-line-${index + 1}`"
                            class="notification-line w-100 px-3 py-2"
                            @click="followNotification(item)"
                            style="cursor: pointer; position: relative"
                            :class="[item.is_read ? 'read' : 'unread']"
                        >
                            <div
                                v-if="!item.is_read"
                                class="unread-circle d-flex"
                                style="position: absolute; right: 5px; top: 5px"
                            ></div>
                            <component :is="template[item.type]" :notification="item" />
                            <small class="p-0 text-muted">
                                {{ format.date(item.created_at) }}
                            </small>
                        </div>
                    </div>
                </li>
                <li v-if="notifications.length === 0" class="list-group-item text-center py-4">No new notifications</li>
            </ul>
            <Paginator
                v-model:first="meta.first"
                :rows="meta.limit"
                :totalRecords="meta.total"
                :rowsPerPageOptions="[10, 20, 50]"
                @page="handlePaginator"
            >
            </Paginator>
        </div>
    </AuthenticatedLayout>
</template>
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { IconField, InputIcon, InputText } from 'primevue';
import queryString from 'query-string';
import { onMounted, ref, watch, reactive } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { template, followNotification, getTotalUnread, totalUnread } from '@composables/useNotification';
import Select from 'primevue/select';
import Paginator from 'primevue/paginator';

const page = usePage();
const user = page.props.auth.user;
const global = ref(null);
const meta = reactive({
    page: 1,
    total: 0,
    first: 1,
    limit: 10
});

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

const getNotifications = () => {
    let string = queryString.stringify({
        order: 'desc',
        orderBy: 'created_at',
        group: group.value,
        groupOrder: 'desc',
        limit: meta.limit,
        page: meta.page,
        filters: JSON.stringify([
            {
                column: 'user_id',
                operator: 'equals',
                value: user.id
            }
        ]),
        search: global.value
    });

    axios
        .get('/api/notifications?' + string)
        .then((response) => {
            notifications.value = response.data.data;
            meta.total = response.data.total;
            meta.first = (meta.page - 1) * meta.limit;
        })
        .catch((error) => {
            console.error('Error fetching notifications:', error);
        });
};

const markAllasRead = async (item) => {
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
        ]),
        limit: 1000
    });

    let res = await axios.get('/api/notifications?' + string);
    let items = res.data.data;
    try {
        for (const x of items) {
            await axios.put(`/api/notifications/${x.id}`, {
                is_read: true
            });
        }
    } catch (ex) {
        alert(ex.response.data.message);
    }
    await getNotifications();
    await getTotalUnread();
};
const handlePaginator = (ev) => {
    meta.limit = ev.rows;
    meta.page = ev.page + 1;
    getNotifications();
};

const resolveFriendlyLabel = (name) => {
    const labels = {
        quote_submitted: 'Quote Submitted',
        quote_approved: 'Quote Approved',
        quote_rejected: 'Quote Rejected',
        quote_expired: 'Quote Expired'
    };
    return labels[name] || name;
};

onMounted(async () => {
    getNotifications();
    var select = document.getElementById('header-notification-button');
    document.addEventListener('click', (ev) => {
        if (!select.contains(ev.target)) {
            showModal.value = false;
        }
    });
});

watch(group, () => {
    meta.page = 1;
    getNotifications();
});
watch(global, () => {
    meta.page = 1;
    getNotifications();
});

const items = ref([{ label: 'Home', url: '/' }, { label: 'Notifications' }]);
</script>
<style scoped>
.notification-line {
    position: relative;
    font-size: 14px;
    border-bottom: 1px solid #e1e3e4;
    transition: background-color 0.1s ease-in-out;
    background-color: #fff;
}

.notification-line:hover {
    background-color: var(--lighter-grey);
}

.notification-line.unread {
    background-color: #e1d3e1;
}
.notification-line.unread:before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    width: 4px;
    height: 100%;
    background-color: var(--color-primary);
}
</style>
