<script setup>
import { ref } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import ExportTable from "../Components/ExportTable.vue";
import CardView from "../Components/CardView.vue";
import DateRangeFilter from "../Components/DateRangeFilter.vue";
import PaginationLinks from "../Components/PaginationLink.vue";

const page = usePage();
const acct_roles = page.props.auth?.user?.acct_roles;
const branch = page.props.auth?.user?.branch_name;

const props = defineProps({
    ProductHistory: Array,
    totals: Object,
});

// Reactive state
const search = ref("");
const dateFilterRef = ref(null);

// Build correct history route
function getHistoryRoute() {
    if (acct_roles === 3) {
        return `/supervisor/${branch}/history`;
    } else if (acct_roles === 2) {
        return `/administrator/history`;
    } else {
        return `/admin/history`;
    }
}

// Filter functions
function filterByDate() {
    router.get(
        getHistoryRoute(),
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

function updateSearch() {
    router.get(
        getHistoryRoute(),
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

// Format date for display
const getDate = (date) =>
    new Date(date).toLocaleDateString("en-us", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
</script>

<template>
    <Head title=" | History" />
    <div data-aos="fade-up">
        <div class="flex flex-wrap justify-center gap-4 mb-3">
            <CardView
                title="Quantity"
                :value="totals.quantity"
                barColor="bg-amber-500"
            />
            <CardView
                v-if="[1, 2].includes($page.props.auth.user.acct_roles)"
                title="Cost"
                :value="`₱ ${parseFloat(totals.cost).toLocaleString(undefined, {
                    maximumFractionDigits: 0,
                })}`"
                barColor="bg-blue-500"
            />
            <CardView
                title="Retail"
                :value="`₱ ${parseFloat(totals.retail).toLocaleString(
                    undefined,
                    {
                        maximumFractionDigits: 0,
                    }
                )}`"
                barColor="bg-red-500"
            />
            <CardView
                title="Wholesale"
                :value="`₱ ${parseFloat(totals.wholesale).toLocaleString(
                    undefined,
                    {
                        maximumFractionDigits: 0,
                    }
                )}`"
                barColor="bg-green-500"
            />
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md w-full overflow-x-auto">
            <div
                class="flex flex-col md:flex-row flex-wrap md:flex-nowrap items-end justify-between gap-4 mb-4"
            >
                <!-- Search Input -->
                <input
                    type="search"
                    v-model="search"
                    @input="updateSearch"
                    placeholder="Search..."
                    class="w-full md:w-1/2 p-2 border rounded-lg"
                />

                <!-- Date Range Filter + Filter Button -->
                <div class="flex items-end gap-4 w-full md:w-auto">
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

                <!-- Export Button -->
                <ExportTable
                    table-id="productHistory-table"
                    filename="productHistory-table.xlsx"
                />
            </div>

            <table
                class="min-w-full table-auto border-collapse border border-gray-300 shadow-md"
                id="productHistory-table"
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
                            v-if="
                                [1, 2].includes(
                                    $page.props.auth.user.acct_roles
                                )
                            "
                        >
                            Branch
                        </th>
                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                        >
                            Barcode
                        </th>
                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                        >
                            Supplier
                        </th>
                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                        >
                            Category
                        </th>
                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                        >
                            Product Name
                        </th>
                        <th
                            v-if="
                                [1, 2].includes(
                                    $page.props.auth.user.acct_roles
                                )
                            "
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                        >
                            Cost Price
                        </th>
                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                        >
                            Retail Price
                        </th>
                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                        >
                            Wholesale Price
                        </th>
                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                        >
                            Quantity
                        </th>
                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                        >
                            Status
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr
                        v-for="(producthistory, index) in ProductHistory.data"
                        :key="producthistory.id"
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
                            {{ getDate(producthistory.created_at) }}
                        </td>
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            {{ producthistory.account }}
                        </td>
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                            v-if="
                                [1, 2].includes(
                                    $page.props.auth.user.acct_roles
                                )
                            "
                        >
                            {{ producthistory.branch?.branch_name }}
                        </td>
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            {{ producthistory.product_code }}
                        </td>
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            {{ producthistory.supplier?.supplier_name }}
                        </td>
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            {{ producthistory.category?.category_name }}
                        </td>
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            {{ producthistory.description }}
                        </td>
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                            v-if="
                                [1, 2].includes(
                                    $page.props.auth.user.acct_roles
                                )
                            "
                        >
                            {{ producthistory.cost_price }}
                        </td>
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            {{ producthistory.retail_price }}
                        </td>
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            {{ producthistory.wholesale_price }}
                        </td>
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            {{ producthistory.quantity }}
                        </td>
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            <span
                                :class="[
                                    'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                                    {
                                        'bg-green-200 text-green-700':
                                            producthistory.status == 1,
                                        'bg-amber-200 text-amber-700':
                                            producthistory.status == 2,
                                        'bg-red-200 text-red-700':
                                            producthistory.status == 3,
                                        'bg-blue-200 text-blue-700':
                                            producthistory.status == 4,
                                    },
                                ]"
                            >
                                <span
                                    :class="[
                                        'h-2 w-2 rounded-full mr-2',
                                        {
                                            'bg-green-600':
                                                producthistory.status == 1,
                                            'bg-amber-500':
                                                producthistory.status == 2,
                                            'bg-red-600':
                                                producthistory.status == 3,
                                            'bg-blue-600':
                                                producthistory.status == 4,
                                        },
                                    ]"
                                ></span>
                                {{
                                    producthistory.status == 1
                                        ? "Created"
                                        : producthistory.status == 2
                                        ? "Update"
                                        : producthistory.status == 3
                                        ? "Delete"
                                        : "Transfer"
                                }}
                            </span>

                            <!-- ✅ Show branch transfer details BELOW if status is 4 -->
                            <template v-if="producthistory.status == 4">
                                <span
                                    class="ml-6 block text-xs text-gray-600 text-center"
                                >
                                    (
                                    {{ producthistory.from_branch_name }} -
                                    {{ producthistory.to_branch_name }})
                                </span>
                            </template>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="mt-4">
                <PaginationLinks :paginator="ProductHistory" />
            </div>
        </div>
    </div>
</template>
