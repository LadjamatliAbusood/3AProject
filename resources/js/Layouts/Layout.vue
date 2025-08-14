<script setup>
import { ref } from "vue";
import Header from "./Header.vue";
import Sidebar from "./Sidebar.vue";

const showSidebar = ref(true); // sidebar is visible by default
</script>

<template>
    <div class="min-h-screen flex flex-col">
        <!-- Header (toggle button is here) -->
        <Header @toggle-sidebar="showSidebar = !showSidebar" />

        <!-- Content area below header -->
        <div class="flex flex-row flex-1">
            <!-- Sidebar toggles on/off -->
            <Sidebar v-if="showSidebar" class="w-64" />

            <!-- Main content -->
            <main class="flex-1 bg-gray-100 p-6 overflow-y-auto">
                <slot />
            </main>
        </div>
    </div>
</template>
