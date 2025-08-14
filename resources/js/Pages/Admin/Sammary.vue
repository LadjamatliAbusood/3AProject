<script setup>
defineProps({
    totals: {
        type: Object,
        default: () => ({
            total_quantity: 0,
            total_sales_price: 0,
            total_expenses: 0,
            total_net_amount: 0,
        }),
    },
});
</script>

<template>
    <Card class="w-full">
        <h5 class="text-md font-semibold text-gray-700 uppercase text-center">
            Summary Totals
        </h5>
        <CardContent
            class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-2 gap-4 p-4 bg-white shadow-lg rounded-2xl"
        >
            <div class="flex flex-col items-center text-center">
                <ShoppingBagIcon class="w-8 h-8 text-amber-400" />
                <p class="text-sm font-semibold">Sold</p>
                <p class="text-xl font-semibold text-gray-800 text-center">
                    {{
                        parseFloat(totals?.total_quantity ?? 0).toLocaleString()
                    }}
                </p>
            </div>
            <div class="flex flex-col items-center text-center">
                <CurrencyDollarIcon class="w-8 h-8 text-blue-600" />
                <p class="text-sm font-semibold">Sales</p>
                <p class="text-xl font-semibold text-gray-800 text-center">
                    ₱{{
                        parseFloat(
                            totals?.total_sales_price ?? 0
                        ).toLocaleString()
                    }}
                </p>
            </div>
            <div class="flex flex-col items-center text-center">
                <ArrowDownCircleIcon class="w-8 h-8 text-red-600" />
                <p class="text-sm font-semibold">Expenses</p>
                <p class="text-xl font-semibold text-gray-800 text-center">
                    ₱{{
                        parseFloat(totals?.total_expenses ?? 0).toLocaleString()
                    }}
                </p>
            </div>
            <div class="flex flex-col items-center text-center">
                <BanknotesIcon class="w-8 h-8 text-green-600" />
                <p class="text-sm font-semibold">Gross Income</p>
                <p class="text-xl font-semibold text-gray-800 text-center">
                    ₱{{
                        parseFloat(
                            totals?.total_net_amount ?? 0
                        ).toLocaleString()
                    }}
                </p>
            </div>
        </CardContent>
    </Card>
</template>
