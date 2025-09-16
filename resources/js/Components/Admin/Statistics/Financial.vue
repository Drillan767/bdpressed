<script setup lang="ts">
import type { ChartData, FinancialApiData, StatCard as StarCardType } from '@/types/statistics'
import useStatistics from '@/Composables/statistics'
import { onMounted, ref } from 'vue'
import { route } from 'ziggy-js'
import Doughnut from './Components/Doughnut.vue'
import StatCard from './Components/StatCard.vue'

const { transformFinancialStatistics } = useStatistics()

const loading = ref(false)
const totalCommands = ref(0)
const cards = ref<StarCardType[]>([])
const chart = ref<ChartData>()

async function loadData() {
    loading.value = true
    try {
        const rawData: FinancialApiData = await fetch(route('admin.statistics.financial'))
            .then(response => response.json())

        const { cards: transformedCards, chart: transformedChart } = transformFinancialStatistics(rawData)

        totalCommands.value = rawData.total_commands
        cards.value = transformedCards
        chart.value = transformedChart
    }
    catch (error) {
        console.error('Error loading financial statistics:', error)
    }
    finally {
        loading.value = false
    }
}

onMounted(loadData)
</script>

<template>
    <VCard
        :loading
        class="mb-6"
        prepend-icon="mdi-chart-line"
        title="Statistiques financières"
    >
        <template #text>
            <VRow>
                <VCol
                    cols="12"
                    lg="7"
                >
                    <VRow class="h-100 align-center">
                        <VCol
                            v-for="card in cards"
                            :key="card.title"
                            cols="12"
                            sm="6"
                        >
                            <StatCard v-bind="card" />
                        </VCol>
                    </VRow>
                </VCol>
                <VCol
                    cols="12"
                    lg="5"
                >
                    <VCard
                        class="chart-card"
                        elevation="3"
                        min-height="300"
                    >
                        <VCardTitle class="pb-2">
                            <div class="d-flex align-center">
                                <VIcon
                                    icon="mdi-chart-donut"
                                    class="me-2"
                                    color="primary"
                                />
                                Commandes par statut
                            </div>
                        </VCardTitle>
                        <VCardText class="d-flex align-center justify-center">
                            <Doughnut
                                v-if="chart"
                                v-bind="chart"
                                :center-text="totalCommands.toString()"
                                center-subtext="commandes"
                            />

                            <VEmptyState
                                v-else
                                icon="mdi-chart-donut"
                                title="Aucune donnée disponible"
                            />
                        </VCardText>
                    </VCard>
                </VCol>
            </VRow>
        </template>
    </VCard>
</template>
