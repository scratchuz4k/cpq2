<template>
  <div
    :style="{ height: show ? '100%' : '80px' }"
    style="position: fixed; bottom: 0; left: 0; right: 0; background: white; z-index: 999"
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
        <div class="row">
          <div v-for="(item, index) in proxyValue" class="col-3">
            <div class="d-flex align-items-center">
              <img
                :src="item.primary_image"
                style="object-fit: contain"
                :style="{
                  width: '80px',
                  height: '75px',
                }"
              />
              <div class="mx-2 w-100">
                <p class="big bold">
                  {{ item.model }}
                </p>
                <p>{{ item.sku }}</p>
              </div>
              <div class="mx-3">
                <font-awesome-icon
                  :icon="['fas', 'trash']"
                  @click="() => proxyValue.splice(index, 1)"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
      <div
        class="col-1 d-flex align-items-center justify-content-end"
        @click="() => (show = !show)"
      >
        <font-awesome-icon
          class="w-100"
          v-if="!show"
          style="font-size: 50px; color: var(--color-purple)"
          :icon="['fas', 'chevron-up']"
        />
        <font-awesome-icon
          class="w-100"
          v-else
          style="font-size: 50px; color: var(--color-purple)"
          :icon="['fas', 'chevron-down']"
        />
      </div>
    </div>
    <div
      class="row p-3"
      style="overflow-y: scroll; overflow-x: hidden; height: calc(100% - 80px)"
    >
      <div class="col">
        <div class="panel h-100" style="border: 2px solid var(--color-purple)">
          <p class="bold">General</p>
          <table class="table">
            <tbody>
              <tr>
                <th>Sku</th>
                <td v-if="proxyValue.length >= 1">{{ proxyValue[0].sku }}</td>
                <td v-if="proxyValue.length >= 2">{{ proxyValue[1].sku }}</td>
                <td v-if="proxyValue.length >= 3">{{ proxyValue[2].sku }}</td>
                <td v-if="proxyValue.length >= 4">{{ proxyValue[3].sku }}</td>
              </tr>
              <tr>
                <th>Model</th>
                <td v-if="proxyValue.length >= 1">{{ proxyValue[0].model }}</td>
                <td v-if="proxyValue.length >= 2">{{ proxyValue[1].model }}</td>
                <td v-if="proxyValue.length >= 3">{{ proxyValue[2].model }}</td>
                <td v-if="proxyValue.length >= 4">{{ proxyValue[3].model }}</td>
              </tr>
              <tr>
                <th>Stock</th>
                <td v-if="proxyValue.length >= 1">{{ proxyValue[0].stock }}</td>
                <td v-if="proxyValue.length >= 2">{{ proxyValue[1].stock }}</td>
                <td v-if="proxyValue.length >= 3">{{ proxyValue[2].stock }}</td>
                <td v-if="proxyValue.length >= 4">{{ proxyValue[3].stock }}</td>
              </tr>
            </tbody>
          </table>
          <p class="mt-5 bold">Characteristics</p>
          <table class="table">
            <tbody></tbody>
          </table>
          <p class="mt-5 bold">Prices</p>
          <hr />
          <table class="table">
            <tbody>
              <tr>
                <th>Net</th>
                <td v-if="proxyValue.length >= 1">
                  {{ GBP.format(proxyValue[0].price.net_spa) }}
                </td>
                <td v-if="proxyValue.length >= 2">
                  {{ GBP.format(proxyValue[1].price.net_spa) }}
                </td>
                <td v-if="proxyValue.length >= 3">
                  {{ GBP.format(proxyValue[2].price.net_spa) }}
                </td>
                <td v-if="proxyValue.length >= 4">
                  {{ GBP.format(proxyValue[3].price.net_spa) }}
                </td>
              </tr>
              <tr>
                <th>Gross</th>
                <td v-if="proxyValue.length >= 1">
                  {{ GBP.format(proxyValue[0].price.gross) }}
                </td>
                <td v-if="proxyValue.length >= 2">
                  {{ GBP.format(proxyValue[1].price.gross) }}
                </td>
                <td v-if="proxyValue.length >= 3">
                  {{ GBP.format(proxyValue[2].price.gross) }}
                </td>
                <td v-if="proxyValue.length >= 4">
                  {{ GBP.format(proxyValue[3].price.gross) }}
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
import { computed, ref } from "vue";
import _ from "lodash";
const props = defineProps({
  index: Number,
  modelValue: Object,
});

const show = ref(false);
const emit = defineEmits(["update:modelValue"]);
const proxyValue = computed({
  get: () => props.modelValue,
  set: (value) => emit("update:modelValue", value),
});
</script>
<style>
.truncate-2-lines {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 2;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 2em; /* or a fixed px value, e.g. 24px */
  height: calc(2em * 2); /* 3 lines × line-height */
}

.truncate-3-lines {
  display: -webkit-box;
  -webkit-box-orient: vertical;
  -webkit-line-clamp: 3;
  overflow: hidden;
  text-overflow: ellipsis;
  line-height: 2em; /* or a fixed px value, e.g. 24px */
  height: calc(2em * 3); /* 3 lines × line-height */
  font-size: 14px !important;
}
.table tbody th,
.table tbody td {
  width: 20%;
}
</style>
