<script setup lang="ts">
import type { IllustrationSpecs } from '@/types'

defineProps<{
    illustrations: IllustrationSpecs[]
}>()
</script>

<template>
    <VContainer>
        <VRow>
            <VCol>
                Illustrations
            </VCol>
        </VRow>
    </VContainer>
</template>
