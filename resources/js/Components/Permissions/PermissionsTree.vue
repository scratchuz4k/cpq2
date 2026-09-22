<script setup>
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import { ref, watch, computed } from 'vue';
import { permissions, groupedPermissions } from '@composables/usePermissions';

import queryString from 'qs';

const props = defineProps({
    modelValue: Array,
    parentPermissions: { type: Array, default: null },
    unrestricted: { type: Boolean, default: false }
});
const emit = defineEmits(['update:modelValue']);
const proxyValue = computed({
    get() {
        return props.modelValue;
    },
    set(val) {
        emit('update:modelValue', val);
    }
});

const search = ref(null);

const getPermissions = async () => {
    let string = queryString.stringify({
        limit: 2000,
        search: search.value,
        order: 'desc',
        orderBy: 'name'
    });
    try {
        const res = await axios.get('/api/permissions?' + string);
        permissions.value = res.data.data;
        groupedPermissions.value = groupPermissions(res.data.data);
    } catch (ex) {
        notyf.error(ex.response?.data?.message ?? 'Something went wrong!');
    }
};

const groupPermissions = (permissions) => {
    let temp = [];
    permissions.forEach((element) => {
        let i = temp.findIndex((x) => x.title == element.group);

        if (i == -1) {
            let item = { title: element.group, children: [] };
            let title = element.title;

            item.children.push({
                title: excludeText(title),
                children: [{ ...element, title: title }]
            });
            temp.push(item);

            return;
        }

        let title = element.title;
        let child = temp[i].children.find((x) => x.title == excludeText(title));

        if (child) {
            child.children.push(element);
        } else {
            temp[i].children.push({
                title: excludeText(title),
                children: [element]
            });
        }
    });

    return temp;
};

const textToExclude = [
    'View Own',
    'View All',
    'View Details',
    'Create',
    'Edit',
    'Delete',
    'Restore',
    'Force Delete',
    'Export',
    'Import'
];

