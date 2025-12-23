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
    <div class="container mx-auto px-6 py-8 max-w-6xl">

      <!-- CABECERA -->
      <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-3">
          <div class="bg-gray-800 text-white p-2 rounded">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
              stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 7V3m8 4V3M3 11h18M5 21h14a2 2 0 002-2V7H3v12a2 2 0 002 2z" />
            </svg>
          </div>
          <h1 class="text-2xl font-bold tituloPag">
            Agenda de Eventos
          </h1>
        </div>

        <button @click="openCreateForm"
          class="bgPrincipal text-white px-5 py-2 rounded text-sm shadow hover:opacity-90 transition flex items-center gap-2">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Nuevo evento
        </button>
      </div>

      <!-- FLASH -->
      <div v-if="flashSuccess" class="bg-green-100 text-green-700 p-3 rounded mb-6">
        {{ flashSuccess }}
      </div>

      <!-- GOOGLE CALENDAR -->
      <div class="mb-8">
        <a :href="route('google.redirect')"
          class="inline-flex items-center gap-2 px-5 py-2 rounded bg-sky-500 text-white text-sm shadow hover:bg-sky-600 transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3M3 11h18" />
          </svg>
          Conectar con Google Calendar
        </a>
      </div>

      <!-- FORMULARIO -->
      <div v-if="showForm" class="mb-10 bg-white rounded shadow border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-5 pb-3 border-b">
          <h2 class="text-lg font-semibold">
            {{ formMode === 'create' ? 'Crear evento' : 'Editar evento' }}
          </h2>
          <button @click="closeForm" class="text-gray-500 hover:text-gray-700 text-sm">
            ✕
          </button>
        </div>

        <form @submit.prevent="submitForm" class="space-y-5 text-sm">

          <!-- TITULO -->
          <div>
            <label class="block font-semibold mb-1">Título *</label>
            <input v-model="form.title" type="text" required
              class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-400" />
          </div>

          <!-- DESCRIPCION -->
          <div>
            <label class="block font-semibold mb-1">Descripción</label>
            <textarea v-model="form.description" rows="3"
              class="w-full border rounded px-3 py-2 focus:ring-2 focus:ring-indigo-400"></textarea>
          </div>

          <!-- TIPO / ESTADO -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block font-semibold mb-1">Tipo *</label>
              <select v-model="form.type" class="w-full border rounded px-3 py-2 bg-white">
                <option value="meeting">Reunión</option>
                <option value="task">Pendiente</option>
                <option value="personal">Personal</option>
                <option value="other">Otro</option>
              </select>
            </div>

            <div>
              <label class="block font-semibold mb-1">Estado *</label>
              <select v-model="form.status" class="w-full border rounded px-3 py-2 bg-white">
                <option value="pending">Pendiente</option>
                <option value="done">Completado</option>
                <option value="cancelled">Cancelado</option>
              </select>
            </div>
          </div>

          <!-- LUGAR -->
          <div>
            <label class="block font-semibold mb-1">Lugar</label>
            <input v-model="form.location" type="text" placeholder="Ej: Aula 301, Google Meet..."
              class="w-full border rounded px-3 py-2" />
          </div>

          <!-- FECHAS -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block font-semibold mb-1">Inicio *</label>
              <input v-model="form.start_at" type="datetime-local" required class="w-full border rounded px-3 py-2" />
            </div>
            <div>
              <label class="block font-semibold mb-1">Fin</label>
              <input v-model="form.end_at" type="datetime-local" class="w-full border rounded px-3 py-2" />
            </div>
          </div>

          <!-- RECORDATORIO -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block font-semibold mb-1">Tipo de recordatorio</label>
              <select v-model="form.reminder_type" class="w-full border rounded px-3 py-2 bg-white">
                <option value="exact">Fecha exacta</option>
                <option value="before">Antes del evento</option>
                <option value="recurrent">Recurrente</option>
              </select>
            </div>

            <div>
              <label class="block font-semibold mb-1">Canal</label>
              <select v-model="form.reminder_channel" class="w-full border rounded px-3 py-2 bg-white">
                <option value="whatsapp">WhatsApp</option>
                <option value="email">Correo</option>
                <option value="both">Ambos</option>
              </select>
            </div>
          </div>

          <!-- EXACT -->
          <div v-if="form.reminder_type === 'exact'">
            <label class="block font-semibold mb-1">Fecha del recordatorio</label>
            <input v-model="form.reminder_at" type="datetime-local" class="w-full border rounded px-3 py-2" />
          </div>

          <!-- BEFORE -->
          <div v-else-if="form.reminder_type === 'before'" class="grid grid-cols-2 gap-4">
            <div>
              <label class="block font-semibold mb-1">Cantidad</label>
              <input v-model.number="form.reminder_interval" type="number" min="1"
                class="w-full border rounded px-3 py-2" />
            </div>
            <div>
              <label class="block font-semibold mb-1">Unidad</label>
              <select v-model="form.reminder_unit" class="w-full border rounded px-3 py-2 bg-white">
                <option value="minutes">Minutos</option>
                <option value="hours">Horas</option>
              </select>
            </div>
          </div>

          <!-- RECURRENT -->
          <div v-else-if="form.reminder_type === 'recurrent'" class="space-y-4">
            <div>
              <label class="block font-semibold mb-1">Inicio</label>
              <input v-model="form.reminder_start_at" type="datetime-local" class="w-full border rounded px-3 py-2" />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <input v-model.number="form.reminder_interval" type="number" min="1" class="border rounded px-3 py-2" />
              <select v-model="form.reminder_unit" class="border rounded px-3 py-2 bg-white">
                <option value="minutes">Minutos</option>
                <option value="hours">Horas</option>
              </select>
            </div>
          </div>

          <!-- BOTONES -->
          <div class="flex justify-end gap-3 pt-4 border-t">
            <button type="button" @click="closeForm" class="px-5 py-2 border rounded text-gray-700">
              Cancelar
            </button>
            <button type="submit" class="bgPrincipal text-white px-6 py-2 rounded shadow hover:opacity-90">
              {{ formMode === 'create' ? 'Guardar' : 'Actualizar' }}
            </button>
          </div>
        </form>
      </div>

      <!-- LISTA -->
      <div class="space-y-4">
        <div v-if="eventsByDate.length === 0" class="text-center text-gray-500 py-10">
          No tienes eventos registrados aún.
        </div>

        <div v-for="group in eventsByDate" :key="group.date" class="bg-white rounded shadow border overflow-hidden">
          <div class="bg-gray-100 px-4 py-2 font-semibold text-sm">
            {{ group.date }}
          </div>

          <div class="divide-y">
            <div v-for="event in group.events" :key="event.id" class="px-4 py-3 flex justify-between hover:bg-gray-50">
              <div>
                <h3 class="font-semibold">{{ event.title }}</h3>
                <div class="text-xs text-gray-500">
                  {{ formatDateTime(event.start_at) }}
                  <span v-if="event.end_at"> → {{ formatDateTime(event.end_at) }}</span>
                </div>
                <div class="text-xs text-gray-400">
                  {{ event.type }} · {{ event.status }}
                </div>
              </div>

              <div class="flex flex-col gap-1">
                <button @click="openEditForm(event)" class="text-xs px-3 py-1 rounded bg-yellow-100 text-yellow-800">
                  Editar
                </button>
                <button @click="destroyEvent(event.id)" class="text-xs px-3 py-1 rounded bg-red-100 text-red-700">
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
