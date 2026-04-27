<script setup>
const props = defineProps({
    title: String
});
</script>

<template>
    <div class="separator col-12 py-5">
        <span class="row">
            <span class="col">
                <hr />
            </span>
            <span class="col-auto">
                <h2 class="m-0">{{ title }}</h2>
            </span>
            <span class="col">
                <hr />
            </span>
        </span>
    </div>
</template>

<style scoped>
hr {
    border-top: 2px solid var(--light-grey);
}
</style>
