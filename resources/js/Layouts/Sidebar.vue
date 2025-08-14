<script setup>
import { defineProps } from "vue";
import { router, usePage } from "@inertiajs/vue3";

const page = usePage();
const acct_roles = page.props.auth?.user?.acct_roles;
const branch = page.props.auth?.user?.branch_name;
const username = page.props.auth.user.acct_name;

defineProps({
    showSidebar: Boolean,
});

const roleLabel = {
    1: "SuperAdmin",
    2: "Admin",
    3: "Supervisor",
    4: "Cashier",
};

// Account URL (handles all roles)
const accountUrl =
    acct_roles === 3
        ? `/supervisor/${branch}/account`
        : acct_roles === 2
        ? route("register")
        : route("account");

const supplierUrl = (() => {
    if (acct_roles === 2) return "/administrator/supplier";
    if (acct_roles === 1) return route("supplier");
    return null;
})();

const BranchhUrl = (() => {
    if (acct_roles === 2) return "/administrator/branch";
    if (acct_roles === 1) return route("branch");
    return null;
})();

const HistoryUrl =
    acct_roles === 3
        ? `/supervisor/${branch}/history`
        : acct_roles === 2
        ? `/administrator/history`
        : route("history");

const ProductUrl =
    acct_roles === 3
        ? `/supervisor/${branch}/product`
        : acct_roles === 2
        ? route("administrator.product")
        : route("product");

const HomeUrl =
    acct_roles === 3
        ? `/supervisor/${branch}`
        : acct_roles === 2
        ? `/administrator/home`
        : route("home");

const TrasnferUrl =
    acct_roles === 3
        ? `/supervisor/${branch}/transfer`
        : acct_roles === 2
        ? route("administrator.transfer")
        : route("transfer");

const SalesReportUrl =
    acct_roles === 3
        ? `/supervisor/${branch}/salesreport`
        : acct_roles === 2
        ? route("administrator.salesreport")
        : route("salesreport");

const ExpensesUrl =
    acct_roles === 3
        ? `/supervisor/${branch}/expenses`
        : acct_roles === 2
        ? route("administrator.expenses")
        : route("expenses");

const ReceiveUrl = (() => {
    if (acct_roles === 3) return `/supervisor/${branch}/receive`;
    return null;
})();

const CategoryUrl = (() => {
    if (acct_roles === 2) return "/administrator/category";
    if (acct_roles === 1) return route("category");
    return null;
})();
const InventoryUrl = (() => {
    if (acct_roles === 3) return `/supervisor/${branch}/inventory`;
    return null;
})();
// Handle password + redirect logic
const askPasswordAndRedirect = () => {
    if (!InventoryUrl) {
        Swal.fire({
            icon: "warning",
            title: "Access Denied",
            text: "You are not authorized to access inventory.",
        });
        return;
    }

    Swal.fire({
        title: "Enter Password",
        input: "password",
        inputPlaceholder: "Enter supervisor password",
        inputAttributes: {
            autocapitalize: "off",
            autocorrect: "off",
        },
        showCancelButton: true,
        confirmButtonText: "Submit",
        preConfirm: (password) => {
            if (!password) {
                Swal.showValidationMessage("Password is required");
            }
            return password;
        },
    }).then((result) => {
        if (result.isConfirmed) {
            if (result.value === "admin123") {
                router.visit(InventoryUrl);
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Incorrect Password",
                    text: "The password you entered is incorrect.",
                });
            }
        }
    });
};
</script>

