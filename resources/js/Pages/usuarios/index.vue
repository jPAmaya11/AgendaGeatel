<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { router } from '@inertiajs/vue3'

// Props desde el backend
const props = defineProps({
  usuarios: Array,
  success: String
})

// Función para eliminar usuario sin recargar
const destroy = (id) => {
  if (confirm('¿Eliminar este usuario?')) {
    router.delete(route('usuarios.destroy', id))
  }
}
</script>

<template>
  <AuthenticatedLayout>
    <div class="container mx-auto p-6">
      <h1 class="text-2xl font-bold mb-6 tituloPag">Lista de Usuarios</h1>

      <!-- Botón para crear usuario -->
      <div class="mb-4">
        <a 
          :href="route('usuarios.create')" 
          class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded shadow transition"
        >
          + Agregar
        </a>
      </div>

      <!-- Mensaje de éxito -->
      <div v-if="success" class="bg-green-100 text-green-700 p-3 rounded mb-4">
        {{ success }}
      </div>

      <!-- Tabla de usuarios -->
      <div class="overflow-x-auto">
        <table class="min-w-full bg-white rounded shadow text-black">
          <thead class="bgPrincipal text-white text-left">
            <tr>
              <th class="px-4 py-2 border-b">ID</th>
              <th class="px-4 py-2 border-b">Nombre</th>
              <th class="px-4 py-2 border-b">Email</th>
              <th class="px-4 py-2 border-b text-center">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="usuario in usuarios" :key="usuario.id" class="hover:bg-gray-50">
              <td class="px-4 py-2 border-b">{{ usuario.id }}</td>
              <td class="px-4 py-2 border-b">{{ usuario.nombre }}</td>
              <td class="px-4 py-2 border-b">{{ usuario.email }}</td>
              <td class="px-4 py-2 border-b flex justify-center gap-2">

                <!-- Ver -->
                <a :href="route('usuarios.show', usuario.id)"
                   class="p-1.5 bg-blue-500 text-white shadow-md transition transform hover:scale-110 rounded"
                   title="Ver">
                  <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 svgIcon" viewBox="0 0 576 512">
                    <path d="M572.52 241.4C518.79 135.5 407.81 64 288 64S57.21 135.5 3.48
                        241.4a48.26 48.26 0 0 0 0 29.2C57.21 376.5 168.19
                        448 288 448s230.79-71.5 284.52-177.4a48.26 48.26
                        0 0 0 0-29.2zM288 400a144 144 0 1 1 144-144
                        143.93 143.93 0 0 1-144 144zm0-240a96
                        96 0 1 0 96 96 96.1 96.1 0 0 0-96-96z"/>
                  </svg>
                </a>

                <!-- Editar -->
                <a :href="route('usuarios.edit', usuario.id)"
                   class="p-1.5 bgIconEdit text-white shadow-md transition transform hover:scale-110 rounded"
                   title="Editar">
                  <svg class="w-3 h-3 svgIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                    <path d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9
                      30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6
                      21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5
                      21.9l-29.6 88.8c-2.9 8.6-.6
                      18.1 5.8 24.6s15.9 8.7
                      24.6 5.8l88.8-29.6c8.2-2.7
                      15.7-7.4 21.9-13.5L437.7
                      172.3 339.7 74.3 172.4
                      241.7z"/>
                  </svg>
                </a>

                <!-- Eliminar -->
                <button @click="destroy(usuario.id)"
                   class="p-1.5 bgIconTrash text-white shadow-md transition transform hover:scale-110 rounded"
                   title="Eliminar">
                  <svg class="w-3 h-3 svgIcon svgTrash" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                    <path d="M144 0L128 32 0 32 0 96l448 0 0-64L320
                      32 304 0 144 0zM416 128L32 128 56
                      512l336 0 24-384z"/>
                  </svg>
                </button>

              </td>
            </tr>

            <!-- Mensaje si no hay usuarios -->
            <tr v-if="usuarios.length === 0">
              <td colspan="4" class="px-4 py-2 text-center text-gray-500">
                No hay usuarios registrados.
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
