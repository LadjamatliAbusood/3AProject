<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import PaginationLinks from "@/Pages/Components/PaginationLink.vue";
import { ref, computed } from "vue";
import TextInput from "@/Pages/Components/TextInput.vue";
import SelectInput from "../Components/SelectInput.vue";
import { StatusOptions } from "../libraries/roles.js";
import PrimaryButton from "../Components/PrimaryButton.vue";

const EditingExpenses = ref(false);

const label = computed(() =>
    EditingExpenses.value ? "Update Expenses" : "Add Expenses"
);
const props = defineProps({
    branches: Object,
    Expenses: Object,
});

const BranchOptions = props.branches.map((b) => ({
    value: b.id,
    label: b.branch_name,
}));
// const page = usePage();
// const userRole = page.props.auth?.user?.acct_roles;

const form = useForm({
    branch_id: null,
    description: null,
    amount: null,
});

const EditExpenses = (branch) => {
    EditingExpenses.value = branch;
    form.branch_id = branch.branch_id;
    form.description = branch.description;
    form.amount = String(branch.amount);
};
const submit = () => {
    if (EditingExpenses.value) {
        form.put(
            `/admin/expenses/${EditingExpenses.value.id}?page=${props.branches.current_page}`,
            {
                onSuccess: () => {
                    form.reset();
                    EditingExpenses.value = null;
                    Swal.fire({
                        icon: "success",
                        title: "Updated!",
                        text: "Expenses updated successfully.",
                        timer: 2000,
                        showConfirmButton: false,
                    });
                },
            }
        );
    } else {
        form.post("/admin/expenses", {
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
    }
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
            <!-- Branches Card -->
            <div class="bg-white p-6 rounded-lg shadow-md h-[420px]">
                <h3 class="text-lg font-semibold">
                    {{ EditingExpenses ? "Edit Expenses" : "Add Expenses" }}
                </h3>
                <div class="flex items-center justify-center">
                    <form
                        class="max-w-md w-[300px] flex flex-col mt-2"
                        @submit.prevent="submit"
                    >
                        <SelectInput
                            name="Branch"
                            v-model="form.branch_id"
                            :options="BranchOptions"
                            :message="form.errors.branch_id"
                            :disableSelect="EditingExpenses"
                        />
                        <label class="block text-md font-medium text-gray-700"
                            >Description</label
                        >
                        <textarea
                            class="bg-white border text-gray-900 font-semibold rounded-lg block w-full p-2.5"
                            name="Description"
                            v-model="form.description"
                            :message="form.errors.description"
                        >
                        </textarea>

                        <TextInput
                            name="total"
                            v-model="form.amount"
                            :message="form.errors.amount"
                        />
                        <PrimaryButton
                            :label="label"
                            :disabled="form.processing"
                            :processing="form.processing"
                        />
                    </form>
                </div>
            </div>
            <!-- Branches Expenses table Card -->
            <div
                class="bg-white p-6 rounded-lg shadow-md col-span-1 lg:col-span-2"
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">
                        List of Branch Expenses
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
                                    Branch
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

                                <th
                                    class="px-4 py-2 text-left text-md font-medium text-gray-800"
                                >
                                    Action
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
                                    class="px-4 py-2 text-left text-md font-medium text-gray-600 uppercase"
                                >
                                    {{ expenses.branch.branch_name }}
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

                                <td
                                    class="px-4 py-2 text-left text-md font-medium text-gray-600"
                                >
                                    <button @click="EditExpenses(expenses)">
                                        <PencilSquareIcon
                                            class="w-6 h-6 text-blue-600 cursor-pointer"
                                        />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- pagination link -->
                <div>
                    <PaginationLinks :paginator="Expenses" />
                </div>
            </div>
        </div>
    </section>
</template>
