<!-- InvoiceForm.vue -->
<script setup>
import TextInput from "../Components/TextInput.vue";
import PrimaryButton from "../Components/PrimaryButton.vue";
import { useForm } from "@inertiajs/vue3";
import { watch } from "vue";
import { result } from "lodash";

const emit = defineEmits(["clearProducts", "printReceipt"]);
const props = defineProps({
    total: Number,
    products: Array, // make sure this is passed properly
});

const form = useForm({
    total: 0,
    pay: null,
    change: 0,
    products: props.products,
});
watch(
    [() => form.total, () => form.pay, () => form.change],
    ([newTotal, newPay, newChange]) => {
        emit("sendInvoiceValues", {
            total: newTotal,
            pay: newPay,
            change: newChange,
        });
    }
);
// Watch total and pay changes
watch(
    () => props.total,
    (newTotal) => {
        form.total = newTotal;

        form.change = form.pay >= newTotal ? form.pay - newTotal : 0;
    }
);
watch(
    () => form.pay,
    (newPay) => {
        form.change = newPay >= form.total ? newPay - form.total : 0;
    }
);

// Submit handler
function submit() {
    if (form.pay < form.total) {
        Swal.fire({
            icon: "warning",
            title: "Insufficient Payment",
            text: "The amount paid is less than the total.",
        });
        return;
    }
    form.products = props.products; // refresh the products on submit
    console.log(form.products);
    form.post(route("salesreport.store"), {
        preserveScroll: true,
        onSuccess: () => {
            Swal.fire({
                icon: "success",
                title: "Success!",
                text: "Invoice added successfully.",
                timer: 2000,
                showConfirmButton: false,
            }).then(() => {
                // Ask if user wants receipt
                Swal.fire({
                    title: "Print receipt?",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonText: "Yes",
                    cancelButtonText: "No",
                }).then((result) => {
                    if (result.isConfirmed) {
                        emit("printReceipt");
                    } else {
                        emit("clearProducts");
                        setTimeout(() => {
                            form.reset(); // Reset all fields including errors
                            form.pay = null;
                            form.change = 0;
                            form.total = 0; // Reset total here too
                        }, 0);
                    }
                });
            });
        },
        onError: (errors) => {
            Swal.fire({
                icon: "error",
                title: "Failed to save invoice",
                text: "Please fix the errors.",
            });
            console.log("Validation errors:", errors); // Helpful for debugging
            console.log(form.products);
        },
    });
}
</script>

<template>
    <div class="flex justify-center items-center">
        <div class="w-[350px] rounded shadow bg-gray-100 p-4">
            <h3 class="text-lg font-semibold text-center">Invoice Form</h3>
            <div>
                <form class="flex flex-col" @submit.prevent="submit">
                    <TextInput
                        name="Total"
                        v-model="form.total"
                        class="text-xl border-white"
                        :message="form.errors.total"
                        readonly
                    />
                    <TextInput
                        name="Pay"
                        type="number"
                        class="text-xl"
                        v-model="form.pay"
                        :message="form.errors.pay"
                    />
                    <TextInput
                        name="Change"
                        class="text-xl border-white"
                        v-model="form.change"
                        :message="form.errors.change"
                        readonly
                    />
                    <PrimaryButton
                        label="Update Invoice"
                        :disabled="form.processing"
                        :processing="form.processing"
                    />
                </form>
            </div>
        </div>
    </div>
</template>
