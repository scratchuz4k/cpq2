<template>
    <div :class="props.class">
        <ul class="tabs">
            <li
                class="tab-item"
                v-for="([identifier, tabData], index) in tabs"
                :key="identifier"
                :class="[activeTab == identifier ? 'active ' : '']"
                style="cursor: pointer"
            >
                <button
                    type="button"
                    class="tab-link fw-semibold"
                    :id="props.id ? props.id + '-' + index + '-' + index : index + '-' + index"
                    @click.prevent="setActiveTab(identifier)"
                    v-html="tabData.name"
                ></button>
            </li>
        </ul>
    </div>
    <div class="tab-body">
        <slot></slot>
    </div>
</template>

<script>
import { defineComponent } from 'vue';
import { useTabs } from '@composables/tabs';

export default defineComponent({
    props: {
        class: {
            required: false,
            default: []
        },
        id: {
            required: false,
            default: null
        }
    },
    name: 'Tabs',
    setup(props) {
        const { tabs, setActiveTab, activeTab } = useTabs();

        return {
            tabs,
            props,
            setActiveTab,
            activeTab
        };
    }
});
</script>
<style scoped>
.tabs {
    display: flex;
    flex-wrap: wrap;
    padding-left: 0;
    margin-bottom: 0;
    list-style: none;
}
.tab-item {
    padding: 4px 12px;
    border-bottom: 2px solid #a7a7a7;
}

.tab-item.active {
    border-bottom: 4px solid var(--color-purple);
}

.tab-link {
    border: 0;
    background-color: transparent;
}

.tab-item.active > .tab-link {
    color: var(--color-purple) !important;
}
</style>
