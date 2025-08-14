<template>
    <div>
        <!-- Trigger -->
        <button @click="openSupplierModal(supplier)">
            <EyeIcon class="w-6 h-6 ml-2 text-amber-400 cursor-pointer" />
        </button>

        <!-- Modal -->
        <Modal :show="showModal" @close="showModal = false">
            <div class="flex items-center justify-between my-4">
                <ExportTable
                    table-id="supplier-summary-table"
                    filename="supplier-summary-table.xlsx"
                />
                <h2 class="text-xl font-semibold">
                    Supplier: {{ selectedSupplier?.supplier_name }}
                </h2>
            </div>
            <table
                class="min-w-full border divide-y divide-gray-200"
                id="supplier-summary-table"
            >
                <thead class="bg-gray-100">
                    <tr>
                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            account
                        </th>
                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            Date
                        </th>

                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            Total Qty
                        </th>
                        <th
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            Total Cost
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(row, index) in summaries.data"
                        :key="index"
                        class="hover:bg-gray-100"
                    >
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            {{ row.account }}
                        </td>
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            {{ row.date }}
                        </td>

                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            {{ row.total_quantity }}
                        </td>
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            ₱{{
                                Number(row.total_cost).toLocaleString(
                                    undefined,
                                    { minimumFractionDigits: 2 }
                                )
                            }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <PaginationLink :paginator="summaries" @page-changed="loadPage" />
        </Modal>
    </div>
</template>

<script>
import Modal from "../Components/Modal.vue";
import PaginationLink from "../Components/PaginationLink.vue";
import axios from "axios";
import ExportTable from "../Components/ExportTable.vue";

export default {
    components: { Modal, PaginationLink, ExportTable },
    props: {
        supplier: Object,
    },
    data() {
        return {
            showModal: false,
            selectedSupplier: null,
            summaries: {
                data: [],
                links: [],
            },
        };
    },
    methods: {
        async openSupplierModal(supplier) {
            this.selectedSupplier = supplier;
            this.showModal = true;
            this.loadPage(1);
        },
        async loadPage(page = 1) {
            try {
                const response = await axios.get(
                    `/supplier/${this.selectedSupplier.id}/summary?page=${page}`
                );
                this.summaries = response.data;
            } catch (error) {
                console.error("Failed to fetch summary:", error);
            }
        },
    },
};
</script>
