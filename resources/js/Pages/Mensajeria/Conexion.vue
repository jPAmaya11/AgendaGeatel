<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Conexión WAHA</h2>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <!-- Botones principales -->
        <div class="flex gap-2 mb-4">
          <button @click="openModal = true"
                  class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            Crear Nueva Conexión
          </button>
          <button @click="fetchConexiones"
                  class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
            Refrescar desde BD
          </button>
        </div>

        <!-- Tabla de Conexiones -->
        <div class="overflow-x-auto bg-white shadow rounded">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">ID</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">Nombre</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">Host</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">Estado</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">Admin</th>
                <th class="px-6 py-3 text-center text-sm font-medium text-gray-500">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">

              <template v-for="conexion in conexiones" :key="conexion.id">
                <!-- Fila principal -->
                <tr>
                  <td class="px-6 py-4 text-sm text-gray-900">{{ conexion.id }}</td>
                  <td class="px-6 py-4 text-sm text-gray-900">{{ conexion.nombre }}</td>
                  <td class="px-6 py-4 text-sm text-gray-900">{{ conexion.host }}</td>
                  <td class="px-6 py-4 text-sm text-gray-900 capitalize">
                    <span :class="{
                      'text-green-600 font-semibold': conexion.estado === 'activo',
                      'text-red-600 font-semibold': conexion.estado === 'error'
                    }">{{ conexion.estado }}</span>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-900">{{ conexion.admin?.name || '-' }}</td>
                  <td class="px-6 py-4 text-center text-sm flex justify-center gap-2">
                    <button @click="eliminarConexion(conexion.id)"
                            class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700">
                      Eliminar
                    </button>
                    <button @click="toggleSesiones(conexion)"
                            class="px-2 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                      {{ conexion.showSesiones ? 'Ocultar' : 'Probar' }}
                    </button>
                  </td>
                </tr>

                <!-- Fila de sesiones -->
<tr v-if="conexion.showSesiones" class="bg-gray-50">
  <td colspan="6" class="px-6 py-4">
    <div v-if="conexion.sesiones?.length">
      <table class="min-w-full divide-y divide-gray-300">
        <thead>
          <tr class="bg-gray-100">
            <th class="px-3 py-1 text-left text-sm text-gray-600">Nombre</th>
            <th class="px-3 py-1 text-left text-sm text-gray-600">Teléfono</th>
            <th class="px-3 py-1 text-left text-sm text-gray-600">Estado</th>
            <th class="px-3 py-1 text-left text-sm text-gray-600">Enviados</th>
            <th class="px-3 py-1 text-left text-sm text-gray-600">Pendientes</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="s in conexion.sesiones" :key="s.nombre">
            <td class="px-3 py-1 text-sm text-gray-700">{{ s.nombre }}</td>
            <td class="px-3 py-1 text-sm text-gray-700">{{ s.telefono }}</td>
            <td class="px-3 py-1 text-sm text-gray-700 capitalize">{{ s.estado }}</td>
            <td class="px-3 py-1 text-sm text-gray-700">{{ s.enviados }}</td>
            <td class="px-3 py-1 text-sm text-gray-700">{{ s.pendientes }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-else class="text-gray-500">No hay sesiones disponibles.</div>
  </td>
</tr>

              </template>

              <tr v-if="conexiones.length === 0">
                <td colspan="6" class="px-6 py-4 text-center text-gray-500">No hay conexiones registradas.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Modal Crear Conexión -->
        <div v-if="openModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50">
          <div class="bg-white p-6 rounded shadow w-full max-w-md">
            <h3 class="text-lg font-semibold mb-4">Nueva Conexión</h3>

            <div class="mb-2">
              <label class="block text-sm font-medium text-gray-700">Nombre</label>
              <input v-model="form.nombre" type="text" class="mt-1 w-full border rounded px-2 py-1" />
            </div>

            <div class="mb-2">
              <label class="block text-sm font-medium text-gray-700">Host (URL)</label>
              <input v-model="form.host" type="url" class="mt-1 w-full border rounded px-2 py-1" />
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">API Key</label>
              <input v-model="form.token_api" type="text" class="mt-1 w-full border rounded px-2 py-1" />
            </div>

            <div class="flex justify-end gap-2">
              <button @click="closeModal"
                      class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400">Cancelar</button>
              <button @click="crearConexion"
                      class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">Guardar</button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const conexiones = ref([])
const openModal = ref(false)
const form = ref({ nombre: '', host: '', token_api: '' })

async function fetchConexiones() {
  try {
    const res = await axios.get('/conexion/list')
    conexiones.value = res.data.data.map(c => ({
      ...c,
      showSesiones: false,
      sesiones: []
    }))
  } catch (err) {
    console.error('Error al cargar conexiones:', err)
  }
}

function closeModal() {
  openModal.value = false
  form.value = { nombre: '', host: '', token_api: '' }
}

async function crearConexion() {
  try {
    await axios.post('/conexion', form.value)
    await fetchConexiones()
    closeModal()
  } catch (err) {
    console.error(err)
    alert('Error al crear la conexión. Revisa los campos.')
  }
}

async function eliminarConexion(id) {
  if (!confirm('¿Eliminar esta conexión?')) return
  try {
    await axios.delete(`/conexion/${id}`)
    await fetchConexiones()
  } catch (err) {
    console.error(err)
    alert('Error al eliminar la conexión.')
  }
}

async function toggleSesiones(conexion) {
  if (conexion.showSesiones) {
    conexion.showSesiones = false
    return
  }

  try {
    const res = await axios.get(`/conexion/${conexion.id}/test`)
    if (res.data.ok) {
      // 🔹 Filtrar solo las sesiones con estado "working"
      conexion.sesiones = res.data.data.filter(s => 
        s.estado && s.estado.toLowerCase() === 'working'
      )
    } else {
      conexion.sesiones = []
      alert('Error al obtener sesiones: ' + res.data.message)
    }
  } catch (err) {
    conexion.sesiones = []
    console.error(err)
    alert('No se pudo conectar al servidor WAHA.')
  } finally {
    conexion.showSesiones = true
  }
}


// Refrescar estado de conexiones automáticamente
async function refrescarEstado() {
  for (let conexion of conexiones.value) {
    try {
      const res = await axios.get(`/conexion/${conexion.id}/sesiones`)
      if (res.data.estado) conexion.estado = res.data.estado
    } catch {
      conexion.estado = 'error'
    }
  }
}

onMounted(() => {
  fetchConexiones()
  setInterval(refrescarEstado, 10000)
})
</script>

<style scoped>
/* Estilos opcionales */
</style>
