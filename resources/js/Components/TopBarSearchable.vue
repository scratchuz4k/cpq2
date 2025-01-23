<template>
  <form class="d-md-flex d-none" @submit.prevent="showSearch()">
    <div class="input-group">
      <input
        type="search"
        class="form-control"
        placeholder="Type to search"
        v-model="search"
      />
      <button class="btn btn-primary btn-search" type="submit" id="button-addon2">
        <font-awesome-icon :icon="['fas', 'magnifying-glass']" />
      </button>
    </div>
    <Dialog
      v-model:visible="visible"
      modal
      :style="{ width: '50rem', marginTop: '4rem' }"
      :breakpoints="{ '1199px': '75vw', '575px': '90vw' }"
      :draggable="false"
      position="top"
    >
      <template #header>
        <div class="d-flex align-items-center w-100">
          <div class="search-control d-flex align-items-center w-100">
            <font-awesome-icon :icon="['fas', 'search']"></font-awesome-icon>
            <input
              v-model="search"
              class="search-button mx-3 border-0 hover-0 w-100"
              placeholder="Search CloudMarket"
            />
          </div>
        </div>
      </template>
      <Loader v-if="results.isLoading"></Loader>
      <template v-if="results.quotes.length">
        <p class="search-title">Quotes</p>
        <a
          class="search-option"
          v-for="quote in results.quotes"
          :href="route('quotes.show', { quote: quote.id })"
        >
          <font-awesome-icon
            class="search-option-icon me-3"
            :icon="['far', 'file']"
          ></font-awesome-icon>
          <div class="w-100 m-0 d-flex justify-content-between">
            <p class="m-0">
              <b>#{{ quote.id }} - {{ quote.account?.name }} </b> /
              {{ quote.solution_type.name }} <br />
              <span style="font-size: 12px">
                {{ quote.user.name }} - {{ format.date(quote.updated_at) }}</span
              >
            </p>
            <p class="m-0">
              <span class="badge text-bg-secondary">{{ quote.status }}</span>
            </p>
          </div>
        </a>
      </template>
      <template v-if="results.orders.length">
        <p class="search-title">Orders</p>
        <a
          class="search-option"
          v-for="order in results.orders"
          :href="route('orders.show', { order: order.id })"
        >
          <font-awesome-icon
            class="search-option-icon me-3"
            :icon="['far', 'file']"
          ></font-awesome-icon>
          <div class="w-100 m-0 d-flex justify-content-between">
            <p class="m-0">
              {{ order.user?.name }}
              <b>#{{ order.id }} - {{ order.account?.name }} </b> /
              {{ order.solution_type.name }} <br />
              <span style="font-size: 12px">
                {{ order.user?.name }} - {{ format.date(order.updated_at) }}</span
              >
            </p>
            <p class="m-0">
              <span class="badge text-bg-secondary">{{ order.status }}</span>
            </p>
          </div>
        </a>
      </template>
      <template v-if="results.accounts.length">
        <p class="search-title">Accounts</p>
        <a
          class="search-option"
          v-for="account in results.accounts"
          :href="route('accounts.edit', { account: account.id })"
        >
          <font-awesome-icon
            class="search-option-icon me-3"
            :icon="['far', 'building']"
          ></font-awesome-icon>
          <div class="w-100 m-0 d-flex justify-content-between">
            <p class="m-0">
              <b>#{{ account.id }} - {{ account.name }} </b> / {{ account.reference }}
              <br />
              {{ format.date(account.updated_at) }}
            </p>
          </div>
        </a>
      </template>
      <template
        v-if="
          !results.accounts.length && !results.quotes.length && !results.orders.length
        "
      >
        <p class="m-0">
          <b>No results found!</b>
        </p>
      </template>
    </Dialog>
  </form>
</template>

<script setup>
import _ from "lodash";
import Dialog from "primevue/dialog";
import axios from "axios";
import { reactive, ref, watch } from "vue";
import queryString from "query-string";
import { usePage } from "@inertiajs/vue3";
import { can } from "@composables/useAuth";

const page = usePage();

const visible = ref(false);
const showSearch = () => {
  if (!search.value) {
    return;
  }
  results.isLoading = true;
  visible.value = true;
  searchCloudMarket(page);
};

const search = ref("");
const results = reactive({
  isLoading: false,
  quotes: [],
  orders: [],
  accounts: [],
});

const searchCloudMarket = _.debounce(async (page) => {
  if (!visible.value || !search.value) {
    return;
  }
  results.isLoading = true;

  if (can("viewAny order")) {
    let queryOrder = queryString.stringify({
      columns:
        "id,address_data,items.data,items.name,account.name,account.contacts.email,solution_type.name,user.name,status,updated_at",
      search: search.value,
      limit: 5,
      order: "desc",
      orderBy: "updated_at",
    });
    let resOrder = await axios.get("/api/orders?" + queryOrder);
    results.orders = resOrder.data.data;
  }

  if (can("viewAny quote")) {
    let queryQuote = queryString.stringify({
      columns:
        "id,address_data,items.data,items.name,account.name,account.contacts.email,solution_type.name,user.name,status,updated_at",
      search: search.value,
      limit: 5,
      order: "desc",
      orderBy: "updated_at",
    });
    let resQuote = await axios.get("/api/quotes?" + queryQuote);
    results.quotes = resQuote.data.data;
  }

  if (can("viewAny account")) {
    let queryAcc = queryString.stringify({
      columns:
        "id,name,reference,contacts.primary_contact_name,contacts.email,updated_at",
      search: search.value,
      limit: 5,
      order: "desc",
      orderBy: "updated_at",
    });
    let resAcc = await axios.get("/api/accounts?" + queryAcc);
    results.accounts = resAcc.data.data;
  }

  results.isLoading = false;
}, 500);

watch(search, () => {
  searchCloudMarket(page);
});
</script>
