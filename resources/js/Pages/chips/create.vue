<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { useForm, Link } from '@inertiajs/vue3'

const props = defineProps({
  companias: Array,
  errors: Object
})

const form = useForm({
  numero: '',
  compania_id: '',
  tiene_senal: '1',
  bloqueado: '0'
})

const submit = () => {
  form.post(route('chips.store'))
}
</script>

<template>
  <AuthenticatedLayout>
    <div class="container mx-auto px-4 py-8">
      <h1 class="text-2xl font-bold mb-6 text-gray-800">Registrar Nuevo Chip</h1>

      <!-- Errores -->
      <div
        v-if="Object.keys(errors).length"
        class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"
      >
        <ul class="list-disc pl-5">
          <li v-for="(msg, key) in errors" :key="key">{{ msg }}</li>
        </ul>
      </div>

      <div class="bg-white shadow rounded-lg p-6">
        <form @submit.prevent="submit">
          <!-- Número -->
          <div class="mb-4">
            <label for="numero" class="block text-gray-700 font-semibold mb-2">Número</label>
            <input
              v-model="form.numero"
              type="text"
              id="numero"
              class="border border-gray-300 rounded w-full py-2 px-3 focus:outline-none focus:ring focus:ring-blue-200"
              required
            />
          </div>

          <!-- Compañía -->
          <div class="mb-4">
            <label for="compania_id" class="block text-gray-700 font-semibold mb-2">Compañía</label>
            <select
              v-model="form.compania_id"
              id="compania_id"
              class="border border-gray-300 rounded w-full py-2 px-3 focus:outline-none focus:ring focus:ring-blue-200"
              required
            >
              <option value="">Seleccione una compañía</option>
              <option v-for="c in companias" :key="c.id" :value="c.id">
                {{ c.nombre }}
              </option>
            </select>
          </div>

          <!-- Tiene señal -->
          <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-2">Tiene señal</label>
            <select
              v-model="form.tiene_senal"
              class="border border-gray-300 rounded w-full py-2 px-3 focus:outline-none focus:ring focus:ring-blue-200"
            >
              <option value="1">Sí</option>
              <option value="0">No</option>
            </select>
          </div>

          <!-- Bloqueado -->
          <div class="mb-4">
            <label class="block text-gray-700 font-semibold mb-2">Bloqueado</label>
            <select
              v-model="form.bloqueado"
              class="border border-gray-300 rounded w-full py-2 px-3 focus:outline-none focus:ring focus:ring-blue-200"
            >
              <option value="0">No</option>
              <option value="1">Sí</option>
            </select>
          </div>

          <!-- Botones -->
          <div class="flex justify-end gap-3 pt-4">
            <Link
              :href="route('chips.index')"
              class="bg-gray-500 hover:bg-gray-600 text-white px-5 py-2 rounded shadow transition"
            >
              Cancelar
            </Link>

            <button
              type="submit"
              class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded shadow transition disabled:opacity-50 disabled:cursor-not-allowed"
              :disabled="form.processing"
            >
              Guardar Chip
            </button>
          </div>
        </form>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
