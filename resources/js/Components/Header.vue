<template>
    <div class="d-flex justify-content-between align-items-center pb-3" style="border-bottom: 1px solid lightgray; ">
        <h2 class="text-4xl font-bold m-0">
            {{ title }}
        </h2>
        <div>
            <slot name="buttons"></slot>
        </div>
    </div>
    <div class="mb-4">
        <Breadcrumb :model="breadcrumbs">
            <template #item="{ item, props }">
                <a v-if="item.url" :href="item.url" :target="item.target" v-bind="props.action">
                    <span class="text-surface-700 dark:text-surface-0">{{ item.label }}</span>
                </a>
                <p v-else class="text-surface-700 dark:text-surface-0 m-0" :href="item.url">
                    {{ item.label }}
                </p>
            </template>
        </Breadcrumb>
    </div>
</template>
<script setup>
import Breadcrumb from 'primevue/breadcrumb';

defineProps({
    title: String,
    breadcrumbs: Array
})
</script>
