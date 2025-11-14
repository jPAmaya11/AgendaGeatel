<script setup>
import CarteraReportesAccordion from "@/Components/CarteraReportesAccordion.vue";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ModalXts from "@/Components/ModalXts.vue";
import Multiselect from "vue-multiselect";
import { Head, usePage, router } from "@inertiajs/vue3";
import { ref, reactive, onMounted, watch } from "vue";
import Swal from "sweetalert2";
import "vue-multiselect/dist/vue-multiselect.css";
import Actions from "@/Components/Actions.vue";
import InputField from "@/Components/InputField.vue";
import ModuloPermisosAccordion from "@/Components/ModuloPermisosAccordion.vue";

//Permisos
const page = usePage();
const { can } = page.props;
const canDo = (key) => !!can[key];

const { roles, carteras, reportes, permissions, success } = usePage().props;

const showModal = ref(false);
const rolEditar = ref(null);

import { computed } from "vue";

const rolForm = reactive({
    name: "",
    carteras: [],
    reportes: [],
    permissions: [],
});

// modelValue para reportes, usando computed para asegurar reactividad
const modelValue = computed({
    get: () => rolForm.reportes,
    set: (val) => {
        rolForm.reportes = val;
    },
});

const carterasConReportes = carteras.map((c) => ({
    ...c,
    reportes: reportes.filter((r) => r.cartera_id === c.id),
}));

onMounted(() => {
    if (success) {
        Swal.fire({
            toast: true,
            position: "top-end",
            icon: "success",
            title: success,
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
        });
    }

    // Habilitar scroll horizontal con la rueda del mouse
    document.querySelectorAll(".scrollbar-ghost").forEach((el) => {
        el.addEventListener("wheel", function (e) {
            if (e.deltaY !== 0) {
                e.preventDefault();
                el.scrollBy({
                    left: e.deltaY,
                    behavior: "smooth",
                });
            }
        });
    });
});

function abrirModalAgregar() {
    Object.assign(rolForm, {
        name: "",
        carteras: [],
        reportes: [],
        permissions: [],
    });
    rolEditar.value = null;
    showModal.value = true;
}

function abrirModalEditar(role) {
    rolEditar.value = role;

    const carterasSeleccionadas = carterasConReportes.filter((c) =>
        role.carteras.some((uc) => uc.id === c.id)
    );

    const reportesSeleccionados = [];
    carterasSeleccionadas.forEach((c) => {
        c.reportes.forEach((r) => {
            if (role.reportes.some((ur) => ur.id === r.id)) {
                reportesSeleccionados.push(r);
            }
        });
    });

    Object.assign(rolForm, {
        name: role.name,
        carteras: carterasSeleccionadas,
        reportes: reportesSeleccionados,
        permissions: role.permissions.map((p) => p.name), // ← Aquí
    });

    showModal.value = true;
}

function cerrarModal() {
    showModal.value = false;
    rolEditar.value = null;
}

function handleSuccess(message) {
    Swal.fire({
        toast: true,
        position: "top-end",
        icon: "success",
        title: message,
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
    });
    recargar();
}

function recargar() {
    router.visit(route("roles.index"), {
        preserveScroll: true,

        only: ["roles", "success"],
        onFinish: () => cerrarModal(),
    });
}

function eliminarRol(role) {
    Swal.fire({
        title: "¿Eliminar rol?",
        text: `¿Estás seguro de eliminar el rol ${role.name}?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/roles/${role.id}`, {
                onSuccess: () => {
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "success",
                        title: "Rol eliminado",
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                    });
                    recargar();
                },
                onError: () => {
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "error",
                        title: "No se pudo eliminar el rol",
                        showConfirmButton: false,
                        timer: 2000,
                        timerProgressBar: true,
                    });
                },
            });
        }
    });
}

function toggleReporteCartera(reporte, checked) {
    // Trabajar siempre con IDs únicos y objetos consistentes
    if (checked) {
        if (!rolForm.reportes.some((r) => r.id === reporte.id)) {
            rolForm.reportes = [...rolForm.reportes, { ...reporte }];
        }
    } else {
        rolForm.reportes = rolForm.reportes.filter((r) => r.id !== reporte.id);
    }
}

watch(
    () => rolForm.carteras,
    (nuevasCarteras) => {
        const nuevasCarterasIds = nuevasCarteras.map((c) => c.id);
        rolForm.reportes = rolForm.reportes.filter((r) =>
            nuevasCarterasIds.includes(r.cartera_id)
        );
    },
    { deep: true }
);
const showCrearModulo = ref(false);

