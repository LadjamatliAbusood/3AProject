<script>
import ImageBarcodeReader from "./ImageBarcodeReader.vue";
import { useForm } from "@inertiajs/vue3";
import TextInput from "@/Pages/Components/TextInput.vue";
import SelectInput from "../Components/SelectInput.vue";
import PrimaryButton from "../Components/PrimaryButton.vue";
import ProductForm from "./ProductForm.vue";
import CashierLayout from "../../Layouts/CashierLayout.vue";
import ProductTable from "./ProductTable.vue";
import InvoiceForm from "./InvoiceForm.vue";
import { h } from "vue";

import Layout from "../../Layouts/Layout.vue";
import Receipt from "../Invoice/Receipt.vue";
import { nextTick } from "vue";

export default {
    layout: CashierLayout,
    components: {
        ImageBarcodeReader,
        TextInput,
        SelectInput,
        ProductForm,
        PrimaryButton,
        ProductTable,
        InvoiceForm,
        Receipt,
    },
    data() {
        return {
            scannedText: "",
            products: [],
            total: 0,
            productStock: {},
            remainingStock: {},
            invoiceValues: {
                total: 0,
                pay: 0,
                change: 0,
            },
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
                    p.selling_price === product.selling_price
            );

            if (existing) {
                existing.quantity += product.quantity;
                existing.total += product.total;
                existing.net_sale += product.net_sale;
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
                return sum + item.selling_price * item.quantity;
            }, 0);
        },
        handleStock({ barcode, stock }) {
            if (!this.productStock[barcode]) {
                this.productStock[barcode] = stock;
                this.remainingStock[barcode] = stock;
            }
        },
        handleInvoiceValues(values) {
            this.invoiceValues = values;
            console.log("Received from InvoiceForm:", values);
        },
        printReceipt() {
            const content = document.getElementById("receipt-content");
            if (!content) return;

            const printWindow = window.open("", "_blank");
            const doc = printWindow.document;

            const tailwindCDN = `
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    `;

            doc.open();
            doc.write(`
        <html>
            <head>
                <title>Receipt</title>
                ${tailwindCDN}
                <style>
                    @media print {
                        @page {
                            size: 80mm auto;
                            margin: 0;
                        }
                        body {
                            margin: 0;
                            padding: 0;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="receipt">
                    ${content.innerHTML}
                </div>
                <script>
                    window.onafterprint = function () {
                        window.opener.location.reload();
                        window.close();
                    };
                    window.print();
                <\/script>
            </body>
        </html>
    `);
            doc.close();
        },
    },
};
</script>

<template>
    <div class="grid grid-cols-3 grid-rows-2 gap-2">
        <!-- Card 1 - Wider and aligned left -->
        <div class="col-span-2 p-4 rounded shadow bg-gray-100">
            <ProductForm
                :barcode="scannedText"
                :available-stock="productStock[scannedText] || 0"
                @add="handleAddToCart"
                @stock="handleStock"
            />
        </div>

        <!-- Card 2 - Full width in its grid cell -->
        <div class="col-span-3 col-start-1 row-start-2 h-[320px] p-4">
            <div class="grid grid-cols-3 grid-rows-0 gap-1">
                <div class="col-span-2 mb-4 bg-gray-100">
                    <ProductTable :products="products" @remove="handleRemove" />
                </div>
                <div id="receipt-content" class="hidden print:block">
                    <Receipt
                        :products="products"
                        @remove="handleRemove"
                        :total="invoiceValues.total"
                        :pay="invoiceValues.pay"
                        :change="invoiceValues.change"
                    />
                </div>

                <div class="col-start-3 p-4">
                    <InvoiceForm
                        :total="total"
                        :products="products"
                        @clearProducts="products = []"
                        @sendInvoiceValues="handleInvoiceValues"
                        @printReceipt="printReceipt"
                    />
                </div>
            </div>
        </div>
        <!-- Card 3 (Barcode Scanner) - row span 2, aligned right -->
        <div class="col-start-3 row-start-1">
            <div class="bg-gray-100 p-4 rounded shadow w-full max-w-4xl">
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
            <h1 class="text-xl font-bold mb-2 text-center">Scan a Barcode</h1>
            <p class="text-center text-gray-700">
                Content for card 8 goes here.
            </p>
        </div> -->
    </div>
</template>
