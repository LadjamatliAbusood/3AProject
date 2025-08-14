<script setup>
import { ref, watch } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import ExportTable from "../Components/ExportTable.vue";
import CardView from "../Components/CardView.vue";
import DateRangeFilter from "../Components/DateRangeFilter.vue";
import PaginationLinks from "../Components/PaginationLink.vue";
import SelectInput from "../Components/SelectInput.vue";

const page = usePage();
const acct_roles = page.props.auth?.user?.acct_roles;
const branch = page.props.auth?.user?.branch_name;

const props = defineProps({
    salesreport: Array,
    totals: Object,
    branches: Array,
});

// Reactive state
const search = ref("");
const dateFilterRef = ref(null);

// Build correct history route
function getHistoryRoute() {
    if (acct_roles === 3) {
        return `/supervisor/${branch}/salesreport`;
    } else if (acct_roles === 2) {
        return `/administrator/salesreport`;
    } else {
        return `/admin/salesreport`;
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

const form = ref({
    branch_id: "",
});
router.get(
    getHistoryRoute(),
    {
        ...form.value,
        search: search.value,
        date_from: dateFilterRef.value?.localDateFrom,
        date_to: dateFilterRef.value?.localDateTo,
    },
    {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    }
);

watch(
    () => form.value.branch_id,
    (newBranchId) => {
        router.get(
            getHistoryRoute(),
            {
                branch_id: newBranchId,
                search: search.value,
                date_from: dateFilterRef.value?.localDateFrom,
                date_to: dateFilterRef.value?.localDateTo,
            },
            {
                preserveScroll: true,
                preserveState: true,
                replace: true,
            }
        );
    }
);
function deleteSales(sr) {
    Swal.fire({
        title: "Are you sure?",
        text: `This will permanently delete sales: ${sr.description}`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete it!",
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/admin/salesreport/${sr.id}`, {
                onSuccess: () => {
                    Swal.fire("Deleted!", "sales has been deleted.", "success");
                },
            });
        }
    });
}
</script>

<template>
    <Head title=" | History" />
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
                v-if="$page.props.auth.user.acct_roles === 1"
                title="Gross"
                :value="`₱ ${parseFloat(totals.net_amount || 0).toLocaleString(
                    undefined,
                    {
                        maximumFractionDigits: 0,
                    }
                )}`"
                barColor="bg-blue-500"
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

        <div class="bg-white p-6 rounded-lg shadow-md w-full overflow-x-auto">
            <div class="flex flex-wrap items-center gap-4 mb-4">
                <!-- Search Input -->
                <input
                    type="search"
                    v-model="search"
                    @input="updateSearch"
                    placeholder="Search..."
                    class="p-2 border rounded-lg w-60 md:w-90"
                />

                <!-- Branch Selector -->
                <SelectInput
                    v-if="[1, 2].includes($page.props.auth.user.acct_roles)"
                    v-model="form.branch_id"
                    :options="[
                        { label: 'All Branches', value: '' },
                        ...props.branches.map((branch) => ({
                            label: branch.branch_name,
                            value: branch.id,
                        })),
                    ]"
                    class="w-48 mt-2"
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

                <!-- Export Button -->
                <ExportTable
                    table-id="product-table"
                    filename="productSales-table.xlsx"
                />
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
                            v-if="
                                [1, 2].includes(
                                    $page.props.auth.user.acct_roles
                                )
                            "
                        >
                            Cost Price
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
                            Sales
                        </th>
                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                            v-if="$page.props.auth.user.acct_roles === 1"
                        >
                            Gross
                        </th>
                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                            v-if="$page.props.auth.user.acct_roles === 1"
                        >
                            Action
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
                            {{ sr.branch_name }}
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
                            v-if="
                                [1, 2].includes(
                                    $page.props.auth.user.acct_roles
                                )
                            "
                        >
                            {{ parseFloat(sr.cost_price).toLocaleString() }}
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
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                            v-if="$page.props.auth.user.acct_roles === 1"
                        >
                            {{ parseFloat(sr.net_amount).toLocaleString() }}
                        </td>
                        <button
                            v-if="$page.props.auth.user.acct_roles === 1"
                            @click="deleteSales(sr)"
                        >
                            <TrashIcon
                                class="w-6 h-6 mt-2 text-red-500 cursor-pointer"
                            />
                        </button>
                    </tr>
                </tbody>
            </table>

            <div class="mt-4">
                <PaginationLinks :paginator="salesreport" />
            </div>
        </div>
    </div>
</template>
