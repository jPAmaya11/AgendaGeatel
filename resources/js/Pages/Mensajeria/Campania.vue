<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Campañas</h2>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <!-- Botones principales -->
        <div class="flex gap-2 mb-4">
          <button @click="openModalCrear = true"
                  class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            Crear Nueva Campaña
          </button>
          <button @click="fetchCampanias"
                  class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
            Refrescar
          </button>
        </div>

        <!-- Tabla de Campañas -->
        <div class="overflow-x-auto bg-white shadow rounded">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">ID</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">Nombre</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">Descripción</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">Destinatarios</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">Estado</th>
                <th class="px-6 py-3 text-left text-sm font-medium text-gray-500">Mensajes</th>
                <th class="px-6 py-3 text-center text-sm font-medium text-gray-500">Acciones</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">

              <template v-for="campania in campanias" :key="campania.id">
                <tr>
                  <td class="px-6 py-4 text-sm text-gray-900">{{ campania.id }}</td>
                  <td class="px-6 py-4 text-sm text-gray-900">{{ campania.nombre }}</td>
                  <td class="px-6 py-4 text-sm text-gray-900">{{ campania.descripcion || '-' }}</td>
                  <td class="px-6 py-4 text-sm text-gray-900">{{ campania.total_destinatarios }}</td>
                  <td class="px-6 py-4 text-sm text-gray-900 capitalize">{{ campania.estado }}</td>
                  <td class="px-6 py-4 text-sm text-gray-900">
                    <ul>
                      <li v-for="m in campania.mensajes" :key="m.mensaje">
                        {{ m.tipo_mensaje.toUpperCase() }}: {{ m.mensaje }}
                      </li>
                    </ul>
                  </td>
                  <td class="px-6 py-4 text-center text-sm flex justify-center gap-2">
                    <button @click="iniciarCampaniaModal(campania)"
                            class="px-2 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                      Iniciar
                    </button>
                    <button @click="eliminarCampania(campania.id)"
                            class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700">
                      Eliminar
                    </button>
                  </td>
                </tr>
              </template>

              <tr v-if="campanias.length === 0">
                <td colspan="7" class="px-6 py-4 text-center text-gray-500">No hay campañas registradas.</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Modal Crear Campaña -->
        <div v-if="openModalCrear" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50">
          <div class="bg-white p-6 rounded shadow w-full max-w-2xl overflow-y-auto max-h-[90vh]">
            <h3 class="text-lg font-semibold mb-4">Nueva Campaña</h3>

            <div class="mb-2">
              <label class="block text-sm font-medium text-gray-700">Nombre</label>
              <input v-model="formCampania.nombre" type="text" class="mt-1 w-full border rounded px-2 py-1" />
            </div>

            <div class="mb-2">
              <label class="block text-sm font-medium text-gray-700">Descripción</label>
              <textarea v-model="formCampania.descripcion" class="mt-1 w-full border rounded px-2 py-1"></textarea>
            </div>

            <!-- Mensajes -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">Mensajes</label>
              <div v-for="(m, index) in formCampania.mensajes" :key="index" class="flex gap-2 items-center mb-2">
                <select v-model="m.tipo_mensaje" class="border rounded px-2 py-1">
                  <option value="texto">Texto</option>
                  <option value="imagen">Imagen</option>
                  <option value="video">Video</option>
                  <option value="documento">Documento</option>
                </select>
                <input v-model="m.mensaje" type="text" placeholder="Mensaje o URL archivo" class="border rounded px-2 py-1 flex-1" />
                <button @click="formCampania.mensajes.splice(index,1)" class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700">Eliminar</button>
              </div>
              <button @click="formCampania.mensajes.push({mensaje:'', tipo_mensaje:'texto'})"
                      class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                Agregar Mensaje
              </button>
            </div>

            <!-- Archivo de destinatarios -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">Archivo Destinatarios</label>
              <input type="file" @change="handleFile" class="mt-1 w-full" />
            </div>

            <!-- Números manuales -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700">Números Manuales</label>
              <div v-for="(n, index) in formCampania.numeros_manual" :key="index" class="flex gap-2 mb-1">
                <input v-model="formCampania.numeros_manual[index]" type="text" placeholder="Número de teléfono" class="border rounded px-2 py-1 flex-1" />
                <button @click="formCampania.numeros_manual.splice(index,1)" class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700">Eliminar</button>
              </div>
              <button @click="formCampania.numeros_manual.push('')" class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                Agregar Número
              </button>
            </div>

            <!-- Retraso entre mensajes (opcional) -->
            <div class="mb-4 border-t pt-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Retraso entre mensajes (opcional)
              </label>
              <div class="flex items-center gap-2 mb-2">
                <input type="checkbox" v-model="formCampania.usar_retraso_mensaje" class="h-4 w-4" />
                <span>Activar retraso entre mensajes</span>
              </div>
              <div v-if="formCampania.usar_retraso_mensaje" class="flex gap-2">
                <div>
                  <label class="block text-sm text-gray-600">Mínimo (segundos)</label>
                  <input type="number" min="0" v-model.number="formCampania.retraso_mensaje_min"
                         class="border rounded px-2 py-1 w-24" placeholder="0" />
                </div>
                <div>
                  <label class="block text-sm text-gray-600">Máximo (segundos)</label>
                  <input type="number" min="0" v-model.number="formCampania.retraso_mensaje_max"
                         class="border rounded px-2 py-1 w-24" placeholder="0" />
                </div>
              </div>
            </div>

            <div class="flex justify-end gap-2">
              <button @click="closeModalCrear"
                      class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400">Cancelar</button>
              <button @click="crearCampania"
                      class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">Guardar</button>
            </div>
          </div>
        </div>

        <!-- Modal Iniciar Campaña -->
        <div v-if="openModalIniciar" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50">
          <div class="bg-white p-6 rounded shadow w-full max-w-md">
            <h3 class="text-lg font-semibold mb-4">Iniciar Campaña: {{ campañaActual.nombre }}</h3>

            <div class="mb-2">
              <label class="block text-sm font-medium text-gray-700">Selecciona Sesiones WAHA</label>
              <div v-for="s in sesionesDisponibles" :key="s.nombre" class="flex items-center gap-2 mb-1">
                <input type="checkbox" v-model="formIniciar.sesiones" :value="s.nombre" />
                <span>{{ s.nombre }} ({{ s.telefono }}) - {{ s.conexion_nombre }}</span>
              </div>
            </div>

            <div class="flex justify-end gap-2">
              <button @click="closeModalIniciar"
                      class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400">Cancelar</button>
              <button @click="iniciarCampania"
                      class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600">Iniciar</button>
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

