<script setup>

</script>

<template>
    <div class="spinner">
        <svg width="100" height="100" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
            <path fill="#f8018a" d="M10.72,19.9a8,8,0,0,1-6.5-9.79A7.77,7.77,0,0,1,10.4,4.16a8,8,0,0,1,9.49,6.52A1.54,1.54,0,0,0,21.38,12h.13a1.37,1.37,0,0,0,1.38-1.54,11,11,0,1,0-12.7,12.39A1.54,1.54,0,0,0,12,21.34h0A1.47,1.47,0,0,0,10.72,19.9Z">
                <animateTransform attributeName="transform" type="rotate" dur="1.5s" values="0 12 12;360 12 12" repeatCount="indefinite" />
            </path>
            <text x="50%" y="50%" text-anchor="middle" dominant-baseline="central" font-family="Montserrat, Helvetica, Arial, sans-serif" font-size="6" font-weight="700">
                CM
            </text>
        </svg>
    </div>
</template>

<style scoped>
.spinner svg path{
    display: inline-block;
    animation: spin 1.5s linear infinite;
    transform-origin: 50% 50%;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
