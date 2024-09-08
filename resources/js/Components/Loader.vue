<template>
    <div :class="['app-loading', props.classBind]">
        <div></div>
        <div></div>
        <div></div>
    </div>
</template>

<script setup>

const props = defineProps({
    classBind: {
        type: String,
        default: ''
    }
})

</script>

<style scoped>
.app-loading {
    display: flex;
    justify-content: center;
    z-index: 100;
    align-items: center;
}

.app-loading div {
    width: .5rem;
    height: .5rem;
    margin: .5rem 0.3rem;
    background: #828282;
    border-radius: 50%;
    -webkit-animation: 0.9s bounce infinite;
    animation: 0.9s bounce infinite;
}

.app-loading div:nth-child(2) {
    -webkit-animation-delay: 0.3s;
    animation-delay: 0.3s;
}

.app-loading div:nth-child(3) {
    -webkit-animation-delay: 0.6s;
    animation-delay: 0.6s;
}

@keyframes bounce {
    to {
        opacity: 0.3;
        transform: translate3d(0, -1rem, 0);
    }
}
</style>