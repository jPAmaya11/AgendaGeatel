<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link } from '@inertiajs/vue3'

// Props que vienen del controlador
const props = defineProps({
  estado: Object
})
</script>

<template>
  <AuthenticatedLayout class="relleno">
    <!-- HEADER -->
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold tituloPag">
          Detalles del Estado
        </h2>
      </div>
    </template>

    <!-- CONTENIDO -->
    <div class="py-6">
      <div class="max-w-2xl mx-auto bg-white shadow rounded-lg p-6 border border-gray-100">
        <!-- DATOS DEL ESTADO -->
        <div class="space-y-3 text-gray-700">
          <div>
            <h3 class="font-semibold text-gray-800">ID:</h3>
            <p>{{ estado.id }}</p>
          </div>

          <div>
            <h3 class="font-semibold text-gray-800">Nombre:</h3>
            <p>{{ estado.nombre }}</p>
          </div>

          <div>
            <h3 class="font-semibold text-gray-800">Descripción:</h3>
            <p>{{ estado.descripcion || 'Sin descripción disponible.' }}</p>
          </div>

          <div>
            <h3 class="font-semibold text-gray-800">Creado el:</h3>
            <p>{{ new Date(estado.created_at).toLocaleString() }}</p>
          </div>

          <div>
            <h3 class="font-semibold text-gray-800">Última actualización:</h3>
            <p>{{ new Date(estado.updated_at).toLocaleString() }}</p>
          </div>
        </div>

        <!-- BOTONES -->
        <div class="flex justify-between mt-8">
          <!-- Volver -->
          <Link
            :href="route('estados.index')"
            class="flex items-center gap-2 bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded shadow transition"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Volver
          </Link>

          <!-- Editar -->
          <Link
            :href="route('estados.edit', estado.id)"
            class="flex items-center gap-2 bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded shadow transition"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l6-6 3.536 3.536-6 6H9v-3.536z" />
            </svg>
            Editar
          </Link>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
