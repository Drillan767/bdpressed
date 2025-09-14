<script setup lang="ts">
import type { ChartData } from '@/types/statistics'
import { computed } from 'vue'
import { useDisplay } from 'vuetify'

defineProps<ChartData>()

const tooltip = { subtitleFormat: '[value]%' }

const { mobile } = useDisplay()

const legend = computed<{ position: 'right' | 'bottom' }>(() => {
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
        <template #legend="{ items: legends, isActive, toggle }">
            <VList
                class="py-0 mb-n5 mb-md-0 bg-transparent"
                density="compact"
                width="300"
            >
                <VListItem
                    v-for="item in legends"
                    :key="item.key"
                    :class="{ 'opacity-40': !isActive(item) }"
                    :title="item.title"
                    class="my-1"
                    rounded="lg"
                    link
                    @click="toggle(item)"
                >
                    <template #prepend>
                        <VAvatar
                            :color="item.color"
                            :size="16"
                        />
                    </template>
                    <template #append>
                        <div class="font-weight-bold">
                            {{ item.value }}%
                        </div>
                    </template>
                </VListItem>
            </VList>
        </template>
    </VPie>
</template>
