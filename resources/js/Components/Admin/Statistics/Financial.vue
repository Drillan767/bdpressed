<script setup lang="ts">
import type { ChartData, FinancialApiData, StatCard } from '@/types/statistics'
import { useStatistics } from '@/Composables/statistics'
import { onMounted, ref } from 'vue'
import { route } from 'ziggy-js'
import Doughnut from './Doughnut.vue'

const { transformFinancialStatistics } = useStatistics()

const loading = ref(false)
const totalCommands = ref(0)
const cards = ref<StatCard[]>([])
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
                    <VRow class="h-100">
                        <VCol
                            v-for="card in cards"
                            :key="card.title"
                            cols="12"
                            sm="6"
                        >
                            <VCard
                                :color="card.color"
                                variant="tonal"
                                class="h-100 stat-card"
                                elevation="2"
                            >
                                <VCardText class="pb-2">
                                    <div class="d-flex align-center justify-space-between mb-3">
                                        <VIcon
                                            :color="card.color"
                                            :icon="card.icon"
                                            size="32"
                                        />
                                        <VChip
                                            :color="card.color"
                                            variant="elevated"
                                            size="small"
                                            class="font-weight-medium"
                                        >
                                            {{ card.subtitle }}
                                        </VChip>
                                    </div>
                                    <div class="text-h4 font-weight-bold mb-1" :class="`text-${card.color}`">
                                        {{ card.title }}
                                    </div>
                                </VCardText>
                            </VCard>
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

<style scoped>
.stat-card {
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
}

.chart-card {
    border-radius: 16px !important;
    background: linear-gradient(135deg, rgb(var(--v-theme-surface)) 0%, rgba(var(--v-theme-primary), 0.02) 100%);
}

:deep(.v-card-title) {
    font-size: 1.1rem !important;
    font-weight: 600 !important;
}

:deep(.v-chip) {
    font-size: 0.75rem !important;
}
</style>