const campanias = ref([])
const sesionesDisponibles = ref([])
const openModalCrear = ref(false)
const openModalIniciar = ref(false)
const campañaActual = ref(null)
const archivoDestinatarios = ref(null)

const formCampania = ref({
  nombre: '',
  descripcion: '',
  mensajes: [{ mensaje: '', tipo_mensaje: 'texto' }],
  numeros_manual: [],
  usar_retraso_mensaje: false,
  retraso_mensaje_min: null,
  retraso_mensaje_max: null
})

const formIniciar = ref({ sesiones: [] })

// 🔹 Fetch campañas
async function fetchCampanias() {
  try {
    const res = await axios.get('/campania/list')
    campanias.value = res.data.data
  } catch (err) {
    console.error(err)
  }
}

function handleFile(event) {
  archivoDestinatarios.value = event.target.files[0]
}

function closeModalCrear() {
  openModalCrear.value = false
  formCampania.value = {
    nombre: '',
    descripcion: '',
    mensajes: [{mensaje:'',tipo_mensaje:'texto'}],
    numeros_manual: [],
    usar_retraso_mensaje: false,
    retraso_mensaje_min: null,
    retraso_mensaje_max: null
  }
  archivoDestinatarios.value = null
}

async function crearCampania() {
  try {
    const formData = new FormData()

    // Datos básicos
    formData.append('nombre', formCampania.value.nombre)
    formData.append('descripcion', formCampania.value.descripcion)

    // Mensajes
    formCampania.value.mensajes.forEach((m, i) => {
      formData.append(`mensajes[${i}][mensaje]`, m.mensaje)
      formData.append(`mensajes[${i}][tipo_mensaje]`, m.tipo_mensaje)
      if (m.url_archivo) formData.append(`mensajes[${i}][url_archivo]`, m.url_archivo)
    })

    // Archivo destinatarios
    if (archivoDestinatarios.value) {
      formData.append('archivo_destinatarios', archivoDestinatarios.value)
    }

    // Números manuales
    formCampania.value.numeros_manual.forEach((n, i) => {
      if (n.trim()) formData.append(`numeros_manual[${i}]`, n.trim())
    })

    // Retraso entre mensajes (solo si está activado)
    formData.append('usar_retraso_mensaje', formCampania.value.usar_retraso_mensaje ? 1 : 0)
    if (formCampania.value.usar_retraso_mensaje) {
      if (formCampania.value.retraso_mensaje_min != null)
        formData.append('retraso_mensaje_min', formCampania.value.retraso_mensaje_min)
      if (formCampania.value.retraso_mensaje_max != null)
        formData.append('retraso_mensaje_max', formCampania.value.retraso_mensaje_max)
    }

    // Enviar request
    await axios.post('/campania', formData, { headers: { 'Content-Type':'multipart/form-data' } })

    // Limpiar formulario y recargar campañas
    closeModalCrear()
    fetchCampanias()
  } catch (err) {
    console.error(err)
    if (err.response && err.response.status === 422) {
      const errores = err.response.data.errors
      console.error('Errores de validación:', errores)
      alert('Error de validación. Revisa los campos obligatorios.')
    } else {
      alert('Error al crear la campaña')
    }
  }
}


function iniciarCampaniaModal(c) {
  campañaActual.value = c
  formIniciar.value.sesiones = []
  fetchSesionesDisponibles()
  openModalIniciar.value = true
}

function closeModalIniciar() {
  openModalIniciar.value = false
  campañaActual.value = null
  formIniciar.value = { sesiones: [] }
}

// 🔹 Fetch sesiones disponibles
async function fetchSesionesDisponibles() {
  try {
    const res = await axios.get('/waha/sesiones/disponibles')
    sesionesDisponibles.value = res.data.data
  } catch (err) {
    console.error(err)
    sesionesDisponibles.value = []
  }
}

// 🔹 Iniciar campaña
async function iniciarCampania() {
  if (formIniciar.value.sesiones.length === 0) {
    alert('Selecciona al menos una sesión.')
    return
  }
  try {
    await axios.post(`/campania/${campañaActual.value.id}/iniciar`, formIniciar.value)
    alert('Campaña iniciada correctamente')
    closeModalIniciar()
    fetchCampanias()
  } catch (err) {
    console.error(err)
    alert('Error al iniciar la campaña')
  }
}

async function eliminarCampania(id) {
  if (!confirm('¿Eliminar esta campaña?')) return
  try {
    await axios.delete(`/campania/${id}`)
    fetchCampanias()
  } catch (err) {
    console.error(err)
    alert('Error al eliminar la campaña')
  }
}

onMounted(() => {
  fetchCampanias()
})
</script>

<style scoped>
/* Opcional */
</style>
