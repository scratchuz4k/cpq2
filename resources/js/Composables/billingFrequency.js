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

const daysPerUnit = {
    DAYS: 1,
    MONTHS: 30,
    YEARS: 360
};

/**
 * Encapsulates contract length / billing frequency logic shared between the Product Create and Edit pages.
 *
 * @param {object} product - product
 * @param {Array} frequencyUnits - frequency units enum values
 */
export function useBillingFrequency(product, frequencyUnits) {
    const contract_length = ref(
        product.contract_term_value
            ? product.contract_term_value * (daysPerUnit[product.contract_term_unit] ?? 0)
            : null
    );

    const calculateContractLengthDays = () => {
        if (!product.contract_term_unit || !product.contract_term_value) {
            contract_length.value = null;
            return;
        }

        contract_length.value = product.contract_term_value * (daysPerUnit[product.contract_term_unit] ?? 0);
        if (contract_length.value) {
            product.billing_frequency_unit = product.contract_term_unit;
            product.billing_frequency_value = product.contract_term_value;
        }
    };

    const billingFrequencyUnits = computed(() => {
        if (!contract_length.value) {
            return [];
        }

        const units = frequencyUnits.value ?? frequencyUnits;

        return units.filter((unit) => contract_length.value >= (daysPerUnit[unit.name] ?? 0));
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
                product.contract_term_unit = null;
                product.contract_term_value = null;
                product.billing_frequency_unit = null;
                product.billing_frequency_value = null;
            }
        }
    );

    watch([() => product.contract_term_unit, () => product.contract_term_value], () => {
        calculateContractLengthDays();
    });

    return {
        contract_length,
        calculateContractLengthDays,
        billingFrequencyUnits,
        billingFrequencyValues
    };
}
