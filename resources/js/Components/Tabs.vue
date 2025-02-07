<template>
  <div :class="props.class">
    <ul class="nav m-0 p-0">
      <li
        class="nav-item fw-semibold"
        v-for="([identifier, tabData], index) in tabs"
        :key="identifier"
        style="cursor: pointer; list-style: none"
      >
        <a
          class="nav-link"
          :class="[activeTab == identifier ? 'active ' : '']"
          :id="index + '-' + index"
          @click.prevent="setActiveTab(identifier)"
          v-html="tabData.name"
        ></a>
      </li>
    </ul>
  </div>
  <div class="tab-body pt-3">
    <slot></slot>
  </div>
</template>

<script>
import { defineComponent } from "vue";
import { useTabs } from "@composables/tabs";

export default defineComponent({
  props: {
    class: {
      required: false,
      default: [],
    },
  },
  name: "Tabs",
  setup(props) {
    const { tabs, setActiveTab, activeTab } = useTabs();

    return {
      tabs,
      props,
      setActiveTab,
      activeTab,
    };
  },
});
</script>
<style scoped>
.nav-link {
  color: var(--white) !important;
  background-color: var(--color-purple);
  border-radius: 20px;
  margin-right: 10px;
}
.nav-link.active {
  color: var(--color-purple) !important;
  background-color: var(--color-purple-light);
}
</style>
