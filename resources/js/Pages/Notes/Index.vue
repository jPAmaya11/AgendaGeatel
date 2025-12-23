<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { router } from '@inertiajs/vue3'
import { toRefs, ref, computed, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  notes: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
  success: { type: String, default: '' },
  aiMessage: { type: String, default: '' },
  selectedNoteIdFromServer: { type: [Number, String], default: null },
  authUser: { type: Object, default: () => ({}) }
})

const { notes, categories } = toRefs(props)

// ID nota seleccionada
const selectedNoteId = ref(
  props.selectedNoteIdFromServer ||
  (notes.value.length ? notes.value[0]?.id : null)
)

const selectedNote = computed(() =>
  notes.value.find(n => n.id === selectedNoteId.value) || null
)

const editableNote = ref(null)

watch(
  () => selectedNote.value,
  (newNote) => {
    if (newNote) editableNote.value = JSON.parse(JSON.stringify(newNote))
    else editableNote.value = null
  },
  { immediate: true }
)

watch(
  () => props.selectedNoteIdFromServer,
  (val) => { if (val) selectedNoteId.value = val }
)

watch(
  () => notes.value,
  (newNotes) => {
    if (!newNotes || !newNotes.length) {
      selectedNoteId.value = null
      editableNote.value = null
      return
    }
    const exists = newNotes.some(n => n.id === selectedNoteId.value)
    if (!exists) selectedNoteId.value = newNotes[0].id
    else {
      const n = newNotes.find(n => n.id === selectedNoteId.value)
      if (n) editableNote.value = JSON.parse(JSON.stringify(n))
    }
  }
)

// permissions/computed
const canEditSelected = computed(() =>
  selectedNote.value ? !!selectedNote.value.can_edit : false
)
const isOwnerSelected = computed(() =>
  selectedNote.value ? !!selectedNote.value.is_owner : false
)

// UI state
const search = ref('')
const activeCategory = ref('Todas')
const newCategoryName = ref('')

// category filters
const categoryFilters = computed(() => {
  const names = categories.value.map(c => c.name)
  return ['Todas', ...names]
})

// filtered notes
const filteredNotes = computed(() =>
  notes.value.filter(n => {
    const matchCategory =
      activeCategory.value === 'Todas' ||
      (n.category && n.category === activeCategory.value)

    const term = (search.value || '').toLowerCase()
    const matchSearch =
      !term ||
      (n.title && n.title.toLowerCase().includes(term)) ||
      (n.content && n.content.toLowerCase().includes(term))

    return matchCategory && matchSearch
  })
)

// create note
const createNote = () => {
  router.post(
    route('notes.store'),
    {
      title: 'Nueva nota',
      category: activeCategory.value === 'Todas' ? null : activeCategory.value,
      content: ''
    },
    {
      preserveScroll: true,
      onSuccess: (page) => {
        const newNotes = page.props.notes ?? []
        if (newNotes.length) selectedNoteId.value = newNotes[newNotes.length - 1].id
      }
    }
  )
}

// save note
const saveNote = async () => {
  if (!editableNote.value) return
  if (!canEditSelected.value) { alert('No tienes permiso para editar esta nota.'); return }

  await router.put(
    route('notes.update', editableNote.value.id),
    {
      title: editableNote.value.title,
      category: editableNote.value.category,
      content: editableNote.value.content,
      notebook_id: editableNote.value.notebook_id ?? null,
      is_pinned: !!editableNote.value.is_pinned
    },
    { preserveScroll: true, preserveState: true }
  )
}

// destroy note
const destroy = (id) => {
  if (!confirm('¿Eliminar esta nota?')) return
  router.delete(route('notes.destroy', id), {
    preserveScroll: true,
    onSuccess: (page) => {
      const newNotes = page.props.notes ?? []
      selectedNoteId.value = newNotes.length ? newNotes[0].id : null
    }
  })
}

// AI 
const improvingWithAI = ref(false)
const improveNoteWithAI = () => {
  if (!editableNote.value) return
  if (!canEditSelected.value) { alert('No puedes usar IA en esta nota (solo lectura).'); return }

  improvingWithAI.value = true
  router.post(
    route('notes.ai-format', editableNote.value.id),
    { content: editableNote.value.content, title: editableNote.value.title },
    {
      preserveScroll: true,
      onFinish: () => { improvingWithAI.value = false }
    }
  )
}

// categories create / delete
const createCategory = () => {
  if (!newCategoryName.value.trim()) return
  router.post(route('categories.store'), { name: newCategoryName.value }, {
    preserveScroll: true,
    onSuccess: () => { newCategoryName.value = '' }
  })
}
const deleteCategory = (id) => {
  if (!confirm('¿Eliminar esta categoría? Las notas quedarán sin categoría.')) return
  router.delete(route('categories.destroy', id), { preserveScroll: true })
}

