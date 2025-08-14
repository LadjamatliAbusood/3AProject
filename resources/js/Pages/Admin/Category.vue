<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import PaginationLinks from "@/Pages/Components/PaginationLink.vue";
import { ref, computed } from "vue";
import TextInput from "@/Pages/Components/TextInput.vue";
import SelectInput from "../Components/SelectInput.vue";
import { StatusOptions } from "../libraries/roles.js";
import PrimaryButton from "../Components/PrimaryButton.vue";

const label = computed(() =>
    editingCategory.value ? "Update Category" : "Add Category"
);

const props = defineProps({
    Categories: Object,
});

const form = useForm({
    category_name: null,
    status: "1",
});
const editingCategory = ref(null);
const editCategory = (cat) => {
    editingCategory.value = cat;
    form.category_name = cat.category_name;
    form.status = String(cat.status);
};

const submit = () => {
    if (editingCategory.value) {
        form.put(
            `/admin/category/${editingCategory.value.id}?page=${props.Categories.current_page}`,
            {
                onSuccess: () => {
                    form.reset();
                    editingCategory.value = null;
                    Swal.fire({
                        icon: "success",
                        title: "Updated!",
                        text: "Category updated successfully.",
                        timer: 2000,
                        showConfirmButton: false,
                    });
                },
            }
        );
    } else {
        form.post("/admin/category", {
            onSuccess: () => {
                form.reset();
                Swal.fire({
                    icon: "success",
                    title: "Success!",
                    text: "Category added successfully.",
                    timer: 2000,
                    showConfirmButton: false,
                });
            },
        });
    }
};
</script>
<template>
    <Head title=" | Category" />

    <div
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
        data-aos="fade-up"
    >
        <!-- Supplier Card -->
        <div class="bg-white p-6 rounded-lg shadow-md h-[300px]">
            <h3 class="text-lg font-semibold">
                {{ editingCategory ? "Edit Category" : "Add Category" }}
            </h3>
            <div class="flex items-center justify-center">
                <form
                    class="max-w-md w-[300px] flex flex-col mt-2"
                    @submit.prevent="submit"
                >
                    <TextInput
                        name="Category"
                        v-model="form.category_name"
                        :message="form.errors.category_name"
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
        <!-- Supplier Card -->
        <div class="bg-white p-6 rounded-lg shadow-md col-span-1 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">
                    List of Category
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
                                Category
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
                    <tbody class="bg-white divide-y divide-gray-400">
                        <tr
                            v-for="(cat, index) in Categories.data"
                            :key="cat.id"
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
                                {{ cat.category_name }}
                            </td>

                            <td
                                class="px-4 py-2 text-left text-md font-medium text-gray-600"
                            >
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                    :class="{
                                        'bg-green-200 text-green-700':
                                            cat.status == 1,
                                        'bg-red-200 text-red-700':
                                            cat.status == 2,
                                    }"
                                >
                                    <span
                                        class="h-2 w-2 rounded-full mr-2"
                                        :class="{
                                            'bg-green-500': cat.status == 1,
                                            'bg-red-500': cat.status == 2,
                                        }"
                                    ></span>
                                    {{
                                        cat.status == 1 ? "Active" : "Deactive"
                                    }}
                                </span>
                            </td>

                            <td class="px-2 py-2 text-md text-gray-600">
                                <div
                                    class="flex items-center gap-3 justify-center"
                                >
                                    <button @click="editCategory(cat)">
                                        <PencilSquareIcon
                                            class="w-6 h-6 text-blue-500 cursor-pointer"
                                        />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Show button -->

            <!-- pagination link -->
            <div>
                <PaginationLinks :paginator="Categories" />
            </div>
        </div>
    </div>
</template>
