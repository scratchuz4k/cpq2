<script setup>
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import queryString from "query-string";
import axios from "axios";
import { Link } from "@inertiajs/vue3";
import { onUnmounted, reactive, ref } from "vue";

const meta = reactive({ page: 0, total: 0 });

const props = defineProps({
  item: Object,
  index: Number,
  columns: Array,
  level: {
    type: Number,
    default: 0,
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
    },
  },
  usedSlots: Array,
});

const showChild = ref(false);
const getValue = (key) => {
  let data = key.split(".");
  let result = props.item;

  for (let i = 0; i < data.length; i++) {
    const prop = data[i];

    // Check if the current property exists in the current object
    if (result && typeof result === "object" && prop in result) {
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
};

const getChildren = async () => {
  showChild.value = true;
  if (meta.page != 0 && meta.page == meta.total) {
    return;
  }
  meta.page += 1;

  let string = queryString.stringify({
    page: meta.page,
    limit: 20,
    filters: JSON.stringify([
      {
        column: "parent_id",
        operator: "equals",
        value: props.item.id,
      },
    ]),
  });

  let res = await axios.get("/api" + props.route + "?" + string);
  if (!Object.keys(props.item).includes("children")) {
    props.item.children = [];
  }
  props.item.children.push(...res.data.data);
  meta.total = res.data.meta.last_page;
};

const selectItem = async () => {
  let index = props.modelValue.findIndex((temp) => temp.id === props.item.id);

  if (index === -1) {
    props.modelValue.push(props.item);
  } else {
    props.modelValue.splice(index, 1);
  }
};

const isChecked = () => {
  let temp = props.modelValue.find((temp) => temp.id === props.item.id);
  return !!temp;
};

const deleteRecord = async (id) => {
  swal
    .fire({
      title: "Are you sure?",
    })
    .then(async (result) => {
      if (["cancel", "backdrop"].includes(result.dismiss)) {
        return;
      }
      try {
        let res = await axios.delete("/api" + props.route + "/" + id);
        emitter.emit("treetable-refresh");
        emitter.emit("reset-items");
        notyf.success("Removed with success");
        return;
      } catch (err) {
        notyf.error(err?.response?.data?.message ?? "Something went wrong");
      }
    });
};

const showModal = () => {
  emitter.emit("change-selected", props.item);
};

emitter.on("reset-items", () => {
  meta.page = 0;
  showChild.value = false;
});

onUnmounted(() => {
  props.item.children = [];
});
</script>

<template>
  <tr>
    <td>
      <div
        class="d-flex me-3"
        :style="{
          marginLeft: level * 15 + 'px',
        }"
      >
        <div style="width: 15px">
          <template v-if="item?.has_children">
            <button
              class="btn m-0 p-0 lh-1"
              v-if="showChild"
              @click="() => (showChild = !showChild)"
            >
              <font-awesome-icon :icon="['fas', 'chevron-down']" />
            </button>
            <button class="btn m-0 p-0 lh-1" v-else @click="getChildren(index)">
              <font-awesome-icon :icon="['fas', 'chevron-right']" /></button
          ></template>
        </div>
        <input
          class="form-check-input ms-2"
          type="checkbox"
          @click="selectItem(modelValue, item)"
          :checked="isChecked(item)"
        />
      </div>
    </td>
    <td v-for="(column, columnKey) in columns" :key="columnKey">
      <div class="d-flex">
        <slot :name="column.data" :value="getValue(column.data)" />
      </div>
    </td>
    <td
      v-if="
        allow.update || allow.link || allow.delete || allow.commission || allow.create
      "
      :class="['p-0']"
    >
      <div class="d-flex justify-content-center">
        <template v-if="allow.create">
          <Link
            :href="props.route + '/create?parent_id=' + item.id"
            class="btn p-2 mx-1 border-0"
          >
            <font-awesome-icon :icon="['fas', 'plus']" />
          </Link>
        </template>

        <template v-if="allow.commission">
          <button class="btn p-2 mx-1 border-0" @click="showModal(item)">
            <font-awesome-icon :icon="['fas', 'gear']" />
          </button>
        </template>

        <template v-if="allow.update">
          <Link
            :href="props.route + '/' + item.id + '/edit'"
            class="btn p-2 mx-1 border-0"
          >
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
  <template v-if="showChild">
    <TreeTableItem
      v-for="(child, childKey) in item.children"
      :key="child.id"
      :item="child"
      :index="childKey"
      :columns="columns"
      :level="level + 1"
      :route="route"
      :state="state"
      :allow="allow"
      v-model="props.modelValue"
      :usedSlots="props.usedSlots"
    >
      <template v-for="column in columns" #[column.data]="{ value }">
        <slot
          v-if="usedSlots.includes(column.data)"
          :name="column.data"
          :value="value"
        ></slot>
        <template v-else>{{ value }}</template>
      </template>
    </TreeTableItem>
  </template>
  <tr
    v-if="
      item?.has_children && showChild && meta.page != meta.total && item.children?.length
    "
  >
    <td :colspan="columns.length + 2" class="text-center p-0">
      <button class="btn m-0 w-100 p-2" @click="getChildren(index)">Load More...</button>
    </td>
  </tr>
</template>

<style scoped></style>
