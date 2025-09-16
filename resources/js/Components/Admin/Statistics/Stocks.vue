<script setup lang="ts">
import type { ChartData, StocksApiData } from '@/types/statistics'
import useStatistics from '@/Composables/statistics'
import { router } from '@inertiajs/vue3'
import { onMounted, ref } from 'vue'
import { route } from 'ziggy-js'
import Doughnut from './Components/Doughnut.vue'
import PaginatedList from './Components/PaginatedList.vue'

const { transformStocksStatistics } = useStatistics()

const loading = ref(false)
const doughnut = ref<ChartData>()
const warnings = ref<StocksApiData['low_stock_alerts']>([])
const oosList = ref<StocksApiData['out_of_stock']>([])

async function loadData() {
    loading.value = true

    const rawData: StocksApiData = await fetch(route('admin.statistics.stocks'))
        .then(response => response.json())

    const { chart, warningList, outOfStock } = transformStocksStatistics(rawData)
    doughnut.value = chart
    warnings.value = warningList
    oosList.value = outOfStock
    loading.value = false
}

onMounted(loadData)
</script>

<template>
    <VCard
        :loading="loading ? 'primary' : undefined"
        class="mb-6"
        prepend-icon="mdi-counter"
        title="Suivi des stocks"
    >
        <template #text>
            <VRow>
                <VCol
                    cols="12"
                    md="4"
                    class="mb-4 d-flex justify-center"
                >
                    <Doughnut
                        v-if="doughnut"
                        v-bind="doughnut"
                    />
                </VCol>
                <VCol
                    cols="12"
                    md="4"
                >
                    <PaginatedList
                        :loading
                        :items="warnings"
                        title="Bientôt en rupture"
                        prepend-icon="mdi-counter"
                    >
                        <template #item="{ item }">
                            <VListItem
                                :title="item.name"
                                @click="router.visit(route('products.show', { slug: item.slug }))"
                            >
                                <template #append>
                                    <VBadge
                                        :content="item.stock"
                                        color="warning"
                                        inline
                                    />
                                </template>
                            </VListItem>
                        </template>
                    </PaginatedList>
                </VCol>
                <VCol
                    cols="12"
                    md="4"
                >
                    <PaginatedList
                        :loading
                        :items="oosList"
                        title="Rupture de stock"
                        prepend-icon="mdi-alert"
                    >
                        <template #item="{ item }">
                            <VListItem
                                :title="item.name"
                                @click="router.visit(route('products.show', { slug: item.slug }))"
                            />
                        </template>
                    </PaginatedList>
                </VCol>
            </VRow>
        </template>
    </VCard>
</template>
