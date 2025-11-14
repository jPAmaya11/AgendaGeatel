<template>
    <span
        :class="badgeClass"
        class="inline-block px-3 py-1 rounded-full text-xs font-semibold"
    >
        {{ active ? "Activo" : "Inactivo" }}
    </span>
</template>

<script setup>
import { computed } from "vue";
const props = defineProps({ active: Boolean });

const badgeClass = computed(() =>
    props.active ? "bg-green-100 text-green-700" : "bg-red-100 text-red-700"
);
</script>