// collaborators (share)
const shareEmail = ref('')
const shareCanEdit = ref(false)
const shareError = ref('')
const collaborators = ref([])
const loadingCollaborators = ref(false)

const loadCollaborators = async () => {
  if (!selectedNote.value) { collaborators.value = []; return }
  loadingCollaborators.value = true
  try {
    const url = route('notes.collaborators', { note: selectedNote.value.id })
    const res = await axios.get(url)
    collaborators.value = res.data || []
  } catch (e) {
    collaborators.value = []
    console.error('Error cargando colaboradores', e)
  } finally {
    loadingCollaborators.value = false
  }
}

const addCollaborator = async () => {
  shareError.value = ''

  if (!shareEmail.value.trim()) {
    shareError.value = 'Ingresa un correo'
    return
  }

  try {
    await axios.post(
      route('notes.share', selectedNote.value.id),
      {
        email: shareEmail.value,
        can_edit: shareCanEdit.value
      }
    )

    shareEmail.value = ''
    shareCanEdit.value = false
    await loadCollaborators()

  } catch (error) {
    shareError.value =
      error.response?.data?.message ||
      'Error al compartir la nota'
  }
}

const removeCollaborator = async (userId) => {
  if (!confirm('Quitar colaborador?')) return
  try {
    // Usa la ruta tal como la definiste en web.php: notes.unshare
    await axios.delete(route('notes.unshare', { note: selectedNote.value.id, user: userId }))
    await loadCollaborators()
  } catch (e) { console.error('Error quitando colaborador', e) }
}

watch(selectedNote, () => { loadCollaborators() })

// IMPORTAR NOTA DE FATHOM
const fathomUrl = ref('')
const importingFathom = ref(false)
const importFromFathom = async () => {
  if (!fathomUrl.value.trim()) {
    alert('Pega el link de Fathom')
    return
  }

  if (!editableNote.value || !canEditSelected.value) {
    alert('No puedes editar esta nota')
    return
  }

  importingFathom.value = true

  try {
    const res = await axios.post(
      route('notes.fathom.import'),
      { url: fathomUrl.value }
    )

    const summary = res.data?.summary

    if (!summary) {
      alert('No se pudo importar el resumen')
      return
    }

    // 🔥 INSERTA TEXTO LIMPIO EN LA NOTA
    editableNote.value.content =
      (editableNote.value.content?.trim()
        ? editableNote.value.content + '\n\n'
        : '') +
      summary

    fathomUrl.value = ''

  } catch (e) {
    alert(
      e.response?.data?.error ||
      'Error al importar desde Fathom'
    )
  } finally {
    importingFathom.value = false
  }
}
</script>



