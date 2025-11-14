<script setup>
import { ref, watch, computed } from "vue";
import { onClickOutside } from "@vueuse/core";

const props = defineProps({
    modelValue: [String, Number, null],
    items: {
        type: Array,
        required: true,
        // Debe ser: [{ label: 'Texto visible', value: 123 }]
    },
    placeholder: {
        type: String,
        default: "Buscar...",
    },
    showAll: {
        type: Boolean,
        default: false,
    },
    labelAll: {
        type: String,
        default: "Todos",
    },
});

const emit = defineEmits(["update:modelValue"]);
const wrapperRef = ref(null);
const open = ref(false);
const search = ref("");

onClickOutside(wrapperRef, () => (open.value = false));

const filtrados = computed(() => {
    const term = search.value.toLowerCase();
    let lista = props.items;

    if (term) {
        lista = lista.filter((item) => item.label.toLowerCase().includes(term));
    }

    if (props.showAll) {
        return [{ label: props.labelAll, value: null }, ...lista];
    }

    return lista;
});

function seleccionar(item) {
    emit("update:modelValue", item.value);
    open.value = false;
    search.value = item.label;
}

// Prellenar texto en input
watch(
    () => props.modelValue,
    (val) => {
        const actual = props.items.find((x) => x.value === val);
        search.value =
            actual?.label ||
            (props.showAll && val === null ? props.labelAll : "");
    },
    { immediate: true }
);
</script>

<template>
    <div class="relative w-full" ref="wrapperRef">
        <input
            type="text"
            v-model="search"
            @focus="open = true"
            @input="open = true"
            :placeholder="placeholder"
            class="w-full border border-gray-300 px-4 py-2 rounded-md shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400"
        />

        <ul
            v-if="open"
            class="absolute z-50 bg-white border border-gray-200 rounded-md mt-1 w-full shadow-lg max-h-60 overflow-y-auto text-sm"
        >
            <li
                v-for="item in filtrados"
                :key="item.value ?? '__all__'"
                class="px-4 py-2 hover:bg-indigo-50 cursor-pointer"
                @click="seleccionar(item)"
            >
                {{ item.label }}
            </li>
            <li
                v-if="filtrados.length === 0"
                class="px-4 py-2 text-gray-400 italic"
            >
                Sin resultados
            </li>
        </ul>
    </div>
</template>
