<script setup>
import { quote, getQuote } from "@composables/useQuote";
import { reactive, watch, ref } from "vue";
import queryString from "query-string";
import axios from "axios";

const props = defineProps({
  preview: Boolean,
  blockedColumns: {
    type: Array,
    default: [],
  },
});

const temp = quote.solution_type?.table?.columns ?? [];
const columns = temp.filter((item) => !props.blockedColumns.includes(item.value));
const group = quote.solution_type?.table?.group ?? null;
const groupName = quote.solution_type?.table?.group_name ?? null;

const form = reactive({
  filters: [{ column: "quote_id", operator: "equals", value: quote.id }],
  order: "asc",
  orderBy: "id",
  limit: 200,
  group: group,
  groupOrder: "asc",
});
const changeGroupOrder = () => {
  form.groupOrder = form.groupOrder == "asc" ? "desc" : "asc";
};
const sortBy = (column) => {
  let changeSort = form.order == "asc" ? "desc" : "asc";
  form.order = form.orderBy === column ? changeSort : form.order;
  form.orderBy = column;
};

const getItems = async () => {
  let string = queryString.stringify({
    ...form,
    filters: JSON.stringify([...form.filters]),
  });

  let res = await axios.get("/api/quote-items?" + string);
  quote.items = res.data.data;
};

watch(form, function () {
  getItems();
});

emitter.on("quote-items:updated", async () => {
  await getQuote(quote.id);
  await getItems();
});

getItems();
</script>

<template>
  <div style="overflow-x: auto">
    <table
      id="table-simple"
      :class="{ 'table-hover': !preview }"
      class="table table-dealmaker table-bordered"
    >
      <!-- Headers -->
      <thead>
        <tr>
          <template v-for="column in columns">
            <th :class="[`column-${column.value}-header`]">
              <p
                v-if="column.has_order"
                class="column-title sortable"
                style="white-space: nowrap"
                @click="sortBy(column.value)"
                :style="{
                  textDecoration: column.value == form.orderBy ? 'underline' : 'none',
                }"
              >
                {{ column.name }}
                <span class="text-center" style="width: 20px">
                  <template v-if="column.value == form.orderBy">
                    <font-awesome-icon
                      v-if="form.order == 'desc'"
                      :icon="['fas', 'sort-down']"
                    />
                    <font-awesome-icon v-else :icon="['fas', 'sort-up']" />
                  </template>
                </span>
              </p>
              <p class="column-title" v-else>{{ column.name }}</p>
            </th>
          </template>
          <th style="width: 5%" v-if="!preview">
            <div v-if="group" class="d-flex justify-content-center">
              <button
                id="btn-more-filters"
                class="btn dropdown-toggle p-0"
                type="button"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                data-bs-auto-close="outside"
              >
                <font-awesome-icon :icon="['fas', 'gear']"></font-awesome-icon>
              </button>
              <ul class="dropdown-menu dropdown-menu-end p-1" style="width: 280px">
                <li>
                  <div class="checkbox d-flex">
                    <button class="btn" @click="changeGroupOrder">
                      Change Group Order ({{ form.groupOrder }})
                    </button>
                  </div>
                </li>
              </ul>
            </div>
          </th>
        </tr>
      </thead>
      <tbody>
        <template v-if="group">
          <template v-for="content in quote.items">
            <TableGroupSimple
              :preview="preview"
              :columns="columns"
              :name="content.name"
              :group="group"
              :groupName="groupName"
              :item="content.items ? content.items[0] : {}"
            >
              <TableItem
                v-for="(item, index) in content.items"
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
      <tfoot v-if="!preview">
        <tr>
          <th :colspan="columns.length + 2">
            <QuoteItemAddNewModal></QuoteItemAddNewModal>
          </th>
        </tr>
      </tfoot>
    </table>
  </div>
</template>
