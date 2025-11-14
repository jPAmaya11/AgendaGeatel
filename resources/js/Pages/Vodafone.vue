<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import Swal from "sweetalert2";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ModalGestion from "@/Components/ModalGestion.vue";
import InputField from "@/Components/InputField.vue";
import Actions from "@/Components/Actions.vue";

const pageProps = usePage().props;
const pagination = computed(() => pageProps.items);
const items = computed(() => pagination.value?.data || []);
const success = pageProps.success;
const canViewGlobal = pageProps.canViewGlobal;
const can = usePage().props.can || {};
const canDo = (key) => !!can[key];

const showModal = ref(false);
const registroEditar = ref(null);
const isLoading = ref(false);
const filters = usePage().props.filters || {};
const search = ref(filters.search || "");

const form = ref({
    nombre_cliente: "",
    dni_nif_cif: "",
    telefono_contacto: "",
    direccion_instalacion: "",
    operador_actual: "",
    oferta_comercial: "",
    observacion_smart: "",
    tipificaciones: "",
});

onMounted(() => {
    if (success) {
        Swal.fire({
            toast: true,
            icon: "success",
            title: success,
            position: "top-end",
            timer: 2000,
            showConfirmButton: false,
        });
    }
});

function abrirModalAgregar() {
    registroEditar.value = null;
    form.value = {
        nombre_cliente: "",
        dni_nif_cif: "",
        telefono_contacto: "",
        direccion_instalacion: "",
        operador_actual: "",
        oferta_comercial: "",
        observacion_smart: "",
        tipificaciones: "",
    };
    showModal.value = true;
}

function abrirModalEditar(item) {
    registroEditar.value = item;
    form.value = { ...item };
    showModal.value = true;
}

function cerrarModal() {
    showModal.value = false;
    registroEditar.value = null;
}

function handleSuccess(msg) {
    Swal.fire({
        toast: true,
        icon: "success",
        title: msg,
        position: "top-end",
        timer: 2000,
        showConfirmButton: false,
    });
    router.visit(route("vodafone.index"), {
        preserveScroll: true,
        only: ["items", "success"],
        onFinish: cerrarModal,
    });
}

