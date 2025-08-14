<!-- InvoiceForm.vue -->
<script setup>
import TextInput from "../Components/TextInput.vue";
import PrimaryButton from "../Components/PrimaryButton.vue";
import { useForm } from "@inertiajs/vue3";
import { watch } from "vue";

const emit = defineEmits(["clearProducts"]);
const props = defineProps({
    // total: Number,
    products: Array, // make sure this is passed properly
});

const form = useForm({
    // total: 0,

    products: props.products,
});

// Watch total and pay changes
// watch(
//     () => props.total,
//     (newTotal) => {
//         form.total = newTotal;
//         form.change = form.pay >= newTotal ? form.pay - newTotal : 0;
//     }
// );
// watch(
//     () => form.pay,
//     (newPay) => {
//         form.change = newPay >= form.total ? newPay - form.total : 0;
//     }
// );

// Submit handler
function submit() {
    form.products = props.products; // refresh the products on submit
    console.log(form.products);
    form.post(route("transfer.store"), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit("clearProducts");
            Swal.fire({
                icon: "success",
                title: "Success!",
                text: "Transfer successfully.",
                timer: 2000,
                showConfirmButton: false,
            });
        },
        onError: (errors) => {
            Swal.fire({
                icon: "error",
                title: "Failed to transfer",
                text: "Please fix the errors.",
            });
            console.log("Validation errors:", errors); // Helpful for debugging
        },
    });
}

console.log(form.products);
</script>

<template>
    <div class="flex justify-center items-center">
        <div class="w-[250px]">
            <div>
                <form class="flex flex-col" @submit.prevent="submit">
                    <!-- <TextInput
                        name="Total"
                        v-model="form.total"
                        class="text-xl"
                        :message="form.errors.total"
                        readonly
                    /> -->

                    <PrimaryButton
                        label="Transfer Now"
                        :disabled="
                            form.processing || props.products.length === 0
                        "
                        :processing="form.processing"
                    />
                </form>
            </div>
        </div>
    </div>
</template>