const nuevoModulo = reactive({
    name: "",
    permissions: [""], // empieza con un permiso vacío
});

function agregarPermisoInput() {
    nuevoModulo.permissions.push("");
}

function eliminarPermisoInput(i) {
    if (nuevoModulo.permissions.length > 1) {
        nuevoModulo.permissions.splice(i, 1);
    }
}

async function crearModulo() {
    try {
        const res = await axios.post("/modulos", {
            name: nuevoModulo.name,
            permissions: nuevoModulo.permissions.filter((p) => p.trim() !== ""),
        });

        permissions.push(...res.data.module.permissions);
        handleSuccess("Módulo y permisos creados");
        showCrearModulo.value = false;

        // Limpiar
        Object.assign(nuevoModulo, {
            name: "",
            permissions: [""],
        });
    } catch (e) {
        Swal.fire("Error", "No se pudo crear el módulo", "error");
    }
}

const tabActiva = ref("basico");

const tabs = [
    { value: "basico", label: "Datos Básicos", icon: null },
    { value: "permisos", label: "Permisos Avanzados", icon: null },
];

function transformarForm(form) {
    return {
        ...form,
        carteras: form.carteras.map((c) => c.id),
        reportes: form.reportes.map((r) => r.id),
    };
}

function permisoAmigable(permiso) {
    // Si el permiso es 'vodafone.ver-global', muestra 'Ver Global'
    const partes = permiso.split(".");
    if (partes.length < 2) return permiso;
    let accion = partes[1]
        .replace("-", " ") // ver-global → ver global
        .replace(/\b\w/g, (l) => l.toUpperCase()); // primera letra mayúscula
    return accion;
}
</script>

