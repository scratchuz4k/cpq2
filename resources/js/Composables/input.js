const clamp = (value, min, max) => Math.min(Math.max(Number(value), min), max);

export const checkValue = (e) => {
    const { target } = e.originalEvent;
    const min = target.min ? target.min : 0;
    const max = target.max ? target.max : 99999999;

    target.value = clamp(target.value, min, max);
};
