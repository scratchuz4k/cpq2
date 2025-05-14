<template>
  <div
    :style="{ height: show ? '100%' : '80px' }"
    style="
      position: fixed;
      bottom: 0;
      left: 0;
      right: 0;
      background: white;
      z-index: 999;
      transition: height 0.5s ease;
    "
  >
    <div
      class="row"
      style="
        height: 80px;
        cursor: pointer;
        border-top: 2px solid var(--color-purple);
        border-bottom: 2px solid var(--color-purple);
      "
    >
      <div class="col px-5">
        <div class="row h-100">
          <div class="col-3" @click="() => (show = !show)">
            <div class="d-flex align-items-center justify-content-start h-100">
              <font-awesome-icon
                v-if="!show"
                style="font-size: 50px; color: var(--color-purple)"
                :icon="['fas', 'chevron-up']"
              />
              <font-awesome-icon
                v-else
                style="font-size: 50px; color: var(--color-purple)"
                :icon="['fas', 'chevron-down']"
              />
              <p class="big bold ms-2" style="color: var(--color-purple)">
                {{ show ? "Close" : "Open" }}
              </p>
            </div>
          </div>
          <div v-for="(item, index) in proxyValue" class="col-2">
            <div class="d-flex align-items-center h-100">
              <div>
                <img
                  :src="item.primary_image"
                  style="object-fit: contain"
                  :style="{
                    width: '60px',
                    height: '60px',
                  }"
                />
              </div>
              <div class="mx-2 w-100">
                <p class="bold truncate-1-line">
                  {{ item.model }}
                </p>
                <p class="truncate-1-line">{{ item.sku }}</p>
              </div>
              <div class="mx-3">
                <button @click="() => proxyValue.splice(index, 1)" class="btn">
                  <font-awesome-icon :icon="['fas', 'trash']" @click="" />
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div
      class="row p-3"
      style="overflow-y: scroll; overflow-x: hidden; height: calc(100% - 80px)"
    >
      <div class="col">
        <div class="panel h-100" style="border: 2px solid var(--color-purple)">
          <p class="bold">General</p>
          <hr />
          <table class="table">
            <tbody>
              <tr>
                <th>Sku</th>
                <td>
                  <template v-if="proxyValue.length >= 1">
                    {{ proxyValue[0].sku }}
                  </template>
                </td>
                <td>
                  <template v-if="proxyValue.length >= 2">
                    {{ proxyValue[1].sku }}
                  </template>
                </td>
                <td>
                  <template v-if="proxyValue.length >= 3">
                    {{ proxyValue[2].sku }}
                  </template>
                </td>
                <td>
                  <template v-if="proxyValue.length >= 4">
                    {{ proxyValue[3].sku }}
                  </template>
                </td>
              </tr>
              <tr>
                <th>Model</th>
                <td>
                  <template v-if="proxyValue.length >= 1">
                    {{ proxyValue[0].model }}
                  </template>
                </td>
                <td>
                  <template v-if="proxyValue.length >= 2">
                    {{ proxyValue[1].model }}
                  </template>
                </td>
                <td>
                  <template v-if="proxyValue.length >= 3">
                    {{ proxyValue[2].model }}
                  </template>
                </td>
                <td>
                  <template v-if="proxyValue.length >= 4">
                    {{ proxyValue[3].model }}
                  </template>
                </td>
              </tr>
            </tbody>
          </table>
          <div class="d-flex justify-content-between align-items-end mt-5">
            <p class="bold">Features</p>
            <div>
              <IconField>
                <InputIcon>
                  <font-awesome-icon icon="fas fa-search" />
                </InputIcon>
                <InputText
                  :id="'select-' + props.id + '-search'"
                  v-model="searchInput"
                  placeholder="Search"
                />
              </IconField>
            </div>
          </div>
          <hr />
          <table class="table">
            <tbody>
              <template v-for="group in temp">
                <tr>
                  <th colspan="5" style="background: whitesmoke">
                    <div
                      @click="() => (group.show = !group.show)"
                      style="cursor: pointer"
                    >
                      <div class="d-flex justify-content-between w-100">
                        <p>{{ group.name }}</p>
                        <div>
                          <font-awesome-icon
                            class="w-100"
                            v-if="group.show"
                            style="color: var(--color-purple)"
                            :icon="['fas', 'chevron-down']"
                          />
                          <font-awesome-icon
                            class="w-100"
                            v-else
                            style="color: var(--color-purple)"
                            :icon="['fas', 'chevron-up']"
                          />
                        </div>
                      </div>
                    </div>
                  </th>
                </tr>
                <tr v-if="group.show" v-for="row in group.features">
                  <th>{{ row.label }}</th>
                  <td>{{ row.items[0] }}</td>
                  <td>{{ row.items[1] }}</td>
                  <td>{{ row.items[2] }}</td>
                  <td>{{ row.items[3] }}</td>
                </tr>
              </template>
            </tbody>
          </table>
          <p class="mt-5 bold">Prices</p>
          <hr />
          <table class="table">
            <tbody>
              <tr>
                <th>Stock</th>
                <td>
                  <template v-if="proxyValue.length >= 1">
                    {{ proxyValue[0].stock }}
                  </template>
                </td>
                <td>
                  <template v-if="proxyValue.length >= 2">
                    {{ proxyValue[1].stock }}
                  </template>
                </td>
                <td>
                  <template v-if="proxyValue.length >= 3">
                    {{ proxyValue[2].stock }}
                  </template>
                </td>
                <td>
                  <template v-if="proxyValue.length >= 4">
                    {{ proxyValue[3].stock }}
                  </template>
                </td>
              </tr>
              <tr>
                <th>Net</th>
                <td>
                  <template v-if="proxyValue.length >= 1">
                    {{ format.GBP(proxyValue[0].price.net_spa) }}</template
                  >
                </td>
                <td>
                  <template v-if="proxyValue.length >= 2">
                    {{ format.GBP(proxyValue[1].price.net_spa) }}
                  </template>
                </td>
                <td>
                  <template v-if="proxyValue.length >= 3">
                    {{ format.GBP(proxyValue[2].price.net_spa) }}</template
                  >
                </td>
                <td>
                  <template v-if="proxyValue.length >= 4">
                    {{ format.GBP(proxyValue[3].price.net_spa) }}</template
                  >
                </td>
              </tr>
              <tr>
                <th>Gross</th>
                <td>
                  <template v-if="proxyValue.length >= 1">
                    {{ format.GBP(proxyValue[0].price.gross) }}</template
                  >
                </td>
                <td>
                  <template v-if="proxyValue.length >= 2">
                    {{ format.GBP(proxyValue[1].price.gross) }}</template
                  >
                </td>
                <td>
                  <template v-if="proxyValue.length >= 3">
                    {{ format.GBP(proxyValue[2].price.gross) }}</template
                  >
                </td>
                <td>
                  <template v-if="proxyValue.length >= 4">
                    {{ format.GBP(proxyValue[3].price.gross) }}</template
                  >
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { computed, ref, watch } from "vue";
import _ from "lodash";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import InputText from "primevue/inputtext";

