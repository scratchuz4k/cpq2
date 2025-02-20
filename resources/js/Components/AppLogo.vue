<template>
  <img v-if="props.model.tenant?.logo?.route" :src="props.model.tenant?.logo?.full_route" :alt="props.model.tenant?.name" class="img-fluid" />
</template>
<script setup>
import { usePage } from "@inertiajs/vue3";

const { props } = usePage();

</script>
