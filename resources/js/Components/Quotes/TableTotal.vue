<script setup>
const props = defineProps({
    quote: Object
});

const group = props.quote.solution_type?.table?.group ?? null;
const pricePlanskeys = { 'One-Off': 'SINGLE', Recurring: 'MONTHLY' };
const quoteTotal = (price_plan, column = 'total') => {
    let total = 0;
    let items = props.quote.items;

    if (group) {
        items = props.quote.items.flatMap((obj) => obj.items ?? obj);
    }
    items.forEach((e) => {
        if (e.children?.lenght) {
            total += e.children
                .filter((x) => x.price_plan && x.price_plan.code == pricePlanskeys[price_plan])
                .reduce((acc, item) => (acc += item[column]), 0);
        } else if (e.price_plan && e.price_plan.code == pricePlanskeys[price_plan]) {
            total += e[column];
        }
    });
    return total;
};
const quoteQuantityPrice = (price_plan, operation = 'simple') => {
    let total = 0;
    let tax = 0;
    let items = props.quote.items;

    if (group) {
        items = props.quote.items.flatMap((obj) => obj.items ?? obj);
    }
    items.forEach((e) => {
        if (e.price_plan && e.price_plan.code == pricePlanskeys[price_plan]) {
            total += e.price * e.quantity;
            tax += e.price * e.quantity * e.tax;
        }
    });
    if (operation == 'total') {
        return total + tax;
    }
    return total;
};
</script>
<template>
    <div class="mobile-price-group-heading d-flex justify-content-between align-items-center">
        <h3>Totals</h3>
    </div>
    <table class="table-dealmaker table table-bordered text-end">
        <thead>
            <tr>
                <th></th>
                <th>NET</th>
                <!-- <th>VAT (20%)</th> -->
                <th>GROSS</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th>
                    <p class="small text-start">Monthly Total</p>
                </th>
                <td id="total_monthly_net">
                    {{ format.GBP(quoteQuantityPrice('Recurring')) }}
                </td>
                <!-- <td id="total_monthly_vat">
                    {{ format.GBP(quoteQuantityPrice("Recurring", "tax")) }}
                </td> -->
                <td id="total_monthly_gross">
                    {{ format.GBP(quoteQuantityPrice('Recurring', 'total')) }}
                </td>
            </tr>
            <tr>
                <th>
                    <p class="small text-start">One Off Total</p>
                </th>
                <td id="total_net">
                    {{ format.GBP(quoteTotal('One-Off')) }}
                </td>
                <!-- <td id="total_vat">
                    {{ format.GBP(quoteTotal("One-Off", "total_tax")) }}
                </td> -->
                <td id="total_gross">
                    {{ format.GBP(quoteTotal('One-Off', 'total_gross')) }}
                </td>
            </tr>
            <tr>
                <th>
                    <p class="small text-start">Total</p>
                </th>
                <td id="total_net">
                    {{ format.GBP(quote.total) }}
                </td>
                <!-- <td id="total_vat">
                    {{ format.GBP(quote.total_vat ?? 0) }}
                </td> -->
                <td id="total_gross">
                    {{ format.GBP(quote.total_gross) }}
                </td>
            </tr>
        </tbody>
    </table>
</template>