<template>
    <aside class="text-gray-500 h-full px-2 py-2">
        <nav class="flex flex-col space-y-1">
            <!-- User Info -->

            <Link
                :href="HomeUrl"
                :class="[
                    'text-md font-medium flex items-center gap-2 px-4 py-2 rounded hover:bg-gray-200 uppercase ',
                    { 'bg-white': page.url.startsWith(HomeUrl) },
                ]"
            >
                {{ username }} | {{ branch }}|
                {{ roleLabel[acct_roles] }}
            </Link>

            <!-- Product -->
            <Link
                :href="ProductUrl"
                :class="[
                    'text-xl font-medium flex items-center gap-2 px-4 py-2 rounded hover:bg-gray-200 transition',
                    { 'bg-gray-200': $page.component === 'Admin/Product' },
                ]"
            >
                <ArchiveBoxArrowDownIcon class="h-6 w-6 text-gray-700" />
                Product
            </Link>

            <!-- History -->
            <Link
                :href="HistoryUrl"
                :class="[
                    'text-xl font-medium flex items-center gap-2 px-4 py-2 rounded hover:bg-gray-200 transition',
                    {
                        'bg-gray-200':
                            $page.component === 'Admin/ProductHistory',
                    },
                ]"
            >
                <ClockIcon class="h-6 w-6 text-gray-700" />
                History
            </Link>
            <!-- Sales Report -->
            <Link
                :href="SalesReportUrl"
                :class="[
                    'text-xl font-medium flex items-center gap-2 px-4 py-2 rounded hover:bg-gray-200 transition',
                    {
                        'bg-gray-200': $page.component === 'Admin/SalesReport',
                    },
                ]"
            >
                <DocumentChartBarIcon class="h-6 w-6 text-gray-700" />
                Sales Report
            </Link>
            <!-- Transfer -->
            <Link
                :href="TrasnferUrl"
                :class="[
                    'text-xl font-medium flex items-center gap-2 px-4 py-2 rounded hover:bg-gray-200 transition',
                    {
                        'bg-gray-200':
                            $page.component === 'Admin/TransferProduct',
                    },
                ]"
            >
                <TruckIcon class="h-6 w-6 text-gray-700" />
                Transfer Product
            </Link>

            <!-- Branch -->
            <Link
                v-if="BranchhUrl"
                :href="BranchhUrl"
                :class="[
                    'text-xl font-medium flex items-center gap-2 px-4 py-2 rounded hover:bg-gray-200 transition',
                    { 'bg-gray-200': $page.component === 'Admin/Branch' },
                ]"
            >
                <BuildingStorefrontIcon class="h-6 w-6 text-gray-700" />
                Branch
            </Link>

            <!-- Supplier -->
            <Link
                v-if="supplierUrl"
                :href="supplierUrl"
                :class="[
                    'text-xl font-medium flex items-center gap-2 px-4 py-2 rounded hover:bg-gray-200 transition',
                    { 'bg-gray-200': $page.component === 'Admin/Supplier' },
                ]"
            >
                <UserGroupIcon class="h-6 w-6 text-gray-700" />
                Supplier
            </Link>

            <!-- Category -->
            <Link
                v-if="CategoryUrl"
                :href="CategoryUrl"
                :class="[
                    'text-xl font-medium flex items-center gap-2 px-4 py-2 rounded hover:bg-gray-200 transition',
                    { 'bg-gray-200': $page.component === 'Admin/Supplier' },
                ]"
            >
                <TagIcon class="h-6 w-6 text-gray-700" />
                Category
            </Link>
            <!-- Recieve -->
            <Link
                v-if="ReceiveUrl"
                :href="ReceiveUrl"
                :class="[
                    'text-xl font-medium flex items-center gap-2 px-4 py-2 rounded hover:bg-gray-200 transition',
                    {
                        'bg-gray-200':
                            $page.component === 'Supervisor/RecieveBranch',
                    },
                ]"
            >
                <ArrowsRightLeftIcon class="h-6 w-6 text-gray-700" />
                Receive
            </Link>

            <!-- Inventory -->
            <button
                v-if="InventoryUrl"
                @click="askPasswordAndRedirect"
                type="button"
                :class="[
                    'text-xl font-medium w-full text-left flex items-center gap-2 px-4 py-2 rounded hover:bg-gray-200 transition',
                    {
                        'bg-gray-200':
                            $page.component === 'Supervisor/Inventory',
                    },
                ]"
            >
                <DocumentCheckIcon class="h-6 w-6 text-gray-700" />
                Items Checker
            </button>
            <!-- Expenses -->
            <Link
                :href="ExpensesUrl"
                :class="[
                    'text-xl font-medium flex items-center gap-2 px-4 py-2 rounded hover:bg-gray-200 transition',
                    { 'bg-gray-200': $page.component === 'Admin/Expenses' },
                ]"
            >
                <DocumentDuplicateIcon class="h-6 w-6 text-gray-700" />
                Expenses
            </Link>

            <!-- Account -->
            <Link
                :href="accountUrl"
                :class="[
                    'text-xl font-medium flex items-center gap-2 px-4 py-2 rounded hover:bg-gray-200 transition',
                    { 'bg-gray-200': $page.component === 'Auth/Register' },
                ]"
            >
                <UserPlusIcon class="h-6 w-6 text-gray-700" />
                Account
            </Link>
        </nav>
    </aside>
</template>
