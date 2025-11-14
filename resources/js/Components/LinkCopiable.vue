<template>
    <div>
        <!-- Si hay link -->
        <div v-if="link" class="flex items-center gap-1 text-xs">
            <span :title="title" class="shrink-0">
                <slot name="icon">
                    <svg
                        class="w-3.5 h-3.5 text-indigo-500"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M7 4h10a1 1 0 011 1v14a1 1 0 01-1 1H7a1 1 0 01-1-1V5a1 1 0 011-1z"
                        />
                    </svg>
                </slot>
            </span>

            <input
                type="text"
                :value="link"
                readonly
                class="flex-1 text-[11px] text-gray-500 bg-transparent border-0 border-b border-gray-200 px-0.5 py-0 h-5 focus:border-indigo-500 focus:ring-0 truncate"
            />

            <button
                @click="copiarAlPortapapeles(link)"
                class="text-indigo-500 hover:text-indigo-700"
                title="Copiar enlace"
            >
                <svg
                    class="w-3.5 h-3.5"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M8 16h8a2 2 0 002-2V6a2 2 0 00-2-2H8a2 2 0 00-2 2v8a2 2 0 002 2z"
                    />
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M16 8h2a2 2 0 012 2v8a2 2 0 01-2 2h-8a2 2 0 01-2-2v-2"
                    />
                </svg>
            </button>
        </div>

        <!-- Si NO hay link -->
        <div
            v-else
            class="flex items-center gap-1 text-[11px] text-gray-400 italic"
        >
            <svg
                class="w-3.5 h-3.5 text-gray-300"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M12 4.75v14.5M19.25 12H4.75"
                />
            </svg>
            <span>No existe {{ title?.toLowerCase() }}</span>
        </div>
    </div>
</template>

<script setup>
import Swal from "sweetalert2";
const props = defineProps({
    link: String,
    title: {
        type: String,
        default: "Enlace",
    },
});

function copiarAlPortapapeles(texto) {
    navigator.clipboard.writeText(texto).then(() => {
        Swal.fire({
            toast: true,
            position: "top-end",
            icon: "success",
            title: "Enlace copiado",
            showConfirmButton: false,
            timer: 1500,
            timerProgressBar: true,
        });
    });
}
</script>
