<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import PaginationLinks from "@/Pages/Components/PaginationLink.vue";
import { ref } from "vue";
import TextInput from "@/Pages/Components/TextInput.vue";
import SelectInput from "../Components/SelectInput.vue";
import { StatusOptions } from "../libraries/roles.js";
import PrimaryButton from "../Components/PrimaryButton.vue";

const props = defineProps({
    branches: Object,
    Expenses: Object,
    branchId: Number,
});

const page = usePage();
const userBranchId = page.props.auth?.user?.branch_id;

// ✅ form setup: must use `ref()` to make reactive
const form = useForm({
    description: "",
    amount: "",
    branch_id: props.branchId || userBranchId, // fallback if props.branchId is undefined
});

const submit = () => {
    form.post(`/supervisor/${form.branch_id}/expenses`, {
        onSuccess: () => {
            form.reset();
            Swal.fire({
                icon: "success",
                title: "Success!",
                text: "Expenses added successfully.",
                timer: 2000,
                showConfirmButton: false,
            });
        },
    });
};

const getDate = (date) =>
    new Date(date).toLocaleDateString("en-us", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
</script>

<template>
    <Head title=" | Expenses" />

    <section class="relative w-full" data-aos="fade-up">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Add Expenses Card -->
            <div class="bg-white p-6 rounded-lg shadow-md h-[320px]">
                <h3 class="text-lg font-semibold">Add Expenses</h3>
                <div class="flex items-center justify-center">
                    <form
                        class="max-w-md w-[300px] flex flex-col mt-2"
                        @submit.prevent="submit"
                    >
                        <label class="block text-md font-medium text-gray-700">
                            Description
                        </label>
                        <textarea
                            class="bg-white border text-gray-900 font-semibold rounded-lg block w-full p-2.5"
                            v-model="form.description"
                        ></textarea>
                        <div
                            v-if="form.errors.description"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ form.errors.description }}
                        </div>

                        <TextInput
                            name="amount"
                            type="number"
                            v-model="form.amount"
                            :message="form.errors.amount"
                        />

                        <PrimaryButton
                            label="Add Expenses"
                            :disabled="form.processing"
                            :processing="form.processing"
                        />
                    </form>
                </div>
            </div>

            <!-- List of Expenses -->
            <div
                class="bg-white p-6 rounded-lg shadow-md col-span-1 lg:col-span-2"
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">
                        List of Expenses
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table
                        class="min-w-full table-auto border-collapse border border-gray-300 shadow-md"
                    >
                        <thead class="bg-gray-100">
                            <tr>
                                <th
                                    class="px-4 py-2 text-left text-md font-medium text-gray-800"
                                >
                                    #
                                </th>
                                <th
                                    class="px-4 py-2 text-left text-md font-medium text-gray-800"
                                >
                                    Date
                                </th>
                                <th
                                    class="px-4 py-2 text-left text-md font-medium text-gray-800"
                                >
                                    Description
                                </th>
                                <th
                                    class="px-4 py-2 text-left text-md font-medium text-gray-800"
                                >
                                    Total
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                class="odd:bg-white even:bg-gray-50 hover:bg-gray-100"
                                v-for="(expenses, index) in Expenses.data"
                                :key="expenses.id"
                            >
                                <td
                                    class="px-4 py-2 text-left text-md font-medium text-gray-600"
                                >
                                    {{ index + 1 }}
                                </td>
                                <td
                                    class="px-4 py-2 text-left text-md font-medium text-gray-600 uppercase"
                                >
                                    {{ getDate(expenses.created_at) }}
                                </td>
                                <td
                                    class="px-4 py-2 text-left text-md font-medium text-gray-600"
                                >
                                    {{ expenses.description }}
                                </td>
                                <td
                                    class="px-4 py-2 text-left text-md font-medium text-gray-600"
                                >
                                    ₱{{
                                        parseFloat(
                                            expenses.amount
                                        ).toLocaleString()
                                    }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <PaginationLinks :paginator="Expenses" />
            </div>
        </div>
    </section>
</template>
