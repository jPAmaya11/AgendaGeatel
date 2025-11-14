<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">📊 Reporte de Mensajería</h2>
    </template>

    <div class="py-6 max-w-7xl mx-auto sm:px-6 lg:px-8">
      <!-- FILTROS -->
      <div class="bg-white p-4 rounded-lg shadow mb-6 flex flex-wrap gap-4 items-center">
        <div>
          <label class="block text-sm font-medium text-gray-700">Campaña</label>
          <input v-model="filtros.campania_id" type="text" placeholder="ID campaña"
                 class="border rounded px-2 py-1 text-sm" @change="filtrar" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Estado</label>
          <select v-model="filtros.estado" class="border rounded px-2 py-1 text-sm" @change="filtrar">
            <option value="">Todos</option>
            <option value="pendiente">Pendiente</option>
            <option value="enviado">Enviado</option>
            <option value="entregado">Entregado</option>
            <option value="leido">Leído</option>
            <option value="error">Error</option>
          </select>
        </div>
      </div>

      <!-- TABS -->
      <div class="flex gap-4 border-b mb-4">
        <button
          v-for="tab in ['envios', 'respuestas']"
          :key="tab"
          @click="activeTab = tab"
          class="pb-2 border-b-2 font-semibold"
          :class="activeTab === tab ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
        >
          {{ tab === 'envios' ? '📬 Envíos' : '💬 Respuestas' }}
        </button>
      </div>

      <!-- TAB ENVÍOS -->
      <div v-if="activeTab === 'envios'">
        <div class="overflow-x-auto bg-white rounded-lg shadow">
          <table class="min-w-full text-sm text-left">
            <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
              <tr>
                <th class="px-4 py-2">Campaña</th>
                <th class="px-4 py-2">Cliente</th>
                <th class="px-4 py-2">Mensaje</th>
                <th class="px-4 py-2">Estado</th>
                <th class="px-4 py-2">Fecha envío</th>
                <th class="px-4 py-2">Sesión</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="e in props.envios.data" :key="e.id" class="border-b hover:bg-gray-50">
                <td class="px-4 py-2">{{ e.campania }}</td>
                <td class="px-4 py-2">{{ e.numero_cliente }}</td>
                <td class="px-4 py-2">{{ e.mensaje_enviado }}</td>
                <td class="px-4 py-2">
                  <span :class="estadoColor(e.estado)" class="px-2 py-1 rounded text-xs font-medium">
                    {{ e.estado }}
                  </span>
                </td>
                <td class="px-4 py-2">{{ e.fecha_envio || '-' }}</td>
                <td class="px-4 py-2">{{ e.waha_sesion_nombre || '-' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- TAB RESPUESTAS -->
      <div v-else>
        <div class="overflow-x-auto bg-white rounded-lg shadow">
          <table class="min-w-full text-sm text-left">
            <thead class="bg-gray-100 text-gray-700 uppercase text-xs">
              <tr>
                <th class="px-4 py-2">Campaña</th>
                <th class="px-4 py-2">Cliente</th>
                <th class="px-4 py-2">Mensaje</th>
                <th class="px-4 py-2">Formato</th>
                <th class="px-4 py-2">Fecha</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="r in props.respuestas.data" :key="r.id" class="border-b hover:bg-gray-50">
                <td class="px-4 py-2">{{ r.campania }}</td>
                <td class="px-4 py-2">{{ r.numero_cliente }}</td>
                <td class="px-4 py-2">{{ r.mensaje }}</td>
                <td class="px-4 py-2">{{ r.formato_mensaje }}</td>
                <td class="px-4 py-2">{{ r.fecha_registro }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const props = defineProps({
  envios: Object,
  respuestas: Object,
  filtros: Object
})

const activeTab = ref('envios')
const filtros = ref({ ...props.filtros })

function filtrar() {
  router.get(route('reporte.view'), filtros.value, { preserveScroll: true })
}

function estadoColor(estado) {
  switch (estado) {
    case 'enviado': return 'bg-green-100 text-green-700'
    case 'entregado': return 'bg-blue-100 text-blue-700'
    case 'leido': return 'bg-purple-100 text-purple-700'
    case 'error': return 'bg-red-100 text-red-700'
    default: return 'bg-gray-100 text-gray-700'
  }
}
</script>
