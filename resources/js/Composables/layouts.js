import { onMounted, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import _ from 'lodash';

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

const isLightColor = (color) => {
    let r, g, b;

    // Check the format of the color, HEX or RGB?
    if (color.match(/^rgb/)) {
        // If HEX --> store the red, green, blue values in separate variables
        const rgb = color.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+(?:\.\d+)?))?\)$/);

        r = rgb[1];
        g = rgb[2];
        b = rgb[3];
    } else {
        color = color.replace(/^\s*#|\s*$/g, '');

        // Convert 3 char codes -> 6, e.g. `E0F` -> `EE00FF`
        if (color.length === 3) {
            color = color.replace(/(.)/g, '$1$1');
        }
        // If RGB --> Convert it to HEX: http://gist.github.com/983661
        const hexColor = +('0x' + color.slice(1).replace(color.length < 5 && /./g, '$&$&'));

        r = hexColor >> 16;
        g = (hexColor >> 8) & 255;
        b = hexColor & 255;
    }

    // HSP equation from http://alienryderflex.com/hsp.html
    const hsp = Math.sqrt(0.299 * (r * r) + 0.587 * (g * g) + 0.114 * (b * b));

    return hsp > 127.5;
};
export const useColors = (initialPageProps) => {
    const context = contextLayout(initialPageProps?.shareableType);
    const colors = initialPageProps?.customization?.colors?.[context];

    if (!colors) return;
    function applyColors(colors) {
        const root = document.documentElement;

        Object.entries(colors).forEach(([key, value]) => {
            root.style.setProperty(`--color-${key}`, `#${value}`);

            if (isLightColor(value)) {
                root.style.setProperty(`--color-${key}-text`, `#000`);
            }
        });
    }
    applyColors(colors);
};
