<script setup>
import { ref, watch } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import ExportTable from "../Components/ExportTable.vue";
import CardView from "../Components/CardView.vue";
import DateRangeFilter from "../Components/DateRangeFilter.vue";
import PaginationLinks from "../Components/PaginationLink.vue";
import { debounce } from "lodash";

// Access page props
const page = usePage();

// Define props passed from backend
const props = defineProps({
    transfers: Object,
    branch: Number,
    date_from: String,
    date_to: String,
    search: String,
});

// Reactive state
const search = ref(props.search || "");
const dateFilterRef = ref(null);

// Search watcher with debounce
watch(
    search,
    debounce((q) => {
        router.get(
            `/supervisor/${props.branch}/receive`,
            {
                search: q || null,
                date_from: dateFilterRef.value?.localDateFrom || null,
                date_to: dateFilterRef.value?.localDateTo || null,
            },
            {
                preserveState: true,
                replace: true,
            }
        );
    }, 300)
);

// Date filter button click
function filterByDate() {
    router.get(
        `/supervisor/${props.branch}/receive`,
        {
            search: search.value || null,
            date_from: dateFilterRef.value?.localDateFrom || null,
            date_to: dateFilterRef.value?.localDateTo || null,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
}

// Format date for table display
const getDate = (date) =>
    new Date(date).toLocaleDateString("en-us", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
</script>

<template>
    <Head title=" | Received" />
    <div data-aos="fade-up">
        <div class="bg-white p-6 rounded-lg shadow-md w-full overflow-x-auto">
            <!-- Top Filters -->
            <div
                class="flex flex-col md:flex-row flex-wrap md:flex-nowrap items-end justify-between gap-4 mb-4"
            >
                <!-- Search Input -->
                <input
                    type="search"
                    v-model="search"
                    placeholder="Search..."
                    class="w-full md:w-1/2 p-2 border rounded-lg"
                />

                <!-- Date Filter + Filter Button -->
                <div class="flex items-end gap-4 w-full md:w-auto">
                    <DateRangeFilter
                        ref="dateFilterRef"
                        :date_from="props.date_from"
                        :date_to="props.date_to"
                    />
                    <button
                        @click="filterByDate"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg"
                    >
                        Filter
                    </button>
                </div>

                <!-- Export -->
                <ExportTable
                    table-id="productReceive-table"
                    filename="productReceive-table.xlsx"
                />
            </div>

            <!-- Data Table -->
            <table
                class="min-w-full table-auto border-collapse border border-gray-300 shadow-md"
                id="productReceive-table"
            >
                <thead class="bg-gray-100">
                    <tr>
                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                        >
                            #
                        </th>
                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                        >
                            Date
                        </th>
                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                        >
                            Transfer by
                        </th>
                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                        >
                            Product Code
                        </th>
                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                        >
                            Product Name
                        </th>
                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                        >
                            Quantity
                        </th>
                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                        >
                            From Branch
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr
                        v-for="(transfer, index) in transfers.data"
                        :key="transfer.id"
                        class="odd:bg-white even:bg-gray-50 hover:bg-gray-100"
                    >
                        <td class="px-4 py-2">{{ index + 1 }}</td>
                        <td class="px-4 py-2">
                            {{ getDate(transfer.created_at) }}
                        </td>
                        <td class="px-4 py-2">
                            {{ transfer.product?.account }}
                        </td>
                        <td class="px-4 py-2">
                            {{ transfer.product?.product_code }}
                        </td>
                        <td class="px-4 py-2">
                            {{ transfer.product?.description || "N/A" }}
                        </td>
                        <td class="px-4 py-2">{{ transfer.quantity }}</td>
                        <td class="px-4 py-2">
                            {{ transfer.from_branch?.branch_name }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="mt-4">
                <PaginationLinks :paginator="transfers" />
            </div>
        </div>
    </div>
</template>
