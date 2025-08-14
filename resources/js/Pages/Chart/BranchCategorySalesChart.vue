<script setup>
import { onMounted, ref, watch } from "vue";
import { Chart, registerables } from "chart.js";

Chart.register(...registerables);

// Props
const props = defineProps({
    categorySales: {
        type: Array,
        default: () => [],
    },
});

// Ref to canvas
const chartRef = ref(null);
let chartInstance = null;

// Helper: Get unique values
function getUnique(array, key) {
    return [...new Set(array.map((item) => item[key]))];
}

// Helper: Assign colors per branch
function getColor(index) {
    const colors = [
        "#f59e0b",
        "#3b82f6",
        "#10b981",
        "#ef4444",
        "#8b5cf6",
        "#ec4899",
        "#14b8a6",
        "#eab308",
        "#6366f1",
        "#f43f5e",
    ];
    return colors[index % colors.length];
}

function renderChart() {
    if (!chartRef.value) return;

    if (chartInstance) {
        chartInstance.destroy();
    }

    const branches = getUnique(props.categorySales, "branch_name");
    const categories = getUnique(props.categorySales, "category_name");

    // Log structured array to console
    const formatted = props.categorySales.map((item) => ({
        branch_name: item.branch_name,
        category_name: item.category_name,
        total_quantity: item.total_quantity,
    }));
    console.log("Formatted Category Sales:", formatted);

    // Create datasets per branch
    const datasets = branches.map((branch, idx) => {
        return {
            label: branch,
            backgroundColor: getColor(idx),
            data: categories.map((cat) => {
                const match = props.categorySales.find(
                    (item) =>
                        item.branch_name === branch &&
                        item.category_name === cat
                );
                return match ? match.total_quantity : 0;
            }),
        };
    });

    const ctx = chartRef.value.getContext("2d");
    chartInstance = new Chart(ctx, {
        type: "bar",
        data: {
            labels: categories,
            datasets,
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: "Quantity sold by Category and Branch",
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
                        size: 18, // 👈 Tooltip body text size
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

// Lifecycle
onMounted(() => {
    renderChart();
});

watch(
    () => props.categorySales,
    () => {
        renderChart();
    }
);
</script>

<template>
    <canvas ref="chartRef" height="100"></canvas>
</template>
