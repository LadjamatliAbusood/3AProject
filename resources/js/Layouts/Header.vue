<script setup>
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const acct_roles = page.props.auth?.user?.acct_roles;
const branch = page.props.auth?.user?.branch_name;

// Construct home URL based on role
const HeaderHomeUrl = (() => {
    if (acct_roles === 3) return `/supervisor/${branch}`;
    if (acct_roles === 2) return `/administrator/home`;
    if (acct_roles === 1) return `/admin/home`;
    return "/"; // fallback
})();
</script>

<template>
    <header class="bg-white shadow p-4 flex items-center justify-between">
        <!-- Left: Toggle + Title -->
        <div class="flex items-center space-x-4">
            <button
                class="text-gray-500 text-2xl"
                @click="$emit('toggle-sidebar')"
            >
                ☰
            </button>
            <h1 class="text-xl font-bold text-gray-500">3A Merchandiser |</h1>
            <Link
                :href="HeaderHomeUrl"
                class="text-xl px-2 py-1 text-gray-500 rounded hover:bg-gray-200"
            >
                <HomeIcon class="h-6 w-6 text-gray-700" />
            </Link>
        </div>

        <!-- Right: Logout -->
        <div class="space-x-6" v-if="$page.props.auth.user">
            <Link
                :href="route('logout')"
                method="post"
                as="button"
                type="button"
                class="text-gray-500 text-xl px-2 py-1 rounded hover:bg-gray-200"
            >
                logout
            </Link>
        </div>
    </header>
</template>
