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
const importingFathom = ref(false)

const importFathomSummary = () => {
  if (!selectedNote.value?.fathom_url) {
    alert('Pega un link de Fathom primero')
    return
  }

  importingFathom.value = true

  router.post(
    route('notes.import-fathom', selectedNote.value.id),
    { fathom_url: selectedNote.value.fathom_url },
    {
      preserveScroll: true,
      onFinish: () => {
        importingFathom.value = false
      }
    }
  )
}
</script>



<template>
  <AuthenticatedLayout>
    <div class="container mx-auto p-6">
      <!-- Cabecera -->
      <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold tituloPag">
          Notas
        </h1>
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
            <h2 class="text-sm font-semibold text-gray-700 mb-2">
              Categorías
            </h2>

            <!-- Filtro de categorías -->
            <ul class="space-y-1 mb-4">
              <li v-for="cat in categoryFilters" :key="cat">
                <button class="w-full flex justify-between items-center px-3 py-1.5 rounded text-sm transition" :class="activeCategory === cat
                  ? 'bgPrincipal text-white'
                  : 'text-gray-700 hover:bg-gray-100'" @click="activeCategory = cat">
                  <span>{{ cat }}</span>
                </button>
              </li>
            </ul>

            <!-- Gestión de categorías reales -->
            <h3 class="text-xs font-semibold text-gray-500 mb-2">
              Administrar categorías
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

            <h3 class="text-xs font-semibold text-gray-500 mt-4 mb-1">
              Estadísticas
            </h3>
            <p class="text-xs text-gray-500">
              Total de notas: <strong>{{ notes.length }}</strong>
            </p>
          </div>
        </div>

        <!-- Columna central: lista de notas -->
        <div class="col-span-12 md:col-span-4">
          <div class="bg-white rounded shadow p-4 h-full flex flex-col">
            <!-- Buscador -->
            <div class="mb-3">
              <input v-model="search" type="text" placeholder="Buscar notas..."
                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
            </div>

            <!-- Encabezado de lista -->
            <div class="mb-3 flex justify-between items-center">
              <span class="text-xs text-gray-500">
                {{ filteredNotes.length }} nota(s) encontradas
              </span>
              <button @click="createNote"
                class="bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 rounded text-xs shadow">
                + Nueva nota
              </button>
            </div>

            <!-- Lista de notas -->
            <div class="flex-1 overflow-y-auto space-y-2">
              <div v-for="note in filteredNotes" :key="note.id" @click="selectedNoteId = note.id"
                class="border rounded px-3 py-2 cursor-pointer transition" :class="selectedNoteId === note.id
                  ? 'border-indigo-500 bg-indigo-50'
                  : 'border-gray-200 hover:bg-gray-50'">
                <div class="flex justify-between items-center mb-1">
                  <h3 class="text-sm font-semibold text-gray-800 truncate">
                    {{ note.title || 'Sin título' }}
                  </h3>
                  <span v-if="note.category" class="text-xs px-2 py-0.5 rounded-full bgPrincipal text-white">
                    {{ note.category }}
                  </span>
                </div>
                <p class="text-xs text-gray-500 truncate">
                  {{ note.content || 'Sin contenido' }}
                </p>
                <p class="text-[11px] text-gray-400 mt-1">
                  Actualizado: {{ note.updated_at || '—' }}
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
              <div class="mb-3">
                <input v-model="editableNote.title" type="text"
                  class="w-full border border-gray-300 rounded px-3 py-2 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-indigo-400"
                  placeholder="Título de la nota">
              </div>

              <!-- Fila categoría + ID + Botón IA -->
              <div class="mb-3 flex items-center justify-between gap-3">
                <!-- IZQUIERDA: categoría + ID -->
                <div class="flex items-center gap-2">
                  <select v-model="editableNote.category"
                    class="w-44 border border-gray-300 rounded px-3 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-400 bg-white">
                    <option value="">Sin categoría</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.name">
                      {{ cat.name }}
                    </option>
                  </select>

                  <span class="text-[11px] text-gray-400">
                    ID: {{ selectedNote.id }}
                  </span>
                </div>

                <!-- DERECHA: botón Reordenar con IA -->
                <button @click="improveNoteWithAI" :disabled="improvingWithAI || !selectedNote"
                  class="flex items-center gap-1 bg-purple-500 bgPrincipal hover:bg-purple-600 disabled:opacity-60 disabled:cursor-not-allowed text-white text-xs px-4 py-1.5 rounded-full shadow">
                  <span class="material-icons text-sm" style="font-size:14px"></span>
                  <span v-if="!improvingWithAI">Reordenar con IA</span>
                  <span v-else>Procesando...</span>
                </button>
              </div>

              <!-- Panel de colaboradores -->
              <div v-if="selectedNote" class="mt-4 border-t pt-3">
                <h3 class="text-xs font-semibold text-gray-600 mb-2">
                  Colaboradores
                  <span v-if="selectedNote.shared_with_me"
                    class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 text-[10px]">
                    Compartida conmigo
                  </span>
                </h3>

                <!-- Solo el dueño puede administrar colaboradores -->
                <div v-if="selectedNote.is_owner">
                  <div class="flex gap-2 mb-2">
                    <input type="email" v-model="shareEmail" placeholder="Correo del colaborador"
                      class="flex-1 border border-gray-300 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-400" />

                    <label class="flex items-center gap-1 text-[11px] text-gray-600">
                      <input type="checkbox" v-model="shareCanEdit" />
                      Puede editar
                    </label>

                    <button @click="addCollaborator"
                      class="px-2 py-1 bgPrincipal text-white text-xs rounded hover:bg-blue-700 transition">
                      Añadir
                    </button>
                  </div>

                  <p v-if="shareError" class="text-[11px] text-red-500 mb-2">
                    {{ shareError }}
                  </p>
                </div>

                <!-- Lista de colaboradores -->
                <div class="bg-white border rounded p-2 max-h-40 overflow-y-auto">
                  <p v-if="loadingCollaborators" class="text-[11px] text-gray-400">
                    Cargando colaboradores...
                  </p>

                  <p v-else-if="!collaborators.length" class="text-[11px] text-gray-400">
                    No hay colaboradores en esta nota.
                  </p>

                  <ul v-else class="space-y-1">
                    <li v-for="collab in collaborators" :key="collab.id"
                      class="flex items-center justify-between text-[11px]">
                      <div class="flex flex-col">
                        <span class="font-medium text-gray-700">{{ collab.name || collab.email }}</span>
                        <span class="text-gray-400">
                          {{ collab.email }} ·
                          <span v-if="collab.can_edit">Puede editar</span>
                          <span v-else>Solo lectura</span>
                        </span>
                      </div>

                      <button v-if="selectedNote.is_owner" @click="removeCollaborator(collab.id)"
                        class="text-red-500 hover:text-red-700 text-[11px]">
                        Quitar
                      </button>
                    </li>
                  </ul>
                </div>
              </div>

              <div class="mt-4 border-t pt-3">
                <h3 class="text-xs font-semibold text-gray-600 mb-2">
                  Fathom – Resumen de reunión
                </h3>

                <div class="flex gap-2">
                  <input v-model="selectedNote.fathom_url" type="url" placeholder="Pega aquí el link de Fathom"
                    class="flex-1 border border-gray-300 rounded px-2 py-1 text-xs focus:outline-none focus:ring-1 focus:ring-indigo-400"
                    :disabled="!canEditSelected" />

                  <button @click="importFathomSummary" :disabled="!canEditSelected || importingFathom"
                    class="bg-indigo-500 hover:bg-indigo-600 text-white text-xs px-3 py-1 rounded shadow disabled:opacity-60">
                    <span v-if="!importingFathom">Importar</span>
                    <span v-else>Importando…</span>
                  </button>
                </div>
              </div>


              <div class="flex-1 mb-3">
                <textarea v-model="editableNote.content"
                  class="w-full h-full min-h-[200px] border border-gray-300 rounded px-3 py-2 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-indigo-400"
                  placeholder="Escribe tu nota aquí...">
                </textarea>
              </div>

              <div class="flex justify-between items-center mt-2">
                <div class="flex gap-2">
                  <button @click="saveNote"
                    class="bgPrincipal text-white px-4 py-1.5 rounded text-sm shadow hover:opacity-90 transition">
                    Guardar
                  </button>
                  <button @click="destroy(selectedNote.id)"
                    class="bgIconTrash text-white px-3 py-1.5 rounded text-sm shadow hover:opacity-90 transition">
                    Eliminar
                  </button>
                </div>
                <span class="text-[11px] text-gray-400">
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
