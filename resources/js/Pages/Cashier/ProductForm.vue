<script setup>
import { ref, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import TextInput from "../Components/TextInput.vue";
import SelectInput from "../Components/SelectInput.vue";
import PrimaryButton from "../Components/PrimaryButton.vue";
import Product from "../Admin/Product.vue";

const props = defineProps({
    barcode: String,
    availableStock: Number,
});

const emit = defineEmits(["add", "stock"]);

const productData = ref(null);
const errorMessage = ref("");
const salesOptions = ref([{ label: "Retail", value: "retail" }]);

const form = useForm({
    product_code: "",
    description: "",
    sales_type: "retail",
    selling_price: 0,
    quantity: 0,
    total: 0,
});

watch(
    () => props.barcode,
    async (newBarcode) => {
        if (newBarcode.trim() !== "") {
            try {
                const response = await axios.get(`/barcode/find/${newBarcode}`);
                const product = response.data;

                if (product) {
                    productData.value = product;

                    form.product_code = product.product_code;
                    form.description = product.description;
                    form.quantity = 0;
                    form.sales_type = "retail";

                    salesOptions.value =
                        parseFloat(product.wholesale_price) > 0
                            ? [
                                  { label: "Retail", value: "retail" },
                                  { label: "Wholesale", value: "wholesale" },
                              ]
                            : [{ label: "Retail", value: "retail" }];

                    emit("stock", {
                        barcode: product.product_code,
                        stock: parseInt(product.quantity),
                    });

                    form.selling_price = parseFloat(product.retail_price);
                    updateTotal();
                    errorMessage.value = "";
                } else {
                    clearForm();
                    errorMessage.value = "Barcode not found.";
                }
            } catch (error) {
                clearForm();
                errorMessage.value =
                    error.response?.data?.error || "Barcode fetch error.";
            }
        }
    }
);

watch(
    () => form.sales_type,
    () => {
        if (!productData.value) return;

        form.selling_price =
            form.sales_type === "retail"
                ? parseFloat(productData.value.retail_price)
                : parseFloat(productData.value.wholesale_price);
        updateTotal();
    }
);
// ✅ When user types a custom price
watch(
    () => form.selling_price,
    () => {
        updateTotal();
    }
);

watch(
    () => form.quantity,
    () => {
        if (!productData.value) return;

        const available = parseInt(productData.value.quantity);
        const requested = parseInt(form.quantity);

        if (requested > available) {
            Swal.fire({
                icon: "warning",
                title: "Not enough stock",
                text: `Available stock: ${available}`,
            });
            return;
        }

        updateTotal();
    }
);

function updateTotal() {
    const qty = parseFloat(form.quantity || 0);
    const price = parseFloat(form.selling_price || 0);
    form.total = qty * price;
}

function handleAdd() {
    if (!productData.value) return;

    if (form.selling_price < productData.value.cost_price) {
        Swal.fire({
            icon: "warning",
            title: "Invalid Price",
            text: "Selling price cannot be lower than the cost price.",
        });
        return;
    }

    if (form.quantity <= 0) {
        Swal.fire({
            icon: "warning",
            title: "Invalid Quantity",
            text: "Quantity must be greater than zero.",
        });
        return;
    }

    if (form.quantity > props.availableStock) {
        Swal.fire({
            icon: "warning",
            title: "Not Enough Stock",
            text: `Only ${props.availableStock} remaining.`,
        });
        return;
    }
    console.log("Cost:", productData.value.cost_price);
    console.log("Price:", form.selling_price);
    console.log("Qty:", form.quantity);

    const net_sale =
        (form.selling_price - productData.value.cost_price) * form.quantity;

    const total_price = form.selling_price * form.quantity;
    console.log("Net Sale:", net_sale);

    emit("add", {
        product_id: productData.value.product_id,
        product_code: form.product_code,
        description: form.description,
        quantity: form.quantity,
        selling_price: form.selling_price,
        cost_price: productData.value.cost_price,
        total_price: total_price,
        total: form.total,
        selling_type: form.sales_type === "retail" ? 1 : 2,
        net_sale: net_sale,
    });

    form.reset();
}

function clearForm() {
    form.product_code = "";
    form.description = "";
    form.sales_type = "retail";
    form.selling_price = 0;
    form.quantity = 0;
    form.total = 0;
    productData.value = null;
}
</script>

<template>
    <form @submit.prevent="handleAdd">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <TextInput
                name="Product Code"
                v-model="form.product_code"
                :readonly="true"
                class="!border-white text-xl"
            />
            <TextInput
                name="Product Name"
                v-model="form.description"
                :readonly="true"
                class="!border-white text-xl"
            />
            <SelectInput
                class="text-xl mt-7"
                v-model="form.sales_type"
                :options="salesOptions"
            />
            <TextInput
                name="Selling Price"
                type="number"
                class="text-xl"
                v-model="form.selling_price"
            />
            <TextInput
                name="Quantity"
                type="number"
                v-model="form.quantity"
                class="text-xl"
            />
            <TextInput
                name="Total"
                type="number"
                v-model="form.total"
                :readonly="true"
                class="!border-white text-xl"
            />
        </div>

        <div class="flex items-center justify-between mt-4">
            <div class="text-red-600 min-h-[1.5rem]">
                <span v-if="errorMessage">{{ errorMessage }}</span>
            </div>

            <div>
                <PrimaryButton
                    class="w-[350px] text-2xl bg-green-500 hover:bg-green-600 text-red-900"
                    label="Add Purchase"
                />
            </div>
        </div>
    </form>
</template>
