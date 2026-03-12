<template>
    <div class="footer">
        <div class="container">
            <div class="row">
                <div class="col">
                    <app-logo type="logo_alt"></app-logo>
                </div>
                <div class="col d-flex justify-center justify-content-sm-end align-items-center">
                    <a title="Facebook" class="fs-4" href="https://www.facebook.com/GiacomGroup" target="_blank">
                        <font-awesome-icon :icon="['fa-brands', 'fa-facebook']"></font-awesome-icon>
                    </a>
                    <a title="LinkedIn" class="fs-4" href="https://uk.linkedin.com/company/giacom" target="_blank">
                        <font-awesome-icon :icon="['fa-brands', 'fa-linkedin']"></font-awesome-icon>
                    </a>
                    <a
                        title="YouTube"
                        class="fs-4"
                        href="https://www.youtube.com/channel/UCwjyeFJQzNaL_JtxqZkCSOA"
                        target="_blank"
                    >
                        <font-awesome-icon :icon="['fa-brands', 'fa-youtube']"></font-awesome-icon>
                    </a>
                    <a title="Vimeo" class="fs-4" href="https://vimeo.com/user75859235" target="_blank">
                        <font-awesome-icon :icon="['fa-brands', 'fa-vimeo']"></font-awesome-icon>
                    </a>
                    <a title="X" class="fs-4" href="https://x.com/GiacomCM" target="_blank">
                        <font-awesome-icon :icon="['fa-brands', 'fa-square-x-twitter']"></font-awesome-icon>
                    </a>
                </div>
            </div>
            <div class="row mt-3" v-if="links.filter((x) => x.is_active).length">
                <div class="col">
                    <div class="d-flex flex-column flex-sm-row justify-content-end">
                        <a
                            class="footer-link"
                            v-for="link in links.filter((x) => x.is_active)"
                            :href="link.url"
                            target="_blank"
                            >{{ link.label }}</a
                        >
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import AppLogo from './AppLogo.vue';

const page = usePage();
const user = page.props.auth.user;
const links = page.props.customization.footer;
</script>
