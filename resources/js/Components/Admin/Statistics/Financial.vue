<script setup lang="ts">
import type { ChartData, StatCard } from '@/types/statistics'
import { useStatistics } from '@/Composables/statistics'
import { onMounted, ref } from 'vue'
import { route } from 'ziggy-js'

const { transformFinancialStatistics } = useStatistics()

const loading = ref(false)
const cards = ref<StatCard[]>([])
const chart = ref<ChartData>({ labels: [], datasets: [] })

async function loadData() {
    loading.value = true
    try {
        const rawData = await fetch(route('admin.statistics.financial'))
            .then(response => response.json())

        const { cards: transformedCards, chart: transformedChart } = transformFinancialStatistics(rawData)

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
        <VCardText>
            <VRow>
                <VCol
                    v-for="card in cards"
                    :key="card.title"
                    cols="12"
                    md="6"
                >
                    <VCard
                        :color="card.color"
                        variant="tonal"
                    >
                        <VCardText>
                            <div class="d-flex align-center justify-space-between">
                                <div>
                                    <div class="text-caption">
                                        {{ card.title }}
                                    </div>
                                    <div class="text-h6">
                                        {{ card.value }}
                                    </div>
                                    <div v-if="card.subtitle" class="text-caption">
                                        {{ card.subtitle }}
                                    </div>
                                </div>
                                <VIcon v-if="card.icon" size="40" :color="card.color">
                                    {{ card.icon }}
                                </VIcon>
                            </div>
                        </VCardText>
                    </VCard>
                </VCol>
            </VRow>

            <VRow class="mt-4">
                <VCol>
                    <VCard>
                        <VCardTitle>Commandes par statut</VCardTitle>
                        <VCardText>
                            <VSkeletonLoader v-if="loading" type="image" />
                            <VPie
                                v-else-if="chart.items?.length > 0"
                                :items="chart.items"
                                height="300"
                                inner-cut="50"
                                animation
                                hide-slice
                                reveal
                            />
                            <div v-else class="text-center text-body-2 py-8">
                                Aucune donnée disponible
                            </div>
                        </VCardText>
                    </VCard>
                </VCol>
            </VRow>
        </VCardText>
    </VCard>
</template>
