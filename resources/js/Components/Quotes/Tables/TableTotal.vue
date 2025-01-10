<script setup>
const props = defineProps({
  quote: Object,
});

const pricePlanskeys = { "One-Off": "SINGLE", Recurring: "MONTHLY" };
const quoteTotal = (price_plan, column = "total") => {
  let total = 0;
  props.quote.items.forEach((e) => {
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
  <table class="table-cpq table table-bordered">
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
        <th>Monthly Total</th>
        <td id="total_monthly_net" class="text-end">
          {{ GBP.format(quoteTotal("Recurring")) }}
        </td>
        <td id="total_monthly_vat" class="text-end">
          {{ GBP.format(quoteTotal("Recurring", "total_tax")) }}
        </td>
        <td id="total_monthly_gross" class="text-end">
          {{ GBP.format(quoteTotal("Recurring", "total_gross")) }}
        </td>
      </tr>
      <tr>
        <th>One Off Total</th>
        <td id="total_net" class="text-end">
          {{ GBP.format(quoteTotal("One-Off")) }}
        </td>
        <td id="total_vat" class="text-end">
          {{ GBP.format(quoteTotal("One-Off", "total_tax")) }}
        </td>
        <td id="total_gross" class="text-end">
          {{ GBP.format(quoteTotal("One-Off", "total_gross")) }}
        </td>
      </tr>
      <tr>
        <th>Total</th>
        <td id="total_net" class="text-end">
          {{ GBP.format(quote.total) }}
        </td>
        <td id="total_vat" class="text-end">
          {{ GBP.format(quote.total_vat) }}
        </td>
        <td id="total_gross" class="text-end">
          {{ GBP.format(quote.total_gross) }}
        </td>
      </tr>
    </tbody>
  </table>
</template>
