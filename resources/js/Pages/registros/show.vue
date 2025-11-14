<script setup>
import { Link, useForm } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

// ✅ Recibe los datos desde el controlador Laravel (Inertia::render)
const props = defineProps({
  registro: Object
})

// ✅ Formulario para eliminar (DELETE)
const form = useForm({})

const eliminar = () => {
  if (confirm('¿Eliminar este registro?')) {
    form.delete(route('registros.destroy', props.registro.id))
  }
}
</script>

<template>
  <AuthenticatedLayout class="relleno">
    <!-- ENCABEZADO -->
    <template #header>
      <div class="flex items-center justify-between">
        <h2 class="text-xl font-semibold tituloPag">
          Detalle del Registro #{{ props.registro.id }}
        </h2>
      </div>
    </template>

    <!-- CONTENIDO -->
    <div class="py-6">
      <div class="max-w-3xl mx-auto bg-white shadow rounded-lg p-6 border border-gray-100 text-gray-800 space-y-3">
        <div class="flex items-center gap-2">
          <span class="font-semibold text-gray-700">ID:</span>
          <span>{{ props.registro.id }}</span>
        </div>

        <div class="flex flex-wrap gap-2">
          <span class="font-semibold text-gray-700">Chip:</span>
          <span>
            {{ props.registro.chip?.numero ?? '—' }}
            <span v-if="props.registro.chip?.compania">
              ({{ props.registro.chip.compania.nombre }})
            </span>
          </span>
        </div>

        <div class="flex items-center gap-2">
          <span class="font-semibold text-gray-700">Estado:</span>
          <span>{{ props.registro.estado?.nombre ?? '—' }}</span>
        </div>

        <div class="flex items-center gap-2">
          <span class="font-semibold text-gray-700">Usuario:</span>
          <span>{{ props.registro.usuario?.nombre ?? '—' }}</span>
        </div>

        <div class="flex items-center gap-2">
          <span class="font-semibold text-gray-700">Conteo de cambios:</span>
          <span>{{ props.registro.conteo_cambios }}</span>
        </div>

        <div class="flex items-center gap-2">
          <span class="font-semibold text-gray-700">Fecha de revisión:</span>
          <span>
            {{
              props.registro.fecha_revision
                ? new Date(props.registro.fecha_revision).toLocaleDateString('es-PE')
                : '—'
            }}
          </span>
        </div>

        <div class="flex items-center gap-2">
          <span class="font-semibold text-gray-700">Creado:</span>
          <span>{{ new Date(props.registro.created_at).toLocaleString('es-PE') }}</span>
        </div>

        <div class="flex items-center gap-2">
          <span class="font-semibold text-gray-700">Actualizado:</span>
          <span>{{ new Date(props.registro.updated_at).toLocaleString('es-PE') }}</span>
        </div>
      </div>

      <!-- BOTONES -->
      <div class="flex justify-end gap-3 max-w-3xl mx-auto mt-6">
        <Link :href="route('registros.index')"
          class="bg-gray-500 hover:bg-gray-600 text-white px-5 py-2 rounded shadow transition">
        Volver
        </Link>

        <Link :href="route('registros.edit', props.registro.id)"
          class="bg-yellow-600 hover:bg-yellow-700 text-white px-5 py-2 rounded shadow transition">
        Editar
        </Link>

        <button @click="eliminar" type="button"
          class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded shadow transition"
          :disabled="form.processing">
          Eliminar
        </button>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
