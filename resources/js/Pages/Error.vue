<script setup>
const props = defineProps({
    status: Number,
    message: String
});
</script>

<template>
    <div class="container error-body">
        <div class="row h-100">
            <div class="d-flex flex-column justify-content-center align-items-center w-100 h-100">
                <div class="logo text-center">
                    <a class="brand-logo d-block w-100" href="/">
                        <img src="/images/cloud-market-tools-logo.png" />
                    </a>
                </div>
                <div class="flex items-center">
                    <div class="error-text text-center fs-4 text-uppercase">{{ props.message }}</div>
                </div>
                <br />
                <div class="flex items-center justify-center">
                    <div>
                        <a class="btn btn-primary text-uppercase" href="/"> Back </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.error-body {
    height: 100vh;
}

.logo {
    margin-bottom: 1.5rem;
    text-align: center;
}

.error-text {
    font-family:
        ui-sans-serif,
        system-ui,
        -apple-system,
        BlinkMacSystemFont,
        'Segoe UI',
        Roboto,
        'Helvetica Neue',
        Arial,
        'Noto Sans',
        sans-serif,
        'Apple Color Emoji',
        'Segoe UI Emoji',
        'Segoe UI Symbol',
        'Noto Color Emoji';
    color: rgba(160, 174, 192, 0.9);
}
</style>
