<script setup>
import { usePage } from "@inertiajs/vue3";

defineProps({
    products: Array,
});

const page = usePage();
const acct_roles = page.props.auth?.user?.acct_roles;
</script>
<template>
    <div>
        <table
            class="min-w-full table-auto border-collapse border border-gray-300 shadow-xl"
        >
            <thead class="bg-gray-100">
                <tr>
                    <th
                        class="px-4 py-2 text-left text-xl font-medium text-gray-800"
                    >
                        Remove
                    </th>
                    <th
                        class="px-4 py-2 text-left text-xl font-medium text-gray-800"
                    >
                        From
                    </th>
                    <th
                        class="px-4 py-2 text-left text-xl font-medium text-gray-800"
                    >
                        Receive
                    </th>
                    <th
                        class="px-4 py-2 text-left text-xl font-medium text-gray-800"
                    >
                        Product code
                    </th>
                    <th
                        class="px-4 py-2 text-left text-xl font-medium text-gray-800"
                    >
                        Product Name
                    </th>
                    <th
                        class="px-4 py-2 text-left text-xl font-medium text-gray-800"
                        v-if="[1, 2].includes($page.props.auth.user.acct_roles)"
                    >
                        Cost Price
                    </th>
                    <th
                        class="px-4 py-2 text-left text-xl font-medium text-gray-800"
                        v-if="[1, 2].includes($page.props.auth.user.acct_roles)"
                    >
                        Retail Price
                    </th>
                    <th
                        class="px-4 py-2 text-left text-xl font-medium text-gray-800"
                        v-if="[1, 2].includes($page.props.auth.user.acct_roles)"
                    >
                        Wholesale Price
                    </th>
                    <th
                        class="px-4 py-2 text-left text-xl font-medium text-gray-800"
                    >
                        Quantity
                    </th>
                    <th
                        class="px-4 py-2 text-left text-xl font-medium text-gray-800"
                    >
                        Total
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-400">
                <tr v-for="(product, index) in products" :key="index">
                    <td
                        class="px-4 py-2 text-left text-xl font-medium text-gray-600"
                    >
                        <TrashIcon
                            @click="$emit('remove', index)"
                            class="w-9 h-9 text-amber-500 cursor-pointer"
                        />
                    </td>
                    <td
                        class="px-4 py-2 text-left text-xl font-medium text-gray-600"
                    >
                        {{ product.from_branch_name }}
                    </td>
                    <td
                        class="px-4 py-2 text-left text-xl font-medium text-gray-600"
                    >
                        {{ product.to_branch_name }}
                    </td>
                    <td
                        class="px-4 py-2 text-left text-xl font-medium text-gray-600"
                    >
                        {{ product.product_code }}
                    </td>
                    <td
                        class="px-4 py-2 text-left text-xl font-medium text-gray-600"
                    >
                        {{ product.description }}
                    </td>
                    <td
                        class="px-4 py-2 text-left text-xl font-medium text-gray-600"
                        v-if="[1, 2].includes($page.props.auth.user.acct_roles)"
                    >
                        {{ product.cost_price }}
                    </td>
                    <td
                        class="px-4 py-2 text-left text-xl font-medium text-gray-600"
                        v-if="[1, 2].includes($page.props.auth.user.acct_roles)"
                    >
                        {{ product.retail_price }}
                    </td>
                    <td
                        class="px-4 py-2 text-left text-xl font-medium text-gray-600"
                        v-if="[1, 2].includes($page.props.auth.user.acct_roles)"
                    >
                        {{ product.wholesale_price }}
                    </td>
                    <td
                        class="px-4 py-2 text-left text-xl font-medium text-gray-600"
                    >
                        {{ product.quantity }}
                    </td>
                    <td
                        class="px-4 py-2 text-left text-xl font-medium text-gray-600"
                    >
                        {{ product.total }}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
