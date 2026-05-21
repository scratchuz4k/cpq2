<script setup>
import { ref } from 'vue';

const props = defineProps({
    bundles: {
        type: Array,
        required: true
    }
});

const visible = ref(false);

const formatTerm = (item) => {
    const parts = [];
    if (item.sku) {
        parts.push(`P${item.sku}`);
    }
    if (item.contract_term_value && item.contract_term_unit) {
        parts.push(`P${item.contract_term_value}${item.contract_term_unit}`);
    }
    if (item.price_plan?.name) {
        parts.push(item.price_plan.name);
    }
    // parts.push('Qty 1');
    return parts.join(' · ');
};
</script>

<template>
    <div>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <p class="small text-muted bold mb-0">
                Bundle includes {{ bundles.length }} item<span v-if="bundles.length !== 1">s</span>
            </p>
        </div>

        <template v-if="visible">
            <div class="bundle-list">
                <div v-for="item in bundles" :key="item.id" class="py-1">
                    <p class="mb-0 small fw-semibold">{{ item.name }}</p>
                    <p class="mb-0 small text-muted">{{ formatTerm(item) }}</p>
                </div>
            </div>

            <a
                class="small fw-bold mt-2 d-inline-block"
                style="text-decoration: none; cursor: pointer"
                @click="visible = false"
            >
                Hide Items
            </a>
        </template>

        <a
            v-else
            class="small fw-bold mt-2 d-inline-block"
            style="text-decoration: none; cursor: pointer"
            @click="visible = true"
        >
            Show Items
        </a>
    </div>
</template>

<style scoped>
.bundle-list {
    max-height: 220px;
    overflow-y: auto;
}
</style>
