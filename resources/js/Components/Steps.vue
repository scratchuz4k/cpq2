<template>
    <div class="content">
        <div class="progress-container">
            <div class="progress" id="progress"
                :style="{ width: ((props.modelValue ) / (steps.length - 1)) * 100 + '%' }"></div>
            <div class="circle" :class="{ 'active': props.modelValue >= key }" v-for="(step, key) in steps"
                @click="() => props.modelValue = key">
                <div class="caption">{{ step }}</div>
            </div>
        </div>
    </div>

</template>

<script setup>
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
const props = defineProps({
    steps: Array,
    modelValue: Number
})


</script>

<style scoped>

.content {
    text-align: center;
}

.progress-container {
    display: flex;
    justify-content: space-between;
    position: relative;
    margin-bottom: 60px;
    max-width: 100%;
    width: 350px;
}

.progress-container::before {
    content: "";
    background-color: #c4c1c1;
    position: absolute;
    top: 50%;
    left: 0;
    transform: translateY(-50%);
    height: 4px;
    width: 100%;
    z-index: -1;
}

.progress {
    background-color: var(--primary-light);
    position: absolute;
    top: 50%;
    left: 0;
    transform: translateY(-50%);
    height: 4px;
    transition: 0.4s ease;
}

.circle {
    background: #c4c1c1;
    color: #c4c1c1;
    border-radius: 50%;
    height: 20px;
    width: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid #c4c1c1;
    transition: 0.4s ease;
}

.circle.active {
    border-color: var(--primary-light);
    background-color: #fff;
    color: var(--primary-light);
    scale: 1.1;
}

.circle .icon {
    position: absolute;
    font-size: 25px;
    /* bottom: 25px; */
}

.circle .caption {
    position: absolute;
    font-size: 14px;
    font-weight: bolder;
    bottom: -30px;
}

.btn {
    background: var(--primary-light);
    color: #fff;
    border: 0;
    border-radius: 20px;
    cursor: pointer;
    font-family: inherit;
    font-size: inherit;
    padding: 8px 30px;
    margin: 5px;
}

.btn:active {
    transform: scale(0.9);
}

.btn:focus {
    outline: 0;
}

.btn:disabled {
    background-color: #c4c1c1;
    cursor: not-allowed;
}
</style>
