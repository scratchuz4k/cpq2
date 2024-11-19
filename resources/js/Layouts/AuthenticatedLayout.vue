<script setup>
import _ from "lodash";
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import Dialog from 'primevue/dialog';
import { reactive, ref, watch } from 'vue'
import queryString from 'query-string';
import { usePage } from "@inertiajs/vue3";


const page = usePage();
import * as Sentry from "@sentry/vue";
Sentry.setUser({
    fullName: page.props.auth.user.name,
    email: page.props.auth.user.email,
});


defineProps({
    quote: Object,
    order: Object
})

const visible = ref(false);
const search = ref('')
const results = reactive({
    isLoading: false,
    quotes: [],
    accounts: []
})
const activeTab = ref(window.location.pathname.replace(/^\/|\/$/g, ''))

const setActive = (menu) =>  {
    activeTab.value = menu;
}

const showSearch = () => {
    if (!search.value) {
        return
    }
    results.isLoading = true
    visible.value = true
    searchCloudMarket()
}

const searchCloudMarket = _.debounce(async () => {
    if (!visible.value || !search.value) {
        return
    }
    results.isLoading = true
    let queryQuote = queryString.stringify({
        columns: 'id,items.data,items.name,account.name,account.contacts.email,solution_type.name,user.name,status,updated_at',
        search: search.value,
        limit: 5,
        order: 'desc',
        orderBy: 'updated_at'
    })
    let resQuote = await axios.get('/api/quotes?' + queryQuote)
    results.quotes = resQuote.data.data

    let queryAcc = queryString.stringify({
        columns: 'id,name,reference,contacts.primary_contact_name,contacts.email,updated_at',
        search: search.value,
        limit: 5,
        order: 'desc',
        orderBy: 'updated_at'
    })
    let resAcc = await axios.get('/api/accounts?' + queryAcc)
    results.accounts = resAcc.data.data
    results.isLoading = false
}, 500)

watch(search, () => {
    searchCloudMarket()
})
</script>

