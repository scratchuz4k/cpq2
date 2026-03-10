<script setup>
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import { ref, watch, computed } from 'vue';
import { permissions, groupedPermissions } from '@composables/usePermissions';

import queryString from 'qs';

const props = defineProps({
    modelValue: Array
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
    'Export'
];

function excludeText(inputString) {
    // Create a regex that matches any of the textToExclude items
    const pattern = textToExclude.map((text) => text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')).join('|');
    const regex = new RegExp(`\\b(${pattern})\\b`, 'g');

    // Replace occurrences with empty string and trim leftover spaces
    return inputString
        .replace(regex, '')
        .replace(/\s{2,}/g, ' ')
        .trim();
}
function keepExcludedText(inputString) {
    // Build a regex pattern for all phrases in the list
    const pattern = textToExclude.map((text) => text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')).join('|');
    const regex = new RegExp(`\\b(${pattern})\\b`, 'g');

    // Match and return all occurrences
    const matches = inputString.match(regex);
    return matches ? matches.join(' ') : '';
}

const check = (item) => {
    let temp = proxyValue.value.findIndex((x) => x.id == item.id);
    if (temp == -1) {
        proxyValue.value.push(item);
    } else {
        proxyValue.value.splice(temp, 1);
    }
};

const checkGroup = (group) => {
    let temp = group.children.some((x) => !!proxyValue.value.find((e) => e.id == x.id));
    if (temp) {
        group.children.forEach((x) => {
            let temp = proxyValue.value.findIndex((e) => e.id == x.id);
            if (temp != -1) {
                proxyValue.value.splice(temp, 1);
            }
        });
    } else {
        group.children.forEach((x) => {
            proxyValue.value.push(x);
        });
    }
};

const getGroupCheck = (group) => {
    let allInSelected = false;
    group.children.forEach((x) => {
        if (!proxyValue.value.find((e) => e.id == x.id)) {
            allInSelected = true;
        }
    });

    if (!allInSelected) {
        return true;
    }

    let temp = group.children.some((x) => !!proxyValue.value.find((e) => e.id == x.id));
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

                                <Checkbox @click="checkGroup(group)" :checked="getGroupCheck(group)" />
                                <p>{{ group.title }}</p>
                            </div>
                            <ul class="ms-2 my-2 tree-childs" v-if="group.is_visble">
                                <li class="ms-3 d-flex" v-for="item in group.children" :key="'permission-' + group.id">
                                    <Checkbox
                                        v-model="item.is_checked"
                                        @click="check(item)"
                                        :checked="!!proxyValue.find((x) => x.id == item.id)"
                                    />
                                    <p
                                        class="ps-1 cursor-pointer"
                                        v-tooltip
                                        data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        :title="getTooltip(item)"
                                    >
                                        {{ keepExcludedText(item.title) }}
                                    </p>
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
