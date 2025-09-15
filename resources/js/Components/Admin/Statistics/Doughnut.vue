<script setup lang="ts">
import type { ChartData } from '@/types/statistics'
import { computed } from 'vue'
import { useDisplay } from 'vuetify'

const props = defineProps<ChartData>()

const tooltip = { subtitleFormat: '[value]%' }

const { mobile } = useDisplay()

const legend = computed<{ position: 'right' | 'bottom' } | undefined>(() => {
    if (!props.showLegend)
        return undefined
    return {
        position: mobile.value ? 'bottom' : 'right',
    }
})
</script>

<template>
    <VPie
        :items
        :legend
        :tooltip
        inner-cut="70"
        item-key="id"
        gap="2"
        rounded="2"
        reveal
        animation
        hide-slice
    >
        <template #center>
            <div class="text-center">
                <div class="text-h5">
                    {{ centerText }}
                </div>
                <div class="opacity-70 mt-1 mb-n1">
                    {{ centerLabel }}
                </div>
            </div>
        </template>
    </VPie>
</template>
