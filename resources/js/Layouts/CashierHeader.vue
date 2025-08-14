<script setup>
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const username = page.props.auth.user.acct_name;
</script>
<template>
    <header class="shadow p-4 flex items-center justify-between">
        <!-- Left: Toggle + Title -->
        <div class="flex items-center space-x-4">
            <h1 class="text-xl font-bold text-gray-500">3A Merchandiser |</h1>
            <Link
                class="text-xl font-bold text-gray-500 px-2 py-1 rounded hover:bg-gray-300"
                :href="route('cashier', $page.props.branch)"
                >Home</Link
            >

            <Link
                class="text-xl font-bold text-gray-500 px-2 py-1 rounded hover:bg-gray-300"
                :href="route('viewproduct', $page.props.branch)"
                >View Product</Link
            >
        </div>

        <!-- Right: Logout -->
        <div class="space-x-6 flex items-center" v-if="$page.props.auth.user">
            <h2 class="text-xl font-bold text-gray-500">{{ username }}</h2>
            <Link
                :href="route('logout')"
                method="post"
                as="button"
                type="button"
                class="text-xl font-bold text-gray-500 px-2 py-1 rounded hover:bg-gray-300"
                >Logout</Link
            >
        </div>
    </header>
</template>
