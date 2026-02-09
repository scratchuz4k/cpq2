<script setup>
import { Head } from '@inertiajs/vue3';

const props = defineProps({ status: Number });

const errors = {
    503: {
        title: 'Service Unavailable',
        description: 'Sorry, we are doing some maintenance. Please check back soon.'
    },
    500: {
        title: 'Server Error',
        description: 'Sorry, we encountered an internal server error.'
    },
    404: {
        title: 'Page Not Found',
        description: 'Sorry, the page you are looking for could not be found.'
    },
    403: {
        title: 'Forbidden',
        description: 'Sorry, you are forbidden from accessing this page.'
    }
};

const error = errors[props.status] ?? {
    title: 'Error',
    description: 'Something went wrong.'
};
</script>

<template>
    <Head :title="error.title" />
    <div class="container error-body">
        <div class="row h-100">
            <div class="d-flex flex-column justify-content-center align-items-center w-100 h-100">
                <div class="flex items-center text-center">
                    <span class="text-primary display-1">{{ props.status }}</span>
                </div>
                <div class="flex items-center mb-3 text-center">
                    <span class="text-primary fs-3 font-weight-bold">{{ error.title }}</span>
                </div>
                <div class="flex items-center text-center">
                    <span class="text-white fs-5">{{ error.description }}</span>
                </div>
                <br />
                <br />
                <div class="flex items-center justify-center">
                    <div>
                        <a class="btn btn-primary text-uppercase px-4" href="/">Home</a>
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
</style>
