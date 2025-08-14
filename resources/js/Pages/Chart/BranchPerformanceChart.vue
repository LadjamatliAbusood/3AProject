<script setup>
import { onMounted, ref, watch } from "vue";
import { Chart, registerables } from "chart.js";

Chart.register(...registerables);

const props = defineProps({
    chartData: {
        type: Array,
        default: () => [],
    },
    acctRoles: {
        type: Number,
        default: 1,
    },
});

const chartRef = ref(null);
let chartInstance = null;

function renderChart() {
    if (!chartRef.value) return;

    if (chartInstance) {
        chartInstance.destroy();
    }

    const labels = props.chartData.map((b) => b.name);
    const quantityData = props.chartData.map((b) => b.quantity);
    const salesData = props.chartData.map((b) => b.sales);
    const ExpensesData = props.chartData.map((b) => b.expenses);
    const incomeData = props.chartData.map((b) => b.income);

    const datasets = [
        {
            label: "Sold Quantity",
            data: quantityData,
            backgroundColor: "#3b82f6",
        },
        {
            label: "Sales Amount",
            data: salesData,
            backgroundColor: "#ef4444",
        },
        {
            label: "Expenses",
            data: ExpensesData,
            backgroundColor: "#fe9a00",
        },
    ];

    // Only show gross income if acctRoles is 1 (Admin)
    if (props.acctRoles === 1) {
        datasets.push({
            label: "Gross Income",
            data: incomeData,
            backgroundColor: "#10b981",
        });
    }

    const ctx = chartRef.value.getContext("2d");

    chartInstance = new Chart(ctx, {
        type: "bar",
        data: {
            labels,
            datasets,
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: "Branch Performance",
                    font: {
                        size: 18,
                        weight: "bold",
                    },
                },
                legend: {
                    position: "top",
                    labels: {
                        font: {
                            size: 18,
                        },
                    },
                },

                tooltip: {
                    bodyFont: {
                        size: 18,
                    },
                    titleFont: {
                        size: 18,
                    },
                },
            },
            scales: {
                x: {
                    ticks: {
                        font: {
                            size: 18,
                        },
                    },
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        font: {
                            size: 18,
                        },
                    },
                },
            },
        },
    });
}

onMounted(() => {
    renderChart();
});

watch(
    () => props.chartData,
    () => {
        renderChart();
    }
);
</script>

<template>
    <canvas ref="chartRef" height="100"></canvas>
</template>
