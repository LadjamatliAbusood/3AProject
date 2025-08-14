<script setup>
import { useForm } from "@inertiajs/vue3";
import PaginationLinks from "@/Pages/Components/PaginationLink.vue";
import { ref, computed } from "vue";
import TextInput from "@/Pages/Components/TextInput.vue";
import SelectInput from "../Components/SelectInput.vue";
import { StatusOptions } from "../libraries/roles.js";
import PrimaryButton from "../Components/PrimaryButton.vue";
import Modal from "../Components/Modal.vue";
import { usePage } from "@inertiajs/vue3";
import SupplierSummary from "./SupplierSummary.vue";

const suppliers = usePage().props.suppliers;
const showModal = ref(false);
const selectedSupplier = ref(null);

function openModal(supplier) {
    selectedSupplier.value = supplier;
    showModal.value = true;
}
const label = computed(() =>
    editingSupplier.value ? "Update Supplier" : "Add Supplier"
);
const props = defineProps({
    Suppliers: Object,
});

const form = useForm({
    supplier_name: null,
    cost: "0",
    status: "1",
});
const editingSupplier = ref(null);
const editSupplier = (supplier) => {
    editingSupplier.value = supplier;
    form.supplier_name = supplier.supplier_name;
    form.status = String(supplier.status);
};

const submit = () => {
    if (editingSupplier.value) {
        form.put(
            `/admin/supplier/${editingSupplier.value.id}?page=${props.Suppliers.current_page}`,
            {
                onSuccess: () => {
                    form.reset();
                    editingSupplier.value = null;
                    Swal.fire({
                        icon: "success",
                        title: "Updated!",
                        text: "Supplier updated successfully.",
                        timer: 2000,
                        showConfirmButton: false,
                    });
                },
            }
        );
    } else {
        form.post("/admin/supplier", {
            onSuccess: () => {
                form.reset();
                Swal.fire({
                    icon: "success",
                    title: "Success!",
                    text: "Supplier added successfully.",
                    timer: 2000,
                    showConfirmButton: false,
                });
            },
        });
    }
};
</script>
<template>
    <Head title=" | Supplier" />

    <div
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
        data-aos="fade-up"
    >
        <!-- Supplier Card -->
        <div class="bg-white p-6 rounded-lg shadow-md h-[300px]">
            <h3 class="text-lg font-semibold">
                {{ editingSupplier ? "Edit Supplier" : "Add Supplier" }}
            </h3>
            <div class="flex items-center justify-center">
                <form
                    class="max-w-md w-[300px] flex flex-col mt-2"
                    @submit.prevent="submit"
                >
                    <TextInput
                        name="Supplier Fullname"
                        v-model="form.supplier_name"
                        :message="form.errors.supplier_name"
                    />
                    <TextInput
                        name="cost"
                        v-model="form.cost"
                        :message="form.errors.cost"
                        hidden
                    />

                    <SelectInput
                        name="Status"
                        v-model="form.status"
                        :options="StatusOptions"
                        :message="form.errors.status"
                    />

                    <PrimaryButton
                        :label="label"
                        :disabled="form.processing"
                        :processing="form.processing"
                    />
                </form>
            </div>
        </div>
        <!-- Supplier Card -->
        <div class="bg-white p-6 rounded-lg shadow-md col-span-1 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">
                    List of Suppliers
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table
                    class="min-w-full table-auto border-collapse border border-gray-300 shadow-md"
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
                                Suppplier Fullname
                            </th>
                            <!-- <th
                                class="px-4 py-2 text-left text-md font-medium text-gray-800"
                            >
                                Cost
                            </th> -->
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
                    <tbody class="bg-white divide-y divide-gray-400">
                        <tr
                            v-for="(supplier, index) in Suppliers.data"
                            :key="supplier.id"
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
                                {{ supplier.supplier_name }}
                            </td>
                            <!-- <td
                                class="px-4 py-2 text-left text-md font-medium text-gray-600"
                            >
                                ₱{{
                                    supplier.cost.toLocaleString(undefined, {
                                        minimumFractionDigits: 2,
                                    })
                                }}
                            </td> -->
                            <td
                                class="px-4 py-2 text-left text-md font-medium text-gray-600"
                            >
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                    :class="{
                                        'bg-green-200 text-green-700':
                                            supplier.status == 1,
                                        'bg-red-200 text-red-700':
                                            supplier.status == 2,
                                    }"
                                >
                                    <span
                                        class="h-2 w-2 rounded-full mr-2"
                                        :class="{
                                            'bg-green-500':
                                                supplier.status == 1,
                                            'bg-red-500': supplier.status == 2,
                                        }"
                                    ></span>
                                    {{
                                        supplier.status == 1
                                            ? "Active"
                                            : "Deactive"
                                    }}
                                </span>
                            </td>

                            <td class="px-2 py-2 text-md text-gray-600">
                                <div
                                    class="flex items-center gap-3 justify-center"
                                >
                                    <button @click="editSupplier(supplier)">
                                        <PencilSquareIcon
                                            class="w-6 h-6 text-blue-500 cursor-pointer"
                                        />
                                    </button>
                                    <!-- <SupplierSummary :supplier="supplier" /> -->
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Show button -->

            <!-- <Modal :show="showModal" @close="showModal = false">
                <h2 class="text-xl font-semibold mb-4">
                    {{ selectedSupplier?.supplier_name }}
                </h2>

                <table class="min-w-full border divide-y divide-gray-200">
                    <thead class="bg-gray-200">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-sm font-medium text-gray-700 border border-gray-300"
                            >
                                Product
                            </th>
                            <th
                                class="px-6 py-3 text-left text-sm font-medium text-gray-700 border border-gray-300"
                            >
                                Cost
                            </th>
                            <th
                                class="px-6 py-3 text-left text-sm font-medium text-gray-700 border border-gray-300"
                            >
                                Qty
                            </th>
                            <th
                                class="px-6 py-3 text-left text-sm font-medium text-gray-700 border border-gray-300"
                            >
                                Total
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200"
                       v-for="product in selectedSupplier?.product ?? []"
                            :key="product.id">
                        <tr
                         v-for="bp in product.branch_products"
                                :key="bp.id"
                         
                        >
                           
                                <tr>
                                    <td
                                        class="px-6 py-3 text-base text-gray-900 border border-gray-300"
                                    >
                                        {{ product.description }}
                                    </td>
                                    <td
                                        class="px-6 py-3 text-base text-gray-900 border border-gray-300"
                                    >
                                        ₱{{ Number(bp.cost_price).toFixed(2) }}
                                    </td>
                                    <td
                                        class="px-6 py-3 text-base text-gray-900 border border-gray-300"
                                    >
                                        {{ bp.quantity }}
                                    </td>
                                    <td
                                        class="px-6 py-3 text-base text-gray-900 border border-gray-300"
                                    >
                                        ₱{{
                                            (
                                                bp.cost_price * bp.quantity
                                            ).toFixed(2)
                                        }}
                                    </td>
                                </tr>
                           
                        </tr>
                    </tbody>
                </table>
            </Modal> -->

            <!-- pagination link -->
            <div>
                <PaginationLinks :paginator="Suppliers" />
            </div>
        </div>
    </div>
</template>
