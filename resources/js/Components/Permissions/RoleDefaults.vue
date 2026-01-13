<script setup>
import { ref, watch, computed } from 'vue';

const props = defineProps({
    modelValue: Array
});
const emit = defineEmits(['default:add']);

const defaults = ref([
    {
        name: 'Quotes',
        permissions: [
            'viewOwn quote',
            'view quote',
            'create quote',
            'update quote',
            'delete quote',
            'restore quote',
            'forceDelete quote',
            'export quote',
            'viewAny quote_item',
            'view quote_item',
            'create quote_item',
            'update quote_item',
            'delete quote_item',
            'restore quote_item',
            'forceDelete quote_item',
            'export quote_item',
            'viewAny product',
            'view product',
            'viewAny category',
            'view category',
            'viewAny step',
            'view step',
            'viewAny price_plan',
            'view price_plan',
            'viewAny price',
            'view price',
            'viewAny account',
            'view account',
            'viewAny spa',
            'view spa',
            'viewAny activity',
            'view activity'
        ]
    },
    {
        name: 'Users',
        permissions: [
            'viewAny user',
            'view user',
            'create user',
            'update user',
            'delete user',
            'restore user',
            'forceDelete user',
            'export user',
            'viewAny role',
            'view role',
            'viewAny permission',
            'view permission',
            'viewAny notification',
            'view notification',
            'create notification',
            'update notification',
            'delete notification',
            'restore notification',
            'forceDelete notification',
            'export notification'
        ]
    }
]);
</script>

<template>
    <div class="row row-cols-3">
        <div class="col" v-for="def in defaults" :key="def.name">
            <PrimaryButton class="w-100" @click="emit('default:add', def)">{{ def.name }}</PrimaryButton>
        </div>
    </div>
</template>
<style scoped>
ul {
    list-style: none;
}
.tree-childs,
.tree-childs li {
    position: relative;
}
.tree-childs:before {
    content: '';
    position: absolute;
    top: 0;
    left: 1.5rem;
    height: 100%;
    width: 1px;
    background: var(--color-secondary);
    overflow: hidden;
}
.tree-childs li:before {
    content: '';
    position: absolute;
    top: 0.675rem;
    left: -1.5rem;
    height: 1px;
    width: 1rem;
    background: var(--color-secondary);
    overflow: hidden;
}
</style>
