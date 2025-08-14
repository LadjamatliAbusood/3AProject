<script>
import { useForm } from "@inertiajs/vue3";

import PrimaryButton from "../Components/PrimaryButton.vue";
import SelectInput from "../Components/SelectInput.vue";
import TextInput from "../Components/TextInput.vue";
import ImageBarcodeReader from "../Cashier/ImageBarcodeReader.vue";
import TransferProductForm from "./TransferProductForm.vue";
import TransferProductTable from "./TransferProductTable.vue";
import TransferProductButton from "./TransferProductButton.vue";
import InvoiceTemplate from "../Invoice/InvoiceTemplate.vue";
import { ref } from "vue";

export default {
    components: {
        ImageBarcodeReader,
        TextInput,
        SelectInput,
        PrimaryButton,
        TransferProductTable,
        TransferProductButton,
        TransferProductForm,
        InvoiceTemplate,
    },
    data() {
        return {
            scannedText: "",
            products: [],
            total: 0,
            productStock: {},
            remainingStock: {},
            branches: this.$page.props.branches || [],
        };
    },
    methods: {
        onDecode(text) {
            console.log("✅ Decoded text:", text);
            this.scannedText = text;
        },
        onResult(result) {
            console.log("📦 Full scan result:", result);
        },
        onError(err) {
            console.error("❌ Scan failed:", err);
        },
        handleAddToCart(product) {
            const remaining =
                this.remainingStock[product.product_code] ?? product.stock;

            if (product.quantity > remaining) {
                Swal.fire({
                    icon: "warning",
                    title: "Stock Error",
                    text: `Only ${remaining} in stock.`,
                });
                return; // ⛔ Don't reset
            }

            const existing = this.products.find(
                (p) =>
                    p.product_code === product.product_code &&
                    p.cost_price === product.cost_price &&
                    p.retail_price === product.retail_price
            );

            if (existing) {
                existing.quantity += product.quantity;
                existing.total += product.total;
            } else {
                this.products.push(product);
            }

            this.remainingStock[product.product_code] =
                remaining - product.quantity;

            this.calculateTotal();
            this.scannedText = "";
        },
        handleRemove(index) {
            const removed = this.products[index];
            this.products.splice(index, 1);

            // ✅ Restore stock
            const barcode = removed.product_code;
            if (this.productStock[barcode] !== undefined) {
                this.productStock[barcode] += removed.quantity;
            }

            this.calculateTotal();
        },
        calculateTotal() {
            this.total = this.products.reduce((sum, item) => {
                return sum + item.cost_price * item.quantity;
            }, 0);
        },
        handleStock({ barcode, stock }) {
            if (!this.productStock[barcode]) {
                this.productStock[barcode] = stock;
                this.remainingStock[barcode] = stock;
            }
        },
        triggerPDF() {
            console.log("triggerPDF called");
            console.log(this.$refs.invoiceRef);

            this.$refs.invoiceRef?.exportPDF();
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        },
    },
};
// const invoiceRef = ref(null);
</script>
<template>
    <div data-aos="fade-up">
        <div class="grid grid-cols-3 grid-rows-2 gap-2">
            <!-- Card 1 - Wider and aligned left -->
            <div class="col-span-2 p-4 rounded shadow">
                <TransferProductForm
                    :barcode="scannedText"
                    :available-stock="productStock[scannedText] || 0"
                    :branches="branches"
                    @add="handleAddToCart"
                    @stock="handleStock"
                />
                <p v-if="scannedText" class="text-lg text-gray-700 mt-2">
                    Remaining Stock: {{ remainingStock[scannedText] ?? 0 }}
                </p>
            </div>

            <!-- Card 2 - Full width in its grid cell -->
            <div class="col-span-3 col-start-1 row-start-2 h-[320px]">
                <div class="grid grid-cols-4 grid-rows-0 gap-1">
                    <div class="col-span-3 mb-4">
                        <TransferProductTable
                            :products="products"
                            @remove="handleRemove"
                        />
                        <InvoiceTemplate
                            ref="invoiceRef"
                            :products="products"
                            class="sr-only-print"
                            @remove="handleRemove"
                        />
                    </div>
                    <div class="col-start-4 ml-5">
                        <TransferProductButton
                            @click="triggerPDF"
                            :products="products"
                            @clearProducts="products = []"
                        />
                    </div>
                </div>
            </div>
            <!-- Card 3 (Barcode Scanner) - row span 2, aligned right -->
            <div class="col-start-3 row-start-1">
                <div class="bg-white p-4 rounded shadow w-full max-w-4xl">
                    <h1 class="text-xl font-bold mb-2 text-center">
                        Scan a Barcode
                    </h1>

                    <div class="w-full max-w-[920px] mx-auto">
                        <ImageBarcodeReader
                            @decode="onDecode"
                            @result="onResult"
                            @error="onError"
                        />
                    </div>

                    <div class="flex justify-center mt-4">
                        <TextInput
                            name="Barcode"
                            class="w-full max-w-md text-xl"
                            v-model="scannedText"
                        />
                    </div>
                </div>
            </div>

            <!-- Card 4 - bottom right card, aligned right -->
            <!-- <div class="col-start-3 row-start-2 bg-white p-4 rounded shadow">
                <h1 class="text-xl font-bold mb-2 text-center">
                    Scan a Barcode
                </h1>
                <p class="text-center text-gray-700">
                    Content for card 8 goes here.
                </p>
            </div> -->
        </div>
    </div>
</template>
<style scoped>
.sr-only-print {
    position: absolute !important;
    left: -9999px !important;
    top: 0;
    width: 1000px; /* or any suitable width for your invoice */
}
</style>
