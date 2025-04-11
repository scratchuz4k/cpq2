<script setup>
import { defineEmits, computed } from "vue";

const props = defineProps({
  modelValue: [Boolean, null]
});
const emit = defineEmits(["update:modelValue"]);

const sliderValue = computed({
  get: () => (props.modelValue === false ? 0 : props.modelValue === null ? 50 : 100),
  set: (newValue) => {
    let newState = newValue <= 35 ? false : newValue <= 65 ? null : true;
    emit("update:modelValue", newState);
  }
});

const handleClasses = computed(() => ({
    "handle-unchecked": props.modelValue === false,
    "handle-null": props.modelValue === null,
    "handle-checked": props.modelValue === true,
}));

</script>

<template>
  <div class="custom-range-container me-2">
    <div :class="['custom-range-handle', handleClasses]">
        <slot name="handle"></slot>
    </div>
    <input
      type="range"
      min="0"
      max="100"
      step="50"
      v-model="sliderValue"
      class="custom-range-input"
    />
  </div>
</template>

<style scoped>
.custom-range-container {
  position: relative;
  z-index: 1;
  width: 40px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 15px;
  transition: background-color 0.2s ease;
  background: #cbd5e1;;
}

.custom-range-container:hover:not(:has(.handle-checked)) {
    background: #94a3b8!important;
    border-color: transparent;
}

.custom-range-container:has(.handle-null) {
    background: var(--lighter-grey);
}
.custom-range-container:has(.handle-checked) {
    background:#18223c;
}

.custom-range-input {
  position: absolute;
  opacity: 0;
  width: 100%;
  height: 100%;
  cursor: pointer;
}

.custom-range-handle {
  position: absolute;
  z-index: 0;
  top: 50%;
  left: 10%;
  transform: translateY(-50%);
  width: 16px;
  height: 16px;
  border-radius: 50%;
  background-color: white;
  box-shadow: 0 0 3px rgba(0, 0, 0, 0.2);
  transition: left 0.2s ease, transform 0.2s ease;
  display: flex;
  justify-content: center;
  align-items: center;
}

/* Handle states */
.handle-checked {
  left: 90%;
  transform: translateX(-100%) translateY(-50%);
}


.handle-unchecked {
  left: 10%;
}

.handle-null {
  left: 50%;
  transform: translateX(-50%) translateY(-50%);
}

.custom-range-handle .text-primary {
  font-size: 16px;
}
</style>
