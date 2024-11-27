<script setup>
const props = defineProps({
    severity: {
        type: String,
        default: 'default'
    },
})
const options = {
    default: { first: "#67236A", second: '#E1D3E1' },
    info: { first: "#356E8E", second: '#DEF1FB' }
}


const current = Object.keys(options).includes(props.severity) ? options[props.severity] : options.default

</script>
<template>
    <div class="info-box d-flex mb-4 py-3 pe-5"
        :style="{ backgroundColor: current.second, borderLeft: `4px solid ${current.first}` }">
        <div class="px-3 pt-1">
            <font-awesome-icon :icon="['fas', 'circle-info']" :style="{ color: current.first, fontSize: '20px' }" />
        </div>
        <div class="w-100">
            <slot></slot>
        </div>
    </div>
</template>
