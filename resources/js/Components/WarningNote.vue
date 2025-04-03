<!-- This component also exists as a blade component, any changes made here should also be made in the blade compoenent as wel (resources/views/components/warning-note.blade.php) -->
<script setup>
import { ref } from "vue";

const props = defineProps({
    warnings: Array,
})
const warnings = ref(props.warnings)

</script>

<template>
    <div class="small">
        <div class="mb-2 fw-bold">Important Information</div>
        <div v-for="provider in warnings" class="mb-2">
            <div v-if="Object.values(provider)[0]">
                <span class="small fw-bold bg-light rounded-2 py-1 px-2 me-1 text-capitalize">
                    {{ Object.keys(provider)[0].toUpperCase() }}
                </span>
                <i>
                    Please note, quote references for
                    {{ Object.keys(provider)[0].toUpperCase() }} quotes cannot
                    currently be used to place an order. To obtain a valid quote for
                    ordering please generate the quote directly on the
                    {{ Object.keys(provider)[0].toUpperCase() }}
                    portal.
                </i>
            </div>
        </div>
    </div>
</template>
