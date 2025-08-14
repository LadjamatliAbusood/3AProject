<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import PaginationLinks from "@/Pages/Components/PaginationLink.vue";
import { ref, computed, watch } from "vue";
import TextInput from "@/Pages/Components/TextInput.vue";
import PrimaryButton from "../Components/PrimaryButton.vue";
import axios from "axios";

import { debounce } from "lodash";
import ExportTable from "../Components/ExportTable.vue";
import { readonly } from "vue";

const props = defineProps({
    barcode: String,
    searchTerm: Object,
    product: Object,
});

const page = usePage();
const acct_roles = page.props.auth?.user?.acct_roles;
const branch = page.props.auth?.user?.branch_name;
const branchId = page.props.auth.user.branch_id;

const editingStock = ref(null);
const updatedRows = ref({});
const savedRows = localStorage.getItem("updatedRows");
if (savedRows) {
    updatedRows.value = JSON.parse(savedRows);
}
watch(
    updatedRows,
    (val) => {
        localStorage.setItem("updatedRows", JSON.stringify(val));
    },
    { deep: true }
);
const form = useForm({
    product_code: "",
    description: "",
    quantity: null,
});

const saveForm = useForm({
    updates: [],
});
const label = computed(() =>
    editingStock.value ? "Update Stock" : "Add Stock"
);

const editStock = (stock) => {
    const userBranchProduct = stock.branch_products.find(
        (bp) => bp.branch_id === branchId
    );

    const currentInput = updatedRows.value[stock.id]?.inputQty ?? 0;
    editingStock.value = stock;

    form.product_code = stock.product_code;
    form.description = stock.description;
    form.quantity = currentInput || 0;
};

const submit = () => {
    if (!editingStock.value) return;

    const stock = editingStock.value;
    const userBranchProduct = stock.branch_products.find(
        (bp) => bp.branch_id === branchId
    );

    const systemQty = userBranchProduct?.quantity ?? 0;
    const inputQty = parseInt(form.quantity);
    const diff = inputQty - systemQty;
    const status = (diff >= 0 ? "+" : "") + diff;

    updatedRows.value[stock.id] = {
        inputQty,
        systemQty,
        status,
        showSystem: true,
    };

    editingStock.value = null;
    form.reset();
};

const getStatusPriority = (stock) => {
    const update = updatedRows.value[stock.id];
    if (!update) return 3; // Lowest priority if not updated

    const systemQty = update.systemQty;
    const inputQty = update.inputQty;

    if (inputQty < systemQty) return 0; // Red
    if (inputQty > systemQty) return 1; // Yellow
    if (inputQty === systemQty) return 2; // Green
    return 3;
};

const sortedProducts = computed(() => {
    return [...props.product.data].sort((a, b) => {
        const priorityA = getStatusPriority(a);
        const priorityB = getStatusPriority(b);
        return priorityA - priorityB;
    });
});

const getRowClass = (stock) => {
    const update = updatedRows.value[stock.id];
    if (!update) return "";

    const systemQty = update.systemQty;
    const inputQty = update.inputQty;

    if (inputQty === systemQty) return "bg-green-400";
    if (inputQty < systemQty) return "bg-red-400";
    if (inputQty > systemQty) return "bg-yellow-400";

    return "";
};

const sortByStatus = () => {
    props.product.sort((a, b) => {
        const priorityA = getStatusPriority(a);
        const priorityB = getStatusPriority(b);
        return priorityA - priorityB;
    });
};

const search = ref(props.searchTerm);
watch(
    search,
    debounce(
        (q) =>
            router.get(
                `/supervisor/${branch}/inventory`,
                { search: q },
                { preserveState: true }
            ),
        500
    )
);

const currentDate = new Date().toLocaleDateString("en-PH", {
    year: "numeric",
    month: "long",
    day: "numeric",
});
const saveStockAdjustments = () => {
    const updates = Object.entries(updatedRows.value).map(
        ([productId, row]) => ({
            product_id: parseInt(productId),
            quantity: row.inputQty,
            quantity_status: row.status,
        })
    );

    if (updates.length === 0) {
        Swal.fire({
            icon: "info",
            title: "No Changes",
            text: "There are no modified items to save.",
        });
        return;
    }

    Swal.fire({
        icon: "warning",
        title: "Download Excel First",
        text: "Please make sure you downloaded the Excel file before saving.",
        showCancelButton: true,
        confirmButtonText: "Yes, continue",
        cancelButtonText: "Cancel",
    }).then((result) => {
        if (!result.isConfirmed) return;

        saveForm.updates = updates;

        saveForm.post(route("supervisor.inventory.update", branch), {
            preserveScroll: true,
            onSuccess: () => {
                updatedRows.value = {};
                localStorage.removeItem("updatedRows");
                editingStock.value = null;

                Swal.fire({
                    icon: "success",
                    title: "Stock Updated",
                    text: "Inventory has been successfully updated.",
                    timer: 2000,
                    showConfirmButton: false,
                });
            },
            onError: (err) => {
                console.error("Save failed", err);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Failed to update inventory. Please try again.",
                });
            },
        });
    });
};
</script>

