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
      <template v-for="(item, index) in quote.items">
        <!-- <TableGroup
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
        </TableGroup> -->
        <TableItem
          :item="item"
          :columns="columns"
          :index="index"
          :showToggle="true"
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
  });

  let res = await axios.get("/api/quote-items?" + string);
  quote.items = res.data.data;
};

getItems();
</script>
