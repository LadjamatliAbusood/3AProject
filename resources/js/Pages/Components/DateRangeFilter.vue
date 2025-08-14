<template>
    <div class="flex items-end gap-4">
        <!-- From Date -->
        <div class="flex flex-col">
            <input
                type="date"
                v-model="localDateFrom"
                class="border rounded px-3 py-2"
            />
        </div>

        <!-- To Date -->
        <div class="flex flex-col">
            <input
                type="date"
                v-model="localDateTo"
                class="border rounded px-3 py-2"
            />
        </div>
    </div>
</template>

<script setup>
import { ref, defineExpose } from "vue";
import { router } from "@inertiajs/vue3";

const props = defineProps({
    routeName: { type: String, required: true },
    date_from: { type: String, default: "" },
    date_to: { type: String, default: "" },
    extraQuery: { type: Object, default: () => ({}) },
    preserveState: { type: Boolean, default: true },
});

const localDateFrom = ref(props.date_from);
const localDateTo = ref(props.date_to);

function filterByDate() {
    router.get(
        route(props.routeName),
        {
            ...props.extraQuery,
            date_from: localDateFrom.value,
            date_to: localDateTo.value,
        },
        {
            preserveState: props.preserveState,
            replace: true,
        }
    );
}

defineExpose({
    filterByDate,
    localDateFrom,
    localDateTo,
});
</script>
