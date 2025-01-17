<template>
  <img v-if="props.tenant?.logo?.route" :src="props.tenant?.logo?.full_route" alt="Sample Logo" class="img-fluid" />
</template>
<script setup>
import { usePage } from "@inertiajs/vue3";

const { props } = usePage();

</script>
