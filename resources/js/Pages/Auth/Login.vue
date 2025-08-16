<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref } from "vue";
import TextInput from "../Components/TextInput.vue";
import SelectInput from "../Components/SelectInput.vue";

// Disable default layout (since this is login)
defineOptions({ layout: null });

const props = defineProps({
    branches: Object,
});

// Convert branches to <option> values
const branchOptions = props.branches.map((b) => ({
    value: b.id,
    label: b.branch_name,
}));

// Form state
const form = useForm({
    branch_id: null,
    acct_name: null,
    password: null,
    remember: null,
});

// Submit login form
const submit = () => {
    form.post("/login", {
        onSuccess: () => {
            form.reset("password");
        },
    });
};
</script>

<template>
    <Head title=" | login" />
    <section class="min-h-screen flex items-center justify-center bg-blue-100">
        <div
            class="flex flex-col items-center justify-center py-2 mx-auto lg:py-0"
        >
            <div
                class="flex items-center mb-6 text-4xl font-bold text-gray-700"
            >
                A&A Merchandiser
            </div>
            <div
                class="w-full bg-white rounded-lg shadow md:mt-0 sm:max-w-md xl:p-0"
            >
                <div class="p-6 space-y-4 md:space-y-6 sm:p-8">
                    <h1
                        class="text-xl font-bold leading-tight tracking-tight text-gray-700 md:text-2xl"
                    >
                        Sign in to your account
                    </h1>

                    <div class="flex items-center justify-center">
                        <form
                            @submit.prevent="submit"
                            class="max-w-md w-[300px] flex flex-col mt-2"
                        >
                            <TextInput
                                name="Account Name"
                                v-model="form.acct_name"
                                :message="form.errors.acct_name"
                            />
                            <SelectInput
                                name="Branch"
                                v-model="form.branch_id"
                                :options="branchOptions"
                                :message="form.errors.branch_id"
                            />
                            <TextInput
                                name="Password"
                                v-model="form.password"
                                :message="form.errors.password"
                                type="password"
                            />
                            <div class="flex items-center mb-2">
                                <input
                                    id="remember"
                                    type="checkbox"
                                    v-model="form.remember"
                                    class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500"
                                />
                                <label
                                    for="remember"
                                    class="ml-2 text-sm text-gray-700"
                                >
                                    Remember me
                                </label>
                            </div>

                            <button
                                :class="[
                                    'mt-2 text-white font-medium rounded-lg text-sm px-5 py-2.5 focus:outline-none',
                                    form.processing
                                        ? 'bg-gray-400 cursor-not-allowed'
                                        : 'bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300',
                                ]"
                                :disabled="form.processing"
                            >
                                Login
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
