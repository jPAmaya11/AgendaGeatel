<script setup lang="ts">
// @ts-nocheck
const props = defineProps({
    label: String,
    modelValue: [String, Number],
    name: String,
    type: {
        type: String,
        default: "text",
    },
    placeholder: String,
    autocomplete: {
        type: String,
        default: "off",
    },
    min: Number,
    error: String,
    required: Boolean,
});

const emit = defineEmits(["update:modelValue"]);
</script>

<template>
    <div class="mb-3">
        <label
            v-if="label"
            class="block text-sm font-medium text-gray-700 mb-1"
        >
            {{ label }}
        </label>

        <template v-if="type === 'textarea'">
            <textarea
                :name="name"
                :placeholder="placeholder"
                :autocomplete="autocomplete"
                :value="modelValue"
                @input="emit('update:modelValue', $event.target.value)"
                :required="required"
                class="border border-gray-300 px-3 py-2 rounded-lg w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none resize-none"
            ></textarea>
        </template>

        <template v-else>
            <input
                :type="type"
                :name="name"
                :placeholder="placeholder"
                :autocomplete="autocomplete"
                :min="min"
                :value="modelValue"
                @input="emit('update:modelValue', $event.target.value)"
                :required="required"
                autocomplete="off
                "
                class="border border-gray-300 px-3 py-2 rounded-lg w-full focus:ring-2 focus:ring-indigo-400 focus:outline-none"
            />
        </template>

        <div v-if="error" class="text-red-500 text-xs mt-1">
            {{ error }}
        </div>
    </div>
</template>
