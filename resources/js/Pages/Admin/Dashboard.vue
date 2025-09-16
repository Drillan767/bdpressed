<script setup lang="ts">
import Business from '@/Components/Admin/Statistics/Business.vue'
import Financial from '@/Components/Admin/Statistics/Financial.vue'
import Stocks from '@/Components/Admin/Statistics/Stocks.vue'
import Customers from '@/Components/Admin/Statistics/Customers.vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useHead } from '@vueuse/head'
import { ref } from 'vue'
import { route } from 'ziggy-js'

defineOptions({ layout: AdminLayout })

useHead({
    title: 'Accueil',
})

interface StatCard {
    title: string
    value: string | number
    subtitle?: string
    color?: string
    icon?: string
}

interface ChartData {
    labels: string[]
    datasets: {
        data: number[]
        backgroundColor?: string[]
    }[]
    items?: {
        label: string
        value: number
        color?: string
    }[]
}

// Loading states
const loadingBusiness = ref(true)
const loadingProducts = ref(true)
const loadingCustomers = ref(true)
const loadingOperational = ref(true)

// Statistics data
const productsStats = ref<any>({})
const customerStats = ref<any>({})
const operationalStats = ref<any>({})

// Chart data
const orderStatusChart = ref<ChartData>({ labels: [], datasets: [] })
const illustrationTypesChart = ref<ChartData>({ labels: [], datasets: [] })
const printDigitalChart = ref<ChartData>({ labels: [], datasets: [] })
const comicsChart = ref<ChartData>({ labels: [], datasets: [] })

// Stat cards
const financialCards = ref<StatCard[]>([])
const businessCards = ref<StatCard[]>([])
const productCards = ref<StatCard[]>([])
const customerCards = ref<StatCard[]>([])
const operationalCards = ref<StatCard[]>([])

// Color schemes for charts
const colorSchemes = {
    primary: ['#1976D2', '#42A5F5', '#90CAF9', '#E3F2FD'],
    success: ['#388E3C', '#66BB6A', '#A5D6A7', '#E8F5E8'],
    warning: ['#F57C00', '#FFB74D', '#FFCC02', '#FFF8E1'],
    error: ['#D32F2F', '#EF5350', '#FFCDD2', '#FFEBEE'],
    info: ['#0288D1', '#29B6F6', '#81D4FA', '#E1F5FE'],
    mixed: ['#1976D2', '#388E3C', '#F57C00', '#D32F2F', '#7B1FA2', '#00796B', '#E64A19', '#455A64'],
}

async function loadCustomerStatistics() {
    try {
        const response = await fetch(route('admin.statistics.customer-analytics'))
        const data = await response.json()
        customerStats.value = data

        customerCards.value = [
            {
                title: 'Utilisateurs inscrits',
                value: data.user_stats?.total_registered_users || 0,
                subtitle: `${data.user_stats?.users_with_orders || 0} ont commandé`,
                color: 'primary',
                icon: 'mdi-account-group',
            },
            {
                title: 'Commandes invités',
                value: data.user_stats?.guest_orders || 0,
                color: 'warning',
                icon: 'mdi-account-question',
            },
            {
                title: 'Clients récurrents',
                value: data.repeat_customers?.count || 0,
                subtitle: `${data.repeat_customers?.percentage || 0}% des clients`,
                color: 'success',
                icon: 'mdi-account-heart',
            },
        ]
    }
    catch (error) {
        console.error('Error loading customer statistics:', error)
    }
    finally {
        loadingCustomers.value = false
    }
}

async function loadOperationalStatistics() {
    try {
        const response = await fetch(route('admin.statistics.operational'))
        const data = await response.json()
        operationalStats.value = data

        operationalCards.value = [
            {
                title: 'Illustrations en attente',
                value: data.pending_illustrations?.length || 0,
                color: 'warning',
                icon: 'mdi-clock-outline',
            },
            {
                title: 'Commandes sans tracking',
                value: data.orders_needing_tracking?.length || 0,
                color: 'error',
                icon: 'mdi-truck-alert',
            },
            {
                title: 'Commandes du jour',
                value: data.todays_orders?.total_today || 0,
                subtitle: `${data.todays_orders?.needing_processing || 0} à traiter`,
                color: 'info',
                icon: 'mdi-calendar-today',
            },
        ]
    }
    catch (error) {
        console.error('Error loading operational statistics:', error)
    }
    finally {
        loadingOperational.value = false
    }
}