<template>
    <Head title="Gestión de Roles" />
    <AuthenticatedLayout class="relleno">
        <div class="flex items-center justify-between">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold tituloPag">Gestionar Roles</h2>
                <span
                    class="ml-4 px-3 py-1 hidden sm:inline text-[11px] font-bold uppercase rounded-full shadow-sm text-white bgPrincipal"
                >
                    {{ roles.length }} roles
                </span>
            </div>
            <button
                v-if="canDo('roles.guardar')"
                @click="abrirModalAgregar"
                class="flex items-center gap-2 bg-green-500 text-white px-5 py-2 rounded shadow hover:bg-green-600 transition"
            >
                <svg
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M12 4v16m8-8H4"
                    />
                </svg>
                Agregar
            </button>
        </div>
        <div class="py-6">
            <div class="mx-auto w-full">
                <!-- Modal para agregar/editar rol -->
                <ModalXts
                    :show="showModal"
                    :title="rolEditar ? 'Editar Rol' : 'Agregar Rol'"
                    :submitLabel="rolEditar ? 'Actualizar' : 'Registrar'"
                    :form="rolForm"
                    :endpoint="rolEditar ? `/roles/${rolEditar.id}` : '/roles'"
                    :method="rolEditar ? 'put' : 'post'"
                    :transform="transformarForm"
                    :carteras="carterasConReportes"
                    :reportes="reportes"
                    @close="cerrarModal"
                    @success="handleSuccess"
                    v-model:tabActiva="tabActiva"
                    :tabs="tabs"
                >
                    <template #default="{ form, errors }">
                        <div v-if="tabActiva === 'basico'" class="space-y-4">
                            <InputField
                                class="modalInputs"
                                label="Nombre del rol"
                                v-model="form.name"
                                placeholder="role name"
                                :error="errors.name"
                                :required="true"
                            />

                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 mb-1"
                                    >Selecciona carteras</label
                                >
                                <Multiselect
                                    v-model="form.carteras"
                                    :options="carterasConReportes"
                                    :multiple="true"
                                    :track-by="'id'"
                                    :label="'nombre'"
                                    :searchable="false"
                                    placeholder="Selecciona carteras"
                                    class="w-full"
                                />
                                <p
                                    v-if="errors.carteras"
                                    class="text-red-600 text-sm"
                                >
                                    {{ errors.carteras }}
                                </p>
                            </div>

                            <div>
                                <CarteraReportesAccordion
                                    v-if="form.carteras.length"
                                    :carteras="form.carteras"
                                    :modelValue="form.reportes"
                                    @update:modelValue="form.reportes = $event"
                                />
                                <p class="mt-2 text-xs text-gray-600">
                                    Total reportes asignados:
                                    {{ form.reportes.length }}
                                </p>
                            </div>
                        </div>

                        <div v-if="tabActiva === 'permisos'" class="space-y-4">
                            <label
                                class="block text-sm font-medium text-gray-700 mb-1"
                                >Permisos</label
                            >
                            <ModuloPermisosAccordion
                                v-model="form.permissions"
                                :permissions="permissions"
                            />
                            <p
                                v-if="errors.permissions"
                                class="text-red-600 text-sm"
                            >
                                {{ errors.permissions }}
                            </p>
                        </div>
                    </template>
                </ModalXts>

                <!-- Listado de roles -->
                <div>
                    <div
                        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"
                    >
                        <div
                            v-for="role in roles"
                            :key="role.id"
                            class="rounded-lg border border-gray-200 bg-white/90 shadow-sm hover:shadow-md transition-all p-4 flex flex-col min-h-[110px]"
                        >
                            <div class="flex justify-between items-center mb-2">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="w-8 h-8 rounded-full bgPrincipal text-white flex items-center justify-center text-base font-bold shadow"
                                    >
                                        <svg
                                            class="w-4 h-4"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            viewBox="0 0 24 24"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M5.121 17.804A9 9 0 0112 15a9 9 0 016.879 2.804M15 11a3 3 0 11-6 0 3 3 0 016 0z"
                                            />
                                        </svg>
                                    </span>
                                    <span
                                        class="text-base font-medium text-gray-900 truncate max-w-[150px]"
                                        :title="role.name"
                                    >
                                        {{ role.name }}
                                    </span>
                                </div>
                                <Actions
                                    :edit="canDo('roles.editar')"
                                    :remove="canDo('roles.eliminar')"
                                    @edit="abrirModalEditar(role)"
                                    @delete="eliminarRol(role)"
                                />
                            </div>
                            <div class="flex flex-wrap gap-1 mb-1">
                                <template v-if="role.carteras.length">
                                    <span
                                        v-for="c in role.carteras"
                                        :key="c.id"
                                        class="flex items-center bg-gray-100 border border-gray-200 rounded-full px-2 py-0.5 text-xs text-gray-700 font-medium"
                                        :title="c.nombre"
                                    >
                                        <!-- <svg
                                                class="w-3 h-3 mr-1 text-indigo-400"
                                                fill="none"
                                                stroke="currentColor"
 stroke-width="2"
                                                                                               viewBox="0 0 24 24"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M3 7h18"
                                                ></path>
                                            </svg> -->
                                        {{ c.nombre }}
                                        <span
                                            class="ml-1 w-4 h-4 rounded-full bg-indigo-50 text-indigo-700 text-[10px] font-bold flex items-center justify-center border border-indigo-100"
                                        >
                                            {{
                                                role.reportes.filter(
                                                    (r) => r.cartera_id === c.id
                                                ).length
                                            }}
                                        </span>
                                    </span>
                                </template>
                                <span
                                    v-else
                                    class="text-gray-400 italic text-xs"
                                >
                                    Sin carteras
                                </span>
                            </div>
                            <div
                                class="flex justify-between items-center border-t border-gray-100 pt-2 mt-auto"
                            >
                                <span class="text-[11px] text-gray-400">
                                    ID: {{ role.id }}
                                </span>
                                <span
                                    v-if="
                                        role.permissions &&
                                        role.permissions.length
                                    "
                                    class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-100"
                                >
                                    {{ role.permissions.length }}
                                    permiso{{
                                        role.permissions.length > 1 ? "s" : ""
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
<style scoped>
/* width */
.scrollbar-ghost::-webkit-scrollbar {
    width: 4px !important;
    height: 3px !important;
}

/* Track */
.scrollbar-ghost::-webkit-scrollbar-track {
    border-radius: 10px;
    background: transparent;
}
.scrollbar-ghost:hover::-webkit-scrollbar-track {
    box-shadow: inset 0 0 5px #7a7a7a;
}

/* Handle */
.scrollbar-ghost::-webkit-scrollbar-thumb {
    border-radius: 10px;
    background: transparent;
    transition: background 0.4s;
    animation: thumbPulse 2s ease-in-out infinite alternate;
}

/* Hover más intenso */
.scrollbar-ghost:hover::-webkit-scrollbar-thumb {
    background: rgba(99, 102, 241, 0.8);
}

/* Definimos la animación */
@keyframes thumbPulse {
    from {
        background: rgba(255, 255, 255, 0.1);
    }
    to {
        background: rgba(99, 102, 241, 0.3);
    }
}
</style>
