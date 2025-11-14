<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import PrimaryButton from "@/Components/PrimaryButton.vue";
import TextInput from "@/Components/TextInput.vue";
import { useForm } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";

const passwordInput = ref(null);
const currentPasswordInput = ref(null);
const successMessage = ref(false);
const form = useForm({
    current_password: "",
    password: "",
    password_confirmation: "",
});

const updatePassword = () => {
    successMessage.value = false;

    form.put(route("profile.password.update"), {
        preserveScroll: true,
        onSuccess: () => {
            successMessage.value = true;
            form.reset();
        },
        onError: (errors) => {
            console.log("❌ Errores desde backend:", errors);
            console.log("❌ Errores en form.errors:", form.errors);
        },

        onFinish: () => {
            console.log("✅ Petición finalizada");
        },
    });
};

onMounted(() => {
    successMessage.value = false;
});
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">
                Actualizar contraseña
            </h2>
            <p class="mt-1 text-sm text-gray-600">
                Aquí podrás cambiar tu contraseña de acceso.
            </p>
        </header>

        <form @submit.prevent="updatePassword" class="mt-6 space-y-6">
            <div>
                <InputLabel for="current_password" value="Contraseña actual" />
                <TextInput
                    id="current_password"
                    ref="currentPasswordInput"
                    v-model="form.current_password"
                    type="password"
                    class="mt-1 block w-full inputs"
                    autocomplete="current-password"
                    required
                />
                <InputError
                    :message="form.errors.current_password"
                    class="mt-2"
                />
            </div>

            <div>
                <InputLabel for="password" value="Nueva contraseña" />
                <TextInput
                    id="password"
                    ref="passwordInput"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full inputs"
                    autocomplete="new-password"
                    required
                />
                <InputError :message="form.errors.password" class="mt-2" />
            </div>

            <div>
                <InputLabel
                    for="password_confirmation"
                    value="Confirmar contraseña"
                />
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full inputs"
                    autocomplete="new-password"
                    required
                />
                <InputError
                    :message="form.errors.password_confirmation"
                    class="mt-2"
                />
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing" class="btn">
                    Guardar
                </PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="successMessage" class="text-sm text-green-600">
                        Contraseña actualizada correctamente.
                    </p>
                </Transition>
            </div>
        </form>
    </section>
</template>
