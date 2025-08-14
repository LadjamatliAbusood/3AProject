<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import PaginationLinks from "@/Pages/Components/PaginationLink.vue";
import { ref, computed } from "vue";
import TextInput from "@/Pages/Components/TextInput.vue";
import SelectInput from "../Components/SelectInput.vue";
import { StatusOptions } from "../libraries/roles.js";
import PrimaryButton from "../Components/PrimaryButton.vue";

const editingBranch = ref(false);

const label = computed(() =>
    editingBranch.value ? "Update Branch" : "Add Branch"
);
const props = defineProps({
    branches: Object,
    allBranches: Array,
});
const page = usePage();
const userRole = page.props.auth?.user?.acct_roles;

const form = useForm({
    branch_name: null,
    location: null,
    status: "1",
});

const editBranch = (branch) => {
    editingBranch.value = branch;
    form.branch_name = branch.branch_name;
    form.location = branch.location;
    form.status = String(branch.status);
};
const submit = () => {
    if (editingBranch.value) {
        form.put(
            `/admin/branch/${editingBranch.value.id}?page=${props.branches.current_page}`,
            {
                onSuccess: () => {
                    form.reset();
                    editingBranch.value = null;
                    Swal.fire({
                        icon: "success",
                        title: "Updated!",
                        text: "Branch updated successfully.",
                        timer: 2000,
                        showConfirmButton: false,
                    });
                },
            }
        );
    } else {
        form.post("/admin/branch", {
            onSuccess: () => {
                form.reset();
                Swal.fire({
                    icon: "success",
                    title: "Success!",
                    text: "Branch added successfully.",
                    timer: 2000,
                    showConfirmButton: false,
                });
            },
        });
    }
};
</script>
<template>
    <Head title=" | Branch" />

    <section class="relative w-full" data-aos="fade-up">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Branches Card -->
            <div class="bg-white p-6 rounded-lg shadow-md h-[400px]">
                <h3 class="text-lg font-semibold">
                    {{ editingBranch ? "Edit Branch" : "Add Branch" }}
                </h3>
                <div class="flex items-center justify-center">
                    <form
                        class="max-w-md w-[300px] flex flex-col mt-2"
                        @submit.prevent="submit"
                    >
                        <TextInput
                            name="Branch Name"
                            v-model="form.branch_name"
                            :message="form.errors.branch_name"
                        />
                        <TextInput
                            name="Location"
                            v-model="form.location"
                            :message="form.errors.location"
                        />

                        <SelectInput
                            name="Status"
                            v-model="form.status"
                            :options="StatusOptions"
                            :message="form.errors.status"
                        />
                        <PrimaryButton
                            :label="label"
                            :disabled="form.processing"
                            :processing="form.processing"
                        />
                    </form>
                </div>
            </div>
            <!-- Branches table Card -->
            <div
                class="bg-white p-6 rounded-lg shadow-md col-span-1 lg:col-span-2"
            >
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">
                        List of Branch
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
                                    Branch Name
                                </th>
                                <th
                                    class="px-4 py-2 text-left text-md font-medium text-gray-800"
                                >
                                    Location
                                </th>
                                <th
                                    class="px-4 py-2 text-left text-md font-medium text-gray-800"
                                >
                                    Stock
                                </th>
                                <th
                                    class="px-4 py-2 text-left text-md font-medium text-gray-800"
                                >
                                    Cost
                                </th>
                                <th
                                    class="px-4 py-2 text-left text-md font-medium text-gray-800"
                                >
                                    Status
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
                                v-for="(branch, index) in branches.data"
                                :key="branch.id"
                            >
                                <td
                                    class="px-4 py-2 text-left text-md font-medium text-gray-600"
                                >
                                    {{ index + 1 }}
                                </td>
                                <td
                                    class="px-4 py-2 text-left text-md font-medium text-gray-600"
                                >
                                    <Link
                                        :href="
                                            userRole === 1
                                                ? route(
                                                      'admin.branch.show',
                                                      branch.branch_name
                                                  )
                                                : userRole === 2
                                                ? route(
                                                      'administrator.branch.show',
                                                      branch.branch_name
                                                  )
                                                : '#'
                                        "
                                        class="px-4 py-2 text-left text-md font-medium text-blue-600 uppercase"
                                    >
                                        {{ branch.branch_name }}
                                    </Link>
                                </td>
                                <td
                                    class="px-4 py-2 text-left text-md font-medium text-gray-600 uppercase"
                                >
                                    {{ branch.location }}
                                </td>
                                <td
                                    class="px-4 py-2 text-left text-md font-medium text-gray-600"
                                >
                                    {{ branch.total_quantity }}
                                </td>
                                <td
                                    class="px-4 py-2 text-left text-md font-medium text-gray-600"
                                >
                                    ₱{{
                                        parseFloat(
                                            branch.total_cost
                                        ).toLocaleString()
                                    }}
                                </td>
                                <td
                                    class="px-4 py-2 text-left text-md font-medium text-gray-600"
                                >
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                        :class="{
                                            'bg-green-200 text-green-700':
                                                branch.status == 1,
                                            'bg-red-200 text-red-700':
                                                branch.status == 2,
                                        }"
                                    >
                                        <span
                                            class="h-2 w-2 rounded-full mr-2"
                                            :class="{
                                                'bg-green-500':
                                                    branch.status == 1,
                                                'bg-red-500':
                                                    branch.status == 2,
                                            }"
                                        ></span>
                                        {{
                                            branch.status == 1
                                                ? "Active"
                                                : "Deactive"
                                        }}
                                    </span>
                                </td>

                                <td
                                    class="px-4 py-2 text-left text-md font-medium text-gray-600"
                                >
                                    <button @click="editBranch(branch)">
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
                    <PaginationLinks :paginator="branches" />
                </div>
            </div>
        </div>
    </section>
</template>
