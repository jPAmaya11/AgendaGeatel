<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useForm, Link } from '@inertiajs/vue3'


// Props que vienen del controlador con Inertia
const props = defineProps({
  compania: Object,
  errors: Object
})

// Formulario con datos iniciales
const form = useForm({
  nombre: props.compania?.nombre || '',
  descripcion: props.compania?.descripcion || ''
})

// Función para actualizar
const submit = () => {
  form.put(route('companias.update', props.compania.id))
}
</script>

<template>
  <AuthenticatedLayout class="relleno">
    <!-- HEADER -->
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold tituloPag">
          Editar Compañía
        </h2>
      </div>
    </template>

    <!-- CONTENIDO -->
    <div class="py-6">
      <div class="max-w-2xl mx-auto bg-white shadow rounded-lg p-6 border border-gray-100">
        <!-- Errores -->
        <div v-if="Object.keys(props.errors || {}).length"
          class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
          <ul class="list-disc pl-5">
            <li v-for="(error, key) in props.errors" :key="key">{{ error[0] }}</li>
          </ul>
        </div>

        <!-- FORMULARIO -->
        <form @submit.prevent="submit">
          <!-- Nombre -->
          <div class="mb-4">
            <label for="nombre" class="block text-gray-700 font-semibold mb-2">
              Nombre de la Compañía
            </label>
            <input id="nombre" v-model="form.nombre" type="text" required
              class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 transition" />
          </div>

          <!-- Descripción -->
          <div class="mb-6">
            <label for="descripcion" class="block text-gray-700 font-semibold mb-2">
              Descripción
            </label>
            <textarea id="descripcion" v-model="form.descripcion" rows="3"
              class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 transition"></textarea>
          </div>

          <!-- BOTONES -->
          <div class="flex justify-between items-center">
            <!-- Volver -->
            <Link :href="route('companias.index')"
              class="flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 font-semibold shadow transition-transform hover:scale-105">
            Volver
            </Link>

            <!-- Actualizar -->
            <button type="submit"
              class="flex items-center gap-2 px-5 py-2 rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold shadow hover:scale-105 transition-transform disabled:opacity-50 disabled:cursor-not-allowed"
              :disabled="form.processing">
              Actualizar
            </button>
          </div>

        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
