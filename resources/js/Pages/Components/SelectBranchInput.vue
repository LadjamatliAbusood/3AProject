<script setup>
defineProps({
    name: String,
    modelValue: [String, Number],
    options: Array,
    disabled: Boolean,
});

defineEmits(["update:modelValue"]);
</script>

<template>
    <div class="mb-4">
        <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ name }}
        </label>
        <select
            class="form-select w-full rounded-lg border-gray-300 text-lg"
            :disabled="disabled"
            :value="modelValue"
            @change="$emit('update:modelValue', $event.target.value)"
        >
            <option value="">Select {{ name }}</option>
            <option
                v-for="option in options"
                :key="option.value"
                :value="option.value"
            >
                {{ option.label }}
            </option>
        </select>
    </div>
</template>
