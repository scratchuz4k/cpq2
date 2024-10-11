<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    order: Object,
});

const solutionsViews = {
    3: 'OrdersConectivityTotalView',
    4: 'OrdersMobileTotalView',
    5: 'OrdersHardwareTotalView',
}

const showDate = (days = 0) => {
   const dateObj = new Date();
   dateObj.setDate(dateObj.getDate() + days);
   const currentDate = dateObj.getDate()+"/"+(dateObj.getMonth()+1)+"/"+dateObj.getFullYear();
   return currentDate;
}

</script>

<template>

    <Head :title="'Quote #' + order.id" />
    <AuthenticatedLayout :order="order">
        <div class="row mb-3">
            <div class="col d-flex">
                <div class="me-3">
                    <font-awesome-icon class="fa-border" :icon="['fas', 'file-pdf']"
                        style="font-size:30px;color:var(--secondary-light);border-color:var(--secondary-light);border-width:4px;border-radius:12px" />
                </div>
                <div style="padding-top:1px">
                    <p class="m-0">Q-{{ order.id }} // <span id="account_name">
                            {{ order.account?.name }}
                        </span>
                    </p>
                    <h1 class="m-0" style="font-size:18px;font-weight:bold">PDF</h1>
                </div>
            </div>
            <div class="col" style="text-align: right">
                <a :href="route('orders.download', { order: order.id })" class="btn btn-secondary mx-2">
                    <font-awesome-icon :icon="['fas', 'file-pdf']"></font-awesome-icon>
                    Download PDF
                </a>
            </div>
        </div>
        <div class="panel">
            <div class="container-fluid">
                <div class="row" id="header">
                    <div class="col" id="header_left">
                        <img src="https://img.logoipsum.com/331.svg" alt="Sample Logo" class="img-fluid">
                        <p>Insert Tagline</p>
                    </div>
                    <div class="col text-end" id="header_right">
                        <p>{{ order.account?.name }}<br>{{ order.address_data.label ?? '' }}</p>
                        <p>Tel: +44 (0) 191 300 700</p>
                        <p>Email: sales@example.com</p>
                    </div>
                </div>
                <div class="cpq-summary row px-3" id="main">
                    <div class="col">
                        <h1 class="mb-3">Quote Q-{{ order.id }}</h1>
                        <div class="row mb-3">
                            <div class="col">
                                <p>Company address, city, state, post code<br>
                                    sales@example.com<br>
                                    +44 (0) 123 456 7890</p>
                            </div>
                            <div class="col">
                                <p>Quote #{{ order.id }}<br>
                                    Quotation Date: {{ showDate() }}<br>
                                    Valid Date: {{showDate(7) }}
                                </p>
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col">
                                <h2>BILL FROM</h2>
                                <p>Name Surname<br>
                                    Company address, city, state, post code<br>
                                    sales@example.com<br>
                                    +44 (0) 123 456 7890</p>
                            </div>
                            <div class="col">
                                <h2>BILL TO</h2>
                                <p>Name Surname<br>
                                    Company address, city, state, post code<br>
                                    contact@account.test<br>
                                    +44 (0) 234 567 8910</p>
                            </div>
                        </div>

                        <component :is="solutionsViews[order.solution_type.code]" v-bind="{
                            modelValue: order,
                            preview: true
                        }"></component>

                    </div>
                </div>
                <div class="row px-3 mb-4" id="terms">
                    <div class="col">
                        <p>Terms & Conditions:</p>
                        <p>Lorem ipsum dolor sit amet...</p>
                    </div>
                </div>
                <div class="row mb-2" id="footer">
                    <div class="col">
                        <img src="https://img.logoipsum.com/331.svg">
                        <p>Insert Tagline</p>
                    </div>
                    <div class="col text-end">
                        <p>Lorem ipsum dolor sit amet...</p>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
