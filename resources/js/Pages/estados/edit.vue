<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

// Props que vienen del controlador
const props = defineProps({
  estado: Object,
  errors: Object
})

// Campos reactivos inicializados con los valores del estado
const nombre = ref(props.estado.nombre)
const descripcion = ref(props.estado.descripcion || '')

// Función para enviar formulario de actualización
const submit = () => {
  router.put(route('estados.update', props.estado.id), {
    nombre: nombre.value,
    descripcion: descripcion.value
  })
}
</script>

<template>
  <AuthenticatedLayout class="relleno">
    <!-- ENCABEZADO -->
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold tituloPag">
          Editar Estado
        </h2>
      </div>
    </template>

    <!-- CONTENIDO -->
    <div class="py-6">
      <div class="max-w-2xl mx-auto bg-white shadow rounded-lg p-6 border border-gray-100">
        <!-- MENSAJES DE ERROR -->
        <div v-if="errors && Object.keys(errors).length"
          class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
          <ul class="list-disc pl-5">
            <li v-for="(msgs, field) in errors" :key="field">
              <span v-for="msg in msgs" :key="msg">{{ msg }}</span>
            </li>
          </ul>
        </div>

        <!-- FORMULARIO -->
        <form @submit.prevent="submit">
          <!-- Nombre -->
          <div class="mb-4">
            <label for="nombre" class="block text-gray-700 font-semibold mb-2">Nombre</label>
            <input id="nombre" v-model="nombre" type="text" required
              class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 transition" />
          </div>

          <!-- Descripción -->
          <div class="mb-6">
            <label for="descripcion" class="block text-gray-700 font-semibold mb-2">Descripción</label>
            <textarea id="descripcion" v-model="descripcion" rows="3" placeholder="Opcional"
              class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 transition"></textarea>
          </div>

          <!-- BOTONES -->
          <div class="flex justify-between items-center">
            <!-- Volver -->
            <a :href="route('estados.index')"
              class="flex items-center gap-2 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded transition">

              Volver
            </a>

            <!-- Actualizar -->
            <button type="submit"
              class="flex items-center gap-2 bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded transition disabled:opacity-50 disabled:cursor-not-allowed">

              Actualizar
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
