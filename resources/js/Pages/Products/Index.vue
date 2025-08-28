<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    solution_types: {
        type: Array,
        default: []
    }
});

const items = ref([{ label: 'Home', url: '/' }, { label: 'My Account', url: '/myaccount' }, { label: 'Products' }]);
</script>
<template>
    <Head title="Products" />
    <AuthenticatedLayout>
        <div class="panel">
            <Header title="Products" :breadcrumbs="items">
                <template #buttons>
                    <Link href="/blacklists" id="blacklist-index-btn" class="btn btn-primary me-2">
                        <font-awesome-icon :icon="['fas', 'shop-lock']" />
                        Blacklisted Items
                    </Link>
                    <Link href="/products/create" id="product-create-btn" class="btn btn-primary me-2">
                        <font-awesome-icon :icon="['fas', 'plus']"></font-awesome-icon>
                        New Product
                    </Link>
                </template>
            </Header>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <Datatable
                    :table-props="{ id: 'products-table' }"
                    :allow="{
                        update: true,
                        delete: true,
                        link: false
                    }"
                    :columns="[
                        { data: 'status', title: 'Status', style: 'width:4%' },
                        { data: 'sku', title: 'SKU', style: 'width:10%' },
                        { data: 'name', title: 'Name', style: 'width:25%' },
                        { data: 'parent.name', title: 'Parent', style: 'width:12%' },
                        { data: 'category.name', title: 'Category', style: 'width:12%' },
                        {
                            data: 'category.solution_type.name',
                            title: 'Solution Type',
                            style: 'width:12%'
                        },
                        { data: 'updated_at', title: 'Updated At', style: 'width:15%' }
                    ]"
                    :filters="{
                        'category.solution_type.name': {
                            type: 'options',
                            options: solution_types.map((x) => x.name)
                        }
                    }"
                    route="/products"
                >
                    <template #status="{ value }">
                        <span class="badge text-bg-secondary">{{ value }}</span>
                    </template>
                    <template #parent.name="{ value }">
                        <span class="badge border border-secondary text-secondary opacity-75 ms-1 lh-sm">{{
                            value
                        }}</span>
                    </template>
                    <template #updated_at="{ value }">
                        {{ format.date(value) }}
                    </template>
                    <template #created_at="{ value }">
                        {{ format.date(value) }}
                    </template>
                    <template #is_active="{ data, value }">
                        <ToggleActive :data="data" :value="value" route="products" />
                    </template>
                </Datatable>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
