<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ModalXts from "@/Components/ModalXts.vue";
import Actions from "@/Components/Actions.vue";
import InputField from "@/Components/InputField.vue";
import StatusBadge from "@/Components/StatusBadge.vue";
import { Head, usePage, router } from "@inertiajs/vue3";
import { reactive, ref, computed, onMounted, watch } from "vue";
import Swal from "sweetalert2";
import Multiselect from "vue-multiselect";
import CarteraReportesAccordion from "@/Components/CarteraReportesAccordion.vue";
import "vue-multiselect/dist/vue-multiselect.css";
import FakePasswordInput from "@/Components/FakePasswordInput.vue";
const { users, carteras, reportes, roles, success } = usePage().props;

//Permisos
const page = usePage();
const { can } = page.props;
const canDo = (key) => !!can[key];

const showModal = ref(false);
const usuarioEditar = ref(null);
const tabActiva = ref("basicos");
const cargandoDesdeEdicion = ref(false);
const ignorarWatchRol = ref(false);
const usuarioForm = reactive({
    name: "",
    email: "",
    password: "",
    active: true,
    roles: null,
    customCarteras: [],
    customReportes: [],
});

const inheritedReportes = computed(() =>
    Array.isArray(usuarioForm.roles?.reportes) ? usuarioForm.roles.reportes : []
);

const carterasConReportes = computed(() =>
    usuarioForm.customCarteras.map((cSel) => {
        const full = carteras.find((c) => c.id === cSel.id);
        return { ...cSel, reportes: full?.reportes || [] };
    })
);

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
});

watch(
    () => usuarioForm.roles,
    (nuevoRol) => {
        if (!nuevoRol || cargandoDesdeEdicion.value || ignorarWatchRol.value)
            return;

        const carterasRol = nuevoRol.carteras || [];
        const reportesDesdeCarteras = [
            ...new Map(
                carterasRol
                    .flatMap((c) => c.reportes || [])
                    .map((r) => [r.id, r])
            ).values(),
        ];

        usuarioForm.customCarteras = carterasRol;
        usuarioForm.customReportes = reportesDesdeCarteras;
    }
);

function abrirModalAgregar() {
    Object.assign(usuarioForm, {
        name: "",
        email: "",
        password: "",
        active: true,
        roles: null,
        customCarteras: [],
        customReportes: [],
    });
    usuarioEditar.value = null;
    tabActiva.value = "basicos";
    showModal.value = true;
}

function abrirModalEditar(user) {
    cargandoDesdeEdicion.value = true;
    usuarioEditar.value = user;
    const rol = user.roles?.[0] || null;
    const agrupados = {};

    (user.effective_reportes || []).forEach((r) => {
        if (!agrupados[r.cartera_id]) agrupados[r.cartera_id] = [];
        agrupados[r.cartera_id].push(r);
    });

    Object.assign(usuarioForm, {
        name: user.name,
        email: user.email,
        password: "",
        active: !!user.active,
        roles: rol,
        customCarteras: Object.keys(agrupados)
            .map((id) => {
                const c = carteras.find((c) => c.id == id);
                return c ? { id: c.id, nombre: c.nombre } : null;
            })
            .filter(Boolean),
        customReportes: user.effective_reportes || [],
    });

    tabActiva.value = "basicos";
    showModal.value = true;
    setTimeout(() => (cargandoDesdeEdicion.value = false), 50);
}

function cerrarModal() {
    showModal.value = false;
    usuarioEditar.value = null;
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
    router.visit(route("users.index"), {
        preserveScroll: true,

        only: ["users", "success"],
        onFinish: cerrarModal,
    });
}

function eliminarUsuario(user) {
    Swal.fire({
        title: "¿Eliminar usuario?",
        text: `¿Estás seguro de eliminar a ${user.name}?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/users/${user.id}`, {
                onSuccess: () => {
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "success",
                        title: `Usuario ${user.name} eliminado`,
                        showConfirmButton: false,
                        timer: 2000,
                    });
                    recargar();
                },
            });
        }
    });
}

function toggleReportePersonalizado(reporte, checked) {
    if (checked) {
        if (!usuarioForm.customReportes.some((r) => r.id === reporte.id)) {
            usuarioForm.customReportes.push(reporte);
        }
    } else {
        usuarioForm.customReportes = usuarioForm.customReportes.filter(
            (r) => r.id !== reporte.id
        );
    }
}

