<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { onMounted, ref } from 'vue'
import { route } from 'ziggy-js'

const tab = ref('site')

onMounted(() => {
    const path = window.location.pathname
    if (path.endsWith('/illustration')) {
        tab.value = 'illustration'
    }
    else {
        tab.value = 'site'
    }
})
</script>

<template>
    <VCard
        prepend-icon="mdi-cog-outline"
        title="Paramètres"
    >
        <template #text>
            <VTabs
                v-model="tab"
            >
                <VTab
                    prepend-icon="mdi-application-cog-outline"
                    text="Site"
                    value="site"
                    @click="router.visit(route('settings.website'))"
                />
                <VTab
                    prepend-icon="mdi-palette-outline"
                    text="Illustrations"
                    value="illustration"
                    @click="router.visit(route('settings.illustration'))"
                />
            </VTabs>

            <slot />
        </template>
    </VCard>
</template>