function eliminar(item) {
    Swal.fire({
        title: "¿Eliminar registro?",
        text: `¿Estás seguro de eliminar a ${item.nombre_cliente}?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, eliminar",
    }).then((res) => {
        if (res.isConfirmed) {
            router.delete(`/vodafone/${item.id}`, {
                onSuccess: () => handleSuccess("Registro eliminado"),
                onError: () =>
                    Swal.fire({
                        toast: true,
                        icon: "error",
                        title: "Error al eliminar",
                        position: "top-end",
                        timer: 2000,
                        showConfirmButton: false,
                    }),
            });
        }
    });
}

function mostrarInfoUsuario(user) {
    Swal.fire({
        title: user.name,
        html: `<b>Email:</b> ${user.email}<br><b>Rol:</b> ${
            user.roles?.map((r) => r.name).join(", ") || "Sin rol"
        }`,
        icon: "info",
    });
}

function parseLabel(label) {
    if (label === "&laquo; Previous") return "«";
    if (label === "Next &raquo;") return "»";
    return label.replace(/&laquo;|&raquo;/g, "").trim();
}

function limpiarBusqueda() {
    search.value = "";
}

// 🟢 Nuevo: búsqueda en tiempo real con loading
watch(search, async (val) => {
    isLoading.value = true;

    await router.get(
        route("vodafone.index"),
        {
            search: val,
            page: 1,
        },
        {
            replace: true,
            preserveScroll: true,
            onFinish: () => {
                isLoading.value = false;
            },
        }
    );
});
</script>

<template>
    <AuthenticatedLayout class="relleno">
        <template #header>
            <div
                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
            >
                <!-- Título centrado en móvil -->
                <h2 class="text-xl font-semibold tituloPag">
                    Gestionar Vodafone
                </h2>

                <!-- Input con buscador y spinner -->
                <div
                    class="flex flex-col sm:flex-row items-stretch gap-3 sm:gap-4 mt-4 sm:mt-0"
                >
                    <!-- Input con lupa -->
                    <div class="relative sm:max-w-xs">
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Buscar.."
                            class="w-full pl-10 pr-10 py-2 rounded-md border border-gray-200 bg-white shadow-sm text-sm placeholder-gray-400 focus:ring-2 focus:ring-indigo-500 focus:outline-none focus:border-indigo-300 transition"
                        />

                        <!-- Ícono lupa -->
                        <svg
                            class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M21 21l-4.35-4.35M10 18a8 8 0 1 1 0-16 8 8 0 0 1 0 16z"
                            />
                        </svg>

                        <!-- Botón limpiar (centrado) -->
                        <button
                            v-if="search"
                            @click="limpiarBusqueda"
                            class="absolute right-7 top-1/2 -translate-y-1/2 text-gray-500 hover:text-red-500 text-base leading-none"
                            title="Limpiar búsqueda"
                        >
                            &times;
                        </button>

                        <!-- Spinner de puntos (centrado) -->
                        <span
                            v-if="isLoading"
                            class="absolute right-2 top-1/2 -translate-y-1/2 flex space-x-0.5"
                        >
                            <span
                                class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce [animation-delay:-0.3s]"
                            ></span>
                            <span
                                class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce [animation-delay:-0.15s]"
                            ></span>
                            <span
                                class="w-1.5 h-1.5 bg-gray-400 rounded-full animate-bounce"
                            ></span>
                        </span>
                    </div>
                    <!-- Botón agregar -->
                    <button
                        v-if="canDo('vodafone.crear')"
                        @click="abrirModalAgregar"
                        class="flex items-center justify-center gap-2 bg-green-500 text-white px-5 py-2 rounded-md shadow hover:bg-green-600 transition text-sm font-semibold"
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
            </div>
        </template>

        <div class="py-6">
            <div
                class="overflow-x-auto rounded-xl border border-gray-100 bg-gray-50"
            >
                <table class="min-w-full text-sm text-gray-800">
                    <thead class="text-white text-xs bgPrincipal">
                        <tr>
                            <th class="px-4 py-2 text-left">ID</th>
                            <th class="px-4 py-2 text-left">DNI</th>
                            <th class="px-4 py-2 text-left">Cliente</th>
                            <th class="px-4 py-2 text-left">Teléfono</th>
                            <th class="px-4 py-2 text-left">Dirección</th>
                            <th class="px-4 py-2 text-left">Operador Actual</th>
                            <th class="px-4 py-2 text-left">
                                Oferta Comercial
                            </th>
                            <th class="px-4 py-2 text-left">
                                Observación SMART
                            </th>
                            <th class="px-4 py-2 text-left">Tipificaciones</th>
                            <th
                                v-if="canViewGlobal"
                                class="px-4 py-2 text-center"
                            >
                                Usuario
                            </th>
                            <th class="px-4 py-2 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in items"
                            :key="item.id"
                            class="bg-white even:bg-indigo-50 hover:bg-indigo-100 transition"
                        >
                            <td class="px-4 py-1.5 text-[13px]">
                                {{ item.id }}
                            </td>
                            <td class="px-4 py-1.5 text-[13px]">
                                {{ item.dni_nif_cif }}
                            </td>
                            <td class="px-4 py-1.5 text-[13px]">
                                {{ item.nombre_cliente }}
                            </td>
                            <td class="px-4 py-1.5 text-[13px]">
                                {{ item.telefono_contacto }}
                            </td>
                            <td class="px-4 py-1.5 text-[13px]">
                                {{ item.direccion_instalacion }}
                            </td>
                            <td class="px-4 py-1.5 text-[13px]">
                                {{ item.operador_actual }}
                            </td>
                            <td class="px-4 py-1.5 text-[13px]">
                                {{ item.oferta_comercial }}
                            </td>
                            <td class="px-4 py-1.5 text-[13px]">
                                {{ item.observacion_smart }}
                            </td>
                            <td class="px-4 py-1.5 text-[13px]">
                                {{ item.tipificaciones }}
                            </td>
                            <td
                                v-if="canViewGlobal"
                                class="px-4 py-1.5 text-center"
                            >
                                {{ item.user ? item.user.name : "Sin usuario" }}
                                <button
                                    v-if="item.user"
                                    @click="mostrarInfoUsuario(item.user)"
                                    class="text-blue-600 hover:text-blue-800"
                                    title="Ver info usuario"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-5 w-5 inline"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                    >
                                        <circle
                                            cx="12"
                                            cy="12"
                                            r="10"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            fill="none"
                                        />
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M12 16v-4m0-4h.01"
                                        />
                                    </svg>
                                </button>
                            </td>
                            <td class="px-4 py-1.5 text-center text-[13px]">
                                <Actions
                                    :edit="canDo('vodafone.editar')"
                                    :remove="canDo('vodafone.eliminar')"
                                    @edit="abrirModalEditar(item)"
                                    @delete="eliminar(item)"
                                />
                            </td>
                        </tr>
                        <tr v-if="items.length === 0">
                            <td
                                :colspan="canViewGlobal ? 11 : 10"
                                class="text-center py-4 text-gray-400 text-lg"
                            >
                                No hay registros.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Paginación -->
        <div
            class="flex justify-center items-center flex-wrap gap-2 mt-4"
            v-if="pagination.links && pagination.links.length > 1"
        >
            <template v-for="(link, index) in pagination.links" :key="index">
                <button
                    v-if="link.url"
                    @click="router.get(link.url, { search: search.value })"
                    class="px-3 py-1 min-w-[32px] rounded border text-sm transition"
                    :class="{
                        'bg-indigo-600 text-white font-bold': link.active,
                        'bg-white text-gray-800 hover:bg-indigo-50':
                            !link.active,
                    }"
                >
                    {{ parseLabel(link.label) }}
                </button>
                <span
                    v-else
                    class="px-3 py-1 min-w-[32px] rounded border bg-gray-200 text-gray-500 text-sm cursor-not-allowed"
                >
                    {{ parseLabel(link.label) }}
                </span>
            </template>
        </div>

        <!-- Modal -->
        <ModalGestion
            :show="showModal"
            :title="registroEditar ? 'Editar Registro' : 'Nuevo Registro'"
            submitLabel="Guardar"
            :initialForm="form"
            :endpoint="
                registroEditar ? `/vodafone/${registroEditar.id}` : '/vodafone'
            "
            :method="registroEditar ? 'put' : 'post'"
            @close="cerrarModal"
            @success="handleSuccess"
        >
            <template #default="{ form: slotForm, errors }">
                <InputField
                    class="modalInputs"
                    label="Nombre Cliente"
                    v-model="slotForm.nombre_cliente"
                    name="nombre_cliente"
                    :error="errors.nombre_cliente"
                    required
                />
                <div class="flex justify-between width-49">
                    <InputField
                        class="modalInputs"
                        label="DNI / NIF / CIF"
                        v-model="slotForm.dni_nif_cif"
                        name="dni_nif_cif"
                        :error="errors.dni_nif_cif"
                        type="number"
                        required
                    />
                    <InputField
                        class="modalInputs"
                        label="Teléfono"
                        v-model="slotForm.telefono_contacto"
                        name="telefono_contacto"
                        type="number"
                        required
                        :error="errors.telefono_contacto"
                    />
                </div>
                <InputField
                    class="modalInputs"
                    label="Dirección"
                    v-model="slotForm.direccion_instalacion"
                    name="direccion_instalacion"
                    :error="errors.direccion_instalacion"
                    required
                />
                <InputField
                    class="modalInputs"
                    label="Operador Actual"
                    v-model="slotForm.operador_actual"
                    name="operador_actual"
                    :error="errors.operador_actual"
                />
                <InputField
                    class="modalInputs"
                    label="Oferta Comercial"
                    v-model="slotForm.oferta_comercial"
                    name="oferta_comercial"
                    :error="errors.oferta_comercial"
                />
                <div class="flex justify-between width-49">
                    <InputField
                        class="modalInputs"
                        label="Observación SMART"
                        v-model="slotForm.observacion_smart"
                        name="observacion_smart"
                        :error="errors.observacion_smart"
                    />
                    <InputField
                        class="modalInputs"
                        label="Tipificaciones"
                        v-model="slotForm.tipificaciones"
                        name="tipificaciones"
                        :error="errors.tipificaciones"
                    />
                </div>
            </template>
        </ModalGestion>
    </AuthenticatedLayout>
</template>
