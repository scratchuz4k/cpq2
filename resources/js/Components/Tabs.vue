<template>

    <div :class="props.class">
        <ul class="nav nav-tabs">
            <li class="nav-item" v-for="([identifier, tabData], index) in tabs" :key="identifier" style="cursor: pointer;">
                <a class="nav-link" :class="[(activeTab == identifier) ? 'active ' : '']" :id="index + '-' + index"
                    @click.prevent="setActiveTab(identifier)" v-html="tabData.name"></a>
            </li>
        </ul>
    </div>
    <div class="tab-body">
        <slot></slot>
    </div>
</template>

<script>

import { defineComponent } from 'vue'
import { useTabs } from '@composables/tabs'

export default defineComponent({
    props: {
        class: {
            required: false,
            default: []
        }
    },
    name: 'Tabs',
    setup(props) {
        const { tabs, setActiveTab, activeTab } = useTabs()

        return {
            tabs,
            props,
            setActiveTab,
            activeTab
        }
    }
})

</script>

<style scoped>
.active{
    color:white!important;
    background-color: var(--primary-light)!important;
    font-weight: bold;
}</style>
