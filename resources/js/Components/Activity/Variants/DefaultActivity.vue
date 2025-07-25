<template>
    <p class="small">{{ props.item.activitable?.name }}</p>
    <table v-if="props.item.readmore" class="table table-activity mt-3">
        <thead>
            <tr>
                <th style="width: 30%">Field</th>
                <th style="width: 35%">From</th>
                <th style="width: 35%">To</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="item in props.item.data.filter((item) => item.field != 'updated_at')" class="field-info">
                <td class="">
                    <p class="small">{{ item.field }}</p>
                </td>
                <td>
                    <p class="small">{{ item.from ?? 'null' }}</p>
                </td>
                <td>
                    <p class="small">{{ item.to ?? 'null' }}</p>
                </td>
            </tr>
        </tbody>
    </table>
    <a
        class="text-secondary small"
        v-if="props.item.data?.length"
        href="#"
        @click="() => (props.item.readmore = !props.item.readmore)"
    >
        Show {{ !props.item.readmore ? 'more' : 'less' }}...
    </a>
    <a v-else class="text-secondary small m-0 p-0">Nothing to show</a>
</template>
<script setup>
const props = defineProps({
    item: Object
});
</script>
