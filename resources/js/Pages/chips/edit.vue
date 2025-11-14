<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useForm, Link } from '@inertiajs/vue3'


const props = defineProps({
    chip: Object,
    companias: Array,
    errors: Object
})

const form = useForm({
    numero: props.chip.numero,
    compania_id: props.chip.compania_id,
    tiene_senal: props.chip.tiene_senal,
    bloqueado: props.chip.bloqueado
})

const submit = () => {
    form.put(route('chips.update', props.chip.id))
}
</script>

<template>
    <AuthenticatedLayout class="relleno">
        <!-- ENCABEZADO -->
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold tituloPag">Editar Chip</h2>
            </div>
        </template>

        <!-- CONTENIDO -->
        <div class="py-6">
            <div class="max-w-3xl mx-auto bg-white shadow rounded-lg p-6 border border-gray-100">
                <!-- Errores -->
                <div v-if="Object.keys(errors).length"
                    class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                    <ul class="list-disc pl-5">
                        <li v-for="(msg, key) in errors" :key="key">{{ msg }}</li>
                    </ul>
                </div>

                <!-- FORMULARIO -->
                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Número -->
                    <div>
                        <label for="numero" class="block text-gray-700 font-semibold mb-1">Número</label>
                        <input v-model="form.numero" type="text" id="numero"
                            class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-400"
                            required />
                    </div>

                    <!-- Compañía -->
                    <div>
                        <label for="compania_id" class="block text-gray-700 font-semibold mb-1">Compañía</label>
                        <select v-model="form.compania_id" id="compania_id"
                            class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-400"
                            required>
                            <option value="">Seleccione una compañía</option>
                            <option v-for="c in companias" :key="c.id" :value="c.id">
                                {{ c.nombre }}
                            </option>
                        </select>
                    </div>

                    <!-- Tiene señal -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1">Tiene señal</label>
                        <select v-model="form.tiene_senal"
                            class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="1">Sí</option>
                            <option value="0">No</option>
                        </select>
                    </div>

                    <!-- Bloqueado -->
                    <div>
                        <label class="block text-gray-700 font-semibold mb-1">Bloqueado</label>
                        <select v-model="form.bloqueado"
                            class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                            <option value="0">No</option>
                            <option value="1">Sí</option>
                        </select>
                    </div>

                    <!-- BOTONES -->
                    <div class="flex justify-end gap-3 pt-4">
                        <!-- Cancelar -->
                        <Link :href="route('chips.index')"
                            class="flex items-center gap-2 px-5 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition">

                        Cancelar
                        </Link>

                        <!-- Actualizar -->
                        <button type="submit"
                            class="flex items-center gap-2 px-5 py-2 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold shadow hover:scale-105 transition-transform disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="form.processing">
                            Actualizar Chip
                        </button>

                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
