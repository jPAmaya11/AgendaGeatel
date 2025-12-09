<!-- =========================
    1. SCRIPT SETUP
========================= -->
<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { usePage, router, Link } from "@inertiajs/vue3";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import ApplicationLogoMini from "@/Components/ApplicationLogoMini.vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import NavLink from "@/Components/NavLink.vue";
import DataTool from "@/Components/DataTool.vue";

const page = usePage();
const { can } = page.props;
const canDo = (key) => !!can[key];

const getIconHTML = (icon) => {
    if (!icon) return null;
    return icon
        .replace("<svg", '<svg class="w-[26px] h-[26px]" fill="white"')
        .replace('stroke="currentColor"', 'stroke="currentColor"');
};

const reportes = computed(() => page.props.userReportes || []);
const carteras = computed(() => page.props.userCarteras || []);

const getCarteraIdFromStorage = () => {
    const stored = localStorage.getItem("selectedCarteraId");
    return stored
        ? parseInt(stored)
        : carteras.value.length
            ? carteras.value[0].id
            : null;
};
const selectedCarteraId = ref(
    page.props.selectedCarteraId ?? getCarteraIdFromStorage()
);
const selectedReporteId = computed(() => page.props.selectedReporteId || null);

watch(
    selectedCarteraId,
    (val) => {
        if (val) localStorage.setItem("selectedCarteraId", val);
    },
    { immediate: true }
);
const selectedCartera = computed(
    () => carteras.value.find((c) => c.id === selectedCarteraId.value) || null
);

const isSidebarOpen = ref(false);
const mobileSidebarOpen = ref(false);
const masividadOpen = ref(true);
function toggleSidebar() {
    isSidebarOpen.value = !isSidebarOpen.value;
}
function toggleMobileSidebar() {
    mobileSidebarOpen.value = !mobileSidebarOpen.value;
}

const reportesFiltrados = computed(() => {
    if (!selectedCarteraId.value) return [];
    return reportes.value.filter(
        (r) => r.cartera_id === selectedCarteraId.value
    );
});

function seleccionarReporte(reporte) {
    router.visit(route("dashboard.reporte", reporte.id), {
        preserveState: true,
        preserveScroll: true,
    });
}

onMounted(() => {
    if (!selectedCarteraId.value && carteras.value.length > 0) {
        selectedCarteraId.value = carteras.value[0].id;
    }
    window.addEventListener("resize", () => {
        if (window.innerWidth >= 1024) {
            mobileSidebarOpen.value = false;
        }
    });
});
</script>

<!-- =========================
    2. TEMPLATE
