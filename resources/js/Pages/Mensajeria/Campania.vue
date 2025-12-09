<template>
  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Gestión de Campañas</h2>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <!-- Botones superiores -->
        <div class="flex gap-2 mb-4">
          <button @click="openModalCrear = true" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            Crear Nueva Campaña
          </button>
        </div>

        <!-- Tabla -->
        <div class="overflow-x-auto bg-white shadow rounded">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left">ID</th>
                <th class="px-6 py-3 text-left">Nombre</th>
                <th class="px-6 py-3 text-left">Destinatarios</th>
                <th class="px-6 py-3 text-left">Estado</th>
                <th class="px-6 py-3 text-center">Acciones</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
              <tr v-for="c in campanias" :key="c.id">
                <td class="px-6 py-4 text-sm">{{ c.id }}</td>
                <td class="px-6 py-4 text-sm">{{ c.nombre }}</td>
                <td class="px-6 py-4 text-sm">{{ c.total_destinatarios }}</td>
                <td class="px-6 py-4 text-sm capitalize">{{ c.estado }}</td>

                <td class="px-6 py-4 text-center flex justify-center gap-2">
                  <!-- Iniciar solo si pendiente -->
                  <button v-if="c.estado === 'pendiente'" @click="iniciarCampaniaModal(c)"
                    class="px-2 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600">
                    Iniciar
                  </button>

                  <!-- Enviar solo si activa -->
                  <button v-if="c.estado === 'activa'" @click="procesarCampania(c)"
                    class="px-2 py-1 bg-green-600 text-white rounded hover:bg-green-700">
                    Enviar
                  </button>

                  <button @click="eliminarCampania(c.id)"
                    class="px-2 py-1 bg-red-600 text-white rounded hover:bg-red-700">
                    Eliminar
                  </button>
                </td>
              </tr>

              <tr v-if="campanias.length === 0">
                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                  No hay campañas registradas.
                </td>
              </tr>
            </tbody>
          </table>
        </div>


        <!-- MODAL CREAR -->
        <div v-if="openModalCrear" class="modal">
          <div class="modal-box">

            <h3 class="text-lg font-semibold mb-4">Nueva Campaña</h3>

            <div class="mb-3">
              <label class="label">Nombre</label>
              <input v-model="form.nombre" type="text" class="input" />
            </div>

            <div class="mb-3">
              <label class="label">Descripción</label>
              <textarea v-model="form.descripcion" class="input"></textarea>
            </div>

            <!-- MENSAJES -->
            <div class="border p-3 rounded mb-3">
              <label class="label">Mensajes</label>

              <div v-for="(m, i) in form.mensajes" :key="i" class="flex gap-2 mb-2">
                <select v-model="m.tipo_mensaje" class="input w-32">
                  <option value="texto">Texto</option>
                  <option value="imagen">Imagen</option>
                  <option value="video">Video</option>
                  <option value="documento">Documento</option>
                </select>

                <input v-model="m.mensaje" placeholder="Mensaje o URL" class="input flex-1" />

                <input v-if="m.tipo_mensaje !== 'texto'" v-model="m.url_archivo" placeholder="URL archivo"
                  class="input flex-1" />

                <button @click="form.mensajes.splice(i, 1)" class="btn-red">X</button>
              </div>

              <button @click="addMensaje" class="btn-blue">+ Agregar Mensaje</button>
            </div>

            <!-- Archivo -->
            <div class="mb-3">
              <label class="label">Archivo Excel</label>
              <input type="file" @change="handleFile" />
            </div>

            <!-- MANUALES -->
            <div class="border p-3 rounded mb-3">
              <label class="label">Destinatarios Manuales</label>

              <div v-for="(d, i) in form.destinatarios_manual" :key="i" class="flex gap-2 mb-1">
                <input v-model="d.codigo_pais" placeholder="Código" class="input w-20" />
                <input v-model="d.numero" placeholder="Número" class="input w-40" />
                <input v-model="d.nombre" placeholder="Nombre" class="input flex-1" />
                <button @click="form.destinatarios_manual.splice(i, 1)" class="btn-red">X</button>
              </div>

              <button @click="addDestinatarioManual" class="btn-blue">
                + Agregar Destinatario
              </button>
            </div>

            <!-- RETRASO -->
            <div class="border p-3 rounded mb-3">
              <label class="label">Retraso entre mensajes</label>

              <div class="flex gap-2 items-center mb-2">
                <input type="checkbox" v-model="form.usar_retraso_mensaje" />
                Activar Retraso
              </div>

              <div v-if="form.usar_retraso_mensaje" class="flex gap-2">
                <input type="number" v-model.number="form.retraso_mensaje_min" placeholder="Min (seg)"
                  class="input w-24" />
                <input type="number" v-model.number="form.retraso_mensaje_max" placeholder="Max (seg)"
                  class="input w-24" />
              </div>
            </div>

            <div class="flex justify-end gap-2">
              <button class="btn-gray" @click="cerrarModalCrear">Cancelar</button>
              <button class="btn-blue" @click="crearCampania">Guardar</button>
            </div>
          </div>
        </div>

        <!-- MODAL INICIAR -->
        <div v-if="openModalIniciar" class="modal">
          <div class="modal-box max-w-md">
            <h3 class="text-lg font-semibold mb-4">
              Iniciar Campaña: {{ campaniaActual?.nombre }}
            </h3>

            <label class="label mb-2">Selecciona sesiones WAHA</label>

            <div v-for="s in sesiones" :key="s.nombre" class="flex items-center gap-2 mb-1">
              <input type="checkbox" v-model="formIniciar.sesiones"
                :value="{ nombre: s.nombre, numero_bot: s.telefono }" />
              <span>{{ s.nombre }} ({{ s.telefono }})</span>
            </div>

            <div class="flex justify-end gap-2 mt-4">
              <button class="btn-gray" @click="cerrarModalIniciar">Cancelar</button>
              <button class="btn-yellow" @click="iniciarCampania">Iniciar</button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import axios from "axios";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const campanias = ref([]);
