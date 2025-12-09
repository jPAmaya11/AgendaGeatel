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

          <button @click="openAsignacionModal = true"
                  class="px-4 py-2 bg-purple-600 text-white rounded hover:bg-purple-700">
            Asignar Usuario / Filtro
          </button>
        </div>

        <!-- TABLA DE CONEXIONES -->
        <div class="overflow-x-auto bg-white shadow rounded mb-6">
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
              <!-- Solo conexiones activas -->
              <template v-for="conexion in conexiones" :key="conexion.id">

                <tr>
                  <td class="px-6 py-4">{{ conexion.id }}</td>
                  <td class="px-6 py-4">{{ conexion.nombre }}</td>
                  <td class="px-6 py-4">{{ conexion.host }}</td>

                  <td class="px-6 py-4 capitalize">
                    <span class="text-green-600 font-bold">{{ conexion.estado }}</span>
                  </td>

                  <td class="px-6 py-4">{{ conexion.admin?.name || '-' }}</td>

                  <td class="px-6 py-4 flex justify-center gap-2">
                    <button @click="eliminarConexion(conexion.id)"
                            class="px-2 py-1 bg-red-600 text-white rounded">
                      Eliminar
                    </button>

                    <button @click="toggleSesiones(conexion)"
                            class="px-2 py-1 bg-yellow-500 text-white rounded">
                      {{ conexion.showSesiones ? 'Ocultar' : 'Probar' }}
                    </button>
                  </td>
                </tr>

                <!-- DETALLE DE SESIONES SOLO WORKING -->
                <tr v-if="conexion.showSesiones" class="bg-gray-50">
                  <td colspan="6" class="px-6 py-4">
                    <div v-if="conexion.sesiones && conexion.sesiones.filter(s => s.estado === 'working').length">
                      <table class="min-w-full divide-y divide-gray-300 text-sm">
                        <thead class="bg-gray-100">
                          <tr>
                            <th class="px-3 py-1">Nombre</th>
                            <th class="px-3 py-1">Teléfono</th>
                            <th class="px-3 py-1">Estado</th>
                          </tr>
                        </thead>

                        <tbody>
                          <tr v-for="s in conexion.sesiones.filter(s => s.estado === 'working')" :key="s.nombre">
                            <td class="px-3 py-1">{{ s.nombre }}</td>
                            <td class="px-3 py-1">{{ s.telefono }}</td>
                            <td class="px-3 py-1 capitalize">{{ s.estado }}</td>
                          </tr>
                        </tbody>
                      </table>
                    </div>

                    <div v-else class="text-gray-500 text-sm">No hay sesiones activas.</div>
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>

        <!-- TABLA DE ASIGNACIONES -->
        <h3 class="text-lg font-semibold mb-2">Asignaciones de Usuario / Filtro</h3>

        <div class="overflow-x-auto bg-white shadow rounded">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-100">
              <tr>
                <th class="px-4 py-2 text-left text-sm">Usuario</th>
                <th class="px-4 py-2 text-left text-sm">Conexión</th>
                <th class="px-4 py-2 text-left text-sm">Filtro</th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="a in asignaciones" :key="a.id" class="border-b">
                <td class="px-4 py-2">{{ a.user.name }}</td>
                <td class="px-4 py-2">{{ a.waha_conexion.nombre }}</td>
                <td class="px-4 py-2">{{ a.filtro }}</td>
              </tr>

              <tr v-if="asignaciones.length == 0">
                <td colspan="3" class="px-4 py-2 text-center text-gray-500">
                  No hay asignaciones registradas
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- MODALES... se mantienen igual -->
        <!-- MODAL CREAR CONEXIÓN -->
        <div v-if="openModal"
             class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50">
          <div class="bg-white p-6 rounded shadow w-full max-w-md">

            <h3 class="text-lg font-semibold mb-4">Nueva Conexión</h3>

            <div class="mb-2">
              <label class="text-sm">Nombre</label>
              <input v-model="form.nombre" class="w-full border rounded px-2 py-1" />
            </div>

            <div class="mb-2">
              <label class="text-sm">Host</label>
              <input v-model="form.host" class="w-full border rounded px-2 py-1" />
            </div>

            <div class="mb-4">
              <label class="text-sm">API Key</label>
              <input v-model="form.token_api" class="w-full border rounded px-2 py-1" />
            </div>

            <div class="flex justify-end gap-2">
              <button @click="closeModal" class="px-3 py-1 bg-gray-300 rounded">Cancelar</button>
              <button @click="crearConexion" class="px-3 py-1 bg-blue-600 text-white rounded">Guardar</button>
            </div>
          </div>
        </div>

        <!-- MODAL ASIGNAR USUARIO -->
        <div v-if="openAsignacionModal"
             class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50">
          <div class="bg-white p-6 rounded shadow w-full max-w-md">

            <h3 class="text-lg font-semibold mb-4">Asignar Usuario / Filtro</h3>

            <div class="mb-3">
              <label class="text-sm">Usuario</label>
              <select v-model="asignar.user_id" class="w-full border rounded px-2 py-1">
                <option disabled value="">Seleccione...</option>
                <option v-for="u in usuarios" :value="u.id">{{ u.name }}</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="text-sm">Conexión WAHA</label>
              <select v-model="asignar.waha_conexion_id" class="w-full border rounded px-2 py-1">
                <option disabled value="">Seleccione...</option>
                <option v-for="c in conexiones" :value="c.id">{{ c.nombre }}</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="text-sm">Filtro</label>
              <input v-model="asignar.filtro" class="w-full border rounded px-2 py-1" />
            </div>

            <div class="flex justify-end gap-2">
              <button @click="openAsignacionModal = false" class="px-3 py-1 bg-gray-300 rounded">Cancelar</button>
              <button @click="guardarAsignacion" class="px-3 py-1 bg-purple-600 text-white rounded">Guardar</button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const conexiones = ref([]);
