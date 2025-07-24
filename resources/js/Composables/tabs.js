import { inject, ref, computed, reactive, onUnmounted, provide, readonly } from 'vue';

const tabsInjectionKey = Symbol('tabs');

export const useTabs = () => {
    const tabs = reactive(new Map());

    const registerTab = (identifier, tabData) => {
        tabs.set(identifier, tabData);
    };

    const deregisterTab = (identifier) => {
        tabs.delete(identifier);
    };

    const activeTab = ref();

    const setActiveTab = (identifier) => {
        activeTab.value = identifier;
    };

    provide(tabsInjectionKey, {
        registerTab,
        deregisterTab,
        setActiveTab,
        activeTab: readonly(activeTab)
    });

    return {
        tabs: readonly(tabs),
        setActiveTab,
        activeTab
    };
};

export const useTab = (tabData, selected) => {
    const tabsInjection = inject(tabsInjectionKey);

    if (!tabsInjection) {
        throw new Error('Tabs were not provided');
    }

    const { registerTab, deregisterTab, activeTab, setActiveTab } = tabsInjection;

    const tabSymbol = Symbol(tabData.name);

    registerTab(tabSymbol, tabData);

    onUnmounted(() => {
        deregisterTab(tabSymbol);
    });

    if (selected) {
        setActiveTab(tabSymbol);
    }

    const is_active = computed(() => activeTab.value === tabSymbol);

    return {
        is_active
    };
};
