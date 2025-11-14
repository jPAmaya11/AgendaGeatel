<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from "vue";

const props = defineProps({
    align: {
        type: String,
        default: "right",
    },
    minWidth: {
        type: String,
        default: "180px", // Mínimo aceptable
    },
    maxWidth: {
        type: String,
        default: "220px", // Máximo aceptable
    },
    contentClasses: {
        type: String,
        default: "py-1 bg-white",
    },
});

const open = ref(false);
const triggerRef = ref(null);
const dropdownRef = ref(null);

const closeOnEscape = (e) => {
    if (open.value && e.key === "Escape") {
        open.value = false;
    }
};

onMounted(() => {
    document.addEventListener("keydown", closeOnEscape);
});
onUnmounted(() => {
    document.removeEventListener("keydown", closeOnEscape);
});

watch(open, (isOpen) => {
    if (isOpen && triggerRef.value && dropdownRef.value) {
        const triggerWidth = triggerRef.value.offsetWidth;

        // Aplicamos límites suaves entre min y max
        const width = Math.max(
            parseInt(props.minWidth),
            Math.min(triggerWidth, parseInt(props.maxWidth))
        );

        dropdownRef.value.style.width = `${width}px`;
    }
});

const alignmentClasses = computed(() => {
    if (props.align === "left") {
        return "ltr:origin-top-left rtl:origin-top-right start-0";
    } else if (props.align === "right") {
        return "ltr:origin-top-right rtl:origin-top-left end-0";
    } else {
        return "origin-top";
    }
});
</script>

<template>
    <div class="relative">
        <!-- Trigger -->
        <div ref="triggerRef" @click="open = !open">
            <slot name="trigger" />
        </div>

        <!-- Overlay -->
        <div
            v-show="open"
            class="fixed inset-0 z-40"
            @click="open = false"
        ></div>

        <!-- Dropdown -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                ref="dropdownRef"
                v-show="open"
                class="absolute z-50 mt-2 rounded-md shadow-lg"
                :class="alignmentClasses"
                style="display: none"
                @click="open = false"
            >
                <div
                    class="rounded-md ring-1 ring-black ring-opacity-5"
                    :class="contentClasses"
                >
                    <slot name="content" />
                </div>
            </div>
        </Transition>
    </div>
</template>