function resetearACarterasYReportesPorDefecto() {
    if (!usuarioForm.roles) return;

    Swal.fire({
        title: "¿Restablecer permisos?",
        text: "Se reemplazarán las carteras y reportes actuales por los del rol seleccionado. ¿Deseas continuar?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, restablecer",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            ignorarWatchRol.value = true;

            // Carteras del rol
            const carterasRol = (usuarioForm.roles.carteras || [])
                .map((c) => {
                    const full = carteras.find((x) => x.id === c.id);
                    return full ? { id: full.id, nombre: full.nombre } : null;
                })
                .filter(Boolean);

            // Reportes heredados del rol (no desde carteras)
            const reportesRol = Array.isArray(usuarioForm.roles.reportes)
                ? usuarioForm.roles.reportes
                : [];

            usuarioForm.customCarteras = carterasRol;
            usuarioForm.customReportes = reportesRol;

            // Pequeño delay para evitar el watch tras el cambio
            setTimeout(() => {
                ignorarWatchRol.value = false;
            }, 100);

            Swal.fire({
                toast: true,
                position: "top-end",
                icon: "success",
                title: "Permisos restablecidos",
                showConfirmButton: false,
                timer: 1500,
            });
        }
    });
}
watch(
    () => usuarioForm.customCarteras,
    (nuevasCarteras) => {
        const idsPermitidos = nuevasCarteras.map((c) => c.id);
        usuarioForm.customReportes = usuarioForm.customReportes.filter((r) =>
            idsPermitidos.includes(r.cartera_id)
        );
    },
    { deep: true }
);
</script>

