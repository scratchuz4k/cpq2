<script setup>
import { Skeleton } from "primevue";
const props = defineProps({
  showImage: {
    default: true,
    type: Boolean,
  },
});
</script>
<template>
  <div
    class="rounded border border-surface-200 dark:border-surface-700 p-4 mb-3 bg-surface-0 dark:bg-surface-900"
  >
    <div class="row">
      <div class="col-2 d-flex justify-content-center" v-if="props.showImage">
        <Skeleton width="100px" height="100px" class="mr-2"></Skeleton>
      </div>
      <div class="col">
        <div class="d-flex w-100 justify-content-between mb-2">
          <Skeleton width="200px" height="25px"></Skeleton>
          <div class="d-flex justify-content-end">
            <Skeleton width="100px" class="me-2" height="25px"></Skeleton>
            <Skeleton width="100px" height="25px"></Skeleton>
          </div>
        </div>
        <Skeleton width="500px" class="mb-2"></Skeleton>
        <div class="pt-3">
          <Skeleton width="100px" class="mb-2"></Skeleton>
          <div class="d-flex align-items-center mb-2">
            <Skeleton width="150px" height="25px" class="me-2"></Skeleton>

            <Skeleton width="50px" height="25px" class="ms-2"></Skeleton>
            <Skeleton width="50px" height="25px" class="ms-2"></Skeleton>
            <Skeleton width="50px" height="25px" class="ms-2"></Skeleton>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
