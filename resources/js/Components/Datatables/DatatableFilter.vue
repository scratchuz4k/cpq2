<template>
    <div :id="'datatable-filter-' + props.id" style="position: absolute; right: 0; z-index: 10">
        <button type="button" class="btn btn-sm btn-filter" @click="showFilter = !showFilter">
            <font-awesome-icon :icon="['fas', 'filter']" />
        </button>
        <div v-if="showFilter" class="filter-content" :class="{ last: index >= lastColumn }">
            <!-- <template v-if="Object.keys(props.options.filters).includes(proxyValue.column)">
                <div class="my-1" v-if="props.options.filters[proxyValue.column].type === 'checkbox'">
                    <div class="form-check p-0">
                        <Checkbox
                            inputId="active-filter"
                            class="form-check-input"
                            v-model="proxyValue.value"
                            :checked="!!proxyValue.value"
                        />
                        <label class="form-check-label ms-2" for="active-filter"> Active </label>
                    </div>
                </div>
                <div v-if="props.options.filters[data.column].type === 'options'" class="mb-3">
                    <label class="form-label" :for="`filter-value-${key}`">Options</label>
                    <select :id="`filter-value-${key}`" class="form-select w-100" v-model="proxyValue.value">
                        <option :value="null">All</option>
                        <option
                            v-for="option in props.options.filters[data.column].options"
                            :value="typeof option === 'object' ? option.value : option"
                        >
                            {{ typeof option === 'object' ? option.label : option }}
                        </option>
                    </select>
                </div>
            </template> -->
            <div class="mb-3">
                <label class="form-label" :for="`filter-value-${index}`">Search</label>
                <input
                    :id="`filter-value-${index}`"
                    type="search"
                    class="form-control me-2 form-control-sm"
                    v-model="proxyValue.value"
                />
            </div>
        </div>
    </div>
</template>
<script setup>
import { computed, onMounted, ref } from 'vue';
import { v4 as uuidv4 } from 'uuid';

const props = defineProps({
    index: Number,
    options: Object,
    modelValue: Object,
    id: {
        required: false,
        default: () => {
            return `${uuidv4()}`;
        }
    }
});

const showFilter = ref(false);
const emit = defineEmits(['update:modelValue']);
const proxyValue = computed({
    get() {
        return props.modelValue;
    },
    set(val) {
        emit('update:modelValue', { ...props.modelValue, value: val });
    }
});

const lastColumn = computed(() => {
    let nrColumns = props.options.columns.filter((x) => !x.hide);
    let threshold = nrColumns > 5 ? props.options.columns.length - 1 : nrColumns.length - 3;
    return threshold;
});

onMounted(() => {
    var select = document.getElementById('datatable-filter-' + props.id);
    document.addEventListener('click', (ev) => {
        if (!select.contains(ev.target)) {
            showFilter.value = false;
        }
    });
});
</script>
<style scoped>
.filter-content {
    max-height: 500px;
    padding: 10px;
    border: 1px solid #e2e8f0;
}

.filter-content {
    position: absolute;
    top: calc(100% + 10px);
    right: calc(50% - 250px);
    width: 250px;
    background-color: white;
    border-radius: 5px;
    box-shadow: rgba(0, 0, 0, 0.1) 0px 4px 12px;
    padding: 0 10px;
    z-index: 9999 !important;
    max-height: 500px;
    padding: 10px;
    border: 1px solid #e2e8f0;
}

.filter-content.last {
    left: auto;
    right: 0;
}
</style>
