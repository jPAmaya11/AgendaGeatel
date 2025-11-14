<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

// Props que vienen del controlador Inertia (opcional si hay errores o datos previos)
const props = defineProps({
  errors: Object
})

// Campos del formulario
const nombre = ref('')
const email = ref('')
const password = ref('')

// Función para enviar formulario con Inertia
const submit = () => {
  router.post(route('usuarios.store'), {
    nombre: nombre.value,
    email: email.value,
    password: password.value,
  })
}
</script>

<template>
  <AuthenticatedLayout>
    <div class="container mx-auto p-6 max-w-lg">
      <h1 class="text-2xl font-bold mb-4"> Crear Usuario</h1>

      <div class="bg-white p-6 rounded shadow">
        <div class="mb-4">
          <label for="nombre" class="block font-medium mb-1">Nombre</label>
          <input 
            id="nombre" 
            v-model="nombre" 
            type="text" 
            class="w-full border rounded p-2" 
            required
          >
          <p v-if="errors?.nombre" class="text-red-600 text-sm">{{ errors.nombre }}</p>
        </div>

        <div class="mb-4">
          <label for="email" class="block font-medium mb-1">Email</label>
          <input 
            id="email" 
            v-model="email" 
            type="email" 
            class="w-full border rounded p-2" 
            required
          >
          <p v-if="errors?.email" class="text-red-600 text-sm">{{ errors.email }}</p>
        </div>

        <div class="mb-4">
          <label for="password" class="block font-medium mb-1">Contraseña</label>
          <input 
            id="password" 
            v-model="password" 
            type="password" 
            class="w-full border rounded p-2" 
            required
          >
          <p v-if="errors?.password" class="text-red-600 text-sm">{{ errors.password }}</p>
        </div>

        <div class="flex justify-between">
          <a 
            :href="route('usuarios.index')" 
            class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600"
          >
            Cancelar
          </a>
          <button 
            @click.prevent="submit" 
            class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700"
          >
            Guardar
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
