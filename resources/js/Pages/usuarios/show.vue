<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link } from '@inertiajs/vue3'

// Recibe los datos enviados desde el controlador por Inertia
const props = defineProps({
  usuario: Object
})
</script>

<template>
  <AuthenticatedLayout class="relleno">
    <!-- ENCABEZADO -->
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold tituloPag">
          Detalles del Usuario
        </h2>
      </div>
    </template>

    <!-- CONTENIDO -->
    <div class="py-6">
      <div class="max-w-2xl mx-auto bg-white shadow rounded-lg p-6 border border-gray-100 text-gray-800">
        <p><strong>ID:</strong> {{ usuario.id }}</p>
        <p><strong>Nombre:</strong> {{ usuario.nombre }}</p>
        <p><strong>Email:</strong> {{ usuario.email }}</p>
        <p><strong>Registrado el:</strong> {{ new Date(usuario.created_at).toLocaleString() }}</p>
        <p><strong>Última actualización:</strong> {{ new Date(usuario.updated_at).toLocaleString() }}</p>
      </div>

      <!-- BOTONES -->
      <div class="flex justify-between max-w-2xl mx-auto mt-6">
        <!-- Volver -->
        <Link :href="route('usuarios.index')"
          class="flex items-center gap-2 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded transition">
        Volver
        </Link>

        <!-- Editar -->
        <Link :href="route('usuarios.edit', usuario.id)"
          class="flex items-center gap-2 bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded transition">
        Editar
        </Link>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