const props = defineProps({
  index: Number,
  modelValue: {
    type: Array,
    default: [],
  },
});

const searchInput = ref("");
const show = ref(false);
const emit = defineEmits(["update:modelValue"]);
const proxyValue = computed({
  get: () => props.modelValue,
  set: (value) => emit("update:modelValue", value),
});

const temp = ref([]);
const getFeaturesTable = () => {
  temp.value = [];
  proxyValue.value.forEach((x, i) => {
    if (!x.features) {
      return;
    }
    let lowerSearch = searchInput.value.toLowerCase();
    let features = x.features.filter((obj) =>
      obj.label.toLowerCase().includes(lowerSearch)
    );

    features.forEach((e) => {
      let gIndex = temp.value.findIndex((t) => t.name == e.group);
      if (gIndex == -1) {
        temp.value.push({ name: e.group, show: true, features: [] });
        gIndex = temp.value.length - 1;
      }

      let index = temp.value[gIndex].features.findIndex((t) => t.key == e.key);
      if (index == -1) {
        temp.value[gIndex].features.push({ key: e.key, label: e.label, items: {} });
        index = temp.value[gIndex].features.length - 1;
      }
      temp.value[gIndex].features[index].items[i] = e.value;
    });
  });
};

watch(searchInput, () => {
  getFeaturesTable();
});

watch(props.modelValue, () => {
  getFeaturesTable();
  if (props.modelValue.length) {
    document.body.style.overflow = "auto";
  }
});

watch(show, () => {
  document.body.style.overflow = show.value ? "hidden" : "auto";
});

getFeaturesTable();
</script>
<style scoped>
.table tbody th {
  width: 20%;
}
.table tbody td {
  width: 20%;
}

.truncate-1-line {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 1;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 1.5em; /* or a fixed px value, e.g. 24px */
  height: calc(1.5em * 1); /* 3 lines × line-height */
}
</style>
