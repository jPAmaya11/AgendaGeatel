<script setup>
defineProps({
    label: {
        type: String,
        required: true,
    },
    show: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div
        v-if="show"
        class="toolTip invisible absolute opacity-0 transition-opacity duration-300 group-hover:visible group-hover:opacity-100 left-full top-1/2 z-50"
        style="
            pointer-events: none;
            transform: translateY(-50%) translateX(10px);
        "
    >
        <div class="relative flex items-center justify-center">
            <div
                class="text-gray-800 whitespace-nowrap rounded-lg px-4 py-2 text-sm shadow-lg border border-gray-200"
                style="background: rgb(95, 97, 255); min-width: max-content"
            >
                {{ label }}
            </div>
            <span
                class="absolute left-0 -translate-x-full top-1/2 -mt-2 w-0 h-0"
                style="
                    border-top: 8px solid transparent;
                    border-bottom: 8px solid transparent;
                    border-right: 10px solid rgb(95, 97, 255);
                    filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.08));
                    left: 3px;
                "
            ></span>
        </div>
    </div>
</template>
