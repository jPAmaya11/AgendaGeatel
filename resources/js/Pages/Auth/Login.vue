<script setup>
import Checkbox from "@/Components/Checkbox.vue";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        onError: (errors) => {
            console.log("Errores del backend:", errors); // <-- ¿se imprime algo?
        },
        onFinish: () => form.reset("password"),
    });
};
</script>
<template>
    <GuestLayout>
        <!-- <figure class="logo_login">
            <img src="https://control.financiera.geatel-telecom.com/assets/logo-9d776287.png" alt="Geatel Telecon">
        </figure> -->
        <div
            v-if="form.errors.email"
            class="flex items-start gap-2 rounded-md border border-red-300 bg-red-50 p-4 text-sm text-red-800"
        >
            <svg
                class="h-5 w-5 mt-0.5 flex-shrink-0 text-red-600"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 9v2m0 4h.01M4.93 4.93l14.14 14.14M12 2a10 10 0 1010 10A10 10 0 0012 2z"
                />
            </svg>
            <span>{{ form.errors.email }}</span>
        </div>
        <div class="tituloPrincipal">
            Panel Ejecutivo de Inteligencia de Negocios
        </div>

        <Head title="Login" />

        <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
            {{ status }}
        </div>
        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Usuarios" class="colorMorado" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full inputs"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />
            </div>

            <div class="mt-4">
                <InputLabel
                    for="password"
                    value="Contraseñas"
                    class="colorMorado"
                />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full inputs"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 block">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-gray-600">Recuérdame</span>
                </label>
            </div>

            <div class="mt-4 center boxBtnl">
                <!-- flex items-center justify-end -->
                <PrimaryButton
                    class="ms-4 btn btnFull"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Ingresar
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
