<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue'
import { Link } from '@inertiajs/vue3'
</script>

<template>
  <AuthenticatedLayout>
    <div class="container mx-auto px-4 py-6">
      <!-- Título con color principal -->
      <h1 class="text-2xl font-bold mb-6 tituloPag">Panel Principal</h1>

      <p class="text-gray-600 mb-6">
        Bienvenido al sistema de gestión de chisp <strong>Waha</strong>.
      </p>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Chips -->
        <div class="bg-white shadow-md rounded-lg p-4 text-center border flex justify-between flex-col gap-5">
          <div>
            <h5 class="text-lg font-semibold mb-2">Chips</h5>
            <p class="text-gray-600 mb-4">Gestiona los chips registrados.</p>
          </div>
          <Link
            href="/prueba/chips"
            class="inline-block bg-green-500 text-white px-4 py-1 rounded hover:bg-green-600 text-sm transition"
          >
            Ver Chips
          </Link>
        </div>

        <!-- Compañías -->
        <div class="bg-white shadow-md rounded-lg p-4 text-center border flex justify-between flex-col gap-5">
          <div>
            <h5 class="text-lg font-semibold mb-2">Compañías</h5>
            <p class="text-gray-600 mb-4">Administra las compañías de telefonía.</p>
          </div>
          <Link
            href="/prueba/companias"
            class="inline-block bg-green-500 text-white px-4 py-1 rounded hover:bg-green-600 text-sm transition"
          >
            Ver Compañías
          </Link>
        </div>

        <!-- Estados -->
        <div class="bg-white shadow-md rounded-lg p-4 text-center border flex justify-between flex-col gap-5">
          <div>
            <h5 class="text-lg font-semibold mb-2">Estados</h5>
            <p class="text-gray-600 mb-4">Define los estados de los chips.</p>
          </div>
          <Link
            href="/prueba/estados"
            class="inline-block bg-green-500 text-white px-4 py-1 rounded hover:bg-green-600 text-sm transition"
          >
            Ver Estados
          </Link>
        </div>

        <!-- Usuarios -->
        <div class="bg-white shadow-md rounded-lg p-4 text-center border flex justify-between flex-col gap-5">
          <div>
            <h5 class="text-lg font-semibold mb-2">Usuarios</h5>
            <p class="text-gray-600 mb-4">Gestiona los usuarios del sistema.</p>
          </div>
          <Link
            href="/prueba/usuarios"
            class="inline-block bg-green-500 text-white px-4 py-1 rounded hover:bg-green-600 text-sm transition"
          >
            Ver Usuarios
          </Link>
        </div>

        <!-- Registros -->
        <div class="bg-white shadow-md rounded-lg p-4 text-center border flex justify-between flex-col gap-5 col-span-full sm:col-span-2 lg:col-span-1">
          <div>
            <h5 class="text-lg font-semibold mb-2">Registros</h5>
            <p class="text-gray-600 mb-4">Consulta los cambios realizados.</p>
          </div>
          <Link
            href="/prueba/registros"
            class="inline-block bg-green-500 text-white px-4 py-1 rounded hover:bg-green-600 text-sm transition"
          >
            Ver Registros
          </Link>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
