<script setup>
const props = defineProps({
    code: String,
    column: Object,
    isGroup: Boolean,
    preview: Boolean,
    index: Number
});
const item = defineModel();
</script>
<template>
    <template v-if="!isGroup">
        <p
            v-if="item.quantity * item.price == item[column.value] || preview"
            :id="'total-' + index"
            class="small column_total"
        >
            {{ format.GBP(item.quantity * item.price) }}
        </p>

        <div
            v-else
            v-if="item.quantity * item.price != item[column.value]"
            :id="'total-' + index"
            class="column_total cpq-tooltip"
        >
            {{ format.GBP(item.quantity * item.price) }}
            <span class="cpq-tooltip-text"> Overall Cost:<br />{{ format.GBP(item[props.column.value]) }} </span>
        </div>
    </template>
</template>
