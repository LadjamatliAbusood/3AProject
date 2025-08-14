<script setup>
import CashierHeader from "./CashierHeader.vue";
</script>

<template>
    <div>
        <!-- Header -->
        <CashierHeader />

        <!-- Entire page is scrollable now -->
        <main class="p-6 min-h-screen">
            <slot />
        </main>
    </div>
</template>
