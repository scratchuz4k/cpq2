<template>
  <div class="d-flex w-100">
    <button
      :id="'btn-' + props.id + '-remove-1'"
      class="btn-quantity btn-minus-quantity"
      type="button"
      @click="() => proxyValue--"
      :disabled="!proxyValue"
    >
      <font-awesome-icon :icon="['fas', 'minus']" />
    </button>
    <input
      :id="'input-' + props.id + '-quantity'"
      type="number"
      class="input-quantity"
      v-model="proxyValue"
    />
    <button
      :id="'btn-' + props.id + '-add-1'"
      class="btn-quantity btn-add-quantity"
      type="button"
      @click="() => proxyValue++"
      :disabled="props.disabled"
    >
      <font-awesome-icon :icon="['fas', 'plus']" />
    </button>
  </div>
</template>
<script setup>
import { computed } from "vue";
const props = defineProps({
  id: String,
  modelValue: Number,
  disabled: Boolean,
});

const emit = defineEmits(["update:modelValue", "change"]);
const proxyValue = computed({
  get() {
    return props.modelValue;
  },
  set(val) {
    emit("update:modelValue", val);
    emit("change", val);
  },
});
</script>
<style scoped>
.input-quantity {
  width: 70px;
  height: 32px;
  padding: 8px 12px 8px 12px;
  gap: 8px;
  border-width: 1px;
  border-style: solid;
  border-color: #bdbdbd;
  text-align: center;
}

.btn-quantity {
  outline: none;
  border-style: solid;
  border-color: #bdbdbd;
}

.btn-minus-quantity {
  width: 36px;
  height: 32px;
  gap: 0px;
  border-radius: 6px 0px 0px 6px;
  background: #ffffff;
  border-width: 1px 0px 1px 1px;
  border-style: solid;
  border-color: #bdbdbd;
}

.btn-add-quantity {
  width: 36px;
  height: 32px;
  gap: 0px;
  border-radius: 0px 6px 6px 0px;
  background: #ffffff;
  border-width: 1px 1px 1px 0px;
}
</style>
