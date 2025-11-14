<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

// Props que vienen del controlador con Inertia
const props = defineProps({
    compania: Object
})
</script>

<template>
    <AuthenticatedLayout class="relleno">
        <!-- ENCABEZADO -->
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold tituloPag">
                    Detalles de la Compañía
                </h2>
            </div>
        </template>

        <!-- CONTENIDO -->
        <div class="py-6">
            <div class="max-w-3xl mx-auto bg-white shadow rounded-lg p-6 border border-gray-100">
                <!-- NOMBRE -->
                <div class="mb-4 text-center">
                    <h1 class="text-2xl font-bold text-gray-800 mb-1">
                        {{ compania.nombre }}
                    </h1>
                    <p class="text-gray-600">
                        {{ compania.descripcion || 'Sin descripción disponible.' }}
                    </p>
                </div>

                <!-- CHIPS ASOCIADOS -->
                <div class="mt-8">
                    <h2 class="text-lg font-semibold text-gray-800 mb-4 text-center">
                        Chips Asociados
                    </h2>

                    <!-- Sin chips -->
                    <div v-if="compania.chips.length === 0" class="text-center">
                        <p class="text-gray-500">
                            No hay chips asociados a esta compañía.
                        </p>
                    </div>

                    <!-- Lista de chips -->
                    <div v-else class="overflow-hidden rounded-lg border border-gray-200">
                        <table class="min-w-full text-sm text-gray-700 text-center">
                            <thead class="bg-gray-100 text-gray-800 uppercase text-xs">
                                <tr>
                                    <th class="px-4 py-2 font-semibold">Número</th>
                                    <th class="px-4 py-2 font-semibold">Señal</th>
                                    <th class="px-4 py-2 font-semibold">Bloqueado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="chip in compania.chips" :key="chip.id"
                                    class="border-t hover:bg-gray-50 transition">
                                    <td class="px-4 py-2 font-medium">
                                        {{ chip.numero }}
                                    </td>
                                    <td class="px-4 py-2" :class="chip.tiene_senal ? 'text-green-600' : 'text-red-600'">
                                        {{ chip.tiene_senal ? 'Sí' : 'No' }}
                                    </td>
                                    <td class="px-4 py-2" :class="chip.bloqueado ? 'text-red-600' : 'text-green-600'">
                                        {{ chip.bloqueado ? 'Sí' : 'No' }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- BOTÓN VOLVER -->
                <div class="flex justify-center mt-8">
                    <a :href="route('companias.index')"
                        class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition">
                        Volver
                    </a>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
