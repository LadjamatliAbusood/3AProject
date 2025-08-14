<script setup>
import { ref, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";

import CardView from "../Components/CardView.vue";
import { debounce } from "lodash";
import PaginationLink from "../Components/PaginationLink.vue";
import ExportTable from "../Components/ExportTable.vue";
import axios from "axios";

const props = defineProps({
    branch: Object,
    total_quantity: Number,
    total_cost: Number,
    total_retail: Number,
    total_wholesale: Number,
    products: Object,
    searchTerm: String,
});
const page = usePage();
const acct_roles = page.props.auth?.user?.acct_roles;

function getBranchRoute() {
    if (acct_roles === 1) {
        return `/admin/branch/${props.branch.branch_name}`;
    } else if (acct_roles === 2) {
        return `/administrator/branch/${props.branch.branch_name}`;
    }
}
const search = ref(props.searchTerm || "");

// Auto-search on input
watch(
    search,
    debounce((value) => {
        router.get(
            getBranchRoute(),
            { search: value },
            { preserveState: true, preserveScroll: true }
        );
    }, 400)
);

const editQuantity = (item) => {
    const quantityStatus = item.branchstock?.quantity_status ?? 0;
    const branchProductId = item.id;

    if (quantityStatus >= 0) {
        Swal.fire({
            icon: "info",
            title: "No Loss Detected",
            text: "This item has no lost quantity to return.",
        });
        return;
    }

    Swal.fire({
        title: "Return Lost Stock",
        input: "number",
        inputValue: Math.abs(quantityStatus),
        inputAttributes: {
            min: 1,
            max: Math.abs(quantityStatus),
        },
        showCancelButton: true,
        confirmButtonText: "Return",
        cancelButtonText: "Cancel",
        preConfirm: (inputVal) => {
            const returnedQty = parseInt(inputVal);
            if (isNaN(returnedQty) || returnedQty <= 0) {
                Swal.showValidationMessage("Enter a valid number");
                return false;
            }
            return returnedQty;
        },
    }).then((result) => {
        if (result.isConfirmed) {
            const return_quantity = result.value;

            axios
                .post("/branch-product/update-quantity", {
                    id: branchProductId,
                    return_quantity,
                })
                .then((res) => {
                    Swal.fire({
                        icon: "success",
                        title: "Stock Returned",
                        text: `Quantity successfully returned.`,
                    }).then(() => {
                        location.reload();
                    });
                })
                .catch((err) => {
                    Swal.fire({
                        icon: "error",
                        title: "Failed",
                        text:
                            err.response?.data?.message ||
                            "Something went wrong.",
                    });
                });
        }
    });
};
</script>

<template>
    <Head :title="` |  Branch`" />
    <div data-aos="fade-up">
        <div class="flex flex-wrap justify-center gap-4 mb-3">
            <CardView
                title="Quantity"
                :value="total_quantity"
                barColor="bg-amber-500"
            />
            <CardView
                title="Cost"
                :value="`₱ ${parseFloat(total_cost).toLocaleString(undefined, {
                    maximumFractionDigits: 0,
                })}`"
                barColor="bg-blue-500"
            />
            <CardView
                title="Retail"
                :value="`₱ ${parseFloat(total_retail).toLocaleString(
                    undefined,
                    {
                        maximumFractionDigits: 0,
                    }
                )}`"
                barColor="bg-red-500"
            />
            <CardView
                title="Wholesale"
                :value="`₱ ${parseFloat(total_wholesale).toLocaleString(
                    undefined,
                    {
                        maximumFractionDigits: 0,
                    }
                )}`"
                barColor="bg-green-500"
            />
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md w-full overflow-x-autos">
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
                <!--      
                    @input="updateSearch" -->

                <!-- Export Button -->
                <ExportTable
                    table-id="product-table"
                    filename="productBranch-table.xlsx"
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
                            Barcode
                        </th>
                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                        >
                            Category
                        </th>

                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                        >
                            Product name
                        </th>
                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                        >
                            Cost Price
                        </th>
                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                        >
                            Retail
                        </th>
                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                        >
                            Wholesale
                        </th>
                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                        >
                            Available Qty
                        </th>
                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                        >
                            Status
                        </th>
                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-800"
                        >
                            Action
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr
                        v-for="item in products.data"
                        :key="item.id"
                        class="hover:bg-gray-100"
                    >
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            <img
                                v-if="item.product?.barcode"
                                :src="'/' + item.product?.barcode"
                                alt="barcode"
                                class="h-5"
                            />
                            <p class="text-md text-left text-gray-700">
                                {{ item.product?.product_code }}
                            </p>
                        </td>
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            {{ item.product?.category?.category_name }}
                        </td>
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            {{ item.product?.description }}
                        </td>
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            ₱{{ item.cost_price }}
                        </td>
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            {{ item.retail_price }}
                        </td>
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            ₱{{ item.wholesale_price }}
                        </td>
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            {{ item.quantity }}
                        </td>
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            <div>
                                {{ item.branchstock?.quantity_status ?? "-" }}
                            </div>
                            <span
                                v-if="item.branchstock?.quantity_status < 0"
                                class="text-xs text-red-500"
                            >
                                Loss
                            </span>
                            <span
                                v-else-if="
                                    item.branchstock?.quantity_status > 0
                                "
                                class="text-xs text-green-500"
                            >
                                Exceed
                            </span>
                            <span
                                v-else-if="
                                    item.branchstock?.quantity_status == 0
                                "
                                class="text-xs text-blue-500"
                            >
                                Exact
                            </span>
                            <span v-else class="text-xs text-gray-400">-</span>
                        </td>
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            <button @click="editQuantity(item)">
                                <PencilSquareIcon
                                    class="w-6 h-6 mr-2 text-blue-500 cursor-pointer"
                                />
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
            <PaginationLink :paginator="products" />
        </div>
    </div>
</template>
