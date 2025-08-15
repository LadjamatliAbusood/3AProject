<script setup>
import { usePage } from "@inertiajs/vue3";

const props = defineProps({
    products: Array,
    total: Number,
    pay: Number,
    change: Number,
});

const page = usePage();
const username = page.props.auth.user.acct_name;

const currentDate = new Date().toLocaleDateString("en-PH", {
    year: "numeric",
    month: "numeric",
    day: "numeric",
});
</script>

<template>
    <div
        class="w-full max-w-xs mx-auto text-center font-sans text-sm p-4 border"
    >
        <h1 class="font-bold text-lg mb-1">RECEIPT</h1>
        <h2 class="font-bold italic text-base">A&A MERCHANDISER</h2>
        <p class="text-xs">Tomas Claudio St.,<br />Zamboanga City, 7000</p>

        <div class="text-left mt-4 text-xs">
            <p><span class="font-semibold">Date:</span> {{ currentDate }}</p>
            <p><span class="font-semibold">Cashier:</span> {{ username }}</p>
        </div>

        <hr class="my-2 border-gray-400" />

        <table
            class="w-full text-sm text-left mt-2 border-separate border-spacing-y-2"
        >
            <th>Items</th>
            <th>Qty</th>
            <th class="text-right">Price</th>
            <tbody>
                <tr v-for="item in products" :key="item" class="bg-white">
                    <td>{{ item.description }}</td>
                    <td>{{ item.quantity }}</td>
                    <td class="text-right">
                        {{ parseFloat(item.selling_price).toLocaleString() }}
                    </td>
                </tr>
            </tbody>
        </table>

        <hr class="my-2 border-gray-400" />

        <div class="grid grid-cols-2 gap-y-1 font-semibold text-sm mt-4">
            <div class="text-left">Total:</div>
            <div class="text-right">
                {{ parseFloat(total).toLocaleString() }}
            </div>

            <div class="text-left">Cash:</div>
            <div class="text-right">{{ parseFloat(pay).toLocaleString() }}</div>

            <div class="text-left">Change Cash:</div>
            <div class="text-right">
                {{ parseFloat(change).toLocaleString() }}
            </div>
        </div>
        <hr class="my-2 border-gray-400" />
        <h3 class="font-semibold">CUSTOMER INFO</h3>

        <div class="space-y-3 w-full max-w-md">
            <div class="flex items-center">
                <span class="text-sm min-w-[95px]">Name:</span>
                <div class="flex-1 border-b border-gray-400 mt-2"></div>
            </div>
            <div class="flex items-center">
                <span class="text-sm min-w-[95px]">Address:</span>
                <div class="flex-1 border-b border-gray-400 mt-2"></div>
            </div>
            <div class="flex items-center">
                <span class="text-sm min-w-[95px]">Contact #:</span>
                <div class="flex-1 border-b border-gray-400 mt-2"></div>
            </div>
        </div>

        <p class="mt-4 font-semibold italic">THANK YOU!</p>
    </div>
</template>
