const clamp = (value, min, max) => Math.min(Math.max(Number(value), min), max);

export const checkPriceValue = (e) => {
    const { target } = e.originalEvent;
    target.value = clamp(target.value, 0, 99999999);
};

export const checkValue = (e) => {
    const { target } = e;
    target.value = clamp(target.value, target.min, target.max);
};
