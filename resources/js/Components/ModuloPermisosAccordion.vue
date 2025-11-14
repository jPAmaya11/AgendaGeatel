<template>
    <div class="space-y-2">
        <div
            v-for="(modulo, idx) in permisosPorModulo"
            :key="modulo.id"
            class="border border-indigo-200 rounded-lg bg-white shadow-sm"
        >
            <div
                @click="toggleAccordion(idx)"
                class="flex items-center justify-between cursor-pointer p-3 bg-indigo-50 hover:bg-indigo-100 rounded-t-lg transition-colors"
            >
                <div
                    class="flex items-center gap-2 text-sm font-semibold text-indigo-700"
                >
                    <svg
                        class="w-4 h-4 text-indigo-400 transform transition-transform duration-300"
                        :class="{ 'rotate-90': openedIndex === idx }"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M9 5l7 7-7 7"
                        />
                    </svg>
                    {{ modulo.nombre }}
                </div>
                <span
                    class="text-xs bg-indigo-100 text-indigo-700 rounded-full px-2 py-0.5"
                >
                    {{ modulo.permisos.length }} permisos
                </span>
            </div>

            <transition name="accordion">
                <div v-if="openedIndex === idx" class="px-4 pb-3 pt-1">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <label
                            v-for="permiso in modulo.permisos"
                            :key="permiso.name"
                            class="flex items-center gap-2 bg-gray-50 rounded px-2 py-1 text-xs text-gray-700"
                        >
                            <input
                                type="checkbox"
                                :value="permiso.name"
                                v-model="model"
                                class="accent-indigo-600"
                            />
                            {{ permiso.name }}
                        </label>
                    </div>
                </div>
            </transition>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from "vue";

const props = defineProps({
    permissions: {
        type: Array,
        required: true,
    },
    modelValue: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["update:modelValue"]);

const model = computed({
    get: () => props.modelValue,
    set: (val) => emit("update:modelValue", val),
});

// Agrupar permisos por módulo
const permisosPorModulo = computed(() => {
    const agrupados = {};
    for (const p of props.permissions) {
        const mod = p.module?.name ?? "Sin módulo";
        if (!agrupados[mod]) agrupados[mod] = [];
        agrupados[mod].push(p);
    }

    return Object.entries(agrupados).map(([nombre, permisos], index) => ({
        id: index,
        nombre,
        permisos,
    }));
});

const openedIndex = ref(null);

function toggleAccordion(idx) {
    openedIndex.value = openedIndex.value === idx ? null : idx;
}
</script>

<style scoped>
.accordion-enter-active,
.accordion-leave-active {
    transition: max-height 0.3s ease, opacity 0.3s ease;
    overflow: hidden;
}
.accordion-enter-from,
.accordion-leave-to {
    max-height: 0;
    opacity: 0;
}
.accordion-enter-to,
.accordion-leave-from {
    max-height: 400px;
    opacity: 1;
}
</style>
