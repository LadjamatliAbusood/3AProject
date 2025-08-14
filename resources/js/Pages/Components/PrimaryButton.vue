<script setup>
defineProps({
    label: {
        type: String,
        required: true,
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    processing: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <button
        :class="[
            'mt-2 text-white font-medium rounded-lg text-sm px-5 py-2.5 focus:outline-none',
            processing
                ? 'bg-gray-400 cursor-not-allowed'
                : 'bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300',
        ]"
        :disabled="disabled || processing"
    >
        {{ label }}
    </button>
</template>
