<script setup>
import {FontAwesomeIcon} from "@fortawesome/vue-fontawesome";
import queryString from "query-string";
import axios from "axios";
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    data:Array,
    columns: Array,
    level: {
        type:Number,
        default: 0
    },
    route: String,
    state: Object,
    modelValue: Array,
    allow: {
        type: Object,
        default: {
            update: false,
            delete: false,
            link: false,
        }
    },
    usedSlots: Array
})
const getValue = (item, key) => {
    let data = key.split(".")
    let result = item;

    for (let i = 0; i < data.length; i++) {
        const prop = data[i];

        // Check if the current property exists in the current object
        if (result && typeof result === 'object' && prop in result) {
            result = result[prop];
            // Access the nested object using the current property
            if (Array.isArray(result) && i < data.length - 1) {
                result = result.length > 0 ? result[0] : undefined;
            }
        } else {
            // Property not found, return undefined
            return undefined;
        }
    }

    return result;
}

const getChildren = async (item, key) => {

    if (Object.keys(item).includes('children')) {
        props.state[props.level + '' + key] = !props.state[props.level + '' + key]
        return
    }

    let string = queryString.stringify({
        filters: JSON.stringify([{
            'column': 'parent_id',
            'operator': 'equals ',
            'value': item.id
        }]),
    })

    let res = await axios.get('/api' + props.route + '?' + string)
    item.children = res.data.data;

    props.state[props.level + '' + key] = !props.state[props.level + '' + key]
}

const selectItem = async (modelValue, item ) => {
    let index = modelValue.findIndex((temp) => temp.id === item.id)

    if (index === -1) {
        modelValue.push(item)
    } else {
        modelValue.splice(index, 1)
    }
}

const isChecked = (item) => {

    let temp = props.modelValue.find((temp) => temp.id === item.id)
    return !!temp
}

const deleteRecord = async (id) => {
    swal.fire({
        title: 'Are you sure?'
    }).then(async (result) => {
        if (['cancel', 'backdrop'].includes(result.dismiss)) {
            return
        }
        try {
            let res = await axios.delete('/api' + props.route + '/' + id)
            emitter.emit('refresh-data');
            notyf.success('Removed with success');
            return
        } catch (err) {
            notyf.error(err?.response?.data?.message ?? 'Something went wrong')
        }

    })
}

const showModal = (item) => {
    emitter.emit('change-selected', item)
}
</script>

<template>
    <template v-for="(item, key) in data" :key="key">
        <tr>
            <td v-for="(column, columnKey) in columns" :key="columnKey">
                <div class="d-flex" :style="{marginLeft:columnKey === 0? (level*15) +'px' : 'auto' }">
                    <div v-if="columnKey === 0" class="d-flex me-3 justify-content-end justify-content-start" :style="{width: '30px'}">
                        <template v-if="item.has_children && columnKey === 0">
                            <button class="btn m-0 p-0"  v-if="state[level + '' + key]" @click="() => state[level + '' + key] = !state[level + '' + key]">
                                <font-awesome-icon :icon="['fas', 'chevron-down']"/>
                            </button>
                            <button class="btn m-0 p-0" v-else @click="getChildren(item, key)">
                                <font-awesome-icon :icon="['fas', 'chevron-right']"/>
                            </button>
                        </template>
                        <input class="form-check-input ms-2" v-if="columnKey === 0" type="checkbox" @click="selectItem(modelValue, item)" :checked="isChecked(item)" />
                    </div>
                    <slot :name="column.data" :value="getValue(item, column.data)" />
                </div>
            </td>
            <td v-if="allow.update || allow.link || allow.delete || allow.commission || allow.create" :class="['p-0']">
                <div class="d-flex justify-content-center">
                    <template v-if="allow.create">
                        <Link :href="props.route + '/create?parent_id=' + item.id" class="btn p-2 mx-1 border-0">
                            <font-awesome-icon :icon="['fas', 'plus']" />
                        </Link>
                    </template>

                    <template v-if="allow.commission">
                        <button class="btn p-2 mx-1 border-0" @click="showModal(item)">
                            <font-awesome-icon :icon="['fas', 'gear']" />
                        </button>
                    </template>

                    <template v-if="allow.update">
                        <Link :href="props.route + '/' + item.id + '/edit'" class="btn p-2 mx-1 border-0">
                            <font-awesome-icon :icon="['fas', 'edit']" />
                        </Link>
                    </template>

                    <template v-if="allow.link">
                        <Link :href="props.route + '/' + item.id" class="btn p-2 mx-1 border-0">
                            <font-awesome-icon :icon="['fas', 'eye']" />
                        </Link>
                    </template>

                    <template v-if="allow.delete">
                        <button class="btn p-2 mx-1 border-0" @click="deleteRecord(item.id)">
                            <font-awesome-icon icon="fas fa-trash-alt" />
                        </button>
                    </template>
                </div>
            </td>
        </tr>
        <TreeTableItem
            v-if="item.has_children && state[level + '' + key]"
            :data="item.children"
            :columns="columns"
            :level="level+1"
            :route="route"
            :state="state"
            :allow="allow"
            v-model="props.modelValue"
            :usedSlots="props.usedSlots"
        >
            <template v-for="column in columns" #[column.data]="{ value }">
                <slot v-if="usedSlots.includes(column.data)" :name="column.data" :value="value"></slot>
                <template v-else>{{value}}</template>
            </template>
        </TreeTableItem>
    </template>
</template>

<style scoped>

</style>
