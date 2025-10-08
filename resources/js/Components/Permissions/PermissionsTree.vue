<script setup>
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import { ref, watch, computed } from 'vue';

import queryString from 'query-string';

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
const permissions = ref([]);

const getPermissions = async () => {
    let string = queryString.stringify({
        limit: 2000,
        search: search.value,
        order: 'desc',
        orderBy: 'name'
    });

    const res = await axios.get('/api/permissions?' + string);
    permissions.value = groupPermissions(res.data.data);
};

const groupPermissions = (permissions) => {
    let temp = [];
    permissions.forEach((element) => {
        let i = temp.findIndex((x) => x.title == element.group);

        if (i == -1) {
            let item = { title: element.group, children: [] };
            let title = element.title;

            item.children.push({
                title: title.split(' ').slice(1).join(' '),
                children: [{ ...element, title: title.split(' ')[1] }]
            });
            temp.push(item);

            return;
        }

        let title = element.title;
        let child = temp[i].children.find((x) => x.title == title.split(' ').slice(1).join(' '));

        if (child) {
            child.children.push({ ...element, title: title.split(' ')[1] });
        } else {
            temp[i].children.push({
                title: title.split(' ').slice(1).join(' '),
                children: [{ ...element, title: title.split(' ')[1] }]
            });
        }
    });

    return temp;
};

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
            let temp = proxyValue.value.push(x);
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

getPermissions();

watch(search, () => {
    getPermissions();
});
</script>
<template>
    <div class="mb-3">
        <IconField>
            <InputIcon>
                <font-awesome-icon :icon="['fas', 'search']" />
            </InputIcon>
            <InputText id="datatable-search" v-model="search" type="search" placeholder="Keyword Search" />
        </IconField>
    </div>
    <div>
        <div class="row row-cols-4">
            <div v-for="section in permissions">
                <div class="d-flex align-items-center">
                    <p>{{ section.title }}</p>
                </div>
                <ul class="mt-2 p-0">
                    <li v-for="group in section.children" :key="'group-' + format.toLowerCase(group.title)">
                        <div class="d-flex align-items-center">
                            <button class="btn p-0 me-2" @click="group.is_visble = !group.is_visble">
                                <font-awesome-icon
                                    :icon="['fas', group.is_visble ? 'minus' : 'plus']"
                                ></font-awesome-icon>
                            </button>

                            <Checkbox
                                class="form-check-input me-2"
                                @click="checkGroup(group)"
                                :checked="getGroupCheck(group)"
                            />
                            <p>{{ group.title }}</p>
                        </div>
                        <ul class="ms-2 mt-2" v-if="group.is_visble">
                            <li v-for="item in group.children" :key="'permission-' + group.id">
                                <Checkbox
                                    class="form-check-input"
                                    v-model="item.is_checked"
                                    @click="check(item)"
                                    :checked="!!proxyValue.find((x) => x.id == item.id)"
                                />
                                {{ item.name.split(' ')[0] }}
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
<style scoped>
ul {
    list-style: none;
}
</style>
