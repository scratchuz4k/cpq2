<script setup>
import { ref } from 'vue';

const props = defineProps({
    code: String,
    index: Number,
    preview: Boolean,
    columns: Array,
    groupColumns: Array,
    item: Object
});
const item = defineModel('item');
const showChildren = ref(true);
emitter.on('quote-items:toggle:is-editing', () => item.value.is_editing);

const colspan = props.columns.length - props.groupColumns.length;
</script>
<template>
    <tr class="item-row" :class="[props.code + '-item-parent']" :style="{ 'vertical-align': 'middle' }">
        <td :colspan="colspan">
            <div class="d-flex">
                <button class="btn p-0 border-0 me-2" @click="() => (showChildren = !showChildren)">
                    <font-awesome-icon v-if="!showChildren" :icon="['fas', 'plus']"></font-awesome-icon>
                    <font-awesome-icon v-else :icon="['fas', 'minus']"></font-awesome-icon>
                </button>
                <template v-for="(column, index) in groupColumns">
                    <div class="me-3 py-2">
                        <component
                            :is="format.toCamelCase(column.value) + 'Column'"
                            v-bind="{
                                column: column,
                                modelValue: item,
                                preview: preview,
                                index: index,
                                code: props.code,
                                isGroup: true
                            }"
                        >
                        </component>
                    </div>
                </template>
            </div>
        </td>
        <td class="text-center" v-if="!preview"></td>
    </tr>
    <slot v-if="showChildren"></slot>
</template>
