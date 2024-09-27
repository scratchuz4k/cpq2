<template>
    <div>
        <div class="box" style="max-height: 400px; overflow: auto;">
            <ul>
                <li v-for="permission in list">
                    {{ permission.title }}
                    <ul>
                        <li v-for="child in permission.children">
                            {{ child.title}}
                            <ul>
                                <li v-for="child in child.children">
                                    {{ child.name}}
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
    permissions: {
        required: true,
        default: Array
    }
})

const list = ref([])

const getPermissions =  () => {
    props.permissions.forEach(element => {
        let i = list.value.findIndex(x => x.title == element.group)
        if (i == -1) {
            let item = { title: element.group, children: [] }
            let title = element.title
            item.children.push({ title: title.split(" ").slice(1).join(" "), children: [{ ...element, title: title.split(" ")[0] }] })
            list.value.push(item)
            return
        }

        let title = element.title
        let child = list.value[i].children.find(x => x.title == title.split(" ").slice(1).join(" "))

        if (child) {
            child.children.push({ ...element, title: title.split(" ")[0] })
        } else {
            list.value[i].children.push({ title: title.split(" ").slice(1).join(" "), children: [{ ...element, title: title.split(" ")[0] }] })
        }
    });
}

getPermissions()

</script>
