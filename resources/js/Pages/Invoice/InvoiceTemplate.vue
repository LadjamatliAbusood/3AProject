<script setup>
import { usePage } from "@inertiajs/vue3";
import { computed, watch } from "vue";
import { ref } from "vue";
import html2canvas from "html2canvas";
import jsPDF from "jspdf";

const props = defineProps({
    products: Array,
    invoiceNumer: String,
});

const page = usePage();
const acct_roles = page.props.auth?.user?.acct_roles;
const username = page.props.auth.user.acct_name;
const invoiceRef = ref(null);
const invoiceNumber = page.props.invoiceNumber;

const currentDate = new Date().toLocaleDateString("en-PH", {
    year: "numeric",
    month: "long",
    day: "numeric",
});

const subtotal = computed(() => {
    return props.products.reduce((sum, product) => {
        return sum + parseFloat(product.total || 0);
    }, 0);
});

const exportPDF = async () => {
    console.log("exportPDF running...");
    const el = invoiceRef.value;
    if (!el) {
        console.error(" invoiceRef is null");
        return;
    }

    const canvas = await html2canvas(el, {
        scale: 2,
        useCORS: true,
    });

    const imgData = canvas.toDataURL("image/png");
    const pdf = new jsPDF("p", "mm", "a4");

    const imgProps = pdf.getImageProperties(imgData);
    const pdfWidth = pdf.internal.pageSize.getWidth();
    const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

    pdf.addImage(imgData, "PNG", 0, 0, pdfWidth, pdfHeight);
    console.log("✅ Saving PDF now...");
    pdf.save(invoiceNumer + ".pdf");
};

defineExpose({
    exportPDF,
});
function generateInvoiceNumber() {
    const date = new Date();

    // Format: YYYYMMDD
    const datePart = date.toISOString().split("T")[0].replace(/-/g, "");

    // 12-hour format time
    let hours = date.getHours();
    const minutes = String(date.getMinutes()).padStart(2, "0");
    const seconds = String(date.getSeconds()).padStart(2, "0");
    const ampm = hours >= 12 ? "PM" : "AM";
    hours = hours % 12 || 12; // Convert to 12-hour format and replace 0 with 12

    const timePart = `${String(hours).padStart(
        2,
        "0"
    )}${minutes}${seconds}${ampm}`;

    // // Random alphanumeric
    // const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    // let randomPart = "";
    // for (let i = 0; i < 5; i++) {
    //     randomPart += chars.charAt(Math.floor(Math.random() * chars.length));
    // } -${randomPart}

    return `INV-${datePart}-${timePart}`;
}
const invoiceNumer = props.invoiceNumer || generateInvoiceNumber();
</script>

<template>
    <div
        id="a4-wrapper"
        ref="invoiceRef"
        class="bg-white text-black px-6 py-4 flex flex-col min-h-[1000px]"
    >
        <!-- Header Section -->
        <div class="mb-1">
            <div class="flex justify-between items-start mb-8">
                <div class="text-center text-2xl -mt-2 font-bold">
                    3A<br />
                    MERCHANDISER
                </div>
                <div class="text-right text-sm leading-tight">
                    <p>Tomas Claudio St,<br />Zamboanga City, 7000</p>
                </div>
            </div>

            <!-- Title just below the header (no spacing) -->
            <div class="text-center font-extrabold text-xl -mt-2">
                STOCK TRANSFER
            </div>

            <!-- Info Row -->
            <div class="flex justify-between text-sm mt-8">
                <!-- Left: Branch Info -->
                <div class="w-1/3">
                    <p class="font-bold">ISSUED TO :</p>
                    <p class="mt-2">
                        Branch:
                        {{
                            props.products.length > 0
                                ? props.products[0].to_branch_name
                                : "N/A"
                        }}
                    </p>
                </div>

                <!-- Center: Issued Date -->
                <div class="w-1/3 text-center">
                    <p class="font-bold">ISSUED DATE :</p>
                    <p class="mt-2">{{ currentDate }}</p>
                </div>

                <!-- Right: Invoice No -->
                <div class="w-1/3 text-right">
                    <p class="font-bold">INVOICE #</p>
                    <p class="mt-2">{{ invoiceNumer }}</p>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="flex-grow mt-8">
            <table class="min-w-full border-t border-b text-sm">
                <thead class="bg-gray-200 font-semibold">
                    <tr>
                        <th class="text-left px-4 py-2 border-r">
                            Product Code
                        </th>
                        <th class="text-left px-4 py-2 border-r">
                            Item Description
                        </th>
                        <th class="text-center px-4 py-2 border-r">Qty</th>
                        <th class="text-right px-4 py-2 border-r">
                            Unit Price
                        </th>
                        <th class="text-right px-4 py-2">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(item, index) in products"
                        :key="index"
                        class="border-t hover:bg-gray-50"
                    >
                        <td class="px-4 py-2">{{ item.product_code }}</td>
                        <td class="px-4 py-2">{{ item.description }}</td>
                        <td class="px-4 py-2 text-center">
                            {{ item.quantity }}
                        </td>
                        <td class="px-4 py-2 text-right">
                            {{ parseFloat(item.retail_price).toLocaleString() }}
                        </td>
                        <td class="px-4 py-2 text-right">
                            {{ parseFloat(item.total).toLocaleString() }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Subtotal -->
            <div class="flex justify-end mt-8">
                <div class="w-1/3 text-sm">
                    <div class="flex justify-between font-semibold">
                        <span>Subtotal</span>
                        <span
                            >₱{{ parseFloat(subtotal).toLocaleString() }}</span
                        >
                    </div>
                    <div class="border-t mt-1 border-black"></div>
                </div>
            </div>
        </div>

        <!-- Footer -->

        <div class="flex justify-between text-sm mt-8 uppercase">
            <!-- Left: Branch Info -->
            <div class="w-1/3">
                <p class="font-bold">SERVED BY: {{ username }}</p>
                <p class="mt-2">
                    Branch:
                    {{
                        props.products.length > 0
                            ? props.products[0].from_branch_name
                            : "N/A"
                    }}
                </p>
            </div>

            <!-- Right: Invoice No -->
            <div class="w-1/3 text-right">
                <p class="font-bold">thank you</p>
            </div>
        </div>
    </div>
</template>
<style scoped>
* {
    color: black !important;
    background-color: white !important;
}

#a4-wrapper {
    width: 210mm;
    min-height: 297mm;
    background-color: white;
    box-sizing: border-box;
    margin: auto;
    overflow: hidden;
    padding: 20mm 15mm;
}

@media print {
    #a4-wrapper {
        page-break-after: always;
    }
}
</style>
