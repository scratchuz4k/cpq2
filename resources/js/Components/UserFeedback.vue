<template>
    <button type="button" :class="class" :disabled="disabled || opening" @click="openFeedback">
        <slot>{{ label }} <font-awesome-icon class="mb-1" :icon="['fas', 'caret-left']" /></slot>
    </button>
</template>

<script setup>
import { computed, ref } from 'vue';
import * as Sentry from '@sentry/vue';

const props = defineProps({
    label: {
        type: String,
        default: 'Feedback'
    },
    class: {
        type: [String, Array, Object],
        default: 'btn-sentry-user-feedback'
    },
    disabled: {
        type: Boolean,
        default: false
    }
});

const opening = ref(false);

async function openFeedback() {
    if (opening.value || props.disabled) return;
    opening.value = true;
    try {
        const feedback = Sentry.getFeedback?.();
        if (!feedback) return;
        const form = await feedback.createForm();
        form.appendToDom();
        form.open();
    } finally {
        opening.value = false;
    }
}
</script>
<style>
.btn-sentry-user-feedback {
    position: fixed;
    top: 50%;
    right: -0.25rem;
    transform: translateY(-50%);
    z-index: 1000;
    padding: 0.5rem 0.5rem 0.5rem 0.25rem;
    background-color: var(--secondary-dark);
    border: 0;
    color: #fff;
    font-weight: 600;
    border-radius: var(--radius) 0 0 var(--radius);
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition:
        background-color 0.2s ease,
        right 0.2s ease;

    writing-mode: sideways-lr;
    text-orientation: mixed;
}

.btn-sentry-user-feedback:hover {
    background-color: var(--secondary-dark-lighter);
    right: 0;
}

#sentry-feedback {
    --font-family: var(--font-primary);
    --button-primary-background: var(--color-purple);
    --button-primary-hover-background: var(--color-purple-dark);
    --dialog-inset: auto 0 auto auto;
    --page-margin: 0;
}
</style>
