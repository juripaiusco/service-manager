<script setup lang="ts">
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import {__} from "../../ComponentsExt/Translations";

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>

                <div class="form-floating">
                    <TextInput
                        id="email"
                        type="email"
                        class="mt-1 block w-full form-control"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="name@example.com"
                    />
                    <InputLabel for="email" value="Email" class="!text-base !text-gray-600" />
                </div>

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">

                <div class="form-floating">
                    <TextInput
                        id="password"
                        type="password"
                        class="mt-1 block w-full form-control"
                        v-model="form.password"
                        required
                        autocomplete="current-password"
                        placeholder="password"
                    />
                    <InputLabel for="password" value="Password" class="!text-base !text-gray-600" />
                </div>

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="block mt-4">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('login.remberme') }}
                    </span>
                </label>
            </div>

            <div class="flex items-center justify-end mt-4">
                <Link
                    v-if="canResetPassword"
                    :href="route('password.request')"
                    class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800"
                >
                    {{ __('login.forgot_password') }}
                </Link>

                <PrimaryButton class="ml-4 btn btn-primary" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    {{ __('login.login') }}
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
