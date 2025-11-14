<template>
  <AuthenticatedLayout class="relleno">
    <!-- HEADER -->
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold tituloPag">Registrar Nuevo Registro</h2>
      </div>
    </template>

    <!-- CONTENIDO -->
    <div class="py-6">
      <div class="max-w-3xl mx-auto bg-white shadow rounded-lg p-6 border border-gray-100">
        <!-- Errores globales -->
        <div
          v-if="Object.keys(form.errors).length"
          class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"
        >
          <ul class="list-disc pl-5">
            <li v-for="(msg, key) in form.errors" :key="key">{{ msg }}</li>
          </ul>
        </div>

        <!-- FORMULARIO -->
        <form @submit.prevent="submit" class="space-y-5">
          <!-- CHIP -->
          <div>
            <label for="chip_id" class="block text-gray-700 font-semibold mb-1">Chip</label>
            <select
              v-model="form.chip_id"
              id="chip_id"
              class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-400"
              required
            >
              <option value="">Seleccione un chip</option>
              <option
                v-for="chip in chips"
                :key="chip.id"
                :value="chip.id"
              >
                {{ chip.numero }} ({{ chip.compania?.nombre ?? 'Sin compañía' }})
              </option>
              <option v-if="chips.length === 0" disabled>⚠️ No hay chips disponibles</option>
            </select>
          </div>

          <!-- ESTADO -->
          <div>
            <label for="estado_id" class="block text-gray-700 font-semibold mb-1">Estado</label>
            <select
              v-model="form.estado_id"
              id="estado_id"
              class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-400"
              required
            >
              <option value="">Seleccione un estado</option>
              <option
                v-for="estado in estados"
                :key="estado.id"
                :value="estado.id"
              >
                {{ estado.nombre }}
              </option>
            </select>
          </div>

          <!-- USUARIO -->
          <div>
            <label for="usuario_id" class="block text-gray-700 font-semibold mb-1">Usuario</label>
            <select
              v-model="form.usuario_id"
              id="usuario_id"
              class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-400"
              required
            >
              <option value="">Seleccione un usuario</option>
              <option
                v-for="usuario in usuarios"
                :key="usuario.id"
                :value="usuario.id"
              >
                {{ usuario.nombre }}
              </option>
            </select>
          </div>

          <!-- CONTEO DE CAMBIOS -->
          <div>
            <label for="conteo_cambios" class="block text-gray-700 font-semibold mb-1">Conteo de Cambios</label>
            <input
              type="number"
              id="conteo_cambios"
              v-model="form.conteo_cambios"
              min="0"
              class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-400"
            />
          </div>

          <!-- FECHA DE REVISIÓN -->
          <div>
            <label for="fecha_revision" class="block text-gray-700 font-semibold mb-1">Fecha de Revisión</label>
            <input
              type="date"
              id="fecha_revision"
              v-model="form.fecha_revision"
              class="border border-gray-300 rounded-lg w-full py-2 px-3 focus:outline-none focus:ring-2 focus:ring-indigo-400"
            />
          </div>

          <!-- BOTONES -->
          <div class="flex justify-end gap-3 pt-4">
            <Link
              :href="route('registros.index')"
              class="bg-gray-500 hover:bg-gray-600 text-white px-5 py-2 rounded shadow transition"
            >
              Cancelar
            </Link>
            <button
              type="submit"
              class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded shadow transition"
              :disabled="form.processing"
            >
              Guardar Registro
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link, useForm, usePage } from '@inertiajs/vue3'

// ✅ Props desde backend
const { props } = usePage()
const chips = props.chips || []
const estados = props.estados || []
const usuarios = props.usuarios || []

// ✅ Formulario reactivo
const form = useForm({
  chip_id: '',
  estado_id: '',
  usuario_id: '',
  conteo_cambios: 0,
  fecha_revision: ''
})

// ✅ Enviar formulario
const submit = () => {
  form.post(route('registros.store'), {
    onSuccess: () => form.reset(),
  })
}
</script>
