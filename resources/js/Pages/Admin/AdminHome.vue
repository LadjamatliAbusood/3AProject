<script setup>
import { ref, computed, onMounted } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import DateRangeFilter from "../Components/DateRangeFilter.vue";
import BranchPerformanceChart from "../Chart/BranchPerformanceChart.vue";
import Sammary from "./Sammary.vue";
import BranchStats from "./BranchStats.vue";
import BranchCategorySalesChart from "../Chart/BranchCategorySalesChart.vue";

// Props
const props = defineProps({
    chartData: Array,
    acct_roles: Number,
    branchTotals: Array,
    date_from: String,
    date_to: String,
    totals: Object,
    highestSalesBranch: Number,
    lowestSalesBranch: Number,
    highestNetBranch: Number,
    lowestNetBranch: Number,
    categorySales: Array,
});

const page = usePage();
const acct_roles = page.props.auth?.user?.acct_roles;

const currentPage = ref(0);
const itemPerPage = 4;

const totalPages = computed(() => {
    return Math.ceil(props.branchTotals.length / itemPerPage);
});

const paginatedBranches = computed(() => {
    const start = currentPage.value * itemPerPage;
    return props.branchTotals.slice(start, start + itemPerPage);
});

const goToPage = (page) => {
    currentPage.value = page;
};

const dateFilterRef = ref(null);
const search = ref(""); // If you later include search

function getHistoryRoute() {
    if (acct_roles === 2) {
        return `/administrator/home`;
    } else {
        return `/admin/home`;
    }
}

function filterByDate() {
    const date_from = dateFilterRef.value?.localDateFrom;
    const date_to = dateFilterRef.value?.localDateTo;

    router.get(
        getHistoryRoute(),
        {
            date_from,
            date_to,
            search: search.value,
        },
        {
            preserveState: true,
            replace: true,
        }
    );
}

const colors = [
    "bg-amber-500",
    "bg-blue-500",
    "bg-green-500",
    "bg-red-500",
    "bg-purple-500",
    "bg-indigo-500",
    "bg-pink-500",
    "bg-teal-500",
    "bg-orange-500",
    "bg-lime-500",
    "bg-cyan-500",
    "bg-emerald-500",
    "bg-fuchsia-500",
    "bg-rose-500",
    "bg-yellow-500",
    "bg-sky-500",
    "bg-violet-500",
    "bg-slate-500",
    "bg-zinc-500",
    "bg-neutral-500",
];

onMounted(() => {
    console.log("Category Sales from props:", props.categorySales);
});
</script>

<template>
    <Head title=" | Home" />

    <div class="w-full flex flex-col items-center" data-aos="fade-up">
        <!-- Cards Grid -->
        <div
            class="flex overflow-x-auto gap-2 mb-2 scrollbar-hide md:grid md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4"
        >
            <div
                v-for="(branch, index) in paginatedBranches"
                :key="branch.branch_id"
                class="relative bg-white shadow-lg rounded-2xl w-full"
            >
                <div class="p-3 flex-1">
                    <h5
                        class="text-xl font-semibold text-gray-700 uppercase text-center"
                    >
                        {{ branch.branch_name }}
                    </h5>
                    <p class="text-xl font-semibold text-gray-700 text-center">
                        <span class="font-medium"
                            >Sold:
                            {{ branch.total_quantity }}
                        </span>
                        |
                        <span class="font-medium"
                            >Sales: ₱{{
                                parseFloat(branch.total_sales).toLocaleString()
                            }}
                        </span>
                        |
                        <span class="font-medium"
                            >Expenses: ₱{{
                                parseFloat(
                                    branch.total_expenses
                                ).toLocaleString()
                            }}
                        </span>
                        |
                        <span
                            class="font-medium"
                            v-if="$page.props.auth.user.acct_roles === 1"
                            >Gross: ₱{{
                                parseFloat(
                                    branch.total_net_sales
                                ).toLocaleString()
                            }}</span
                        >
                    </p>
                </div>

                <div
                    class="absolute left-0 top-0 h-full w-2 rounded-l-2xl"
                    :class="colors[index % colors.length]"
                ></div>
            </div>
        </div>

        <!-- Page Indicator Dots -->
        <div class="flex justify-center items-center gap-2 mb-2">
            <span
                v-for="(page, index) in totalPages"
                :key="index"
                @click="goToPage(index)"
                class="w-3 h-3 rounded-full cursor-pointer transition-all duration-200"
                :class="{
                    'bg-blue-600 scale-110': currentPage === index,
                    'bg-gray-300': currentPage !== index,
                }"
            ></span>
        </div>
    </div>

    <div class="w-full overflow-x-auto">
        <div class="gap-4 mb-4">
            <div class="grid grid-cols-5 grid-rows-2 gap-4">
                <div
                    class="col-span-4 row-span-2 bg-white p-6 rounded-lg shadow-md"
                >
                    <div class="w-full flex justify-end mb-4">
                        <div class="flex items-end gap-4">
                            <DateRangeFilter
                                ref="dateFilterRef"
                                route-name="history"
                                :date_from="$page.props.date_from"
                                :date_to="$page.props.date_to"
                                :extra-query="{ search: search }"
                            />
                            <button
                                @click="filterByDate"
                                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                            >
                                Filter
                            </button>
                        </div>
                    </div>

                    <BranchPerformanceChart
                        :chartData="chartData"
                        :acctRoles="acct_roles"
                    />
                </div>

                <div
                    class="row-span-3 col-start-5"
                    v-if="$page.props.auth.user.acct_roles === 1"
                >
                    <Sammary :totals="totals" />
                    <BranchStats
                        :highestSalesBranch="highestSalesBranch"
                        :lowestSalesBranch="lowestSalesBranch"
                        :highestNetBranch="highestNetBranch"
                        :lowestNetBranch="lowestNetBranch"
                    />
                </div>
                <div
                    class="col-span-4 row-span-2 bg-white p-6 rounded-lg shadow-md"
                >
                    <BranchCategorySalesChart :categorySales="categorySales" />
                </div>
            </div>
        </div>
    </div>
</template>
