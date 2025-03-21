<template>
  <img v-if="tenant?.logo?.route" :src="tenant?.logo?.full_route" :alt="tenant?.name" class="img-fluid" />
</template>
<script setup>
import { usePage } from "@inertiajs/vue3";

const { props } = usePage();
let tenant = props.model?.tenant ?? props.tenant

</script>
