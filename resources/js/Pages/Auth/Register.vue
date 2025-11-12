<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const isAdminRegistration = window.location.pathname.includes('/admin/register');

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    referral_code: isAdminRegistration ? '' : new URLSearchParams(window.location.search).get('ref') || '',
    is_admin: isAdminRegistration,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="name" value="Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

                        <!-- Referral Code Field (only shown for non-admin registration) -->
            <div v-if="!isAdminRegistration" class="mt-4">
                <InputLabel for="referral_code" value="Referral Code (Optional)" />
                <TextInput
                    id="referral_code"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.referral_code"
                    autocomplete="referral-code"
                />
                <InputError class="mt-2" :message="form.errors.referral_code" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel
                    for="password_confirmation"
                    value="Confirm Password"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="flex items-center justify-between mt-4">
                <Link
                    :href="isAdminRegistration ? route('admin.login') : route('login')"
                    class="underline text-sm text-gray-600 hover:text-gray-900"
                >
                    {{ isAdminRegistration ? 'Back to admin login' : 'Already registered?' }}
                </Link>

                <div class="flex space-x-4">
                    <Link
                        v-if="!isAdminRegistration"
                        :href="route('admin.register')"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                    >
                        Register as Admin
                    </Link>
                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        {{ isAdminRegistration ? 'Register Admin' : 'Register' }}
                    </PrimaryButton>
                </div>
            </div>
        </form>
    </GuestLayout>
</template>
