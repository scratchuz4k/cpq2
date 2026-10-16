<template>
    <!-- Below lg the buttons drop under the title instead of squeezing it. -->
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center pb-1">
        <div class="d-flex justify-content-between" style="justify-content: space-between">
            <h1 id="page-title" class="m-0 font-bold text-break truncate-2-lines">{{ title }}</h1>
            <div class="d-block my-auto ms-3">
                <slot name="badge"></slot>
            </div>
        </div>
        <div class="page-header-buttons d-flex flex-wrap mt-2 mt-lg-0">
            <slot name="buttons"></slot>
        </div>
    </div>
    <div class="my-2">
        <Breadcrumb :model="breadcrumbs">
            <template #item="{ item, props }">
                <a v-if="item.url && item.url !== '#'" :href="item.url" :target="item.target" v-bind="props.action">
                    <span class="text-surface-700 dark:text-surface-0">{{ item.label }}</span>
                </a>
                <p v-else class="text-surface-700 dark:text-surface-0 m-0 font-bold" :href="item.url">
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
});
</script>
<style>
.font-bold {
    font-weight: bold;
}
.page-header-buttons {
    row-gap: 0.5rem;
}

/* No buttons passed (or an empty/v-if'd slot - comments don't count): drop the stacked-layout gap. */
.page-header-buttons:empty {
    display: none !important;
}

#page-title.truncate-2-lines {
    line-height: 48px;
    height: auto;
}
</style>
