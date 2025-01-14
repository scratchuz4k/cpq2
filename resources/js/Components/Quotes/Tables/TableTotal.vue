<script setup>
import { getLeafNodesWithChildren } from "@composables/useQuote";
const props = defineProps({
  quote: Object,
});

const pricePlanskeys = { "One-Off": "SINGLE", Recurring: "MONTHLY" };
const quoteTotal = (price_plan, column = "total") => {
  let total = 0;
  let items = getLeafNodesWithChildren(props.quote.items);
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
</script>
<template>
  <div
    class="mobile-price-group-heading d-flex justify-content-between align-items-center"
  >
    <h3>Totals</h3>
  </div>
  <table class="table-dealmaker table table-bordered text-end">
    <thead>
      <tr>
        <th></th>
        <th>NET</th>
        <th>VAT (20%)</th>
        <th>GROSS</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <th><p class="small text-start">Monthly Total</p></th>
        <td id="total_monthly_net">
          {{ GBP.format(quoteTotal("Recurring")) }}
        </td>
        <td id="total_monthly_vat">
          {{ GBP.format(quoteTotal("Recurring", "total_tax")) }}
        </td>
        <td id="total_monthly_gross">
          {{ GBP.format(quoteTotal("Recurring", "total_gross")) }}
        </td>
      </tr>
      <tr>
        <th><p class="small text-start">One Off Total</p></th>
        <td id="total_net">
          {{ GBP.format(quoteTotal("One-Off")) }}
        </td>
        <td id="total_vat">
          {{ GBP.format(quoteTotal("One-Off", "total_tax")) }}
        </td>
        <td id="total_gross">
          {{ GBP.format(quoteTotal("One-Off", "total_gross")) }}
        </td>
      </tr>
      <tr>
        <th><p class="small text-start">Total</p></th>
        <td id="total_net">
          {{ GBP.format(quote.total) }}
        </td>
        <td id="total_vat">
          {{ GBP.format(quote.total_vat) }}
        </td>
        <td id="total_gross">
          {{ GBP.format(quote.total_gross) }}
        </td>
      </tr>
    </tbody>
  </table>
</template>
