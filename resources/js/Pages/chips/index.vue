<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link, router } from '@inertiajs/vue3'

// Props que vienen del controlador con Inertia
defineProps({
  chips: Array,
  success: String,
})

// Eliminar chip
const destroy = (id) => {
  if (confirm('¿Eliminar este chip?')) {
    router.delete(route('chips.destroy', id))
  }
}
</script>

<template>
  <AuthenticatedLayout>
    <template #default>
      <div class="container mx-auto px-4 py-8">
        <!-- 🟢 Título principal -->
        <h1 class="text-2xl font-bold mb-6 tituloPag">
          Listado de Chips
        </h1>

        <!-- ✅ Mensaje de éxito -->
        <div v-if="success" class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mb-4">
          {{ success }}
        </div>

        <!-- ➕ Botón para crear nuevo chip -->
        <div class="mb-4">
          <Link :href="route('chips.create')"
            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded shadow transition">
          + Agregar
          </Link>
        </div>

        <!-- 📋 Tabla de chips -->
        <div class="bg-white shadow rounded-lg overflow-hidden">
          <table class="w-full text-left border-collapse">
            <thead class="text-white text-xs bgPrincipal">
              <tr class="uppercase text-sm">
                <th class="py-3 px-4 border-b text-left">ID</th>
                <th class="py-3 px-4 border-b text-left">Número</th>
                <th class="py-3 px-4 border-b text-left">Compañía</th>
                <th class="py-3 px-4 border-b text-left">Tiene Señal</th>
                <th class="py-3 px-4 border-b text-left">Bloqueado</th>
                <th class="py-3 px-4 border-b text-center">Acciones</th>
              </tr>
            </thead>


            <tbody>
              <tr v-for="chip in chips" :key="chip.id" class="border-b hover:bg-gray-50 transition">
                <td class="py-3 px-4">{{ chip.id }}</td>
                <td class="py-3 px-4">{{ chip.numero }}</td>
                <td class="py-3 px-4">{{ chip.compania?.nombre ?? 'Sin compañía' }}</td>

                <td class="py-3 px-4">
                  <span :class="chip.tiene_senal ? 'text-green-600 font-semibold' : 'text-red-600 font-semibold'">
                    {{ chip.tiene_senal ? 'Sí' : 'No' }}
                  </span>
                </td>

                <td class="py-3 px-4">
                  <span :class="chip.bloqueado ? 'text-red-600 font-semibold' : 'text-green-600 font-semibold'">
                    {{ chip.bloqueado ? 'Sí' : 'No' }}
                  </span>
                </td>

                <!-- 🔧 Acciones -->
                <td class="py-3 px-4 text-center">
                  <div class="flex justify-center gap-2 botonesTablaReportes">
                    <!-- 👁️ Ver -->
                    <Link :href="route('chips.show', chip.id)"
                      class="flex items-center justify-center p-1.5 bg-blue-500 text-white shadow-md transition transform hover:scale-110"
                      title="Ver">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3 svgIcon" viewBox="0 0 576 512">
                      <path d="M572.52 241.4C518.79 135.5 407.81 64 288 64S57.21 
                          135.5 3.48 241.4a48.26 48.26 0 0 0 0 29.2C57.21 
                          376.5 168.19 448 288 448s230.79-71.5 
                          284.52-177.4a48.26 48.26 0 0 0 0-29.2zM288 
                          400a144 144 0 1 1 144-144 143.93 143.93 
                          0 0 1-144 144zm0-240a96 96 0 1 0 96 
                          96 96.1 96.1 0 0 0-96-96z" />
                    </svg>
                    </Link>

                    <!-- ✏️ Editar -->
                    <Link :href="route('chips.edit', chip.id)"
                      class="flex items-center justify-center p-1.5 bgIconEdit text-white shadow-md transition transform hover:scale-110"
                      title="Editar">
                    <svg class="w-3 h-3 svgIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                      <path d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 
                          0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 
                          21.9-57.3 0-79.2L471.6 21.7zm-299.2 
                          220c-6.1 6.1-10.8 13.6-13.5 
                          21.9l-29.6 88.8c-2.9 8.6-.6 
                          18.1 5.8 24.6s15.9 8.7 
                          24.6 5.8l88.8-29.6c8.2-2.7 
                          15.7-7.4 21.9-13.5L437.7 
                          172.3 339.7 74.3 172.4 241.7z" />
                    </svg>
                    </Link>

                    <!-- 🗑️ Eliminar -->
                    <button @click="destroy(chip.id)"
                      class="flex items-center justify-center p-1.5 bgIconTrash text-white shadow-md transition transform hover:scale-110"
                      title="Eliminar">
                      <svg class="w-3 h-3 svgIcon svgTrash" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                        <path d="M144 0L128 32 0 32 0 
                          96l448 0 0-64L320 32 304 0 
                          144 0zM416 128L32 128 56 
                          512l336 0 24-384z" />
                      </svg>
                    </button>
                  </div>
                </td>
              </tr>

              <!-- 🕳️ Sin resultados -->
              <tr v-if="chips.length === 0" class="text-center text-gray-500">
                <td colspan="6" class="py-4">No hay chips registrados.</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </template>
  </AuthenticatedLayout>
</template>