const asignaciones = ref([]);
const usuarios = ref([]);

const openModal = ref(false);
const openAsignacionModal = ref(false);

const form = ref({ nombre: '', host: '', token_api: '' });
const asignar = ref({ user_id: '', waha_conexion_id: '', filtro: '' });

// CARGAR CONEXIONES
async function fetchConexiones() {
  const res = await axios.get('/conexion/list');
  conexiones.value = res.data.data.map(c => ({ ...c, showSesiones: false, sesiones: [] }));
}

// CARGAR ASIGNACIONES
async function fetchAsignaciones() {
  const allAsignaciones = [];
  for (const c of conexiones.value) {
    try {
      const res = await axios.get(`/conexion/${c.id}/asignaciones`);
      allAsignaciones.push(...res.data.data);
    } catch {}
  }
  asignaciones.value = allAsignaciones;
}

// CARGAR USUARIOS
async function fetchUsuarios() {
  const res = await axios.get('/usuarios/list');
  usuarios.value = res.data.data;
}

// CRUD CONEXIONES
function closeModal() {
  openModal.value = false;
  form.value = { nombre: '', host: '', token_api: '' };
}

async function crearConexion() {
  await axios.post('/conexion', form.value);
  await fetchConexiones();
  closeModal();
}

async function eliminarConexion(id) {
  if (!confirm('¿Eliminar esta conexión?')) return;
  await axios.delete(`/conexion/${id}`);
  await fetchConexiones();
}

// SESIONES SOLO WORKING
async function toggleSesiones(conexion) {
  if (conexion.showSesiones) {
    conexion.showSesiones = false;
    return;
  }
  const res = await axios.get(`/conexion/${conexion.id}/test`);
  conexion.sesiones = res.data.ok ? res.data.data.filter(s => s.estado === 'working') : [];
  conexion.showSesiones = true;
}

// ASIGNACIONES
async function guardarAsignacion() {
  if (!asignar.value.waha_conexion_id) {
    alert('Selecciona la conexión primero.');
    return;
  }
  await axios.post(`/conexion/${asignar.value.waha_conexion_id}/asignacion`, asignar.value);
  openAsignacionModal.value = false;
  asignar.value = { user_id: '', waha_conexion_id: '', filtro: '' };
  await fetchAsignaciones();
}

// AUTOLOAD
onMounted(async () => {
  await fetchConexiones();
  await fetchUsuarios();
  await fetchAsignaciones();
});
</script>