<template>
    <Head title=" | Inventory" />

    <div
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
        data-aos="fade-up"
    >
        <div class="bg-white p-6 rounded-lg shadow-md h-[380px]">
            <h3 class="text-lg font-semibold">
                {{ editingStock ? "Edit Items" : "Add Items" }}
            </h3>
            <div class="flex items-center justify-center">
                <form
                    class="max-w-md w-[300px] flex flex-col mt-2"
                    @submit.prevent="submit"
                >
                    <TextInput
                        name="Product code"
                        :readonly
                        v-model="form.product_code"
                        :message="form.errors.product_code"
                    />
                    <TextInput
                        name="Product Name"
                        :readonly
                        v-model="form.description"
                        :message="form.errors.description"
                    />
                    <TextInput
                        name="Quantity"
                        type="number"
                        v-model="form.quantity"
                        :message="form.errors.quantity"
                    />
                    <PrimaryButton
                        v-if="editingStock"
                        :label="label"
                        :disabled="form.processing"
                        :processing="form.processing"
                    />
                </form>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md col-span-1 lg:col-span-2">
            <h3 class="text-lg font-semibold text-gray-800">List of Items</h3>
            <div
                class="flex flex-col md:flex-row md:items-center md:justify-between gap-2 mb-4"
            >
                <input
                    type="search"
                    v-model="search"
                    placeholder="Search..."
                    class="w-full md:w-1/2 p-2 border rounded-lg"
                />

                <div class="flex flex-col sm:flex-row gap-2 md:w-auto">
                    <ExportTable
                        table-id="product-table"
                        :filename="`product-stock-${currentDate}.xlsx`"
                    />

                    <PrimaryButton
                        class="text-white hover:bg-yellow-800 bg-yellow-600 font-medium rounded-lg text-sm px-5 py-2.5 focus:outline-none"
                        label="save Items"
                        :disabled="saveForm.processing"
                        @click="saveStockAdjustments"
                    />
                </div>
            </div>

            <div class="w-full overflow-x-auto">
                <table
                    class="min-w-[800px] table-auto border-collapse border border-gray-300 shadow-md text-xs sm:text-sm"
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
                                Input Qty
                            </th>
                            <th
                                class="px-4 py-2 text-left text-md font-medium text-gray-800"
                            >
                                System Qty
                            </th>
                            <th
                                class="px-4 py-2 text-left text-md font-medium text-gray-800 cursor-pointer"
                                @click="sortByStatus"
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
                    <tbody class="bg-white divide-y divide-gray-400">
                        <tr
                            v-for="(stock, index) in sortedProducts"
                            :key="stock.id"
                            :class="[getRowClass(stock), 'transition-colors']"
                        >
                            <td
                                class="px-4 py-2 text-left text-md font-medium text-gray-600"
                            >
                                {{ index + 1 }}
                            </td>
                            <td
                                class="px-4 py-2 text-left text-md font-medium text-gray-600"
                            >
                                {{ stock.product_code }}
                            </td>
                            <td
                                class="px-4 py-2 text-left text-md font-medium text-gray-600"
                            >
                                {{ stock.description }}
                            </td>

                            <td
                                class="px-4 py-2 text-left text-md font-medium text-gray-600"
                            >
                                {{ updatedRows[stock.id]?.inputQty ?? "-" }}
                            </td>

                            <td
                                class="px-4 py-2 text-left text-md font-medium text-gray-600"
                            >
                                <span v-if="updatedRows[stock.id]?.showSystem">
                                    {{ updatedRows[stock.id]?.systemQty }}
                                </span>
                                <span
                                    v-else-if="
                                        stock.branch_products[0]?.quantity !== 0
                                    "
                                    class="select-none text-gray-400"
                                >
                                    ******
                                </span>
                                <span v-else> 0 </span>
                            </td>

                            <td
                                class="px-4 py-2 text-left text-md font-medium text-gray-600"
                            >
                                {{ updatedRows[stock.id]?.status ?? "" }}
                            </td>

                            <td class="px-2 py-2 text-md text-gray-600">
                                <div
                                    class="flex items-center gap-3 justify-center"
                                >
                                    <button @click="editStock(stock)">
                                        <PencilSquareIcon
                                            class="w-6 h-6 text-blue-500 cursor-pointer"
                                        />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <PaginationLinks :paginator="product" />

                <p class="text-xs text-gray-500 mt-1 block sm:hidden">
                    Swipe left/right to scroll
                </p>
            </div>
        </div>
    </div>
</template>