========================= -->
<template>
    <div class="min-h-screen bg-gray-100 flex">
        <!-- MOBILE OVERLAY -->
        <div v-if="mobileSidebarOpen" @click="toggleMobileSidebar"
            class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden"></div>

        <!-- ===== SIDEBAR ===== -->
        <aside :class="[
            'bgPrincipal customMenu fixed inset-y-0 left-0 z-50 shadow-lg flex flex-col transition-all duration-300 ease-in-out ',
            mobileSidebarOpen
                ? 'translate-x-0'
                : '-translate-x-full lg:translate-x-0',
            isSidebarOpen ? 'w-64 menuAbierto' : 'w-20',
        ]">
            <!-- Logo -->
            <div class="flex items-center justify-center p-4 border-b h-16">
                <Link :href="route('dashboard')" class="flex items-center">
                <ApplicationLogo v-if="isSidebarOpen" :variant="'white'" class="block h-9 w-auto" />

                <ApplicationLogoMini v-else class="block h-9 w-auto text-white" />
                </Link>
            </div>

            <!-- Menu -->
            <nav class="flex-1 overflow-y-auto">
                <button @click="toggleSidebar" class="w-full flex items-center px-3 py-3 hover:bg-white/10 transition"
                    :class="isSidebarOpen ? 'justify-start' : 'justify-center'">
                    <svg v-if="isSidebarOpen" class="svgMenu" style="width: 26px; height: 26px; color: #fff"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path fill="white"
                            d="M0 80c0-8.8 7.2-16 16-16l416 0c8.8 0 16 7.2 16 16s-7.2 16-16 16L16 96C7.2 96 0 88.8 0 80zM64 240c0-8.8 7.2-16 16-16l416 0c8.8 0 16 7.2 16 16s-7.2 16-16 16L80 256c-8.8 0-16-7.2-16-16zM448 400c0 8.8-7.2 16-16 16L16 416c-8.8 0-16-7.2-16-16s7.2-16 16-16l416 0c8.8 0 16 7.2 16 16z" />
                    </svg>
                    <svg v-else class="svgMenu" style="width: 26px; height: 26px; color: #fff"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                        <path fill="white"
                            d="M0 80c0-8.8 7.2-16 16-16l416 0c8.8 0 16 7.2 16 16s-7.2 16-16 16L16 96C7.2 96 0 88.8 0 80zM0 240c0-8.8 7.2-16 16-16l416 0c8.8 0 16 7.2 16 16s-7.2 16-16 16L16 256c-8.8 0-16-7.2-16-16zM448 400c0 8.8-7.2 16-16 16L16 416c-8.8 0-16-7.2-16-16s7.2-16 16-16l416 0c8.8 0 16 7.2 16 16z" />
                    </svg>
                    <span v-if="isSidebarOpen" class="ml-2 text-sm text-white">Cerrar menú</span>
                </button>

                <div class="separador separadorAdmin" v-if="canDo('carteras.ver')"></div>
                <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded" rel="stylesheet" />

                
                <NavLink v-if="canDo('reportes.ver')" :href="route('reportes.index')"
                    :active="route().current('reportes.index')"
                    class="w-full flex items-center px-3 py-3 hover:bg-white/10 transition group relative"
                    :class="isSidebarOpen ? 'justify-start' : 'justify-center'">
                    <svg class="w-6 h-6 text-white flex-shrink-0" xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 512 512" fill="currentColor">
                        <path xmlns="http://www.w3.org/2000/svg"
                            d="M32 96c0-17.7 14.3-32 32-32l64 0 0 176 0 32 0 176-64 0c-17.7 0-32-14.3-32-32L32 96zM160 272l320 0 0 144c0 17.7-14.3 32-32 32l-288 0 0-176zm320-32l-320 0 0-176 288 0c17.7 0 32 14.3 32 32l0 144zM0 416c0 35.3 28.7 64 64 64l384 0c35.3 0 64-28.7 64-64l0-320c0-35.3-28.7-64-64-64L64 32C28.7 32 0 60.7 0 96L0 416z" />
                    </svg>
                    <transition name="fade-slide">
                        <span v-if="isSidebarOpen" class="ml-3 text-sm text-white">Gestión de Reportes</span>
                    </transition>
                    <DataTool :label="'Gestión de Reportes'" :show="!isSidebarOpen" />
                </NavLink>

                <NavLink v-if="canDo('roles.ver')" :href="route('roles.index')" :active="route().current('roles.index')"
                    class="w-full flex items-center px-3 py-3 hover:bg-white/10 transition group relative"
                    :class="isSidebarOpen ? 'justify-start' : 'justify-center'">
                    <svg class="w-6 h-6 text-white flex-shrink-0" style="width: 26px; height: 26px; color: #fff"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
                        <path fill="white"
                            d="M544 32L96 32C78.3 32 64 46.3 64 64l0 165.5c-11.9 4.2-22.8 10.7-32 19L32 64C32 28.7 60.7 0 96 0L544 0c35.3 0 64 28.7 64 64l0 184.4c-9.2-8.3-20.1-14.8-32-19L576 64c0-17.7-14.3-32-32-32zM96 352a32 32 0 1 0 0-64 32 32 0 1 0 0 64zm0-96a64 64 0 1 1 0 128 64 64 0 1 1 0-128zm224 96a32 32 0 1 0 0-64 32 32 0 1 0 0 64zm0-96a64 64 0 1 1 0 128 64 64 0 1 1 0-128zm256 64a32 32 0 1 0 -64 0 32 32 0 1 0 64 0zm-96 0a64 64 0 1 1 128 0 64 64 0 1 1 -128 0zM32 480l0 16c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-16c0-35.3 28.7-64 64-64l64 0c35.3 0 64 28.7 64 64l0 16c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-16c0-17.7-14.3-32-32-32l-64 0c-17.7 0-32 14.3-32 32zm256-32c-17.7 0-32 14.3-32 32l0 16c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-16c0-35.3 28.7-64 64-64l64 0c35.3 0 64 28.7 64 64l0 16c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-16c0-17.7-14.3-32-32-32l-64 0zm192 32l0 16c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-16c0-35.3 28.7-64 64-64l64 0c35.3 0 64 28.7 64 64l0 16c0 8.8-7.2 16-16 16s-16-7.2-16-16l0-16c0-17.7-14.3-32-32-32l-64 0c-17.7 0-32 14.3-32 32z" />
                    </svg>
                    <transition name="fade-slide">
                        <span v-if="isSidebarOpen" class="ml-3 text-sm text-white">
                            Asignación de Roles
                        </span>
                    </transition>
                    <DataTool :label="'Asignación de Roles'" :show="!isSidebarOpen" />
                </NavLink>

                <NavLink v-if="canDo('usuarios.ver')" :href="route('users.index')"
                    :active="route().current('users.index')"
                    class="w-full flex items-center px-3 py-3 hover:bg-white/10 transition group relative"
                    :class="isSidebarOpen ? 'justify-start' : 'justify-center'">
                    <svg class="w-6 h-6 text-white flex-shrink-0" style="width: 26px; height: 26px; color: #fff"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path fill="white"
                            d="M64 64C46.3 64 32 78.3 32 96l0 320c0 17.7 14.3 32 32 32l384 0c17.7 0 32-14.3 32-32l0-320c0-17.7-14.3-32-32-32L64 64zM0 96C0 60.7 28.7 32 64 32l384 0c35.3 0 64 28.7 64 64l0 320c0 35.3-28.7 64-64 64L64 480c-35.3 0-64-28.7-64-64L0 96zm288 96a32 32 0 1 0 -64 0 32 32 0 1 0 64 0zm-96 0a64 64 0 1 1 128 0 64 64 0 1 1 -128 0zM299.4 320l-86.9 0c-18.6 0-34 14-36.3 32l159.4 0c-2.2-18-17.6-32-36.3-32zm-86.9-32l43.4 0 43.4 0c37.9 0 68.6 30.7 68.6 68.6c0 15.1-12.3 27.4-27.4 27.4l-169.1 0c-15.1 0-27.4-12.3-27.4-27.4c0-37.9 30.7-68.6 68.6-68.6z" />
                    </svg>
                    <transition name="fade-slide">
                        <span v-if="isSidebarOpen" class="ml-3 text-sm text-white whitespace-nowrap">Gestión de
                            Usuarios</span>
                    </transition>
                    <DataTool :label="'Gestion de Usuarios'" :show="!isSidebarOpen" />
                </NavLink>

                <!-- Enlace a Agenda -->
                <NavLink :href="route('events.index')" :active="route().current('events.*')"
                    class="w-full flex items-center px-3 py-3 hover:bg-white/10 transition group relative"
                    :class="isSidebarOpen ? 'justify-start' : 'justify-center'">
                    <svg class="w-6 h-6 text-white flex-shrink-0" style="width: 26px; height: 26px; color: #fff"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white">
                        <path
                            d="M7 2a1 1 0 0 1 2 0v1h6V2a1 1 0 1 1 2 0v1h1.5A2.5 2.5 0 0 1 21 5.5v13A2.5 2.5 0 0 1 18.5 21h-13A2.5 2.5 0 0 1 3 18.5v-13A2.5 2.5 0 0 1 5.5 3H7V2Zm11.5 5h-13c-.28 0-.5.22-.5.5V9h14V7.5c0-.28-.22-.5-.5-.5Zm-13 12h13c.28 0 .5-.22.5-.5V11h-14v7.5c0 .28.22.5.5.5Z" />
                    </svg>
                    <transition name="fade-slide">
                        <span v-if="isSidebarOpen" class="ml-3 text-sm text-white whitespace-nowrap"> Agenda /
                            Calendario </span>
                    </transition>
                    <DataTool :label="'Agenda / Calendario'" :show="!isSidebarOpen" />
                </NavLink>
            </nav>
        </aside>

        <!-- ===== MAIN CONTENT ===== -->
        <div :class="[
            'flex-1 flex flex-col overflow-hidden transition-all duration-300 ease-in-out',
            isSidebarOpen ? 'lg:pl-64' : 'lg:pl-20',
        ]">
            <!-- ===== TOP BAR ===== -->
            <header class="bg-white shadow-sm z-10">
                <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16 items-center">
                        <!-- Sidebar Toggle (Mobile) -->
                        <button @click="toggleMobileSidebar" class="md:hidden text-white hover:colorPrincipal mr-2">
                            <svg class="w-6 h-6" fill="#000" style="color: #000" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>

                        <!-- Carteras Dropdown -->
                        <Dropdown v-if="carteras.length > 0" align="left" width="full">
                            <template #trigger>
                                <button class="w-full flex items-center px-3 py-2 rounded-md" :class="{
                                    'bg-purple-50 text-purple-600':
                                        !!selectedCartera,
                                }">
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                    </svg>
                                    <transition name="fade">
                                        <span class="ml-3">
                                            {{
                                                selectedCartera?.nombre ||
                                                "Carteras"
                                            }}
                                        </span>
                                    </transition>
                                </button>
                            </template>
                            <template #content>
                                <button v-for="cartera in [...carteras].sort(
                                    (a, b) => a.orden - b.orden
                                )" :key="cartera.id" @click="selectedCarteraId = cartera.id"
                                    class="w-full text-left px-4 py-2 text-sm text-gray-700">
                                    {{ cartera.nombre }}
                                </button>
                            </template>
                        </Dropdown>

                        <div class="flex-1"></div>

                        <!-- Menú de Usuario -->
                        <div class="flex items-center gap-2">
                            <Dropdown align="right" width="48">
                                <!-- Trigger -->
                                <template #trigger>
                                    <div
                                        class="flex items-center gap-2 bg-white rounded-fulls px-2 py-1 shadow border border-gray-200 cursor-pointer relative">
                                        <div
                                            class="w-8 h-8 rounded-fulls bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold text-base uppercase shadow">
                                            {{
                                                $page.props.auth.user.name.charAt(
                                                    0
                                                )
                                            }}
                                        </div>
                                        <div class="hidden sm:block text-gray-800 leading-tight">
                                            <div class="font-semibold text-sm">
                                                {{ $page.props.auth.user.name }}
                                                <span class="rollBar">{{
                                                    $page.props.auth.role
                                                }}</span>
                                            </div>
                                        </div>
                                        <!-- Flecha caret -->
                                        <svg class="w-4 h-4 text-gray-400 ml-2 mr-1" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>
                                </template>

                                <!-- Contenido del Dropdown -->
                                <template #content>
                                    <!-- Solo visible en mobile -->
                                    <div class="block lg:hidden px-4 py-3 border-b border-gray-100 bg-white">
                                        <div class="font-semibold text-gray-800 text-base">
                                            {{ $page.props.auth.user.name }}
                                        </div>
                                        <div v-if="$page.props.auth.role"
                                            class="inline-block mt-1 px-2 py-0.5 rounded bg-indigo-100 text-indigo-700 text-xs font-semibold">
                                            {{ $page.props.auth.role }}
                                        </div>
                                    </div>

                                    <DropdownLink :href="route('profile.edit')">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M5.121 17.804A13.937 13.937 0 0112 15c2.5 0 4.847.655 6.879 1.804M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <span>Perfil</span>
                                        </div>
                                    </DropdownLink>

                                    <DropdownLink :href="route('logout')" method="post" as="button">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M17 16l4-4m0 0l-4-4m4 4H3" />
                                            </svg>
                                            <span>Cerrar Sesión</span>
                                        </div>
                                    </DropdownLink>
                                </template>
                            </Dropdown>
                        </div>
                    </div>
                </div>
            </header>

            <!-- ===== PAGE CONTENT ===== -->
            <main class="flex-1 overflow-y-auto p-4 sm:p-6 bg-gray-100" style="padding: 1.5rem 2rem">
                <div class="mx-auto rellenoInternas">
                    <slot name="header" />
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<!-- =========================
    3. STYLE
========================= -->
<style scoped>
/* Menú colapsado */
[style*="width: 80px"] .dropdown-content {
    left: 80px;
    min-width: 200px;
}

/* Contenido ancho máximo */
.max-w-7xl {
    max-width: 100%;
}

@media (min-width: 1280px) {
    .max-w-7xl {
        max-width: 80rem;
    }
}

/* Sidebar altura completa */
.h-screen {
    height: 100vh;
}

/* Sidebar posición */
.fixed.inset-y-0 {
    top: 0;
    bottom: 0;
}

.bgPrincipal {
    background: rgb(95, 97, 255);
}

.svgMenu {
    display: inline-block;
    vertical-align: middle;
}

@media (max-width: 1023px) {

    /* Oculta el tooltip en mobile */
    .group:hover .group-hover\:visible {
        visibility: hidden !important;
        opacity: 0 !important;
    }
}
</style>
