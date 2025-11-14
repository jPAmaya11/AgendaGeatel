<template>
    <img :src="logoSrc" alt="Geatel Telecon" />
</template>

<script setup>
import { computed } from "vue";
const props = defineProps({
    variant: {
        type: String,
        default: "default", // puede ser "default" o "white"
    },
});

const logoSrc = computed(() => {
    return props.variant === "white"
        ? "https://geatel-telecom.com/wp-content/uploads/2023/07/logogeatelblanco2.png"
        : "https://control.financiera.geatel-telecom.com/assets/logo-9d776287.png";
});
</script>
