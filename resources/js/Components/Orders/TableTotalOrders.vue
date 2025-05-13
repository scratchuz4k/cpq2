<script setup>
const props = defineProps({
  order: Object,
});

const pricePlanskeys = { "One-Off": "SINGLE", Recurring: "MONTHLY" };
const orderTotal = (price_plan, column = "total") => {
  let total = 0;
  let items = props.order.items;
  items.forEach((e) => {
    if (e.children?.lenght) {
      total += e.children
        .filter((x) => x.price_plan && x.price_plan.code == pricePlanskeys[price_plan])
        .reduce((acc, item) => (acc += item[column]), 0);
    } else if (e.price_plan && e.price_plan.code == pricePlanskeys[price_plan]) {
      total += e[column];
    }
    orderQuantityPrice;
  });
  return total;
};
const orderQuantityPrice = (price_plan, operation = "simple") => {
  let total = 0;
  let tax = 0;
  let items = props.order.items;
  items.forEach((e) => {
    if (e.price_plan && e.price_plan.code == pricePlanskeys[price_plan]) {
      total += e.price * e.quantity;
      tax += e.price * e.quantity * e.tax;
    }
  });
  if (operation == "tax") {
    return tax;
  }
  if (operation == "total") {
    return total + tax;
  }
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
          {{ format.GBP(orderQuantityPrice("Recurring")) }}
        </td>
        <!-- <td id="total_monthly_vat">
                    {{ format.GBP(orderQuantityPrice("Recurring", "tax")) }}
                </td> -->
        <td id="total_monthly_gross">
          {{ format.GBP(orderQuantityPrice("Recurring", "total")) }}
        </td>
      </tr>
      <tr>
        <th>
          <p class="small text-start">One Off Total</p>
        </th>
        <td id="total_net">
          {{ format.GBP(orderTotal("One-Off")) }}
        </td>
        <!-- <td id="total_vat">
                    {{ format.GBP(orderTotal("One-Off", "total_tax")) }}
                </td> -->
        <td id="total_gross">
          {{ format.GBP(orderTotal("One-Off", "total_gross")) }}
        </td>
      </tr>
      <tr>
        <th>
          <p class="small text-start">Total</p>
        </th>
        <td id="total_net">
          {{ format.GBP(order.total) }}
        </td>
        <!-- <td id="total_vat">
                    {{ format.GBP(order.total_vat ?? 0) }}
                </td> -->
        <td id="total_gross">
          {{ format.GBP(order.total_gross) }}
        </td>
      </tr>
    </tbody>
  </table>
</template>
