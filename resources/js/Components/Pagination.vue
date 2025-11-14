<template>
    <div class="flex justify-center mt-4">
        <nav
            class="inline-flex rounded-md shadow-sm overflow-hidden border border-gray-200"
            aria-label="Pagination"
        >
            <template v-for="(link, i) in links" :key="i">
                <a
                    v-if="link.url"
                    :href="link.url"
                    v-html="link.label"
                    class="px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 transition"
                    :class="{
                        'bg-indigo-100 text-indigo-700 font-semibold':
                            link.active,
                        'rounded-l-md': i === 0,
                        'rounded-r-md': i === links.length - 1,
                    }"
                />
                <span
                    v-else
                    v-html="link.label"
                    class="px-3 py-2 text-sm text-gray-400 cursor-not-allowed"
                />
            </template>
        </nav>
    </div>
</template>

<script setup>
defineProps({
    links: {
        type: Array,
        required: true,
    },
});
</script>
