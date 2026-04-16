import { onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

export const defaultColors = {
    header: 'AAAAAA',
    navbar: 'EEEEEE',
    footer: 'AAAAAA'
};

const contextLayout = (key) => {
    switch (key) {
        case 'shared-link':
            return 'shareable';
        case 'quote-approval':
            return 'customer_approval';
        default:
            return 'app';
    }
};
export const useColors = () => {
    const page = usePage();
    const context = contextLayout(page.props.shareableType);
    const colors = page.props.customization.colors?.[context];

    function applyColors(colors) {
        if (!colors) colors = defaultColors;

        const root = document.documentElement;

        Object.entries(colors).forEach(([key, value]) => {
            root.style.setProperty(`--color-${key}`, `#${value}`);
        });
    }

    onMounted(() => applyColors(colors));
    watch(() => colors, applyColors);
};
