<script setup>
import { useForm } from "@inertiajs/vue3";
import PaginationLinks from "../Components/PaginationLink.vue";
import { computed, ref } from "vue";
import TextInput from "../Components/TextInput.vue";
import SelectInput from "../Components/SelectInput.vue";
import { usePage } from "@inertiajs/vue3";
import {
    roleLabels,
    roleOptions,
    canDelete,
    getAssignableRoles,
    StatusOptions,
} from "../libraries/roles.js";

const page = usePage();
const auth = computed(() => page.props.auth.user);
// Use for filtering role dropdown
const assignableRoles = computed(() => getAssignableRoles(auth.value));

// Use for delete
const isDeletable = (account) => canDelete(auth.value, account);

const props = defineProps({
    branches: Array,
    Account: Object,
});

const branchOptions = props.branches.map((b) => ({
    value: b.id,
    label: b.branch_name,
}));

const form = useForm({
    branch_id: null,
    acct_name: null,
    acct_roles: null,
    password: null,
    status: "1",
});
const editingAccount = ref(null);
const EditAccount = (Account) => {
    editingAccount.value = Account;
    form.acct_name = Account.acct_name;
    form.branch_id = String(Account.branch_id);
    form.acct_roles = String(Account.acct_roles);
    form.status = String(Account.status);
};

const productBaseUrl = computed(() => {
    if (userRole.value === 3) return `/supervisor/${branch}/product`;
    if (userRole.value === 2) return `/administrator/product`;
    return `/admin/product`;
});
const submit = () => {
    if (editingAccount.value) {
        form.put(
            `/admin/account/${editingAccount.value.id}?page=${props.Account.current_page}`,
            {
                onSuccess: () => {
                    form.reset();
                    editingAccount.value = null;
                    Swal.fire({
                        icon: "success",
                        title: "Updated!",
                        text: "Account updated successfully.",
                        timer: 2000,
                        showConfirmButton: false,
                    });
                },
            }
        );
    } else {
        form.post("/admin/account", {
            onSuccess: () => {
                form.reset();
                Swal.fire({
                    icon: "success",
                    title: "Success!",
                    text: "Account added successfully.",
                    timer: 2000,
                    showConfirmButton: false,
                });
            },
        });
    }
};

const DeleteAccount = (account) => {
    Swal.fire({
        title: "Are you sure?",
        text: `This will permanently delete account: ${account.acct_name}`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete it!",
    }).then((result) => {
        if (result.isConfirmed) {
            form.delete(`/admin/account/${account.id}`, {
                onSuccess: () => {
                    Swal.fire(
                        "Deleted!",
                        "Account has been deleted.",
                        "success"
                    );
                },
            });
        }
    });
};
</script>
<template>
    <Head title=" | Account" />

    <div
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
        data-aos="fade-up"
    >
        <!-- Account Card -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="text-lg font-semibold">
                Register |
                {{ editingAccount ? "Update Account" : "Add Account" }}
            </h3>
            <div class="flex items-center justify-center">
                <form
                    class="max-w-md w-[300px] flex flex-col mt-2"
                    @submit.prevent="submit"
                >
                    <TextInput
                        name="Account Name"
                        v-model="form.acct_name"
                        :message="form.errors.acct_name"
                    />

                    <TextInput
                        v-if="
                            !editingAccount ||
                            (editingAccount &&
                                $page.props.auth.user.acct_roles == 1)
                        "
                        name="Password"
                        type="password"
                        v-model="form.password"
                        :message="form.errors.password"
                    />
                    <SelectInput
                        name="Branch"
                        v-model="form.branch_id"
                        :options="branchOptions"
                        :message="form.errors.branch_id"
                    />

                    <SelectInput
                        name="Account Role"
                        v-model="form.acct_roles"
                        :options="assignableRoles"
                        :message="form.errors.acct_roles"
                    />

                    <SelectInput
                        name="Status"
                        v-model="form.status"
                        :options="StatusOptions"
                        :message="form.errors.status"
                    />

                    <button
                        :disabled="
                            form.processing ||
                            (editingAccount &&
                                editingAccount.acct_roles == 1 &&
                                $page.props.auth.user.acct_roles != 1)
                        "
                        :class="[
                            'mt-2 text-white font-medium rounded-lg text-md px-5 py-2.5 focus:outline-none',
                            form.processing ||
                            (editingAccount &&
                                editingAccount.acct_roles == 1 &&
                                $page.props.auth.user.acct_roles != 1)
                                ? 'bg-gray-400 cursor-not-allowed'
                                : 'bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300',
                        ]"
                    >
                        {{ editingAccount ? "Update Account" : "Add Account" }}
                    </button>
                </form>
            </div>
        </div>
        <!-- Branches table Card -->
        <div class="bg-white p-6 rounded-lg shadow-md col-span-1 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">
                    List of Account Register
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
                                Name
                            </th>
                            <th
                                class="px-4 py-2 text-left text-md font-medium text-gray-800"
                            >
                                Branch
                            </th>
                            <th
                                class="px-4 py-2 text-left text-md font-medium text-gray-800"
                            >
                                Account Roles
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
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr
                            v-for="(account, index) in Account.data"
                            :key="account.id"
                            class="hover:bg-gray-100"
                        >
                            <td
                                class="px-4 py-2 text-left text-md font-medium text-gray-600"
                            >
                                {{ index + 1 }}
                            </td>
                            <td
                                class="px-4 py-2 text-left text-md font-medium text-gray-600"
                            >
                                {{ account.acct_name }}
                            </td>
                            <td
                                class="px-4 py-2 text-left text-md font-medium text-gray-600"
                            >
                                {{ account.branch.branch_name }}
                            </td>
                            <td
                                class="px-4 py-2 text-left text-md font-medium text-gray-600"
                            >
                                {{ roleLabels[account.acct_roles] }}
                            </td>
                            <td
                                class="px-4 py-2 text-left text-md font-medium text-gray-600"
                            >
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                    :class="{
                                        'bg-green-200 text-green-700':
                                            account.status == 1,
                                        'bg-red-200 text-red-700':
                                            account.status == 2,
                                    }"
                                >
                                    <span
                                        class="h-2 w-2 rounded-full mr-2"
                                        :class="{
                                            'bg-green-500': account.status == 1,
                                            'bg-red-500': account.status == 2,
                                        }"
                                    ></span>
                                    {{
                                        account.status == 1
                                            ? "Active"
                                            : "Deactive"
                                    }}
                                </span>
                            </td>

                            <td class="px-2 py-2 text-xl text-gray-500">
                                <button @click="EditAccount(account)">
                                    <PencilSquareIcon
                                        class="w-6 h-6 mr-2 text-blue-500 cursor-pointer"
                                    />
                                </button>

                                <button
                                    v-if="isDeletable(account)"
                                    @click="DeleteAccount(account)"
                                >
                                    <TrashIcon
                                        class="w-6 h-6 ml-2 text-red-500 cursor-pointer"
                                    />
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- pagination link -->
            <div>
                <PaginationLinks :paginator="Account" />
            </div>
        </div>
    </div>
</template>
