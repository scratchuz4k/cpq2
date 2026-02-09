import { ref } from 'vue';

export const currentStep = ref(0);
export const initSteps = ['StepAccount', 'StepSolutionType'];
export const steps = ref(initSteps);

export const isDisabled = ref(true);

export const actionBarButtons = ref([]);
