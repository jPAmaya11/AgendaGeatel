<template>
    <AuthenticatedLayout class="relleno">
        <template #header>
            <div class="flex items-center justify-between">
                <div class="flex items-center justify-between">
                    <h2 class="text-xl font-semibold tituloPag">
                        Gestionar Carteras
                    </h2>
                    <!-- <span
                        class="ml-4 px-3 py-1 hidden sm:inline text-[11px] font-bold uppercase rounded-full shadow-sm text-white bgPrincipal"
                    >
                        {{ carteras.length }} Itens
                    </span> -->
                </div>

                <button
                    v-if="canDo('carteras.guardar')"
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

        <div class="py-6">
            <div
                class="overflow-x-auto rounded-xl border border-gray-100 bg-gray-50"
            >
                <table class="min-w-full text-sm text-gray-800">
                    <thead class="text-white text-xs bgPrincipal">
                        <tr>
                            <th class="px-4 py-2 text-left">ID</th>
                            <th class="px-4 py-2 text-left">Nombre</th>
                            <th class="px-4 py-2 text-left">Descripción</th>
                            <th class="px-4 py-2 text-left">Orden</th>
                            <th class="px-4 py-2 text-left">Estado</th>
                            <th class="px-4 py-2 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="cartera in carteras"
                            :key="cartera.id"
                            class="bg-white even:bg-indigo-50 hover:bg-indigo-100 transition"
                        >
                            <td class="px-4 py-1.5 text-gray-700 text-[13px]">
                                {{ cartera.id }}
                            </td>
                            <td class="px-4 py-1.5 text-gray-800 text-[13px]">
                                {{ cartera.nombre }}
                            </td>
                            <td class="px-4 py-1.5 text-gray-700 text-[13px]">
                                {{ cartera.descripcion }}
                            </td>
                            <td class="px-4 py-1.5 text-gray-700 text-[13px]">
                                {{ cartera.orden }}
                            </td>
                            <td class="px-4 py-1.5 text-gray-700 text-[13px]">
                                <StatusBadge :active="!!cartera.estado" />
                            </td>
                            <td class="px-4 py-1.5 text-center text-[13px]">
                                <Actions
                                    :edit="canDo('carteras.editar')"
                                    :remove="canDo('carteras.eliminar')"
                                    @edit="abrirModalEditar(cartera)"
                                    @delete="eliminarCartera(cartera)"
                                />
                            </td>
                        </tr>
                        <tr v-if="carteras.length === 0">
                            <td
                                colspan="6"
                                class="text-center py-4 text-gray-400 text-lg"
                            >
                                No hay carteras registradas.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <ModalGestion
            :show="showModal"
            :title="carteraEditar ? 'Editar Cartera' : 'Agregar Cartera'"
            submitLabel="Guardar"
            :initialForm="carteraForm"
            :endpoint="
                carteraEditar ? `/carteras/${carteraEditar.id}` : '/carteras'
            "
            :method="carteraEditar ? 'put' : 'post'"
            @close="cerrarModal"
            @success="handleSuccess"
        >
            <template #default="{ form, errors }">
                <InputField
                    class="modalInputs"
                    label="Nombre"
                    v-model="form.nombre"
                    name="cartera_nombre"
                    placeholder="Telefonía, Etc"
                    :error="errors.nombre"
                    :required="true"
                />
                <InputField
                    class="modalInputs"
                    label="Descripción"
                    v-model="form.descripcion"
                    name="cartera_descripcion"
                    type="textarea"
                    placeholder="Breve descripción"
                    :error="errors.descripcion"
                />
                <div class="flex" style="justify-content: space-between">
                    <div style="width: 48%">
                        <InputField
                            class="modalInputs"
                            label="Orden"
                            v-model="form.orden"
                            name="cartera_orden"
                            type="number"
                            :min="1"
                            :error="errors.orden"
                        />
                    </div>
                    <div style="width: 48%" class="modalInputs">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                            for="estado"
                            >Estado</label
                        >
                        <select
                            id="estado"
                            v-model="form.estado"
                            class="border border-gray-300 px-3 py-2 rounded-lg w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                        >
                            <option :value="true">Activa</option>
                            <option :value="false">Inactiva</option>
                        </select>
                    </div>
                </div>
            </template>
        </ModalGestion>
    </AuthenticatedLayout>
</template>

<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ModalGestion from "@/Components/ModalGestion.vue";
import Actions from "@/Components/Actions.vue";
import InputField from "@/Components/InputField.vue";
import StatusBadge from "@/Components/StatusBadge.vue";
import { usePage, router } from "@inertiajs/vue3";
import { ref, onMounted } from "vue";
import Swal from "sweetalert2";

//Permisos
const page = usePage();
const { can } = page.props;
const canDo = (key) => !!can[key];

const carteras = ref(usePage().props.carteras);
const success = usePage().props.success;
const showModal = ref(false);
const carteraEditar = ref(null);
const carteraForm = ref({
    nombre: "",
    descripcion: "",
    orden: 0,
    estado: true,
});

onMounted(() => {
    if (success) {
        Swal.fire({
            toast: true,
            position: "top-end",
            icon: "success",
            title: success,
            showConfirmButton: false,
            timer: 2000,
        });
    }
});

function abrirModalAgregar() {
    carteraEditar.value = null;
    carteraForm.value = { nombre: "", descripcion: "", orden: 0, estado: true };
    showModal.value = true;
}

function abrirModalEditar(cartera) {
    carteraEditar.value = cartera;
    carteraForm.value = {
        nombre: cartera.nombre,
        descripcion: cartera.descripcion,
        orden: cartera.orden,
        estado: !!cartera.estado,
    };
    showModal.value = true;
}

function cerrarModal() {
    showModal.value = false;
    carteraEditar.value = null;
}

function handleSuccess(message) {
    Swal.fire({
        toast: true,
        position: "top-end",
        icon: "success",
        title: message,
        showConfirmButton: false,
        timer: 2000,
    });
    recargar();
}

function recargar() {
    router.visit(route("carteras.index"), {
        preserveScroll: true,
        only: ["carteras", "success"],
        onFinish: () => {
            carteras.value = usePage().props.carteras;
            cerrarModal();
        },
    });
}

function eliminarCartera(cartera) {
    Swal.fire({
        title: "¿Eliminar cartera?",
        text: `¿Estás seguro de eliminar "${cartera.nombre}"?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
    }).then((result) => {
        if (result.isConfirmed) {
            router.delete(`/carteras/${cartera.id}`, {
                onSuccess: () => handleSuccess("Cartera eliminada"),
                onError: () => {
                    Swal.fire({
                        toast: true,
                        position: "top-end",
                        icon: "error",
                        title: "No se pudo eliminar la cartera",
                        showConfirmButton: false,
                        timer: 2000,
                    });
                },
            });
        }
    });
}
</script>
