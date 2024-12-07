<script setup lang="ts">
import { useHead } from '@vueuse/head'

useHead({
    title: '404 - Page non trouvée',
})
</script>

<template>
    <VContainer>
        <VRow>
            <VCol class="d-flex justify-center">
                <h1>404 - Page non trouvée</h1>
            </VCol>
        </VRow>
    </VContainer>
</template>
