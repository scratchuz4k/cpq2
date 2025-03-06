<template>
  <table
    id="table-simple"
    :class="{ 'table-hover': !preview }"
    class="table table-dealmaker table-bordered"
  >
    <!-- Headers -->
    <thead>
      <tr>
        <th style="width: 5%" v-if="!preview && group"></th>
        <template v-for="column in columns">
          <th :class="[`column-${column.value}-header`]">
            {{ column.name }}
          </th>
        </template>
        <th style="width: 5%" v-if="!preview"></th>
      </tr>
    </thead>
    <tbody>
      <template v-if="group">
        <template v-for="group in quote.items">
          <TableGroupSimple :columns="columns" :item="group.name">
            <TableItem
              v-for="(item, index) in group.items"
              :item="item"
              :columns="columns"
              :index="index"
              :isChild="true"
              :showToggle="true"
              :preview="preview"
              code="Simple"
            >
            </TableItem>
          </TableGroupSimple>
        </template>
      </template>
      <template v-else v-for="(item, index) in quote.items">
        <TableItem
          :item="item"
          :columns="columns"
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
<script setup>
import { quote } from "@composables/useQuote";
import queryString from "query-string";
import axios from "axios";

defineProps({
  preview: Boolean,
});

const columns = quote.solution_type?.table?.columns ?? [];
const group = quote.solution_type?.table?.group ?? null;

const getItems = async () => {
  let string = queryString.stringify({
    filters: JSON.stringify([]),
    order: "desc",
    orderBy: group ?? "id",
    limit: 200,
    group: group,
  });

  let res = await axios.get("/api/quote-items?" + string);
  quote.items = res.data.data;
};

getItems();
</script>
