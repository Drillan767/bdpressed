<script setup lang="ts">
import type { StatCard as StatCardType, CustomersAnalyticsApiData } from '@/types/statistics'
import { onMounted, ref } from 'vue'
import StatCard from './Components/StatCard.vue'
import PaginatedList from './Components/PaginatedList.vue'
import useStatistics from '@/Composables/statistics'
import { route } from 'ziggy-js'

const { transformCustomerStatistics } = useStatistics()

const loading = ref(false)
const stats = ref<StatCardType[]>([])
const bestCustomers = ref<CustomersAnalyticsApiData['top_customers']>([])

async function loadData() {
    loading.value = true
    const rawData: CustomersAnalyticsApiData = await fetch(route('admin.statistics.customer-analytics'))
        .then((response) => response.json())

    const { cards, list } = transformCustomerStatistics(rawData)

    stats.value = cards
    bestCustomers.value = list
    loading.value = false
}

onMounted(loadData)
</script>

<template>
    <VCard
        title="Analyse des clients"
        prepend-icon="mdi-account-group"
        class="mb-6"
    >
        <template #text>
            <VRow>
                <VCol
                    cols="12"
                    md="6"
                >
                    <VRow>
                        <VCol
                            v-for="(card, i) in stats"
                            :key="i"
                            cols="12"
                        >
                            <StatCard v-bind="card" />
                        </VCol>
                    </VRow>
                </VCol>
                <VCol
                    cols="12"
                    md="6"
                >
                    <PaginatedList
                        :items="bestCustomers"
                        :loading
                        prepend-icon="mdi-account-heart"
                        title="Meilleurs clients"
                    >
                        <template #item="{ item }">
                            <VListItem
                                :title="item.email"
                                :subtitle="`${item.order_count} commandes`"
                            >
                                <template #append>
                                    <span class="text-success">
                                        {{ item.total_spent }}
                                    </span>
                                </template>
                            </VListItem>
                        </template>
                    </PaginatedList>
                </VCol>
            </VRow>
        </template>
    </VCard>
</template>