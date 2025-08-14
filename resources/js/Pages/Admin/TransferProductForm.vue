<script setup>
import { ref, watch, computed } from "vue";
import { useForm, usePage } from "@inertiajs/vue3";
import TextInput from "../Components/TextInput.vue";
import PrimaryButton from "../Components/PrimaryButton.vue";

import SelectInput from "../Components/SelectInput.vue";
const props = defineProps({
    barcode: String,
    availableStock: Number,
    branches: Array,
});

const page = usePage();
const branches = page.props.branches || [];
const acct_roles = page.props.auth?.user?.acct_roles;
const branchname = page.props.auth?.user?.branch_name;
const username = page.props.auth.user.acct_name;

const emit = defineEmits(["add", "stock"]);

const productData = ref(null);
const errorMessage = ref("");

const form = useForm({
    product_code: "",
    description: "",
    cost_price: "",
    retail_price: "",
    wholesale_price: "",
    quantity: "",
    from_branch_id: null,
    to_branch_id: null,
});

watch(
    () => props.barcode,
    async (newBarcode) => {
        if (newBarcode.trim() !== "") {
            try {
                const response = await axios.get(
                    `/barcode/find/${newBarcode}`,
                    {
                        params: {
                            branch_id: form.from_branch_id,
                        },
                    }
                );

                const product = response.data;

                if (product) {
                    productData.value = product;

                    form.product_code = product.product_code;
                    form.description = product.description;
                    form.cost_price = product.cost_price;
                    form.retail_price = product.retail_price;
                    form.wholesale_price = product.wholesale_price;
                    form.from_branch_id = product.from_branch_id;

                    form.quantity = 0;

                    emit("stock", {
                        barcode: product.product_code,
                        stock: parseInt(product.quantity),
                    });

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
        }
    }
);

function handleAdd() {
    if (!productData.value) return;

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
    const total_price = form.retail_price * form.quantity;

    emit("add", {
        product_id: productData.value.product_id,
        product_code: form.product_code,
        description: form.description,
        quantity: form.quantity,
        retail_price: parseFloat(form.retail_price),
        wholesale_price: parseFloat(form.wholesale_price),
        cost_price: parseFloat(form.cost_price),
        from_branch_id: form.from_branch_id,
        to_branch_id: form.to_branch_id,
        total: total_price,

        from_branch_name:
            branches.find((b) => b.id === form.from_branch_id)?.branch_name ||
            "",
        to_branch_name:
            branches.find((b) => b.id === form.to_branch_id)?.branch_name || "",
    });

    const currentReceiveBranch = form.to_branch_id;
    const currentFromBranch = form.from_branch_id;

    form.reset();
    form.to_branch_id = currentReceiveBranch;
    form.from_branch_id = currentFromBranch;
}
const fromBranchOptions = computed(() => {
    if (acct_roles === 1 || acct_roles === 2) {
        return branches.map((branch) => ({
            label: branch.branch_name,
            value: branch.id, // <-- send ID instead of name
        }));
    } else {
        // Get current branch object
        const current = branches.find((b) => b.branch_name === branchname);
        return current
            ? [{ label: current.branch_name, value: current.id }]
            : [];
    }
});

function clearForm() {
    form.product_code = "";
    form.description = "";
    form.cost_price = "";
    form.quantity = "";
    form.retail_price = "";
    form.wholesale_price = "";
    productData.value = null;
}
</script>

<template>
    <form @submit.prevent="handleAdd">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- From Branch -->
            <SelectInput
                name="From Branch"
                v-model="form.from_branch_id"
                class="text-xl mt-2"
                :options="fromBranchOptions"
                :message="form.errors.from_branch_id"
            />

            <!-- Receive Branch -->
            <SelectInput
                name="Receive Branch"
                v-model="form.to_branch_id"
                class="text-xl mt-2"
                :options="
                    branches.map((branch) => ({
                        label: branch.branch_name,
                        value: branch.id,
                    }))
                "
                :message="form.errors.to_branch_id"
            />

            <TextInput
                name="Product Code"
                v-model="form.product_code"
                :readonly="true"
                class="!border-white text-xl"
                :message="form.errors.product_code"
            />
            <TextInput
                name="Product Name"
                v-model="form.description"
                :readonly="true"
                class="!border-white text-xl"
                :message="form.errors.description"
            />

            <TextInput
                v-if="[1, 2].includes($page.props.auth.user.acct_roles)"
                name="Cost Price"
                type="number"
                class="text-xl"
                v-model="form.cost_price"
                :message="form.errors.cost_price"
            />

            <TextInput
                v-if="[1, 2].includes($page.props.auth.user.acct_roles)"
                name="Retail Price"
                type="number"
                class="text-xl"
                v-model="form.retail_price"
                :message="form.errors.retail_price"
            />

            <TextInput
                v-if="[1, 2].includes($page.props.auth.user.acct_roles)"
                name="Wholesale Price"
                type="number"
                class="text-xl"
                v-model="form.wholesale_price"
                :message="form.errors.wholesale_price"
            />

            <TextInput
                name="Quantity"
                type="number"
                v-model="form.quantity"
                class="text-xl"
                :message="form.errors.quantity"
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
                    :disabled="form.processing"
                    :processing="form.processing"
                />
            </div>
        </div>
    </form>
</template>
