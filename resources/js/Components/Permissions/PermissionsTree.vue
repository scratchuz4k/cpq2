<script setup>
import { Button } from 'primevue';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import { ref, watch } from 'vue';

import queryString from 'query-string';

const search = ref(null);
const permissions = ref([]);
const selectedItem = ref([]);

const getPermissions = async () => {
    let string = queryString.stringify({
        limit: 2000,
        search: search.value
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
                children: [{ ...element, title: title.split(' ')[0] }]
            });
            temp.push(item);

            return;
        }

        let title = element.title;
        let child = temp[i].children.find((x) => x.title == title.split(' ').slice(1).join(' '));

        if (child) {
            child.children.push({ ...element, title: title.split(' ')[0] });
        } else {
            temp[i].children.push({
                title: title.split(' ').slice(1).join(' '),
                children: [{ ...element, title: title.split(' ')[0] }]
            });
        }
    });

    return temp;
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
        <ul>
            <li v-for="section in permissions">
                {{ section.title }}
                <ul class="ms-2">
                    <li v-for="group in section.children">
                        {{ group.title }}

                        <ul class="ms-2">
                            <li v-for="item in group.children">
                                <Button type="button" :class="['p-0']">
                                    <font-awesome-icon :icon="['fas', 'x']" />
                                </Button>
                                {{ item.name }}
                            </li>
                        </ul>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</template>
