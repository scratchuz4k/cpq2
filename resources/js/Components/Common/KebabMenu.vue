<script setup>
// Importing Dropdown registers Bootstrap's dropdown data API (data-bs-toggle="dropdown")
import { Dropdown } from 'bootstrap';
import { onBeforeUnmount, ref } from 'vue';

defineProps({
    id: String,
    // [{ id, label, icon, danger, onClick }]
    actions: {
        type: Array,
        default: () => []
    }
});

const button = ref(null);

onBeforeUnmount(() => {
    Dropdown.getInstance(button.value)?.dispose();
});
</script>
<template>
    <div class="dropdown kebab-menu" v-if="actions.length">
        <button
            :id="id"
            ref="button"
            class="btn btn-kebab"
            type="button"
            data-bs-toggle="dropdown"
            aria-expanded="false"
            aria-label="Actions"
        >
            <font-awesome-icon :icon="['fas', 'ellipsis-vertical']" />
        </button>
        <ul class="dropdown-menu dropdown-menu-end">
            <li v-for="action in actions" :key="action.label">
                <button
                    :id="action.id"
                    class="dropdown-item"
                    :class="{ 'text-danger': action.danger }"
                    type="button"
                    @click="action.onClick"
                >
                    <font-awesome-icon v-if="action.icon" class="me-2" :icon="action.icon" />
                    {{ action.label }}
                </button>
            </li>
        </ul>
    </div>
</template>
