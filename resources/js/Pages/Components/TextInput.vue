<script setup>
const model = defineModel({
    type: null,
    required: true,
});
defineProps({
    name: {
        type: String,
        required: true,
    },
    type: {
        type: String,
        default: "text",
    },
    message: String,
    readonly: {
        type: Boolean,
        default: false,
    },
});
</script>
<template>
    <div>
        <div class="mb-2">
            <label for="name" class="block text-md font-medium text-gray-700">{{
                name
            }}</label>
            <input
                :type="type"
                v-model="model"
                :readonly="readonly"
                :class="[
                    'bg-white border text-gray-900 font-semibold rounded-lg block w-full p-2.5',
                    'border-black',
                    $attrs.class,
                ]"
            />
            <small class="text-md font-semibold text-red-500" v-if="message">{{
                message
            }}</small>
        </div>
    </div>
</template>
