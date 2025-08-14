<script setup>
import { ref } from "vue";
import { Link, router } from "@inertiajs/vue3";

defineProps({
    paginator: {
        type: Object,
        required: true,
    },
});

const makeLabel = (label) => {
    if (label.includes("Previous")) {
        return "Previous";
    } else if (label.includes("Next")) {
        return "Next";
    } else {
        return label;
    }
};

const pageInput = ref("");

const goToPage = (totalPages) => {
    const page = parseInt(pageInput.value);
    if (!isNaN(page) && page >= 1 && page <= totalPages) {
        router.get(`?page=${page}`, {}, { preserveScroll: true });
    }
};
</script>

<template>
    <div class="flex justify-between items-center flex-wrap gap-4 mt-4">
        <!-- Pagination Links OR Page Input -->
        <div class="flex items-center space-x-1">
            <template v-if="paginator.last_page < 100">
                <template v-for="(link, index) in paginator.links" :key="index">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        preserve-scroll
                        class="px-4 py-2 rounded border text-sm"
                        :class="{
                            'bg-blue-500 text-white': link.active,
                            'hover:bg-gray-200': !link.active,
                        }"
                        v-html="makeLabel(link.label)"
                    />
                    <span
                        v-else
                        class="px-2 py-2 rounded border text-sm text-gray-400 cursor-default"
                        v-html="makeLabel(link.label)"
                    />
                </template>
            </template>

            <!-- Page input for large pagination -->
            <template v-else>
                <span class="text-sm">Go to page:</span>
                <input
                    v-model="pageInput"
                    type="number"
                    min="1"
                    :max="paginator.last_page"
                    @keyup.enter="goToPage(paginator.last_page)"
                    class="w-20 px-2 py-1 border rounded text-sm"
                />
                <button
                    @click="goToPage(paginator.last_page)"
                    class="px-3 py-1 bg-blue-500 text-white rounded text-sm hover:bg-blue-600"
                >
                    Go
                </button>
                <span class="text-sm text-gray-500"
                    >of {{ paginator.last_page }}</span
                >
            </template>
        </div>

        <!-- Showing X to Y of Z -->
        <div class="text-sm text-gray-600">
            Showing {{ paginator.from }} to {{ paginator.to }} of
            {{ paginator.total }} results
        </div>
    </div>
</template>
