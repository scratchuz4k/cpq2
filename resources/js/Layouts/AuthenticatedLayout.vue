<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Head, Link } from '@inertiajs/vue3';
import Dialog from 'primevue/dialog';
import { ref } from 'vue'

defineProps({
    order: Object
})

const visible = ref(false);
const search = ref('')

const showSearch = () => {
    visible.value = true
}
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
                        <a class="nav-link active" aria-current="page" href="#">QUOTES</a>
                    </li>
                    <li class="nav-item d-md-none d-lg-block">
                        <a class="nav-link" href="#">ORDER</a>
                    </li>
                    <li class="nav-item d-md-none d-lg-block">
                        <a class="nav-link" href="#">BILLING</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <font-awesome-icon :icon="['far', 'building']" />
                            {{ $page.props.auth.user.name }}
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" :href="route('myaccount')">
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
                            aria-controls="dashboard" aria-selected="false">DASHBOARD</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" :class="{ 'active': $page.component == 'Order/Create' }" id="create-tab"
                            data-toggle="tab" href="/orders/create" role="tab" aria-controls="create"
                            aria-selected="false">
                            <font-awesome-icon :icon="['fas', 'plus']" />
                            NEW
                        </a>
                    </li>
                    <li class="nav-item" v-if="order">
                        <Link :href="route('orders.configure', { order: order.id })" class="nav-link"
                            :class="{ 'active': $page.component == 'Order/Configure' }">CONFIGURE</Link>
                    </li>
                    <li class="nav-item" v-if="order">
                        <Link :href="route('orders.edit', { order: order.id })" class="nav-link"
                            :class="{ 'active': $page.component == 'Order/Edit' }">PRICE</Link>
                    </li>
                    <li class="nav-item" v-if="order">
                        <Link :href="route('orders.show', { order: order.id })" class="nav-link"
                            :class="{ 'active': $page.component == 'Order/Show' }">QUOTE</Link>
                    </li>
                </ul>
                <form class="d-md-flex d-none" role="search">
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Type to search" v-model="search">
                        <button class="btn btn-primary" type="button" id="button-addon2" @click="showSearch()">
                            <font-awesome-icon :icon="['fas', 'magnifying-glass']" />
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </nav>
    <Dialog v-model:visible="visible" modal :style="{ width: '50rem', marginTop: '5rem' }" position="top"
        :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
        <template #container="{ closeCallback }">
            <div class="p-3">
                <div class="d-flex align-items-center w-100 mb-3">
                    <div class="search-control  d-flex align-items-center w-100">
                        <font-awesome-icon :icon="['fas', 'search']"></font-awesome-icon>
                        <input v-model="search" class="search-button mx-3 border-0 hover-0 w-100" placeholder="Search CloudMarket">
                    </div>
                    <font-awesome-icon @click="closeCallback" :icon="['fas', 'close']"
                        style="cursor: pointer;" class=" mx-3"></font-awesome-icon>
                </div>
                <p>
                    At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum
                    deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non
                    provident, similique sunt in culpa
                    qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis
                    est
                    et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit
                    quo
                    minus id quod
                    maxime placeat facere possimus, omnis voluptas assumenda est, omnis dolor repellendus. Temporibus
                    autem
                    quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates
                    repudiandae
                    sint et molestiae
                    non recusandae. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus
                    maiores alias consequatur aut perferendis doloribus asperiores repellat.
                </p>
            </div>
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
</style>
