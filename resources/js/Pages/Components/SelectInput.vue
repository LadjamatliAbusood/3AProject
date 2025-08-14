<script setup>
import { readonly } from "vue";

const model = defineModel({
    type: null,
    required: true,
});
defineProps({
    name: {
        type: String,
        required: true,
    },
    options: {
        type: Array,
        required: true,
    },
    disableSelect: {
        type: Boolean,
        default: false,
    },
    message: String,
});
</script>
<template>
    <div>
        <div class="mb-2">
            <label
                for="location"
                class="block text-sm font-medium text-gray-700"
                >{{ name }}</label
            >
            <select
                v-model="model"
                class="bg-white border border-gray-600 text-gray-900 font-semibold rounded-lg block w-full p-2.5"
                :class="{ 'border-red-500': message }"
                :disabled="disableSelect"
            >
                <option
                    v-for="option in options"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </option>
            </select>
            <small class="text-red-500" v-if="message">{{ message }}</small>
        </div>
    </div>
</template>
