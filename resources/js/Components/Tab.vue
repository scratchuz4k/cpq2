<template>
    <div v-if="is_active" class="container-fluid">
        <slot />
    </div>
</template>

<script>
import { defineComponent, toRef, reactive } from 'vue';
import { useTab } from '@composables/tabs';

export default defineComponent({
    name: 'Tab',
    props: {
        title: {
            type: String,
            required: true
        },
        selected: {
            type: [Boolean, Number],
            required: false,
            default: false
        },
        style: {
            type: String,
            required: false,
            default: ''
        }
    },
    setup(props) {
        const name = toRef(props, 'title');
        const style = toRef(props, 'style');

        const tabData = reactive({ name, style });
        const { is_active } = useTab(tabData, props.selected);

        return {
            is_active
        };
    }
});
</script>
