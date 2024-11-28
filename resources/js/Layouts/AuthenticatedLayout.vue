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
    <slot />
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
