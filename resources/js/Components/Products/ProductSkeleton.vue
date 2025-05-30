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
<div class="mobile-item mobile-product p-4 mb-3">
    <div class="row">
        <div class="col-2">
          <Skeleton width="100%" height="8.5rem" />
        </div>
        <div class="col-10">
          <div class="d-flex w-100 justify-content-between">
            <Skeleton width="10rem" height="1.5rem" />
            <div class="d-flex justify-content-end">
              <Skeleton width="5rem" height="1.5rem" class="me-2" />
              <Skeleton width="5rem" height="1.5rem" />
            </div>
          </div>
          <Skeleton width="100%" height="1rem" class="mt-2" />
          <Skeleton width="100%" height="1rem" class="mt-1 mb-2" />
          <div class="pt-2 pb-2">
            <Skeleton width="5rem" height="1.5rem" class="mb-1" />
            <div class="d-flex align-items-center">
              <Skeleton width="9rem" height="1.7rem" class="me-3" />
              <Skeleton width="3.5rem" height="1.7rem" borderRadius="16px" class="me-2" />
              <Skeleton width="3.5rem" height="1.7rem" borderRadius="16px" class="me-2" />
              <Skeleton width="3.5rem" height="1.7rem" borderRadius="16px" />
            </div>
          </div>
        </div>
      </div>
    </div>
</template>
