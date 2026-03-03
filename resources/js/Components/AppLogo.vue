<template>
    <div class="app-logo">
        <img v-if="logo" :src="logo?.full_route" :alt="text[props.type]" />
        <!-- <img v-else :src="default_logo[props.type]" /> -->
    </div>
</template>
<script setup>
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    type: {
        type: String,
        default: 'logo',
        validator: (value) => ['logo', 'logo_alt', 'logo_docs'].includes(value)
    }
});

const page = usePage();
let tenant = page.props.model?.tenant ?? page.props.tenant;
const logo = Object.keys(tenant).includes(props.type) ? tenant[props.type] : null;

const text = {
    logo: `${tenant.name} main logo`,
    logo_alt: `${tenant.name} alternative logo`,
    logo_docs: `${tenant.name} document logo`
};

const default_logo = {
    logo: '/images/cloud-market-tools-logo.png',
    logo_alt: '/images/cloud-market-tools-giacom-logo.png',
    logo_docs: '/images/cloud-market-tools-logo.png'
};
</script>
