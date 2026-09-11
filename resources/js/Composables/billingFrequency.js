import { ref, computed, watch } from 'vue';

const dividers = (num) => {
    const n = num ?? 360;
    const divisors = [];

    for (let i = 1; i * i <= n; i++) {
        if (n % i === 0) {
            divisors.push(i);
            if (i !== n / i) {
                divisors.push(n / i);
            }
        }
    }

    return divisors.sort((a, b) => a - b);
};

export const daysPerUnit = {
    DAYS: 1,
    MONTHS: 30,
    YEARS: 360
};

export const frequencyUnitOptions = [
    { name: 'DAYS', value: 'Days' },
    { name: 'MONTHS', value: 'Months' },
    { name: 'YEARS', value: 'Years' }
];

/**
 * Encapsulates contract length / billing frequency logic shared between the Product Create and Edit pages.
 *
 * @param {object} product - product
 * @param {Array} frequencyUnits - frequency units enum values
 */
export function useBillingFrequency(product, frequencyUnits) {
    const contract_length = ref(
        product.renewal_terms_value
            ? product.renewal_terms_value * (daysPerUnit[product.renewal_terms_unit] ?? 0)
            : null
    );

    const calculateContractLengthDays = () => {
        if (!product.renewal_terms_unit || !product.renewal_terms_value) {
            contract_length.value = null;
            return;
        }

        contract_length.value = product.renewal_terms_value * (daysPerUnit[product.renewal_terms_unit] ?? 0);
        const billingUnitDays = daysPerUnit[product.billing_frequency_unit] ?? 0;
        const billingLengthDays = product.billing_frequency_value * billingUnitDays;
        const billingFrequencyIsValid = billingLengthDays > 0 && contract_length.value % billingLengthDays === 0;

        if (contract_length.value && !billingFrequencyIsValid) {
            product.billing_frequency_unit = product.renewal_terms_unit;
            product.billing_frequency_value = product.renewal_terms_value;
        }
    };

    const billingFrequencyUnits = computed(() => {
        if (!contract_length.value) {
            return [];
        }

        const units = frequencyUnits.value ?? frequencyUnits;

        return units.filter((unit) => {
            const unitDays = daysPerUnit[unit.name] ?? 0;

            return unitDays > 0 && contract_length.value % unitDays === 0;
        });
    });

    const billingFrequencyValues = computed(() => {
        if (!contract_length.value || !product.billing_frequency_unit) {
            return [];
        }

        const numOfDays = daysPerUnit[product.billing_frequency_unit] ?? 1;

        if (contract_length.value % numOfDays !== 0) {
            return [];
        }

        const values = contract_length.value / numOfDays;

        return dividers(values);
    });

    watch(
        () => product.price_plan_id,
        (newVal) => {
            if (newVal !== 1) {
                product.renewal_terms_unit = null;
                product.renewal_terms_value = null;
                product.billing_frequency_unit = null;
                product.billing_frequency_value = null;
            }
        }
    );

    watch(
        [() => product.renewal_terms_unit, () => product.renewal_terms_value],
        () => {
            calculateContractLengthDays();
        },
        { immediate: true }
    );

    return {
        contract_length,
        calculateContractLengthDays,
        billingFrequencyUnits,
        billingFrequencyValues
    };
}
