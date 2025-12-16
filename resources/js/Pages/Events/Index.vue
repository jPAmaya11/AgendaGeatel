<script setup>
import { ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'

const formatDateTime = (value) => {
  if (!value) return ''
  return new Date(value).toLocaleString('es-PE', {
    dateStyle: 'short',
    timeStyle: 'short',
  })
}

const toLocalInputValue = (value) => {
  if (!value) return ''

  if (!value.includes('T') && value.includes(' ')) {
    return value.replace(' ', 'T').slice(0, 16)
  }

  const date = new Date(value)
  if (Number.isNaN(date.getTime())) {
    return ''
  }

  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  const hours = String(date.getHours()).padStart(2, '0')
  const minutes = String(date.getMinutes()).padStart(2, '0')

  return `${year}-${month}-${day}T${hours}:${minutes}`
}

// Props desde el backend
const props = defineProps({
  events: {
    type: Array,
    default: () => [],
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
})

// Flash messages (success)
const page = usePage()
const flashSuccess = computed(() => page.props.flash?.success ?? '')

// Estado del formulario
const showForm = ref(false)
const formMode = ref('create') // create | edit
const editingId = ref(null)

const emptyForm = () => ({
  title: '',
  description: '',
  type: 'meeting',
  location: '',
  start_at: '',
  end_at: '',
  all_day: false,
  reminder_at: '',
  reminder_start_at: '',
  reminder_channel: 'whatsapp',
  reminder_type: 'exact',
  reminder_unit: 'minutes',
  status: 'pending',
})

const form = ref(emptyForm())

const openCreateForm = () => {
  formMode.value = 'create'
  editingId.value = null
  form.value = emptyForm()
  showForm.value = true
}

const openEditForm = (event) => {
  formMode.value = 'edit'
  editingId.value = event.id
  form.value = {
    title: event.title ?? '',
    description: event.description ?? '',
    type: event.type ?? 'meeting',
    location: event.location ?? '',
    start_at: toLocalInputValue(event.start_at),
    end_at: toLocalInputValue(event.end_at),
    all_day: !!event.all_day,
    reminder_at: toLocalInputValue(event.reminder_at),
    reminder_channel: event.reminder_channel ?? 'whatsapp',
    status: event.status ?? 'pending',
  }
  showForm.value = true
}

const closeForm = () => {
  showForm.value = false
}

// Agrupar eventos por fecha (agenda tipo calendario)
const eventsByDate = computed(() => {
  const map = {}
    ; (props.events || []).forEach((e) => {
      const dateKey = (e.start_at || '').slice(0, 10) // YYYY-MM-DD
      if (!dateKey) return
      if (!map[dateKey]) map[dateKey] = []
      map[dateKey].push(e)
    })

  const sortedDates = Object.keys(map).sort()
  return sortedDates.map((date) => ({
    date,
    events: map[date].sort((a, b) =>
      (a.start_at || '').localeCompare(b.start_at || '')
    ),
  }))
})

// Enviar formulario (crear/editar)
const submitForm = () => {
  const payload = { ...form.value }

  if (!payload.end_at) {
    delete payload.end_at
  }
  if (!payload.reminder_at) {
    delete payload.reminder_at
  }

  if (formMode.value === 'create') {
    router.post(route('events.store'), payload, {
      onSuccess: () => {
        closeForm()
      },
    })
  } else if (formMode.value === 'edit' && editingId.value) {
    router.put(route('events.update', editingId.value), payload, {
      onSuccess: () => {
        closeForm()
      },
    })
  }
}

// Eliminar evento
const destroyEvent = (id) => {
  if (!confirm('¿Eliminar este evento?')) return

  router.delete(route('events.destroy', id))
}
</script>


<template>
  <AuthenticatedLayout>
    <div class="w-full max-w-3xl mx-auto p-4 sm:p-6">
      <!-- Título -->
      <h1 class="text-2xl font-bold mb-4 tituloPag text-center">
        Agenda de eventos
      </h1>

      <!-- Mensaje de éxito -->
      <div v-if="flashSuccess" class="mb-4 rounded-lg bg-green-100 text-green-800 px-4 py-2 text-sm">
        {{ flashSuccess }}
      </div>

      <!-- Botón crear -->
      <div class="flex justify-end mb-4">
        <button type="button"
          class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold shadow hover:bg-blue-700 active:scale-95 transition"
          @click="openCreateForm">
          + Nuevo evento
        </button>
      </div>

      <!-- Botón para conectar con Calendaer -->
      <a :href="route('google.redirect')"
        class="inline-flex items-center px-3 py-2 rounded-md text-sm font-semibold bg-green-600 text-white hover:bg-green-700 mb-4">
        Conectar con Google Calendar
      </a>


      <!-- Formulario -->
      <div v-if="showForm" class="mb-6 rounded-2xl bg-white shadow p-4 sm:p-5 border border-gray-100">
        <div class="flex items-center justify-between mb-3">
          <h2 class="font-semibold text-lg">
            {{ formMode === 'create' ? 'Crear evento' : 'Editar evento' }}
          </h2>
          <button type="button" class="text-xs text-gray-500 hover:text-gray-700" @click="closeForm">
            Cerrar ✕
          </button>
        </div>

        <form @submit.prevent="submitForm" class="space-y-3 text-sm">
          <div>
            <label class="block font-semibold mb-1">Título *</label>
            <input v-model="form.title" type="text"
              class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200" required />
          </div>

          <div>
            <label class="block font-semibold mb-1">Descripción</label>
            <textarea v-model="form.description" rows="3"
              class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200"></textarea>
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold mb-1">Tipo *</label>
              <select v-model="form.type"
                class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200">
                <option value="meeting">Reunión</option>
                <option value="task">Pendiente</option>
                <option value="personal">Personal</option>
                <option value="other">Otro</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold mb-1">Estado *</label>
              <select v-model="form.status"
                class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200">
                <option value="pending">Pendiente</option>
                <option value="done">Completado</option>
                <option value="cancelled">Cancelado</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block font-semibold mb-1">Lugar</label>
            <input v-model="form.location" type="text"
              class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200"
              placeholder="Ej: Aula 301, Google Meet..." />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="block font-semibold mb-1">Inicio *</label>
              <input v-model="form.start_at" type="datetime-local"
                class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200" required />
            </div>
            <div>
              <label class="block font-semibold mb-1">Fin</label>
              <input v-model="form.end_at" type="datetime-local"
                class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200" />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">

            <!-- Recordatorio: Tipo -->
            <div>
              <label class="block font-semibold mb-1">Tipo de Recordatorio</label>
              <select v-model="form.reminder_type"
                class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200">
                <option value="exact">Fecha y hora exacta</option>
                <option value="before">Antes del evento</option>
                <option value="recurrent">Recordatorio recurrente</option>
              </select>
            </div>

            <!-- Exact -->
            <div v-if="form.reminder_type === 'exact'">
              <label class="block font-semibold mb-1 mt-2">Recordatorio en fecha/hora específica</label>
              <input v-model="form.reminder_at" type="datetime-local"
                class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200" />
            </div>

            <!-- Before -->
            <div v-else-if="form.reminder_type === 'before'" class="grid grid-cols-2 gap-3 mt-2">
              <div>
                <label class="block font-semibold mb-1">Recordar</label>
                <input v-model.number="form.reminder_interval" type="number" min="1"
                  class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200" />
              </div>
              <div>
                <label class="block font-semibold mb-1">Unidad</label>
                <select v-model="form.reminder_unit"
                  class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200">
                  <option value="minutes">Minutos antes</option>
                  <option value="hours">Horas antes</option>
                </select>
              </div>
            </div>

            <!-- Recurrent -->
            <div v-else-if="form.reminder_type === 'recurrent'" class="space-y-2 mt-2">
              <div>
                <label class="block font-semibold mb-1">Inicio de los recordatorios</label>
                <input v-model="form.reminder_start_at" type="datetime-local"
                  class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200" />
              </div>
              <div class="grid grid-cols-2 gap-3">
                <div>
                  <label class="block font-semibold mb-1">Cada</label>
                  <input v-model.number="form.reminder_interval" type="number" min="1"
                    class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200" />
                </div>
                <div>
                  <label class="block font-semibold mb-1">Unidad</label>
                  <select v-model="form.reminder_unit"
                    class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200">
                    <option value="minutes">Minutos</option>
                    <option value="hours">Horas</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Recordatorio: Tipo -->
            <div>
              <label class="block font-semibold mb-1">Tipo de Recordatorio</label>
              <select v-model="form.reminder_channel"
                class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring focus:ring-blue-200">
                <option value="whatsapp">WhatsApp</option>
                <option value="email">Correo (Gmail)</option>
                <option value="both">WhatsApp y Correo</option>
              </select>
            </div>
          </div>

          <div class="pt-2 flex gap-2 justify-end">
            <button type="button" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm"
              @click="closeForm">
              Cancelar
            </button>
            <button type="submit"
              class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold shadow hover:bg-blue-700 active:scale-95 transition">
              {{ formMode === 'create' ? 'Guardar' : 'Actualizar' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Lista tipo agenda (calendario) -->
      <div class="space-y-4">
        <div v-if="eventsByDate.length === 0" class="text-center text-gray-500 text-sm mt-8">
          No tienes eventos registrados aún.
        </div>

        <div v-for="group in eventsByDate" :key="group.date"
          class="rounded-2xl bg-white shadow border border-gray-100 overflow-hidden">
          <div class="bg-gray-100 px-4 py-2 text-xs font-semibold text-gray-700">
            {{ group.date }}
          </div>
          <div class="divide-y divide-gray-100">
            <div v-for="event in group.events" :key="event.id" class="px-4 py-3 flex items-start justify-between">
              <div class="flex-1">
                <div class="text-xs text-gray-500 mt-0.5">
                  <span v-if="event.start_at">
                    {{ formatDateTime(event.start_at) }}
                  </span>
                  <span v-if="event.end_at"> → {{ formatDateTime(event.end_at) }}</span>
                </div>
                <div v-if="event.location" class="text-xs text-gray-500 mt-0.5">
                  📍 {{ event.location }}
                </div>
                <div class="text-[11px] text-gray-400 mt-0.5">
                  Tipo: {{ event.type }} · Estado: {{ event.status }}
                </div>
              </div>
              <div class="flex flex-col gap-1 ml-2">
                <button type="button"
                  class="text-xs px-2 py-1 rounded bg-yellow-100 text-yellow-800 hover:bg-yellow-200"
                  @click="openEditForm(event)">
                  Editar
                </button>
                <button type="button" class="text-xs px-2 py-1 rounded bg-red-100 text-red-700 hover:bg-red-200"
                  @click="destroyEvent(event.id)">
                  Eliminar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
