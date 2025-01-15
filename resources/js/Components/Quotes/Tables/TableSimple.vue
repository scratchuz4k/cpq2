<script setup>
import { quote } from "@composables/useQuote";

defineProps({
  preview: Boolean,
});
const columns = quote.solution_type?.table?.columns ?? [];
const groupColumns = columns.filter((x) => x.is_group);
</script>
<template>
  <table
    :class="{ 'table-hover': !preview }"
    class="table table-dealmaker table-bordered"
  >
    <!-- Headers -->
    <thead>
      <tr>
        <th
          style="width: 5%"
          v-if="!preview && quote.items.find((e) => !!e.children.length)"
        ></th>
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
      <template v-for="(item, index) in quote.items">
        <TableGroup
          v-if="item.children.length"
          :item="item"
          :columns="columns"
          :index="index"
          :preview="preview"
          :groupColumns="groupColumns"
        >
          <template v-for="(child, childIndex) in item.children">
            <TableItem
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
