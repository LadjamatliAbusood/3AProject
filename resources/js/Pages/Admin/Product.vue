<script setup>
import { router, useForm, usePage } from "@inertiajs/vue3";
import { ref, reactive, watch, computed } from "vue";
import TextInput from "../Components/TextInput.vue";
import SelectInput from "../Components/SelectInput.vue";
import PrimaryButton from "../Components/PrimaryButton.vue";
import axios from "axios";
import PaginationLinks from "../Components/PaginationLink.vue";
import ExportTable from "../Components/ExportTable.vue";
import { debounce } from "lodash";
import { BarsArrowDownIcon } from "@heroicons/vue/16/solid";

const page = usePage();
const userRole = page.props.auth?.user?.acct_roles;
const branch = page.props.auth?.user?.branch_name;

const showCostPrice = computed(() => {
    // Show cost_price if:
    // - Role is 1 or 2 (always)
    // - Role is 3 but not editing
    return userRole !== 3 || (userRole === 3 && !isEditing.value);
});
const showForm = ref(true);
const props = defineProps({
    Suppliers: Object,
    nextCode: String,
    Branches: Array,
    Product: Array,
    searchTerm: String,
    Category: Object,
});

const isEditing = ref(false);
const label = computed(() =>
    isEditing.value ? "Update Product" : "Add Product"
);
const supplierOptions = props.Suppliers.map((s) => ({
    value: s.id,
    label: s.supplier_name,
}));

const CategoryOptions = props.Category.map((s) => ({
    value: s.id,
    label: s.category_name,
}));
const form = useForm({
    account: page.props.auth.user.acct_name,
    supplier_id: null,
    category_id: null,
    product_code: null,
    barcode: null,
    description: null,
    branch_id: page.props.auth.user.branch_id,
    quantity: null,
    cost_price: null,
    retail_price: null,
    wholesale_price: "0",
    add_quantity: 0,
});
watch(
    () => form.description,
    (newVal) => {
        if (newVal && !isEditing.value) {
            form.product_code = props.nextCode;
            form.barcode = `/barcode/${props.nextCode}`;
        } else if (!newVal && !isEditing.value) {
            form.product_code = null;
            form.barcode = null;
        }
    }
);
watch(
    () => form.retail_price,
    (retail) => {
        if (parseFloat(retail) < parseFloat(form.cost_price)) {
            form.errors.retail_price =
                "Retail price should not be less than cost price.";
        } else {
            form.errors.retail_price = null;
        }
    }
);

const productBaseUrl = computed(() => {
    if (userRole.value === 3) return `/supervisor/${branch}/product`;
    if (userRole.value === 2) return `/administrator/product`;
    return `/admin/product`;
});

const submit = () => {
    if (isEditing.value) {
        form.put(`${productBaseUrl.value}/${isEditing.value.id}`, {
            onSuccess: () => {
                form.reset();
                isEditing.value = false;
                Swal.fire({
                    icon: "success",
                    title: "Updated!",
                    text: "Product updated successfully.",
                    timer: 2000,
                    showConfirmButton: false,
                });
            },
        });
    } else {
        form.post(`${productBaseUrl.value}`, {
            onSuccess: () => {
                form.reset();
                Swal.fire({
                    icon: "success",
                    title: "Success!",
                    text: "Product added successfully.",
                    timer: 2000,
                    showConfirmButton: false,
                });
            },
        });
    }
};

function deleteProduct(product) {
    Swal.fire({
        title: "Are you sure?",
        text: `This will permanently delete product: ${product.description}`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete it!",
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`${productBaseUrl.value}/${product.id}`, {
                onSuccess: () => {
                    Swal.fire(
                        "Deleted!",
                        "Product has been deleted.",
                        "success"
                    );
                },
            });
        }
    });
}

