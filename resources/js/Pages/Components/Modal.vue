<template>
    <div
        v-if="show"
        class="fixed inset-0 flex items-start justify-center z-50 pt-[90px]"
    >
        <div
            class="bg-white p-6 rounded-lg w-full max-w-3xl relative shadow-md"
        >
            <XCircleIcon
                @click="$emit('close')"
                class="absolute w-9 h-9 top-2 right-2 text-red-500 hover:text-red-300"
            />

            <slot />
        </div>
    </div>
</template>

<script setup>
import { XCircleIcon } from "@heroicons/vue/24/solid";

defineProps({ show: Boolean });
</script>
