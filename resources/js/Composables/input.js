const clamp = (value, min, max) => Math.min(Math.max(Number(value), min), max);

export const checkValue = (e) => {
    const target = e.target ? e.target : e.originalEvent.target;
    const min = target.min ? Number(target.min) : 0;
    const max = target.max ? Number(target.max) : 99999999;
    const temp_value = target.value ?? target.target.value;
    const value = parseFloat(temp_value.replace(',', '.'));

    const clamped = clamp(value, min, max);

    if (clamped !== value) {
        target.value = !isNaN(clamped) ? clamped : min;
        target.dispatchEvent(new Event('input'));
    }
};

export const integerOnly = (e) => {
    if (['e', 'E', '+', '-', '.', ','].includes(e.key)) e.preventDefault();
};
