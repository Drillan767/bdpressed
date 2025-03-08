<script setup lang="ts">
import useIllustrationStore from '@/Stores/illustrationStore'
import { storeToRefs } from 'pinia'

const { recap } = storeToRefs(useIllustrationStore())
</script>

<template>
    <h2>Récapitulatif</h2>
    <VList :items="recap">
        <template #append="{ item }">
            <p>{{ item.append }}</p>
        </template>
    </VList>
</template>
