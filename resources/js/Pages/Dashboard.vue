<script setup>
import { computed, ref, onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

const page = usePage();
const reportes = computed(() => page.props.userReportes || []);
const selectedReporteId = computed(() => page.props.selectedReporteId || null);

const reporteActual = computed(() => {
    if (!selectedReporteId.value) return null;
    return reportes.value.find((r) => r.id === selectedReporteId.value) || null;
});

const isMobile = ref(false);
onMounted(() => {
    isMobile.value = window.matchMedia("(max-width: 768px)").matches;
});

const linkReporte = computed(() => {
    if (!reporteActual.value) return null;

    // Prioridad: si es móvil y tiene link_mobile -> usar ese
    if (isMobile.value && reporteActual.value.link_mobile) {
        return reporteActual.value.link_mobile;
    }

    // Si no hay link_mobile o no es móvil, usar link_desktop como fallback
    return reporteActual.value.link_desktop || null;
});
</script>

<template>
    <AuthenticatedLayout>
        <div class="w-full">
            <transition name="fade" mode="out-in">
                <!-- Si hay reporte, muestra el iframe -->
                <iframe
                    v-if="reporteActual && linkReporte"
                    :key="reporteActual.id"
                    class="w-full h-[90vh] rounded-lg border border-indigo-100 shadow-md"
                    :src="linkReporte"
                    frameborder="0"
                    allowfullscreen
                    sandbox="allow-scripts allow-same-origin allow-popups"
                ></iframe>

                <!-- Si no hay reporte, muestra la bienvenida -->
                <div
                    v-else
                    key="sin-reporte"
                    class="flex flex-col items-center justify-center text-gray-500 py-12 transition-all duration-500 animate-fade-in"
                >
                    <!-- Texto de bienvenida animado -->
                    <div
                        class="mt-6 text-center px-4 animate-slide-up transition-opacity duration-700 ease-in-out"
                    >
                        <h2
                            class="text-4xl font-extrabold text-indigo-600 drop-shadow"
                        >
                            ¡Bienvenido!
                        </h2>
                        <p
                            class="mt-3 text-base md:text-lg text-gray-500 max-w-xl mx-auto font-light"
                        >
                            Selecciona un reporte del menú lateral para comenzar
                            a explorar tus datos.
                        </p>
                    </div>

                    <!-- Animación Lottie -->
                    <lottie-player
                        id="analyticsLottie"
                        src="/animation/analytics.json"
                        background="transparent"
                        speed="1"
                        style="width: 100%; max-width: 1520px; height: 620px"
                        class=""
                    ></lottie-player>
                </div>
            </transition>
        </div>
    </AuthenticatedLayout>
</template>
<style scoped>
@keyframes fade-in {
    0% {
        opacity: 0;
    }
    100% {
        opacity: 1;
    }
}

@keyframes slide-up {
    0% {
        transform: translateY(20px);
        opacity: 0;
    }
    100% {
        transform: translateY(0);
        opacity: 1;
    }
}

.animate-fade-in {
    animation: fade-in 0.6s ease-out both;
}

.animate-slide-up {
    animation: slide-up 0.8s ease-out both;
}
</style>
