<!-- Mejora propuesta al componente SvgUploader.vue para mayor control desde el padre -->

<!-- src/Components/SvgUploader.vue -->
<script setup>
import { ref, watch, onMounted } from "vue";

const props = defineProps({
    modelValue: String,
    error: String,
    maxHeight: {
        type: [String, Number],
        default: "192px", // max-h-48
    },
});

const emit = defineEmits(["update:modelValue"]);

const svgPreview = ref(props.modelValue || "");
const dropZone = ref(null);

watch(
    () => props.modelValue,
    (val) => {
        svgPreview.value = val;
    }
);

function handlePaste(event) {
    const pasted = event.clipboardData.getData("text/plain");
    if (pasted?.startsWith("<svg")) {
        svgPreview.value = pasted;
        emit("update:modelValue", pasted);
    }
}

function handleDrop(event) {
    event.preventDefault();
    const file = event.dataTransfer.files[0];
    if (file && file.type === "image/svg+xml") {
        const reader = new FileReader();
        reader.onload = (e) => {
            svgPreview.value = e.target.result;
            emit("update:modelValue", svgPreview.value);
        };
        reader.readAsText(file);
    }
}

function preventDefaults(e) {
    e.preventDefault();
}

onMounted(() => {
    document.addEventListener("paste", handlePaste);
});
</script>

<template>
    <!-- SvgUploader.vue -->
    <div
        ref="dropZone"
        class="relative border-2 border-dashed rounded-lg p-4 cursor-pointer hover:border-indigo-400 transition bg-white"
        :class="{ 'border-red-500': error }"
        @dragover="preventDefaults"
        @dragenter="preventDefaults"
        @drop="handleDrop"
    >
        <div class="text-sm text-gray-500 text-center">
            <div v-if="!svgPreview" class="py-4">
                Pega un SVG o arrástralo aquí
            </div>
            <div
                v-else
                class="inline-flex items-center justify-center border rounded bg-gray-50 p-2"
            >
                <div
                    class="w-6 h-6"
                    v-html="svgPreview"
                    style="display: inline-block"
                />
            </div>
        </div>
        <input type="hidden" :value="svgPreview" />
        <p v-if="error" class="text-red-600 text-sm mt-1">{{ error }}</p>
    </div>
</template>