<template>
    <Head title="Listado de Usuarios" />

    <AuthenticatedLayout class="relleno">
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold tituloPag">
                        Gestionar Usuarios
                    </h2>
                    <span
                        class="ml-4 px-3 py-1 hidden sm:inline text-[11px] font-bold uppercase rounded-full shadow-sm text-white bgPrincipal"
                    >
                        {{ users.length }} usuarios
                    </span>
                </div>
                <button
                    v-if="canDo('usuarios.guardar')"
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
        </template>

        <div class="py-8">
            <ModalXts
                :show="showModal"
                :title="usuarioEditar ? 'Editar Usuario' : 'Agregar Usuario'"
                :submitLabel="usuarioEditar ? 'Actualizar' : 'Registrar'"
                :form="usuarioForm"
                :endpoint="
                    usuarioEditar ? `/users/${usuarioEditar.id}` : '/users'
                "
                :method="usuarioEditar ? 'put' : 'post'"
                :transform="
                    (form) => ({
                        ...form,
                        roles: form.roles ? [form.roles.id] : [],
                        carteras: form.customCarteras.map((c) => c.id),
                        reportes: form.customReportes.map((r) => r.id),
                    })
                "
                :tabs="[
                    { value: 'basicos', label: 'Información', icon: UserIcon },
                    {
                        value: 'avanzado',
                        label: 'Permisos',
                        icon: SettingsIcon,
                    },
                ]"
                :tabActiva="tabActiva"
                @update:tabActiva="tabActiva = $event"
                @close="cerrarModal"
                @success="handleSuccess"
            >
                <template #default="{ form, errors }">
                    <div v-if="tabActiva === 'basicos'">
                        <input
                            type="text"
                            name="fake_username"
                            autocomplete="username"
                            style="display: none"
                        />
                        <input
                            type="password"
                            name="fake_password"
                            autocomplete="new-password"
                            style="display: none"
                        />

                        <InputField
                            class="modalInputs"
                            label="Nombre"
                            v-model="form.name"
                            :error="errors.name"
                            :required="true"
                        />
                        <InputField
                            class="modalInputs"
                            label="Email"
                            v-model="form.email"
                            :error="errors.email"
                            :required="true"
                        />
                        <FakePasswordInput
                            class="modalInputs"
                            :label="
                                usuarioEditar
                                    ? 'Nueva contraseña (opcional)'
                                    : 'Contraseña'
                            "
                            v-model="form.password"
                            placeholder="Deja en blanco para no cambiar"
                            :error="errors.password"
                        />

                        <div class="modalInputs">
                            <label class="block text-sm font-medium mb-1"
                                >Estado</label
                            >
                            <select
                                v-model="form.active"
                                class="w-full border rounded px-3 py-2"
                            >
                                <option :value="true">Activo</option>
                                <option :value="false">Inactivo</option>
                            </select>
                        </div>
                        <div class="modalInputs" style="margin-top: 10px">
                            <label class="block text-sm font-medium mb-1"
                                >Rol</label
                            >
                            <Multiselect
                                v-model="usuarioForm.roles"
                                :options="roles"
                                track-by="id"
                                label="name"
                                placeholder="Selecciona un rol"
                                class="w-full"
                                :searchable="false"
                            />
                            <p v-if="errors.roles" class="text-red-600 text-sm">
                                {{ errors.roles }}
                            </p>
                        </div>
                    </div>

                    <div v-if="tabActiva === 'avanzado'">
                        <div class="mb-4">
                            <label class="block text-sm font-medium mb-1"
                                >Carteras</label
                            >
                            <Multiselect
                                v-model="form.customCarteras"
                                :options="carteras"
                                :multiple="true"
                                track-by="id"
                                label="nombre"
                                :searchable="false"
                                placeholder="Selecciona carteras…"
                                class="w-full"
                            />
                        </div>
                        <div class="mt-5 mb-6" v-if="form.roles">
                            <div
                                class="rounded-lg border border-indigo-100 bg-indigo-50 p-3"
                            >
                                <button
                                    type="button"
                                    @click="
                                        resetearACarterasYReportesPorDefecto
                                    "
                                    class="flex items-center gap-2 text-sm font-medium text-indigo-700 hover:text-indigo-900 transition"
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
                                            d="M4 4v6h6M20 20v-6h-6M4 20l6-6M20 4l-6 6"
                                        />
                                    </svg>
                                    Usar valores por defecto del rol
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium mb-1"
                                >Reportes</label
                            >
                            <CarteraReportesAccordion
                                v-model="form.customReportes"
                                :carteras="carterasConReportes"
                                :inheritedReportes="inheritedReportes"
                            />
                            <p class="mt-2 text-xs text-gray-600">
                                Total reportes asignados:
                                {{ form.customReportes.length }}
                            </p>
                        </div>
                    </div>
                </template>
            </ModalXts>

            <div
                class="overflow-x-auto rounded-xl border border-gray-100 bg-gray-50"
            >
                <table class="min-w-full text-sm text-gray-800">
                    <thead class="text-white text-xs bgPrincipal">
                        <tr>
                            <th class="px-4 py-2 text-left">ID</th>
                            <th class="px-4 py-2 text-left">Nombre</th>
                            <th class="px-4 py-2 text-left">Email</th>
                            <th class="px-4 py-2 text-left">Rol</th>
                            <th class="px-4 py-2 text-left">Estado</th>
                            <th class="px-4 py-2 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(user, i) in users"
                            :key="user.id"
                            :class="i % 2 === 0 ? 'bg-white' : 'bg-gray-50'"
                            class="bg-white even:bg-indigo-50 hover:bg-indigo-100 transition"
                        >
                            <td class="px-4 py-1.5">{{ user.id }}</td>
                            <td class="px-4 py-1.5">{{ user.name }}</td>
                            <td class="px-4 py-1.5">{{ user.email }}</td>
                            <td class="px-4 py-1.5">
                                <span
                                    v-for="r in user.roles"
                                    :key="r.id"
                                    class="inline-block text-xs bg-gray-200 text-gray-700 rounded px-2 py-0.5 mr-1"
                                >
                                    {{ r.name }}
                                </span>
                                <span
                                    v-if="!user.roles.length"
                                    class="text-gray-400"
                                    >—</span
                                >
                            </td>
                            <td class="px-4 py-1.5">
                                <StatusBadge :active="!!user.active" />
                            </td>
                            <td class="px-4 py-1.5 text-center">
                                <Actions
                                    :edit="canDo('usuarios.editar')"
                                    :remove="canDo('usuarios.eliminar')"
                                    @edit="abrirModalEditar(user)"
                                    @delete="eliminarUsuario(user)"
                                />
                            </td>
                        </tr>
                        <tr v-if="!users.length">
                            <td
                                colspan="6"
                                class="px-4 py-4 text-center text-gray-400"
                            >
                                No hay usuarios registrados.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
