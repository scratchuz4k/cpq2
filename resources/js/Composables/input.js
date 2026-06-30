const clamp = (value, min, max) => Math.min(Math.max(Number(value), min), max);

export const checkValue = (e) => {
    const target = e.target ? e.target : e.originalEvent.target;
    const min = target.min ? Number(target.min) : 0;
    const max = target.max ? Number(target.max) : 99999999;
    const value = parseFloat(target.value.replace(',', '.'));

    const clamped = clamp(value, min, max);

    if (clamped !== value) {
        target.value = !isNaN(clamped) ? clamped : min;
        target.dispatchEvent(new Event('input'));
    }
};

export const integerOnly = (e) => {
    const allowedKeys = ['Backspace', 'Delete', 'ArrowLeft', 'ArrowRight', 'Tab', 'Home', 'End'];
    if (!/^\d$/.test(e.key) && !allowedKeys.includes(e.key)) {
        e.preventDefault();
    }
};

export const checkLength = (e) => {
    if (e.target.maxLength < 0) return;

    if (e.target.value.length > e.target.maxLength) {
        e.target.value = e.target.value.slice(0, e.target.maxLength);
    }
};
