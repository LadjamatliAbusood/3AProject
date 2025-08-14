<script setup>

defineProps({
    highestSalesBranch: Object,
    lowestSalesBranch: Object,
    highestNetBranch: Object,
    lowestNetBranch: Object,
});
</script>

<template>
    <Card class="w-full">
        <h5
            class="text-md font-semibold text-gray-700 uppercase text-center mt-2"
        >
            Branch Stats
        </h5>
        <CardContent
            class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-2 xl:grid-cols-2 gap-4 p-4 bg-white shadow-lg rounded-2xl"
        >
            <div class="flex flex-col items-center text-center">
                <ArrowTrendingUpIcon class="w-8 h-8 text-blue-600" />
                <p class="text-sm font-semibold">
                    Highest Sales <br />
                    <p class="uppercase">
                        {{ highestSalesBranch?.branch_name || 'N/A' }}
                    </p>
                </p>
                <p class="text-xl font-semibold text-gray-800 text-center">
                    ₱{{
                        parseFloat(
                            highestSalesBranch?.total_sales || 0
                        ).toLocaleString()
                    }}
                </p>
            </div>
            <div class="flex flex-col items-center text-center">
                <ArrowTrendingDownIcon class="w-8 h-8 text-red-600" />
                <p class="text-sm font-semibold">
                    Lowest Sales <br />
                     <p class="uppercase">
                    {{ lowestSalesBranch?.branch_name || 'N/A'}}
                    </p>

                    
                </p>
                <p class="text-xl font-semibold text-gray-800 text-center">
                    ₱{{
                        parseFloat(
                            lowestSalesBranch?.total_sales || 0
                        ).toLocaleString()
                    }}
                </p>
            </div>
            <div class="flex flex-col items-center text-center">
                <BanknotesIcon class="w-8 h-8 text-green-600" />
                <p class="text-sm font-semibold">
                    Highest Gross <br />
                       <p class="uppercase">
                      {{ highestNetBranch?.branch_name  || 'N/A'}}
                    </p>
                  
                </p>
                <p class="text-xl font-semibold text-gray-800 text-center">
                    ₱{{
                        parseFloat(
                            highestNetBranch?.total_net_sales || 0
                        ).toLocaleString()
                    }}
                </p>
            </div>
            <div class="flex flex-col items-center text-center">
                <ArrowDownOnSquareStackIcon class="w-8 h-8 text-gray-700" />
                <p class="text-sm font-semibold">
                    Lowest Gross <br />
                        <p class="uppercase">
                     {{ lowestNetBranch?.branch_name || 'N/A'}}
                    </p>
                    
                </p>
                <p class="text-xl font-semibold text-gray-800 text-center">
                    ₱{{
                        parseFloat(
                            lowestNetBranch?.total_net_sales || 0
                        ).toLocaleString()
                    }}
                </p>
            </div>
        </CardContent>
    </Card>
</template>
