<script setup>
import { useForm, Link } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

// ✅ Recibe los datos desde el controlador Laravel (Inertia::render)
const props = defineProps({
  registro: Object,
  chips: Array,
  estados: Array,
  usuarios: Array,
  errors: Object
})

// ✅ Formulario con datos iniciales
const form = useForm({
  chip_id: props.registro.chip_id,
  estado_id: props.registro.estado_id,
  usuario_id: props.registro.usuario_id,
  conteo_cambios: props.registro.conteo_cambios,
  fecha_revision: props.registro.fecha_revision
})

// ✅ Envío del formulario con PUT
const submit = () => {
  form.put(route('registros.update', props.registro.id))
}
</script>

<template>
  <AuthenticatedLayout class="relleno">
    <!-- ENCABEZADO -->
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold tituloPag">
          Editar Registro #{{ props.registro.id }}
        </h2>
      </div>
    </template>

    <!-- CONTENIDO -->
    <div class="py-6">
      <div class="max-w-3xl mx-auto bg-white shadow rounded-lg p-6 border border-gray-100 text-gray-800">
        <!-- ERRORES -->
        <div v-if="Object.keys(props.errors).length"
          class="bg-red-100 text-red-700 p-3 rounded mb-5 border border-red-200">
          <ul class="list-disc pl-5 text-sm">
            <li v-for="(mensaje, campo) in props.errors" :key="campo">{{ mensaje }}</li>
          </ul>
        </div>

        <!-- FORMULARIO -->
        <form @submit.prevent="submit" class="space-y-4">
          <!-- CHIP -->
          <div>
            <label class="block font-medium mb-1 text-gray-700">Chip</label>
            <select v-model="form.chip_id"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-indigo-200" required>
              <option value="">Seleccione un chip</option>
              <option v-for="chip in props.chips" :key="chip.id" :value="chip.id">
                {{ chip.numero }}
                <span v-if="chip.compania">({{ chip.compania.nombre }})</span>
              </option>
            </select>
          </div>

          <!-- ESTADO -->
          <div>
            <label class="block font-medium mb-1 text-gray-700">Estado</label>
            <select v-model="form.estado_id"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-indigo-200" required>
              <option value="">Seleccione un estado</option>
              <option v-for="estado in props.estados" :key="estado.id" :value="estado.id">
                {{ estado.nombre }}
              </option>
            </select>
          </div>

          <!-- USUARIO -->
          <div>
            <label class="block font-medium mb-1 text-gray-700">Usuario</label>
            <select v-model="form.usuario_id"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-indigo-200" required>
              <option value="">Seleccione un usuario</option>
              <option v-for="usuario in props.usuarios" :key="usuario.id" :value="usuario.id">
                {{ usuario.nombre }}
              </option>
            </select>
          </div>

          <!-- CONTEO CAMBIOS -->
          <div>
            <label class="block font-medium mb-1 text-gray-700">Conteo de Cambios</label>
            <input type="number" v-model="form.conteo_cambios" min="0"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-indigo-200" required />
          </div>

          <!-- FECHA REVISIÓN -->
          <div>
            <label class="block font-medium mb-1 text-gray-700">Fecha de Revisión</label>
            <input type="date" v-model="form.fecha_revision"
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring focus:ring-indigo-200" required />
          </div>

          <!-- BOTONES -->
          <div class="flex justify-end gap-3 pt-4 border-t border-gray-200 mt-6">
            <Link :href="route('registros.index')"
              class="bg-gray-500 hover:bg-gray-600 text-white px-5 py-2 rounded-lg shadow transition">
              Volver
            </Link>

            <button type="submit"
              class="bg-yellow-600 hover:bg-yellow-700 text-white px-5 py-2 rounded-lg shadow transition"
              :disabled="form.processing">
              Actualizar
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
