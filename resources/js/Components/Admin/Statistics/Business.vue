<script setup lang="ts">
import type { BusinessApiData, ChartData, StatCard as StarCardType } from '@/types/statistics'
import { useStatistics } from '@/Composables/statistics'
import { onMounted, ref } from 'vue'
import Doughnut from './Doughnut.vue'
import StatCard from './StatCard.vue'

const { transformBusinessStatistics } = useStatistics()

const cards = ref<StarCardType[]>([])
const charts = ref<ChartData[]>([])
const loading = ref(false)

async function loadData() {
    loading.value = true
    const rawData: BusinessApiData = await fetch(route('admin.statistics.business-performance'))
        .then(response => response.json())

    const { cards: transformedCards, charts: transformedCharts } = transformBusinessStatistics(rawData)
    cards.value = transformedCards
    charts.value = transformedCharts
    loading.value = false
}

onMounted(loadData)
</script>

<template>
    <VCard
        :loading
        class="mb-6"
        prepend-icon="mdi-palette"
        title="Performance métier"
    >
        <template #text>
            <VRow>
                <VCol
                    v-for="card in cards"
                    :key="card.title"
                    cols="12"
                    md="4"
                >
                    <StatCard
                        v-bind="card"
                    />
                </VCol>
            </VRow>
            <VRow>
                <VCol
                    v-for="(chart, i) in charts"
                    :key="i"
                    cols="12"
                    md="4"
                    class="mb-4 d-flex justify-center"
                >
                    <Doughnut v-bind="chart" />
                </VCol>
            </VRow>
        </template>
    </VCard>
</template>
