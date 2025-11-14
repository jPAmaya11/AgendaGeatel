<template>
  <AuthenticatedLayout class="relleno">
    <!-- HEADER -->
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold tituloPag">Registrar Nuevo Estado</h2>
      </div>
    </template>

    <!-- CONTENIDO -->
    <div class="py-6">
      <div class="max-w-3xl mx-auto bg-white shadow rounded-lg p-6 border border-gray-100">
        <!-- ERRORES -->
        <div
          v-if="errors && Object.keys(errors).length"
          class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"
        >
          <ul class="list-disc pl-5">
            <li v-for="(errorMessages, field) in errors" :key="field">
              <span v-for="msg in errorMessages" :key="msg">{{ msg }}</span>
            </li>
          </ul>
        </div>

        <!-- FORMULARIO -->
        <form @submit.prevent="submit" class="space-y-5">
          <!-- NOMBRE -->
          <div>
            <label for="nombre" class="block text-gray-700 font-semibold mb-1">Nombre</label>
            <input
              type="text"
              id="nombre"
              v-model="nombre"
              required
              class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-400"
            />
          </div>

          <!-- DESCRIPCIÓN -->
          <div>
            <label for="descripcion" class="block text-gray-700 font-semibold mb-1">Descripción</label>
            <textarea
              id="descripcion"
              v-model="descripcion"
              rows="3"
              placeholder="Opcional"
              class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-400"
            ></textarea>
          </div>

          <!-- BOTONES -->
          <div class="flex justify-end gap-3 pt-4">
            <a
              :href="route('estados.index')"
              class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300 transition"
            >
              Cancelar
            </a>
            <button
              type="submit"
              class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded shadow transition"
            >
              Guardar Estado
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

// ✅ Props desde el backend
const props = defineProps({
  errors: Object
})

// ✅ Campos del formulario
const nombre = ref('')
const descripcion = ref('')

// ✅ Enviar formulario
const submit = () => {
  router.post(route('estados.store'), {
    nombre: nombre.value,
    descripcion: descripcion.value
  })
}
</script>