/*
onMounted(async () => {
    // Load all statistics in parallel
    await Promise.all([
        // loadBusinessStatistics(),
        loadProductsStatistics(),
        loadCustomerStatistics(),
        loadOperationalStatistics(),
    ])
})
*/
</script>

<template>
    <VContainer fluid>
        <h1 class="text-h4 mb-6">
            Tableau de bord
        </h1>

        <Financial />
        <Business />
        <Stocks />
        <Customers />

        <!-- Customer Analytics -->
        <VCard class="mb-6">
            <VCardTitle class="d-flex align-center">
                <VIcon class="me-2">
                    mdi-account-group
                </VIcon>
                Analyse des clients
            </VCardTitle>
            <VCardText>
                <VRow>
                    <VCol v-for="card in customerCards" :key="card.title" cols="12" sm="6" md="4">
                        <VCard :loading="loadingCustomers" :color="card.color" variant="tonal">
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
                    <VCol cols="12">
                        <VCard v-if="customerStats.top_customers?.length > 0">
                            <VCardTitle>Top clients</VCardTitle>
                            <VCardText>
                                <VTable>
                                    <thead>
                                        <tr>
                                            <th>Email</th>
                                            <th>Commandes</th>
                                            <th>Total dépensé</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="customer in customerStats.top_customers" :key="customer.email">
                                            <td>{{ customer.email }}</td>
                                            <td>{{ customer.order_count }}</td>
                                            <td>{{ customer.formatted_total_spent }}</td>
                                        </tr>
                                    </tbody>
                                </VTable>
                            </VCardText>
                        </VCard>
                    </VCol>
                </VRow>
            </VCardText>
        </VCard>

        <!-- Operational -->
        <VCard class="mb-6">
            <VCardTitle class="d-flex align-center">
                <VIcon class="me-2">
                    mdi-cog
                </VIcon>
                Opérationnel
            </VCardTitle>
            <VCardText>
                <VRow>
                    <VCol v-for="card in operationalCards" :key="card.title" cols="12" sm="6" md="4">
                        <VCard :loading="loadingOperational" :color="card.color" variant="tonal">
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
                    <VCol v-if="operationalStats.pending_illustrations?.length > 0" cols="12" md="6">
                        <VCard>
                            <VCardTitle>Illustrations en attente</VCardTitle>
                            <VCardText>
                                <VList>
                                    <VListItem
                                        v-for="illustration in operationalStats.pending_illustrations.slice(0, 5)"
                                        :key="illustration.id"
                                        :title="`${illustration.type} - ${illustration.order_reference}`"
                                        :subtitle="`${illustration.status} - ${illustration.days_pending} jours`"
                                    >
                                        <template #prepend>
                                            <VIcon color="warning">
                                                mdi-clock-outline
                                            </VIcon>
                                        </template>
                                    </VListItem>
                                </VList>
                                <div v-if="operationalStats.pending_illustrations.length > 5" class="text-caption text-center pt-2">
                                    Et {{ operationalStats.pending_illustrations.length - 5 }} autres...
                                </div>
                            </VCardText>
                        </VCard>
                    </VCol>
                    <VCol v-if="operationalStats.orders_needing_tracking?.length > 0" cols="12" md="6">
                        <VCard>
                            <VCardTitle class="text-error">
                                Commandes sans tracking
                            </VCardTitle>
                            <VCardText>
                                <VList>
                                    <VListItem
                                        v-for="order in operationalStats.orders_needing_tracking"
                                        :key="order.id"
                                        :title="order.reference"
                                        :subtitle="`${order.days_waiting} jours d'attente`"
                                    >
                                        <template #prepend>
                                            <VIcon color="error">
                                                mdi-truck-alert
                                            </VIcon>
                                        </template>
                                    </VListItem>
                                </VList>
                            </VCardText>
                        </VCard>
                    </VCol>
                </VRow>
            </VCardText>
        </VCard>
    </VContainer>
</template>
