<script setup lang="ts">
import type { IllustrationSpecs } from '@/types'

defineProps<{
    illustration: IllustrationSpecs
}>()
</script>

<template>
    <VContainer>
        <VRow>
            <VCol>
                <p>Illustration.</p>
            </VCol>
        </VRow>
    </VContainer>
</template>