<template>
    <nav class="navbar navbar-expand-md bg-theme-secondary-dark">
        <div class="container d-flex justify-space-between">
            <a class="navbar-brand" href="/">
                <ApplicationLogo />
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <i class="fa-solid fa-bars"></i>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarSupportedContent">
                <ul class="navbar-nav navme-auto mb-2 mb-lg-0">
                    <li class="nav-item d-md-none d-lg-block">
                        <a class="nav-link" href="#">CUSTOMERS</a>
                    </li>
                    <li class="nav-item d-md-none d-lg-block">
                        <a class="nav-link" href="#">ASSETS</a>
                    </li>
                    <li class="nav-item d-md-none d-lg-block">
                        <a class="nav-link" :class="{ 'active': activeTab === 'quotes' }" aria-current="page" :href="route('quotes.index')">QUOTES</a>
                    </li>
                    <li class="nav-item d-md-none d-lg-block">
                        <a class="nav-link" :class="{ 'active': activeTab === 'orders' }" aria-current="page" :href="route('orders.index')">ORDER</a>
                    </li>
                    <li class="nav-item d-md-none d-lg-block">
                        <a class="nav-link" href="#">BILLING</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <font-awesome-icon :icon="['far', 'building']" />
                            {{ $page.props.tenant?.name }} ( T{{ $page.props.tenant?.id }} )
                        </a>
                    </li>

                    <li class="nav-item">
                        <a id="my-account" class="nav-link" :href="route('myaccount')">
                            <font-awesome-icon :icon="['far', 'user']" />

                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <font-awesome-icon :icon="['far', 'bell']" />
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <font-awesome-icon :icon="['far', 'bookmark']" />
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <nav class="navbar navbar-expand bg-theme-secondary-light">
        <div class="container">
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" id="dashboard-tab" data-toggle="tab" href="/" role="tab"
                            aria-controls="dashboard" aria-selected="false"
                           :class="{ 'active': activeTab === ''}"
                        >DASHBOARD</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" :class="{ 'active': $page.component == 'Quotes/Create' }" id="create-tab"
                            data-toggle="tab" href="/quotes/create" role="tab" aria-controls="create"
                            aria-selected="false">
                            <font-awesome-icon :icon="['fas', 'plus']" />
                            NEW
                        </a>
                    </li>
                    <li class="nav-item" v-if="quote">
                        <Link :href="route('quotes.configure', { quote: quote.id })" class="nav-link"
                            :class="{ 'active': $page.component == 'Quotes/Configure' }">CONFIGURE</Link>
                    </li>
                    <li class="nav-item" v-if="quote">
                        <Link :href="route('quotes.edit', { quote: quote.id })" class="nav-link"
                            :class="{ 'active': $page.component == 'Quotes/Edit' }">PRICE</Link>
                    </li>
                    <li class="nav-item" v-if="quote">
                        <Link :href="route('quotes.show', { quote: quote.id })" class="nav-link"
                            :class="{ 'active': $page.component == 'Quotes/Show' }">QUOTE</Link>
                    </li>
                </ul>
                <form class="d-md-flex d-none" @submit.prevent="showSearch()">
                    <div class="input-group">
                        <input type="search" class="form-control" placeholder="Type to search" v-model="search">
                        <button class="btn btn-primary btn-search" type="submit" id="button-addon2">
                            <font-awesome-icon :icon="['fas', 'magnifying-glass']" />
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </nav>
    <Dialog v-model:visible="visible" modal :style="{ width: '50rem', marginTop: '4rem' }"
        :breakpoints="{ '1199px': '75vw', '575px': '90vw' }" :draggable="false" position="top">
        <template #header>
            <div class="d-flex align-items-center w-100">
                <div class="search-control  d-flex align-items-center w-100">
                    <font-awesome-icon :icon="['fas', 'search']"></font-awesome-icon>
                    <input v-model="search" class="search-button mx-3 border-0 hover-0 w-100"
                        placeholder="Search CloudMarket">
                </div>
            </div>
        </template>
        <Loader v-if="results.isLoading"></Loader>
        <template v-if="results.quotes.length">
            <p class="search-title">Quotes</p>
            <a class="search-option" v-for="quote in results.quotes" :href="route('quotes.show', { quote: quote.id })">
                <font-awesome-icon class="search-option-icon me-3" :icon="['far', 'file']"></font-awesome-icon>
                <div class="w-100 m-0 d-flex justify-content-between">
                    <p class="m-0">
                        <b>#{{ quote.id }} - {{ quote.account?.name }} </b> / {{ quote.solution_type.name }} <br>
                        <span style="font-size: 12px;"> {{ quote.user.name }} - {{ format.date(quote.updated_at) }}</span>
                    </p>
                    <p class="m-0">
                        <span class="badge text-bg-secondary">{{ quote.status }}</span>
                    </p>
                </div>
            </a>
        </template>
        <template v-if="results.accounts.length">
            <p class="search-title">Accounts</p>
            <a class="search-option" v-for="account in results.accounts"
                :href="route('accounts.edit', { account: account.id })">
                <font-awesome-icon class="search-option-icon me-3" :icon="['far', 'building']"></font-awesome-icon>
                <div class="w-100 m-0 d-flex justify-content-between">
                    <p class="m-0">
                        <b>#{{ account.id }} - {{ account.name }} </b> / {{ account.reference }} <br>
                        {{ format.date(account.updated_at) }}
                    </p>
                </div>
            </a>
        </template>
        <template v-if="!results.accounts.length && !results.quotes.length">
            <p class="m-0">
                <b>No results found!</b>
            </p>
        </template>
    </Dialog>
    <!-- Page Content -->
    <main>
        <div class="container ">
            <slot />
        </div>
    </main>
    <Footer></Footer>
</template>
<style>
.search-button:focus-visible,
.search-button:hover {
    outline: none;
}

.search-control {
    padding: 10px;
    border-radius: 5px;
    border: 1px solid whitesmoke;
}

.search-control:hover {
    border: 1px solid black;
}

.search-title {
    font-size: .85em;
    font-weight: 600;
    line-height: 32px;
    margin: 0;
}

.search-option {
    text-decoration: none;
    color: black;
    font-size: .5m;
    cursor: pointer;
    margin-bottom: 5px;
    padding: 15px;
    border-radius: 5px;
    border: 1px solid whitesmoke;
    display: flex;
    align-items: center;
}

.search-option p {
    font-size: 14px;
}

.search-option:hover {
    color: white;
    background: black;
}

.search-option .search-option-icon {
    font-size: 1.5em;
}
</style>
