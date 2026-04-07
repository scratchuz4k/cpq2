const clamp = (value, min, max) => Math.min(Math.max(Number(value), min), max);

export const checkValue = (e) => {
    debugger;
    const target = e.target ? e.target : e.originalEvent.target;
    const min = target.min ? target.min : 0;
    const max = target.max ? target.max : 99999999;
    const value = target.value ?? target.target.value;

    target.value = clamp(!isNaN(value) ? value : min, min, max);
};
