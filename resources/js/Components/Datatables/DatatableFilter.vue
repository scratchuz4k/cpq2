<template>
    <div style="position: absolute; right: 0; z-index: 10">
        <button class="btn btn-sm btn-filter">
            <font-awesome-icon :icon="['fas', 'filter']" />
        </button>
        <div class="filter-content" :class="{ last: key + 1 == columns.length }">
            <template v-if="Object.keys(props.options.filters).includes(data.column)">
                <div class="my-1" v-if="props.options.filters[data.column].type === 'checkbox'">
                    <div class="form-check p-0">
                        <Checkbox
                            inputId="active-filter"
                            class="form-check-input"
                            v-model="form.filters[key].value"
                            :checked="!!form.filters[key].value"
                        />
                        <label class="form-check-label ms-2" for="active-filter"> Active </label>
                    </div>
                </div>
                <div v-if="props.options.filters[data.column].type === 'options'" class="mb-3">
                    <label class="form-label" :for="`filter-value-${key}`">Options</label>
                    <select :id="`filter-value-${key}`" class="form-select w-100" v-model="form.filters[key].value">
                        <option :value="null">All</option>
                        <option
                            v-for="option in filters[data.column].options"
                            :value="typeof option === 'object' ? option.value : option"
                        >
                            {{ typeof option === 'object' ? option.label : option }}
                        </option>
                    </select>
                </div>
            </template>
            <div v-else class="mb-3">
                <label class="form-label" :for="`filter-value-${key}`">Search</label>
                <input
                    :id="`filter-value-${key}`"
                    type="search"
                    class="form-control me-2 form-control-sm"
                    v-model="form.filters[key].value"
                />
            </div>
        </div>
    </div>
</template>
<script setup>
const props = defineProps({
    data: Object,
    options: Object
});
</script>