const sesiones = ref([]);

const openModalCrear = ref(false);
const openModalIniciar = ref(false);
const campaniaActual = ref(null);
const archivoExcel = ref(null);

const form = ref({
  nombre: "",
  descripcion: "",
  mensajes: [{ mensaje: "", tipo_mensaje: "texto", url_archivo: "" }],
  destinatarios_manual: [],
  usar_retraso_mensaje: false,
  retraso_mensaje_min: null,
  retraso_mensaje_max: null,
});

const formIniciar = reactive({ sesiones: [] });

async function fetchCampanias() {
  const res = await axios.get("/campania/list");
  campanias.value = res.data.data.map(c => ({
    ...c,
    sesiones_usadas: c.sesiones_usadas || [],
  }));
}

function addMensaje() {
  form.value.mensajes.push({ mensaje: "", tipo_mensaje: "texto", url_archivo: "" });
}

function addDestinatarioManual() {
  form.value.destinatarios_manual.push({ codigo_pais: "", numero: "", nombre: "" });
}

function handleFile(e) {
  archivoExcel.value = e.target.files[0];
}

function cerrarModalCrear() {
  openModalCrear.value = false;
  form.value = {
    nombre: "",
    descripcion: "",
    mensajes: [{ mensaje: "", tipo_mensaje: "texto", url_archivo: "" }],
    destinatarios_manual: [],
    usar_retraso_mensaje: false,
    retraso_mensaje_min: null,
    retraso_mensaje_max: null,
  };
  archivoExcel.value = null;
}

async function crearCampania() {
  const fd = new FormData();

  fd.append("nombre", form.value.nombre);
  fd.append("descripcion", form.value.descripcion);

  form.value.mensajes.forEach((m, i) => {
    fd.append(`mensajes[${i}][mensaje]`, m.mensaje);
    fd.append(`mensajes[${i}][tipo_mensaje]`, m.tipo_mensaje);
    if (m.url_archivo) fd.append(`mensajes[${i}][url_archivo]`, m.url_archivo);
  });

  form.value.destinatarios_manual.forEach((d, i) => {
    fd.append(`destinatarios_manual[${i}][codigo_pais]`, d.codigo_pais);
    fd.append(`destinatarios_manual[${i}][numero]`, d.numero);
    fd.append(`destinatarios_manual[${i}][nombre]`, d.nombre);
  });

  if (archivoExcel.value) fd.append("archivo_destinatarios", archivoExcel.value);

  fd.append("usar_retraso_mensaje", form.value.usar_retraso_mensaje ? 1 : 0);
  if (form.value.usar_retraso_mensaje) {
    fd.append("retraso_mensaje_min", form.value.retraso_mensaje_min ?? "");
    fd.append("retraso_mensaje_max", form.value.retraso_mensaje_max ?? "");
  }

  await axios.post("/campania", fd);
  cerrarModalCrear();
  fetchCampanias();
}

async function iniciarCampaniaModal(c) {
  campaniaActual.value = c;
  formIniciar.sesiones = [];
  await fetchSesiones();
  openModalIniciar.value = true;
}

function cerrarModalIniciar() {
  openModalIniciar.value = false;
  formIniciar.sesiones = [];
}

async function fetchSesiones() {
  const res = await axios.get("/waha/sesiones/disponibles");
  sesiones.value = res.data.data;
}

async function iniciarCampania() {
  if (!formIniciar.sesiones.length) {
    alert("Selecciona al menos una sesión");
    return;
  }

  try {
    await axios.post(`/campania/${campaniaActual.value.id}/iniciar`, {
      sesiones: formIniciar.sesiones
    });

    cerrarModalIniciar();
    fetchCampanias();
  } catch (error) {
    console.error("Error al iniciar campaña:", error.response?.data || error);
    alert("Hubo un error al iniciar la campaña. Revisa la consola.");
  }
}

async function procesarCampania(c) {
  if (!confirm(`¿Enviar todos los mensajes de la campaña "${c.nombre}"?`)) return;

  try {
    await axios.post(`/campania/${c.id}/enviar`);
    alert("Campaña procesada correctamente");
    fetchCampanias();
  } catch (error) {
    console.error("Error al procesar campaña:", error.response?.data || error);
    alert("Hubo un error al enviar la campaña. Revisa la consola.");
  }
}

async function eliminarCampania(id) {
  if (!confirm("¿Seguro de eliminar?")) return;
  await axios.delete(`/campania/${id}`);
  fetchCampanias();
}

onMounted(fetchCampanias);
</script>

<style scoped>
.modal {
  position: fixed;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #0007;
  z-index: 50;
}

.modal-box {
  background: white;
  padding: 20px;
  border-radius: 8px;
  max-height: 90vh;
  overflow-y: auto;
}

.label {
  display: block;
  font-size: 14px;
  margin-bottom: 4px;
}

.input {
  border: 1px solid #ccc;
  padding: 6px;
  border-radius: 5px;
}

.btn-blue {
  background: #2563eb;
  color: white;
  padding: 6px 10px;
  border-radius: 4px;
}

.btn-yellow {
  background: #eab308;
  color: white;
  padding: 6px 10px;
  border-radius: 4px;
}

.btn-gray {
  background: #d1d5db;
  padding: 6px 10px;
  border-radius: 4px;
}

.btn-red {
  background: #dc2626;
  color: white;
  padding: 6px 10px;
  border-radius: 4px;
}
</style>
