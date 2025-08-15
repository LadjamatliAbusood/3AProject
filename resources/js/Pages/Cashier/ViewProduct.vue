<script setup>
import { ref, watch } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import PaginationLinks from "../Components/PaginationLink.vue";
import CashierLayout from "../../Layouts/CashierLayout.vue";
import { debounce } from "lodash";

defineOptions({ layout: CashierLayout });

const props = defineProps({
    ProductView: Object,
    branch: String,
    branch_name: String,
    searchTerm: String,
});

const search = ref(props.searchTerm);
watch(
    search,
    debounce((q) => {
        router.get(
            `/cashier/${props.branch}/view`,
            { search: q },
            { preserveState: true }
        );
    }, 100)
);
</script>

<template>
    <Head title=" | View" />
    <div data-aos="fade-up">
        <div
            class="bg-gray-100 p-6 rounded-lg shadow-md w-full overflow-x-auto"
        >
            <!-- Search Input -->
            <input
                type="search"
                v-model="search"
                placeholder="Search..."
                class="w-full md:w-1/2 p-2 border rounded-lg mb-4"
            />

            <table
                class="min-w-full table-auto border-collapse border border-gray-300 shadow-md"
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
                            Product Name
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
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr
                        v-for="(pv, index) in ProductView.data"
                        :key="pv.id"
                        class="hover:bg-gray-100"
                    >
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            {{ pv.product_code }}
                        </td>

                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            {{ pv.category_name }}
                        </td>
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            {{ pv.description }}
                        </td>

                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            {{ pv.retail_price }}
                        </td>
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            {{ pv.wholesale_price }}
                        </td>
                        <td
                            class="px-4 py-2 text-left text-md font-medium text-gray-600"
                        >
                            {{ pv.quantity }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="mt-4">
                <PaginationLinks :paginator="ProductView" />
            </div>
        </div>
    </div>
</template>
