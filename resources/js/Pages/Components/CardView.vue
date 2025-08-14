<template>
    <div class="w-40 sm:w-48">
        <div
            class="bg-white rounded-lg shadow-md overflow-hidden flex flex-col justify-between h-full"
        >
            <div class="p-4 text-center">
                <h5 class="text-xl font-semibold text-gray-700">
                    {{ title }}
                </h5>
                <h4 class="text-2xl font-bold text-gray-700">
                    {{ value }}
                </h4>
            </div>
            <!-- Colored line at the bottom -->
            <div :class="`h-2 w-full ${barColor}`"></div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    title: String,
    value: [String, Number],
    barColor: String, // e.g. "bg-red-500"
});
</script>
