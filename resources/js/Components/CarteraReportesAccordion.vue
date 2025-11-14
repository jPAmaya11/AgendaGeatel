<template>
    <div class="space-y-2">
        <div
            v-for="(cartera, idx) in carteras"
            :key="cartera.id"
            class="border border-indigo-200 rounded-lg bg-white shadow-sm"
        >
            <div>
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
                        {{ cartera.nombre }}
                    </div>
                    <span
                        class="text-xs bg-indigo-100 text-indigo-700 rounded-full px-2 py-0.5"
                    >
                        {{ cartera.reportes?.length ?? 0 }} reportes
                    </span>
                </div>

                <transition name="accordion">
                    <div v-if="openedIndex === idx" class="px-4 pb-3 pt-1">
                        <!-- contenido renderizado -->
                        <template v-if="cartera.reportes?.length">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <div
                                    v-for="reporte in cartera.reportes"
                                    :key="reporte.id"
                                    class="flex items-center gap-2 bg-gray-50 border border-gray-100 rounded px-2 py-1"
                                >
                                    <input
                                        type="checkbox"
                                        :id="
                                            'reporte-' +
                                            cartera.id +
                                            '-' +
                                            reporte.id
                                        "
                                        :checked="
                                            modelValue.some(
                                                (r) => r.id === reporte.id
                                            ) ||
                                            inheritedReportes.some(
                                                (r) => r.id === reporte.id
                                            )
                                        "
                                        :disabled="
                                            inheritedReportes.some(
                                                (r) => r.id === reporte.id
                                            )
                                        "
                                        @change="
                                            handleToggle(
                                                reporte,
                                                $event.target.checked
                                            )
                                        "
                                        class="accent-indigo-600"
                                    />
                                    <label
                                        :for="
                                            'reporte-' +
                                            cartera.id +
                                            '-' +
                                            reporte.id
                                        "
                                        class="flex items-center justify-between text-xs text-gray-700 cursor-pointer w-full"
                                    >
                                        <span
                                            class="truncate max-w-[120px]"
                                            :title="reporte.nombre"
                                        >
                                            {{ reporte.nombre }}
                                        </span>

                                        <span
                                            v-if="
                                                inheritedReportes.some(
                                                    (r) => r.id === reporte.id
                                                )
                                            "
                                            class="ml-2 px-1.5 py-0.5 rounded-full bg-emerald-200 text-emerald-800 text-[11px] font-semibold shadow-sm border border-emerald-300 flex-shrink-0"
                                        >
                                            rol
                                        </span>
                                    </label>
                                </div>
                            </div>
                        </template>
                        <p v-else class="text-xs text-gray-400 italic mt-1">
                            Esta cartera no tiene reportes disponibles.
                        </p>
                    </div>
                </transition>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";

const props = defineProps({
    carteras: Array,
    modelValue: Array,
    inheritedReportes: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(["update:modelValue"]);
const openedIndex = ref(null);

function handleToggle(reporte, checked) {
    const id = reporte.id;
    const currentIds = props.modelValue.map((r) => r.id);

    if (props.inheritedReportes.some((r) => r.id === id)) return;

    if (checked) {
        if (!currentIds.includes(id)) {
            emit("update:modelValue", [...props.modelValue, reporte]);
        }
    } else {
        emit(
            "update:modelValue",
            props.modelValue.filter((r) => r.id !== id)
        );
    }
}

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