<template>
  <AuthenticatedLayout>
    <div class="container mx-auto p-6">
      <!-- Cabecera -->
      <div class="flex items-center justify-between mb-6">
        <div>
          <div class="flex items-center gap-3">
            <div class="bg-gray-800 text-white p-2 rounded">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
            </div>
            <div>
              <h1 class="text-2xl font-bold tituloPag">Sistema de Notas</h1>

            </div>
          </div>
        </div>

      </div>

      <!-- Mensajes -->
      <div v-if="success" class="bg-green-100 text-green-700 p-3 rounded mb-4">
        {{ success }}
      </div>

      <div v-if="aiMessage" class="bg-blue-100 text-blue-700 p-3 rounded mb-4">
        {{ aiMessage }}
      </div>

      <!-- Layout tipo Evernote -->
      <div class="grid grid-cols-12 gap-4">
        <!-- Columna izquierda: categorías -->
        <div class="col-span-12 md:col-span-3">
          <div class="bg-white rounded shadow p-4">
            <h2 class="text-sm font-semibold text-gray-700 mb-3">
              CATEGORÍAS
            </h2>

            <!-- Filtro de categorías -->
            <ul class="space-y-1 mb-4">
              <li v-for="cat in categoryFilters" :key="cat">
                <button class="w-full flex items-center gap-2 px-3 py-2 rounded text-sm transition" :class="activeCategory === cat
                  ? 'bgPrincipal text-white'
                  : 'text-gray-700 hover:bg-gray-100'" @click="activeCategory = cat">
                  <svg v-if="cat === 'Todas'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  <svg v-else-if="cat === 'Reuniones'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                  <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                  </svg>
                  <span>{{ cat === 'Todas' ? 'Todas las notas' : cat }}</span>
                </button>
              </li>
            </ul>

            <!-- Gestión de categorías reales -->
            <h3 class="text-xs font-semibold text-gray-500 mb-2 mt-6">
              ADMINISTRAR
            </h3>

            <div class="flex mb-2 gap-2">
              <input v-model="newCategoryName" type="text" placeholder="Nueva categoría"
                class="flex-1 border border-gray-300 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-400">
              <button @click="createCategory"
                class="bg-green-500 hover:bg-green-600 text-white text-xs px-3 py-1 rounded shadow">
                +
              </button>
            </div>

            <ul class="max-h-40 overflow-y-auto space-y-1 text-xs text-gray-700">
              <li v-for="cat in categories" :key="cat.id"
                class="flex justify-between items-center px-2 py-1 rounded hover:bg-gray-100">
                <span>{{ cat.name }}</span>
                <button @click="deleteCategory(cat.id)" class="text-red-500 hover:text-red-700 text-[11px]">
                  Eliminar
                </button>
              </li>
            </ul>

            <h3 class="text-xs font-semibold text-gray-500 mt-6 mb-2">
              Estadísticas
            </h3>
            <div class="text-center">
              <p class="text-3xl font-bold text-gray-800">{{ notes.length }}</p>
              <p class="text-xs text-gray-500">Notas totales</p>
            </div>
          </div>
        </div>

        <!-- Columna central: lista de notas -->
        <div class="col-span-12 md:col-span-4">
          <div class="bg-white rounded shadow p-4 h-full flex flex-col">
            <!-- Buscador y botón Nueva nota -->
            <div class="mb-3 flex gap-2">
              <div class="flex-1 relative">
                <svg xmlns="http://www.w3.org/2000/svg"
                  class="h-4 w-4 absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400" fill="none"
                  viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input v-model="search" type="text" placeholder="Buscar notas por título, contenido o etiqueta"
                  class="w-full border border-gray-300 rounded pl-10 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
              </div>
              <button @click="createNote"
                class="bgPrincipal text-white px-4 py-2 rounded text-sm shadow hover:opacity-90 transition flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nueva nota
              </button>
            </div>

            <!-- Encabezado de lista -->
            <div class="mb-3">
              <span class="text-sm text-gray-600 font-medium">
                {{ filteredNotes.length }} notas encontradas
              </span>
            </div>

            <!-- Lista de notas -->
            <div class="flex-1 overflow-y-auto space-y-2">
              <div v-for="note in filteredNotes" :key="note.id" @click="selectedNoteId = note.id"
                class="border rounded-lg px-4 py-3 cursor-pointer transition" :class="selectedNoteId === note.id
                  ? 'border-indigo-500 bg-indigo-50'
                  : 'border-gray-200 hover:bg-gray-50'">
                <div class="flex justify-between items-start mb-2">
                  <h3 class="text-sm font-semibold text-gray-800">
                    {{ note.title || 'Sin título' }}
                  </h3>
                  <span v-if="note.category"
                    class="text-xs px-2 py-0.5 rounded-full bgPrincipal text-white ml-2 flex-shrink-0">
                    {{ note.category }}
                  </span>
                </div>
                <div class="flex items-center gap-2 text-[11px] text-gray-400">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span>Actualizado hace {{ note.updated_at || '—' }}</span>
                </div>
                <p class="text-xs text-gray-600 mt-2 line-clamp-2">
                  {{ note.content || 'Sin contenido' }}
                </p>
              </div>

              <p v-if="filteredNotes.length === 0" class="text-xs text-gray-500 text-center py-4">
                No hay notas que coincidan con el filtro.
              </p>
            </div>
          </div>
        </div>

        <!-- Columna derecha: editor de nota -->
        <div class="col-span-12 md:col-span-5">
          <div class="bg-white rounded shadow p-4 h-full flex flex-col">
            <template v-if="selectedNote">
              <!-- Header Nota Seleccionada -->
              <div class="flex items-center justify-between mb-4 pb-3 border-b">
                <h2 class="text-lg font-semibold text-gray-800">Nota Seleccionada</h2>
                <button class="text-gray-600 hover:text-gray-800">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </button>
              </div>

              <!-- Título de la nota -->
              <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 mb-2">TÍTULO DE LA NOTA</label>
                <input v-model="editableNote.title" type="text"
                  class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                  placeholder="Título de la nota" :disabled="!canEditSelected">
              </div>

              <!-- Categoría -->
              <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 mb-2">CATEGORÍA</label>
                <select v-model="editableNote.category"
                  class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white">
                  <option value="">Sin categoría</option>
                  <option v-for="cat in categories" :key="cat.id" :value="cat.name">
                    {{ cat.name }}
                  </option>
                </select>
              </div>

              <!-- ID de Nota + Reordenar con IA -->
              <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 mb-2">ID DE NOTA</label>
                <div class="flex items-center justify-between gap-3">
                  <span class="text-sm text-gray-700">ID-{{ selectedNote.id }}</span>
                  <button @click="improveNoteWithAI" :disabled="improvingWithAI || !selectedNote || !canEditSelected"
                    class="flex items-center gap-1 bgPrincipal hover:opacity-90 disabled:opacity-60 disabled:cursor-not-allowed text-white text-xs px-3 py-1.5 rounded shadow">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                      stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <span v-if="!improvingWithAI">Reordenar con IA</span>
                    <span v-else>Procesando...</span>
                  </button>
                </div>
              </div>

              <!-- Panel de colaboradores -->
              <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 mb-2">
                  COLABORADORES
                  <span v-if="selectedNote.shared_with_me"
                    class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 text-[10px]">
                    Compartida conmigo
                  </span>
                </label>

                <!-- Solo el dueño puede administrar colaboradores -->
                <div v-if="selectedNote.is_owner" class="mb-2">
                  <div class="mb-2">
                    <input type="email" v-model="shareEmail" placeholder="Correo del colaborador"
                      class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400" />
                  </div>
                  <div class="flex items-center justify-between gap-2">
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                      <input type="checkbox" v-model="shareCanEdit" class="rounded" />
                      Puede editar
                    </label>
                    <button @click="addCollaborator"
                      class="px-4 py-2 bgPrincipal text-white text-sm rounded hover:opacity-90 transition">
                      Añadir
                    </button>
                  </div>

                  <p v-if="shareError" class="text-xs text-red-500 mb-2">
                    {{ shareError }}
                  </p>
                </div>

                <!-- Lista de colaboradores -->
                <div class="bg-gray-50 border rounded p-3">
                  <p v-if="loadingCollaborators" class="text-xs text-gray-400">
                    Cargando colaboradores...
                  </p>

                  <p v-else-if="!collaborators.length" class="text-xs text-gray-500">
                    No hay colaboradores en esta nota
                  </p>

                  <ul v-else class="space-y-2">
                    <li v-for="collab in collaborators" :key="collab.id"
                      class="flex items-center justify-between text-xs">
                      <div class="flex flex-col">
                        <span class="font-medium text-gray-700">{{ collab.name || collab.email }}</span>
                        <span class="text-gray-400 text-[11px]">
                          {{ collab.email }}
                          <span v-if="collab.can_edit"> · Puede editar</span>
                          <span v-else> · Solo lectura</span>
                        </span>
                      </div>

                      <button v-if="selectedNote.is_owner" @click="removeCollaborator(collab.id)"
                        class="text-red-500 hover:text-red-700 text-xs">
                        Quitar
                      </button>
                    </li>
                  </ul>
                </div>
              </div>

              <!-- Fathom - Resumen de reunión -->
              <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 mb-2">
                  IMPORTAR DESDE FATHOM
                </label>

                <div class="flex gap-2">
                  <input v-model="fathomUrl" type="text" placeholder="https://fathom.video/share/..."
                    class="flex-1 border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
                    :disabled="!canEditSelected" />

                  <button @click="importFromFathom" :disabled="importingFathom || !canEditSelected"
                    class="bg-sky-500 hover:bg-sky-600 text-white text-sm px-4 py-2 rounded shadow disabled:opacity-50">
                    {{ importingFathom ? 'Importando…' : 'Importar' }}
                  </button>
                </div>
              </div>

              <!-- Contenido de la nota -->
              <div class="flex-1 mb-4">
                <label class="block text-xs font-semibold text-gray-600 mb-2">CONTENIDO DE LA NOTA</label>
                <textarea v-model="editableNote.content"
                  class="w-full h-full min-h-[200px] border border-gray-300 rounded px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-indigo-400"
                  placeholder="Escribe tu nota aquí..." :disabled="!canEditSelected">
                </textarea>
              </div>

              <!-- Botones de acción -->
              <div class="flex justify-between items-center pt-3 border-t">
                <button @click="saveNote" :disabled="!canEditSelected"
                  class="bgPrincipal text-white px-6 py-2 rounded text-sm shadow hover:opacity-90 transition disabled:opacity-60">
                  Guardar
                </button>
                <button @click="destroy(selectedNote.id)" :disabled="!isOwnerSelected"
                  class="text-red-500 hover:text-red-700 p-2 disabled:opacity-40">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>

              <!-- Última edición -->
              <div class="mt-2 text-center">
                <span class="text-xs text-gray-400">
                  Última edición: {{ selectedNote.updated_at || '—' }}
                </span>
              </div>
            </template>

            <template v-else>
              <p class="text-sm text-gray-500">
                Selecciona una nota de la lista para verla o editarla.
              </p>
            </template>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
