import { ref } from 'vue';
import queryString from 'qs';
import axios from 'axios';

export const active_steps = ref([]);
export const loading = ref(true);
export const currentStep = ref(0);
export const initSteps = ['StepAccount', 'StepSolutionType'];
export const steps = ref(initSteps);

export const isDisabled = ref(false);
export const actionBarButtons = ref([]);

export const getStepsWithProducts = async (solution_type) => {
    if (!solution_type.steps.length) {
        loading.value = false;
        return;
    }

    loading.value = true;
    let query = queryString.stringify({
        limit: 200,
        filters: [
            {
                column: 'id',
                operator: 'in',
                value: solution_type.steps.map((x) => x.id).join(',')
            },
            {
                column: 'products.id',
                operator: 'not_equals',
                value: ''
            },
            {
                column: 'is_active',
                operator: 'equals',
                value: 1
            }
        ]
    });
    let res = await axios.get('/api/steps' + '?' + query);

    const steps_order = solution_type.steps.map((x) => x.id);

    active_steps.value = res.data.data.sort((a, b) => {
        return steps_order.indexOf(a.id) - steps_order.indexOf(b.id);
    });
    loading.value = false;
};
