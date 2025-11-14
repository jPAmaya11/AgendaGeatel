<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useForm } from '@inertiajs/vue3'

// Props del controlador
const props = defineProps({
  usuario: Object,
  errors: Object
})

// Inicializamos el formulario con useForm
const form = useForm({
  nombre: props.usuario.nombre,
  email: props.usuario.email,
  password: ''
})

// Función para enviar formulario
const submit = () => {
  form.put(route('usuarios.update', props.usuario.id))
}
</script>

<template>
  <AuthenticatedLayout class="relleno">
    <!-- ENCABEZADO -->
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold tituloPag">Editar Usuario</h2>
      </div>
    </template>

    <!-- CONTENIDO -->
    <div class="py-6">
      <div class="max-w-2xl mx-auto bg-white shadow rounded-lg p-6 border border-gray-100">

        <!-- MENSAJES DE ERROR -->
        <div v-if="Object.keys(errors).length"
          class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
          <ul class="list-disc pl-5">
            <template v-for="(msgs, field) in errors" :key="field">
              <li v-for="msg in msgs" :key="msg">{{ msg }}</li>
            </template>
          </ul>
        </div>

        <!-- FORMULARIO -->
        <form @submit.prevent="submit" class="space-y-4">

          <!-- Nombre -->
          <div>
            <label for="nombre" class="block text-gray-700 font-semibold mb-2">Nombre</label>
            <input id="nombre" v-model="form.nombre" type="text" required
              class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 transition" />
          </div>

          <!-- Email -->
          <div>
            <label for="email" class="block text-gray-700 font-semibold mb-2">Email</label>
            <input id="email" v-model="form.email" type="email" required
              class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 transition" />
          </div>

          <!-- Contraseña -->
          <div>
            <label for="password" class="block text-gray-700 font-semibold mb-2">Nueva Contraseña (opcional)</label>
            <input id="password" v-model="form.password" type="password"
              class="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 transition" />
            <p class="text-sm text-gray-500 mt-1">
              Deja este campo vacío si no deseas cambiar la contraseña.
            </p>
          </div>

          <!-- BOTONES -->
          <div class="flex justify-between items-center mt-4">
            <!-- Volver -->
            <a :href="route('usuarios.index')"
              class="flex items-center gap-2 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded transition">

              Volver
            </a>

            <!-- Actualizar -->
            <button type="submit" :disabled="form.processing"
              class="flex items-center gap-2 bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded transition disabled:opacity-50 disabled:cursor-not-allowed">

              Actualizar
            </button>
          </div>

        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>