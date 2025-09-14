<script setup lang="ts">
import type { ChartData, FinancialApiData, StatCard } from '@/types/statistics'
import { useStatistics } from '@/Composables/statistics'
import { onMounted, ref } from 'vue'
import { route } from 'ziggy-js'

const { transformFinancialStatistics } = useStatistics()

const loading = ref(false)
const totalCommands = ref(0)
const cards = ref<StatCard[]>([])
const chart = ref<ChartData[]>([])

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
                <VCol>
                    <VRow class="h-100">
                        <VCol
                            v-for="card in cards"
                            :key="card.title"
                            cols="12"
                            md="6"
                        >
                            <VCard
                                :color="card.color"
                                variant="tonal"
                                class="h-100"
                            >
                                <VCardSubtitle>
                                    {{ card.subtitle }}
                                </VCardSubtitle>
                                <div class="d-flex align-center justify-space-between">
                                    <VCardTitle>
                                        {{ card.title }}
                                    </VCardTitle>
                                    <VIcon
                                        :color="card.color"
                                        :icon="card.icon"
                                        size="40"
                                    />
                                </div>
                            </VCard>
                        </VCol>
                    </VRow>
                </VCol>
                <VCol
                    cols="12"
                    md="6"
                >
                    <VCard>
                        <VCardTitle>Commandes par statut</VCardTitle>
                        <VCardText>
                            <VSkeletonLoader v-if="loading" type="image" />
                            <VPie
                                v-else-if="chart.length > 0"
                                :items="chart"
                                :legend="{ position: 'right' }"
                                inner-cut="50"
                                gap="4"
                                animation
                                hide-slice
                                reveal
                                tooltip
                            >
                                <template #center>
                                    <div class="text-h5 text-center">
                                        {{ totalCommands }}
                                    </div>
                                    <div class="opacity-70 mt-1 mb-n1">
                                        commandes
                                    </div>
                                </template>
                            </VPie>
                            <div v-else class="text-center text-body-2 py-8">
                                Aucune donnée disponible
                            </div>
                        </VCardText>
                    </VCard>
                </VCol>
            </VRow>
        </template>
    </VCard>
</template>

<style scoped>
:deep(.v-card-subtitle) {
    line-height: 2.2;
}
</style>
