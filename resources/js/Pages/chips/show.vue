<template>
    <AuthenticatedLayout class="relleno">
        <!-- ENCABEZADO -->
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold tituloPag">Detalles del Chip</h2>
            </div>
        </template>

        <!-- CONTENIDO -->
        <div class="py-6">
            <div class="max-w-3xl mx-auto bg-white shadow rounded-lg p-6 border border-gray-100">
                <!-- Número -->
                <div class="mb-4">
                    <h2 class="text-gray-700 font-semibold">Número:</h2>
                    <p class="text-gray-900">{{ chip.numero }}</p>
                </div>

                <!-- Compañía -->
                <div class="mb-4">
                    <h2 class="text-gray-700 font-semibold">Compañía:</h2>
                    <p class="text-gray-900">{{ chip.compania?.nombre ?? 'Sin compañía' }}</p>
                </div>

                <!-- Tiene señal -->
                <div class="mb-4">
                    <h2 class="text-gray-700 font-semibold">Tiene señal:</h2>
                    <p :class="chip.tiene_senal ? 'text-green-600' : 'text-red-600'">
                        {{ chip.tiene_senal ? 'Sí' : 'No' }}
                    </p>
                </div>

                <!-- Bloqueado -->
                <div class="mb-4">
                    <h2 class="text-gray-700 font-semibold">Bloqueado:</h2>
                    <p :class="chip.bloqueado ? 'text-red-600' : 'text-green-600'">
                        {{ chip.bloqueado ? 'Sí' : 'No' }}
                    </p>
                </div>

                <!-- Fecha de creación -->
                <div class="mb-4">
                    <h2 class="text-gray-700 font-semibold">Fecha de creación:</h2>
                    <p class="text-gray-900">{{ new Date(chip.created_at).toLocaleString() }}</p>
                </div>

                <!-- BOTONES -->
                <div class="flex justify-end gap-3 pt-4">
                    <Link :href="route('chips.index')"
                        class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition">
                    Volver
                    </Link>

                    <Link :href="route('chips.edit', chip.id)"
                        class="px-5 py-2 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold shadow hover:scale-105 transition-transform">
                    Editar
                    </Link>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link } from '@inertiajs/vue3'

// Props recibidos desde el controlador
const props = defineProps({
    chip: Object
})
</script>
