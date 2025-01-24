<script setup>

</script>

<template>
    <div class="spinner">
        <svg width="100" height="100" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path class="spinner-path" fill="#f8018a" d="M10.72,19.9a8,8,0,0,1-6.5-9.79A7.77,7.77,0,0,1,10.4,4.16a8,8,0,0,1,9.49,6.52A1.54,1.54,0,0,0,21.38,12h.13a1.37,1.37,0,0,0,1.38-1.54,11,11,0,1,0-12.7,12.39A1.54,1.54,0,0,0,12,21.34h0A1.47,1.47,0,0,0,10.72,19.9Z"/>
        </svg>
        <div class="spinner-text">CM</div>
    </div>
</template>

<style scoped>
.spinner {
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
}

.spinner svg {
    display: inline-block;
    width: 100px;
    height: 100px;
}

.spinner-path {
    transform-origin: 50% 50%;
    animation: spin 1.5s linear infinite;
}

.spinner-text {
    position: absolute;
    font-family: Montserrat, Helvetica, Arial, sans-serif;
    font-size: 18px;
    font-weight: 700;
    text-align: center;
    color: #f8018a;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
