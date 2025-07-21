<script setup>
import { ref } from 'vue';
import { quote, getQuote } from '@composables/useQuote';

defineProps({
    preview: Boolean
});
const columns = quote.solution_type?.table?.columns ?? [];
const groupColumns = columns.filter((x) => x.is_group);

const groups = ref([]);
const groupItems = () => {
    groups.value = [];
    quote.items.forEach((e) => {
        let index = groups.value.findIndex((x) => x.group == e.group);
        if (index == -1) {
            let temp = quote.items.find((x) => x.group == e.group && x.price_plan.code == 'MONTHLY');
            groups.value.push({ ...e, name: temp?.name ?? e.name, children: [e] });
        } else {
            groups.value[index].children.push(e);
        }
    });
};
groupItems();

emitter.on('quote-items:updated', async () => {
    await getQuote(quote.id);
    groupItems();
});
</script>
<template>
    <table id="table-simple" :class="{ 'table-hover': !preview }" class="table table-dealmaker table-bordered">
        <!-- Headers -->
        <thead>
            <tr>
                <template v-for="column in columns">
                    <th
                        :class="[`column-${column.value}-header`]"
                        v-if="!groupColumns.find((x) => x.value == column.value)"
                    >
                        {{ column.name }}
                    </th>
                </template>
                <th style="width: 5%" v-if="!preview"></th>
            </tr>
        </thead>
        <tbody>
            <template v-for="(item, index) in groups">
                <TableGroup
                    v-if="quote.solution_type.code == 3"
                    :item="item"
                    :columns="columns"
                    :index="index"
                    :preview="preview"
                    :groupColumns="groupColumns"
                >
                    <template v-if="item.children.length">
                        <TableItem
                            v-for="(child, childIndex) in item.children"
                            :item="child"
                            :columns="columns"
                            :index="childIndex"
                            :isChild="true"
                            :groupColumns="groupColumns"
                            :showToggle="true"
                            :preview="preview"
                            code="Simple"
                        >
                        </TableItem>
                    </template>
                    <template v-else>
                        <TableItem
                            :item="item"
                            :columns="columns"
                            :groupColumns="groupColumns"
                            :index="index"
                            :showToggle="true"
                            :preview="preview"
                            :isChild="true"
                            code="Simple"
                        ></TableItem>
                    </template>
                </TableGroup>
                <TableItem
                    v-else
                    :item="item"
                    :columns="columns"
                    :groupColumns="groupColumns"
                    :index="index"
                    :showToggle="false"
                    :preview="preview"
                    code="Simple"
                >
                </TableItem>
            </template>
        </tbody>
    </table>
</template>
