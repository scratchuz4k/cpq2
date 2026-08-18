<template>
    <div class="footer">
        <div class="container">
            <div class="row">
                <div class="col">
                    <app-logo type="logo_alt"></app-logo>
                </div>
                <div
                    class="col d-flex justify-center justify-content-sm-end align-items-center"
                    v-if="socialLinks?.filter((x) => x.is_active).length"
                >
                    <a
                        v-for="link in socialLinks?.filter((x) => x.is_active)"
                        :title="link.label"
                        :href="link.url"
                        class="fs-4 social-link"
                        target="_blank"
                    >
                        <font-awesome-icon :icon="['fa-brands', link.icon]" />
                    </a>
                </div>
            </div>
            <div class="row mt-3" v-if="links?.filter((x) => x.is_active).length">
                <div class="col">
                    <div class="d-flex flex-column flex-sm-row justify-content-end">
                        <a
                            class="footer-link"
                            v-for="link in links?.filter((x) => x.is_active)"
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
import { usePage } from '@inertiajs/vue3';
import AppLogo from './AppLogo.vue';
import { onBeforeUnmount, ref } from 'vue';

const page = usePage();
const links = ref(page.props.customization?.footer ?? []);
const socialLinks = ref(page.props.customization?.social ?? []);

const updateFooterLinks = (updatedLinks) => {
    links.value = updatedLinks ?? [];
};

const updateSocialLinks = (updatedLinks) => {
    socialLinks.value = updatedLinks ?? [];
};

emitter.on('customization:footer-updated', updateFooterLinks);
emitter.on('customization:social-updated', updateSocialLinks);

onBeforeUnmount(() => {
    emitter.off('customization:footer-updated', updateFooterLinks);
    emitter.off('customization:social-updated', updateSocialLinks);
});
</script>
