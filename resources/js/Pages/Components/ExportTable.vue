<template>
    <button
        @click="exportTable"
        class="text-white hover:bg-green-800 bg-green-600 font-medium rounded-lg text-sm px-5 py-2.5 focus:outline-none"
    >
        Excel
    </button>
</template>

<script>
import * as XLSX from "xlsx";
import { saveAs } from "file-saver";

export default {
    props: {
        tableId: String,
        filename: {
            type: String,
            default: "table_data.xlsx",
        },
    },
    methods: {
        exportTable() {
            const table = document.getElementById(this.tableId);
            if (!table) return alert("Table not found");

            const rows = Array.from(table.querySelectorAll("tr")).map((tr) =>
                Array.from(tr.querySelectorAll("th, td")).map((td) => {
                    const value = td.innerText.trim();
                    return /^0\d+/.test(value)
                        ? { v: value, t: "s" } // keep leading zeros
                        : value;
                })
            );

            const ws = XLSX.utils.aoa_to_sheet(rows);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Sheet1");

            const excelData = XLSX.write(wb, {
                bookType: "xlsx",
                type: "array",
            });
            saveAs(
                new Blob([excelData], { type: "application/octet-stream" }),
                this.filename
            );
        },
    },
};
</script>
