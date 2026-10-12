import { computed, onUnmounted, ref } from 'vue';
import { getQuote, quote } from '@composables/useQuote';
import { can, hasRole } from '@/Composables/useAuth';

export const canViewDiscounts = () => can('viewAny quote_discount') || hasRole('Super Admin');
export const canUpdateDiscounts = () => can('update quote_discount') || hasRole('Super Admin');
export const canDeleteDiscounts = () => can('delete quote_discount') || hasRole('Super Admin');

/**
 * Inline edit / delete of a quote discount (one-off, billing frequency or quote discount).
 *
 * @param {Function} find - returns the existing discount from quote.discounts, if any
 * @param {Function} payload - base payload for creating / updating the discount
 */
export function useQuoteDiscount(find, payload) {
    const discount = computed(() => find() ?? null);
    const isEditing = ref(false);
    const isLoading = ref(false);
    const temp = ref(0);

    const edit = () => {
        temp.value = discount.value?.discount ?? 0;
        emitter.emit('quote-items:toggle:is-editing');
        isEditing.value = true;
    };

    const cancel = () => {
        isEditing.value = false;
    };

    const save = async () => {
        isLoading.value = true;
        let data = { ...payload(), quote_id: quote.id, discount: temp.value };

        try {
            if (discount.value) {
                await axios.put(`/api/quote-discounts/${discount.value.id}`, data);
            } else {
                await axios.post(`/api/quote-discounts`, data);
            }

            await getQuote(quote.id);
            isEditing.value = false;
        } catch (err) {
            notyf.error(err?.response?.data?.message ?? 'Something went wrong');
        } finally {
            isLoading.value = false;
        }
    };

    const destroy = async () => {
        let result = await swal.fire({
            title: 'Are you sure?'
        });

        if (['cancel', 'backdrop'].includes(result.dismiss) || !discount.value) {
            return;
        }

        try {
            await axios.delete(`/api/quote-discounts/${discount.value.id}`);
            await getQuote(quote.id);
            notyf.success('Removed with success');
        } catch (err) {
            notyf.error(err?.response?.data?.message ?? 'Something went wrong');
        }
    };

    // only one row (item or discount) is edited at a time
    const stopEditing = () => (isEditing.value = false);
    emitter.on('quote-items:toggle:is-editing', stopEditing);
    onUnmounted(() => emitter.off('quote-items:toggle:is-editing', stopEditing));

    return { discount, isEditing, isLoading, temp, edit, cancel, save, destroy };
}
