<script setup>
import { ref, watch } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import ExportTable from "../Components/ExportTable.vue";
import CardView from "../Components/CardView.vue";
import DateRangeFilter from "../Components/DateRangeFilter.vue";
import PaginationLinks from "../Components/PaginationLink.vue";

import CashierLayout from "../../Layouts/CashierLayout.vue";

defineOptions({ layout: CashierLayout });

const page = usePage();

const props = defineProps({
    salesreport: Object,
    totals: Object,
    branches: Array,
});

// Get user’s branch (used to build the URL)
const branch = page.props.auth.user.branch_id;

// Filters
const search = ref("");
const dateFilterRef = ref(null);

// Utilities
const getDate = (date) =>
    new Date(date).toLocaleDateString("en-us", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });

// Handlers
function fetchData() {
    router.get(
        `/cashier/${branch}/sales`,
        {
            search: search.value,
            date_from: dateFilterRef.value?.localDateFrom,
            date_to: dateFilterRef.value?.localDateTo,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
}

const updateSearch = () => fetchData();
const filterByDate = () => fetchData();
</script>

<template>
    <Head title=" | Sales" />
    <div data-aos="fade-up">
        <div class="flex flex-wrap justify-center gap-4 mb-3">
            <CardView
                title="Quantity"
                :value="totals.total_quantity"
                barColor="bg-amber-500"
            />
            <CardView
                title="Sales"
                :value="`₱ ${parseFloat(totals.total_price || 0).toLocaleString(
                    undefined,
                    {
                        maximumFractionDigits: 0,
                    }
                )}`"
                barColor="bg-green-500"
            />

            <CardView
                title="Expenses"
                :value="`₱ ${parseFloat(
                    totals.total_expenses || 0
                ).toLocaleString(undefined, {
                    maximumFractionDigits: 0,
                })}`"
                barColor="bg-amber-500"
            />
        </div>

        <div
            class="bg-gray-100 p-6 rounded-lg shadow-md w-full overflow-x-auto"
        >
            <div class="flex flex-wrap items-center gap-4 mb-4">
                <!-- Search Input -->
                <input
                    type="search"
                    v-model="search"
                    @input="updateSearch"
                    placeholder="Search..."
                    class="p-2 border rounded-lg w-60 md:w-90"
                />

                <!-- Date Range + Filter Button -->
                <div class="flex items-center gap-2">
                    <DateRangeFilter
                        ref="dateFilterRef"
                        route-name="history"
                        :date_from="$page.props.date_from"
                        :date_to="$page.props.date_to"
                        :extra-query="{ search: search }"
                    />
                    <button
                        @click="filterByDate"
                        class="text-white hover:bg-blue-800 bg-blue-600 font-medium rounded-lg text-sm px-5 py-2.5 focus:outline-none"
                    >
                        Filter
                    </button>
                </div>
            </div>

            <table
                class="min-w-full table-auto border-collapse border border-gray-300 shadow-md"
                id="product-table"
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
                            Account
                        </th>

                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                        >
                            Barcode
                        </th>

                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                        >
                            Product Name
                        </th>
                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                        >
                            Type
                        </th>
                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                        >
                            Category
                        </th>

                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                        >
                            Selling Price
                        </th>
                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                        >
                            Quantity
                        </th>
                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                        >
                            Total Sales
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr
                        v-for="(sr, index) in salesreport.data"
                        :key="sr.id"
                        class="hover:bg-gray-100"
                    >
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            {{ index + 1 }}
                        </td>
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            {{ getDate(sr.created_at) }}
                        </td>
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            {{ sr.account }}
                        </td>

                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            {{ sr.product_code }}
                        </td>
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            {{ sr.description }}
                        </td>
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            <span
                                :class="[
                                    'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                                    {
                                        'bg-green-200 text-green-700':
                                            sr.selling_type == 1,
                                        'bg-amber-200 text-amber-700':
                                            sr.selling_type == 2,
                                    },
                                ]"
                            >
                                {{
                                    sr.selling_type == 1
                                        ? "Retail"
                                        : "Wholesale"
                                }}
                            </span>
                        </td>
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            {{ sr.product?.category?.category_name }}
                        </td>

                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            {{ parseFloat(sr.selling_price).toLocaleString() }}
                        </td>
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            {{ sr.quantity }}
                        </td>
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            {{ parseFloat(sr.total_price).toLocaleString() }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="mt-4">
                <PaginationLinks :paginator="salesreport" />
            </div>
        </div>
    </div>
</template>
