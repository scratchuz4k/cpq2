import { reactive, ref } from 'vue';

export function useDatatableTooltip() {
    const showTooltip = ref(false);
    const tooltipValues = reactive({ items: [], keys: [] });
    const tooltipPosition = ref({ top: 0, left: 0 });

    const openTooltip = (ev, object) => {
        const indicator = ev.target.closest('.tooltip-indicator');
        const rect = (indicator ?? ev.target).getBoundingClientRect();

        tooltipPosition.value = {
            top: rect.bottom + window.scrollY,
            left: rect.left + window.scrollX + rect.width / 2
        };

        showTooltip.value = true;
        tooltipValues.items = object.items.slice(0, 10);
        tooltipValues.keys = object.key.join('.');
    };

    const hideTooltip = () => {
        showTooltip.value = false;
    };

    return { showTooltip, tooltipValues, tooltipPosition, openTooltip, hideTooltip };
}