function excludeText(inputString) {
    const pattern = textToExclude.map((text) => text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')).join('|');
    // Only match the action phrase at the START of the string
    const regex = new RegExp(`^(${pattern})\\b`);

    const stripped = inputString
        .replace(regex, '')
        .replace(/\s{2,}/g, ' ')
        .trim();

    // If nothing is left (title was just the action word itself, e.g. "Import"),
    // keep the original title as the group name.
    return stripped || inputString;
}

function keepExcludedText(inputString) {
    const pattern = textToExclude.map((text) => text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')).join('|');
    // Only grab the action phrase if it's at the START of the string
    const regex = new RegExp(`^(${pattern})\\b`);

    const match = inputString.match(regex);
    return match ? match[1] : inputString;
}

const isAllowedByParent = (item) => {
    if (props.unrestricted) {
        return true;
    }
    if (!props.parentPermissions) {
        return false;
    }
    return props.parentPermissions.some((x) => x.id == item.id);
};

const isGroupAllowed = (group) => group.children.some(isAllowedByParent);

const check = (item) => {
    if (!isAllowedByParent(item)) {
        return;
    }
    let temp = proxyValue.value.findIndex((x) => x.id == item.id);
    if (temp == -1) {
        proxyValue.value.push(item);
    } else {
        proxyValue.value.splice(temp, 1);
    }
};

const checkGroup = (group) => {
    let allowedChildren = group.children.filter(isAllowedByParent);
    if (!allowedChildren.length) {
        return;
    }

    let temp = allowedChildren.some((x) => !!proxyValue.value.find((e) => e.id == x.id));
    if (temp) {
        allowedChildren.forEach((x) => {
            let temp = proxyValue.value.findIndex((e) => e.id == x.id);
            if (temp != -1) {
                proxyValue.value.splice(temp, 1);
            }
        });
    } else {
        allowedChildren.forEach((x) => {
            proxyValue.value.push(x);
        });
    }
};

const getGroupCheck = (group) => {
    let allowedChildren = group.children.filter(isAllowedByParent);
    if (!allowedChildren.length) {
        return false;
    }

    let allInSelected = false;
    allowedChildren.forEach((x) => {
        if (!proxyValue.value.find((e) => e.id == x.id)) {
            allInSelected = true;
        }
    });

    if (!allInSelected) {
        return true;
    }

    let temp = allowedChildren.some((x) => !!proxyValue.value.find((e) => e.id == x.id));
    if (temp) {
        return null;
    }

    return false;
};

const getTooltip = (item) => {
    let perm = item.name.split(' ')[0];
    let name = item.title;
    let text = {
        viewOwn: `Allows viewing the list of own ${name}.`,
        viewAny: `Allows viewing the list of all ${name}.`,
        view: `Allows viewing detailed info of a specific ${name}.`,
        create: `Allows adding new ${name} to the system.`,
        update: `Allows editing ${name} details or settings.`,
        delete: `Allows soft-deleting (or archiving) ${name}.`,
        restore: `Allows restoring deleted ${name}.`,
        forceDelete: `Allows permanently deleting ${name} (cannot be undone).`,
        export: `Allows exporting ${name} data to a file (e.g. CSV, Excel).`
    };
    return text[perm];
};

const getBlockedReason = () => {
    if (!props.parentPermissions) {
        return 'Blocked: select a parent role to enable permissions.';
    }
    return 'Blocked: the parent role does not have this permission.';
};

const getGroupBlockedReason = () => {
    if (!props.parentPermissions) {
        return 'Blocked: select a parent role to enable permissions.';
    }
    return 'Blocked: the parent role does not have any permission in this group.';
};

getPermissions();

watch(search, () => {
    getPermissions();
});
</script>
<template>
    <div class="row row-cols-4 mb-3">
        <div class="col">
            <IconField>
                <InputIcon>
                    <font-awesome-icon :icon="['fas', 'search']" />
                </InputIcon>
                <InputText
                    id="datatable-search"
                    v-model="search"
                    type="search"
                    class="w-100"
                    placeholder="Keyword Search"
                />
            </IconField>
        </div>
        <div class="col">
            <SecondaryButton @click="() => (proxyValue = [])">Clear</SecondaryButton>
        </div>
    </div>
    <div>
        <div class="row row-cols-4">
            <div v-for="section in groupedPermissions" class="col">
                <div class="card bg-light border-0 h-100">
                    <div class="card-header d-flex align-items-center border-bottom-0">
                        <span class="fw-bold py-1 small">{{ section.title }}</span>
                    </div>
                    <ul class="mt-2 px-4 py-2 card-body">
                        <li v-for="group in section.children" :key="'group-' + format.toLowerCase(group.title)">
                            <div class="d-flex align-items-center">
                                <button class="btn p-0 me-2 text-secondary" @click="group.is_visble = !group.is_visble">
                                    <font-awesome-icon
                                        :icon="['fas', group.is_visble ? 'minus' : 'plus']"
                                    ></font-awesome-icon>
                                </button>

                                <div
                                    class="d-flex align-items-center"
                                    :class="{ 'permission-blocked': !isGroupAllowed(group) }"
                                    v-tooltip
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    :title="isGroupAllowed(group) ? null : getGroupBlockedReason()"
                                >
                                    <Checkbox
                                        @click="checkGroup(group)"
                                        :checked="getGroupCheck(group)"
                                        :disabled="!isGroupAllowed(group)"
                                    />
                                    <p class="ms-2 mb-0">{{ group.title }}</p>
                                    <font-awesome-icon
                                        v-if="!isGroupAllowed(group)"
                                        :icon="['fas', 'lock']"
                                        class="ms-2 text-muted small"
                                    ></font-awesome-icon>
                                </div>
                            </div>
                            <ul class="ms-2 my-2 tree-childs" v-if="group.is_visble">
                                <li
                                    class="ms-3 d-flex align-items-center"
                                    :class="{ 'permission-blocked': !isAllowedByParent(item) }"
                                    v-for="item in group.children"
                                    :key="'permission-' + group.id"
                                    v-tooltip
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    :title="isAllowedByParent(item) ? getTooltip(item) : getBlockedReason()"
                                >
                                    <Checkbox
                                        v-model="item.is_checked"
                                        @click="check(item)"
                                        :checked="!!proxyValue.find((x) => x.id == item.id)"
                                        :disabled="!isAllowedByParent(item)"
                                    />
                                    <p class="ps-1 mb-0" :class="isAllowedByParent(item) ? 'cursor-pointer' : ''">
                                        {{ keepExcludedText(item.title) }}
                                    </p>
                                    <font-awesome-icon
                                        v-if="!isAllowedByParent(item)"
                                        :icon="['fas', 'lock']"
                                        class="ms-1 text-muted small"
                                    ></font-awesome-icon>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
            <div v-if="!permissions.length">No Permissions to show</div>
        </div>
    </div>
</template>
<style scoped>
ul {
    list-style: none;
}
.permission-blocked {
    opacity: 0.55;
    cursor: not-allowed;
}
.permission-blocked p {
    cursor: not-allowed;
}
.tree-childs,
.tree-childs li {
    position: relative;
}
.tree-childs:before {
    content: '';
    position: absolute;
    top: 0;
    left: 1.5rem;
    height: 100%;
    width: 1px;
    background: var(--color-secondary);
    overflow: hidden;
}
.tree-childs li:before {
    content: '';
    position: absolute;
    top: 0.675rem;
    left: -1.5rem;
    height: 1px;
    width: 1rem;
    background: var(--color-secondary);
    overflow: hidden;
}
</style>