function editProduct(product) {
    const branchId = page.props.auth.user.branch_id;
    const userBranchProduct = product.branch_products.find(
        (bp) => bp.branch_id === branchId
    );
    isEditing.value = product; // ✅ store the whole product
    form.description = product.description;
    form.product_code = product.product_code;
    form.cost_price = userBranchProduct?.cost_price ?? 0;
    form.retail_price = userBranchProduct?.retail_price ?? 0;
    form.wholesale_price = userBranchProduct?.wholesale_price ?? 0;
    form.quantity = userBranchProduct?.quantity ?? 0;
    form.add_quantity = 0;
    form.supplier_id = product.supplier_id;
    form.category_id = product.category_id;
}

function getHistoryRoute() {
    if (userRole === 3) {
        return `/supervisor/${branch}/product`;
    } else if (userRole === 2) {
        return `/administrator/product`;
    } else {
        return `/admin/product`;
    }
}

const search = ref(props.searchTerm);
watch(
    search,
    debounce(
        (q) =>
            router.get(
                getHistoryRoute(),
                { search: q },
                { preserveState: true }
            ),
        500
    )
);
function triggerSale(product, branchProduct) {
    Swal.fire({
        title: "Enter Bargain Details",
        html:
            `<label for="swal-qty" style="display:block; text-align:left; ">Quantity (max ${branchProduct.quantity})</label>` +
            `<input id="swal-qty" class="swal2-input" type="number" max="${branchProduct.quantity}" placeholder="Enter quantity">` +
            `<label for="swal-cost" style="display:block; text-align:left; ">Cost Price</label>` +
            `<input id="swal-cost" class="swal2-input" type="number" value="${branchProduct.cost_price}" placeholder="Enter cost price">` +
            `<label for="swal-retail" style="display:block; text-align:left; ">Retail Price</label>` +
            `<input id="swal-retail" class="swal2-input" type="number" value="${branchProduct.retail_price}" placeholder="Enter retail price">` +
            `<label for="swal-wholesale" style="display:block; text-align:left; ">Wholesale Price</label>` +
            `<input id="swal-wholesale" class="swal2-input" type="number" value="${branchProduct.wholesale_price}" placeholder="Enter wholesale price">`,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: "Bargain",
        preConfirm: () => {
            const qty = parseInt(document.getElementById("swal-qty").value);
            const cost = parseFloat(document.getElementById("swal-cost").value);
            const retail = parseFloat(
                document.getElementById("swal-retail").value
            );
            const wholesale = parseFloat(
                document.getElementById("swal-wholesale").value
            );

            if (!qty || qty <= 0 || qty > branchProduct.quantity) {
                Swal.showValidationMessage("Invalid quantity.");
                return false;
            }

            return {
                quantity: qty,
                cost_price: cost,
                retail_price: retail,
                wholesale_price: wholesale,
            };
        },
    }).then((result) => {
        if (result.isConfirmed) {
            axios
                .post(`/product/sale/${product.id}`, {
                    ...result.value,
                    branch_id: branchProduct.branch_id,
                })
                .then(() => {
                    Swal.fire(
                        "Updated!",
                        "Product quantity and prices updated.",
                        "success"
                    );
                    location.reload(); // optional
                })
                .catch((error) => {
                    Swal.fire(
                        "Error",
                        error.response?.data?.error || "Something went wrong",
                        "error"
                    );
                });
        }
    });
}
</script>
<template>
    <Head title=" | Product" />
    <div>
        <div class="text-right">
            <button
                @click="showForm = !showForm"
                class="px-2 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600"
            >
                <EyeIcon v-if="showForm" class="w-5 h-5 text-wite-" />
                <EyeSlashIcon v-else class="w-5 h-5 text-white-" />
            </button>
        </div>
        <div
            class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-1 gap-4 p-4"
            data-aos="fade-up"
        >
            <!-- Add Product -->

            <div
                v-if="showForm"
                class="bg-white p-6 rounded-lg shadow-md w-full max-w-screen-xl mx-auto"
            >
                <h3
                    class="text-2xl font-semibold mb-6 text-center md:text-left text-gray-800"
                >
                    Add | Edit Product
                </h3>

                <form @submit.prevent="submit">
                    <div
                        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4"
                    >
                        <TextInput
                            name="Product Name"
                            v-model="form.description"
                            :message="form.errors.description"
                            :readonly="
                                isEditing &&
                                $page.props.auth.user.acct_roles === 3
                            "
                        />

                        <TextInput
                            name="Cost Price"
                            v-if="showCostPrice"
                            v-model="form.cost_price"
                            :message="form.errors.cost_price"
                            type="number"
                        />

                        <TextInput
                            name="Retail Price"
                            v-model="form.retail_price"
                            :message="form.errors.retail_price"
                            type="number"
                        />

                        <TextInput
                            name="Wholesale Price"
                            v-model="form.wholesale_price"
                            :message="form.errors.wholesale_price"
                            type="number"
                        />

                        <TextInput
                            name="Quantity"
                            v-model="form.quantity"
                            :message="form.errors.quantity"
                            :readonly="isEditing"
                            :class="{
                                'border-none': isEditing,
                            }"
                            type="number"
                        />
                        <TextInput
                            v-if="isEditing"
                            name="Add Quantity"
                            v-model="form.add_quantity"
                            :message="form.errors.add_quantity"
                            type="number"
                        />
                        <SelectInput
                            name="Category"
                            v-model="form.category_id"
                            :options="CategoryOptions"
                            :message="form.errors.category_id"
                        />
                        <SelectInput
                            name="Suppliers"
                            v-model="form.supplier_id"
                            :options="supplierOptions"
                            :message="form.errors.supplier_id"
                        />

                        <TextInput
                            name="Product Code"
                            v-model="form.product_code"
                            :message="form.errors.product_code"
                            :readonly="true"
                            class="border-none"
                        />

                        <div
                            class="w-60 h-10 mt-6 text-center hidden"
                            v-if="form.barcode"
                        >
                            <img
                                :src="form.barcode"
                                alt="Barcode"
                                class="w-full h-full object-contain"
                            />
                            <p class="text-sm text-gray-700 mt-1">
                                {{ form.product_code }}
                            </p>
                        </div>

                        <!-- Hidden Account Field -->
                    </div>
                    <div class="text-right">
                        <PrimaryButton
                            :label="label"
                            :disabled="form.processing"
                            :processing="form.processing"
                        />
                    </div>
                </form>
            </div>

            <!-- Table -->
            <div
                class="bg-white p-6 rounded-lg shadow-md w-full overflow-x-auto"
            >
                <div
                    class="flex flex-col md:flex-row justify-between items-center mb-4"
                >
                    <input
                        type="search"
                        v-model="search"
                        placeholder="Search..."
                        class="w-full md:w-1/2 p-2 border rounded-lg mt-2 md:mt-0"
                    />
                    <ExportTable
                        table-id="product-table"
                        filename="product-table.xlsx"
                    />
                </div>
                <table
                    class="min-w-full table-auto border-collapse border border-gray-300 shadow-md"
                    id="product-table"
                >
                    <thead class="bg-gray-100">
                        <tr>
                            <th
                                class="px-4 py-2 text-left text-md font-medium text-gray-600"
                            >
                                Barcode
                            </th>
                            <th
                                class="px-4 py-2 text-left text-md font-medium text-gray-600"
                            >
                                Supplier
                            </th>
                            <th
                                class="px-4 py-2 text-left text-md font-medium text-gray-600"
                            >
                                Branch
                            </th>
                            <th
                                class="px-4 py-2 text-left text-md font-medium text-gray-600"
                            >
                                Category
                            </th>
                            <th
                                class="px-4 py-2 text-left text-md font-medium text-gray-600"
                            >
                                Product Name
                            </th>
                            <th
                                v-if="
                                    [1, 2].includes(
                                        $page.props.auth.user.acct_roles
                                    )
                                "
                                class="px-4 py-2 text-left text-md font-medium text-gray-600"
                            >
                                Cost Price
                            </th>
                            <th
                                class="px-4 py-2 text-left text-md font-medium text-gray-600"
                            >
                                Retail Price
                            </th>
                            <th
                                class="px-4 py-2 text-left text-md font-medium text-gray-600"
                            >
                                Wholesale Price
                            </th>
                            <th
                                class="px-4 py-2 text-left text-md font-medium text-gray-600"
                            >
                                Quantity
                            </th>
                            <th
                                class="px-4 py-2 text-left text-md font-medium text-gray-600"
                            >
                                Action
                            </th>
                        </tr>
                    </thead>

                    <tbody class="bg-white divide-y divide-gray-200">
                        <template
                            v-for="(product, index) in Product.data"
                            :key="product.id"
                        >
                            <tr
                                v-for="(
                                    bp, bpIndex
                                ) in product.branch_products.filter((bp) =>
                                    $page.props.auth.user.acct_roles === 3
                                        ? bp.branch_id ===
                                          $page.props.auth.user.branch_id
                                        : true
                                )"
                                :key="`${product.id}-${bp.id}`"
                                class="hover:bg-gray-100"
                            >
                                <td
                                    class="px-4 py-2 text-left text-md font-medium text-gray-600"
                                >
                                    <img
                                        v-if="product.barcode"
                                        :src="`/${product.barcode}`"
                                        alt="barcode"
                                        class="h-5"
                                    />

                                    <p class="text-md text-left text-gray-700">
                                        {{ product.product_code }}
                                    </p>
                                </td>

                                <td
                                    class="px-4 py-2 text-left text-md font-medium text-gray-600"
                                >
                                    {{ product.supplier?.supplier_name }}
                                </td>

                                <td
                                    class="px-4 py-2 text-left text-md font-medium text-gray-600"
                                >
                                    {{ bp.branch?.branch_name }}
                                </td>
                                <td
                                    class="px-4 py-2 text-left text-md font-medium text-gray-600"
                                >
                                    {{ product.category?.category_name }}
                                </td>

                                <td
                                    class="px-4 py-2 text-left text-md font-medium text-gray-600"
                                >
                                    {{ product.description }}
                                </td>

                                <td
                                    v-if="
                                        [1, 2].includes(
                                            $page.props.auth.user.acct_roles
                                        )
                                    "
                                    class="px-4 py-2 text-left text-md font-medium text-gray-600"
                                >
                                    {{ bp.cost_price }}
                                </td>

                                <td
                                    class="px-4 py-2 text-left text-md font-medium text-gray-600"
                                >
                                    {{ bp.retail_price }}
                                </td>

                                <td
                                    class="px-4 py-2 text-left text-md font-medium text-gray-600"
                                >
                                    {{ bp.wholesale_price }}
                                </td>

                                <td
                                    class="px-4 py-2 text-left text-md font-medium text-gray-600"
                                >
                                    {{ bp.quantity }}
                                </td>

                                <td class="px-4 py-2 flex space-x-2">
                                    <button
                                        v-if="
                                            bp.branch_id ===
                                            $page.props.auth.user.branch_id
                                        "
                                        @click="editProduct(product)"
                                    >
                                        <PencilSquareIcon
                                            class="w-6 h-6 mr-2 text-blue-500 cursor-pointer"
                                        />
                                    </button>
                                    <button
                                        v-if="
                                            [1, 2].includes(
                                                $page.props.auth.user.acct_roles
                                            )
                                        "
                                        @click="triggerSale(product, bp)"
                                    >
                                        <BarsArrowDownIcon
                                            class="w-6 h-6 mr-2 text-green-500 cursor-pointer"
                                        />
                                    </button>

                                    <!-- &&
                                            bp.branch_id ===
                                                $page.props.auth.user.branch_id -->

                                    <button
                                        v-if="
                                            ($page.props.auth.user
                                                .acct_roles !== 3 &&
                                                bp.branch_id ===
                                                    $page.props.auth.user
                                                        .branch_id) ||
                                            bp.quantity === 0
                                        "
                                        @click="deleteProduct(product)"
                                    >
                                        <TrashIcon
                                            class="w-6 h-6 mr-2 text-red-500 cursor-pointer"
                                        />
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>

                <div>
                    <PaginationLinks :paginator="Product" />
                </div>
            </div>
        </div>
    </div>
</template>
