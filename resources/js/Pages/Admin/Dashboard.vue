<script setup lang="ts">
import Financial from '@/Components/Admin/Statistics/Financial.vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useHead } from '@vueuse/head'
import { onMounted, ref } from 'vue'
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
const loadingFinancial = ref(true)
const loadingBusiness = ref(true)
const loadingProducts = ref(true)
const loadingCustomers = ref(true)
const loadingOperational = ref(true)

// Statistics data
const financialStats = ref<any>({})
const businessStats = ref<any>({})
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

async function loadFinancialStatistics() {
    try {
        const response = await fetch(route('admin.statistics.financial'))
        const data = await response.json()
        console.log('Financial data received:', data)
        financialStats.value = data

        financialCards.value = [
            {
                title: 'Chiffre d\'affaires total',
                value: data.total_revenue?.formatted_amount || '0 €',
                subtitle: `${data.total_revenue?.order_count || 0} commandes`,
                color: 'success',
                icon: 'mdi-currency-eur',
            },
            {
                title: 'Valeur moyenne des commandes',
                value: data.average_order_value?.formatted_amount || '0 €',
                color: 'info',
                icon: 'mdi-chart-line',
            },
            {
                title: 'Commandes récentes (7j)',
                value: data.recent_orders?.last_7_days || 0,
                color: 'warning',
                icon: 'mdi-calendar-week',
            },
            {
                title: 'Commandes récentes (30j)',
                value: data.recent_orders?.last_30_days || 0,
                color: 'primary',
                icon: 'mdi-calendar-month',
            },
        ]

        // Order status chart
        if (data.orders_by_status) {
            const colors = colorSchemes.mixed.slice(0, Object.keys(data.orders_by_status).length)
            const chartItems = Object.entries(data.orders_by_status).map(([label, value], index) => ({
                label,
                value: value as number,
                color: colors[index],
            }))

            orderStatusChart.value = {
                labels: Object.keys(data.orders_by_status),
                datasets: [{
                    data: Object.values(data.orders_by_status) as number[],
                    backgroundColor: colors,
                }],
                items: chartItems,
            }
        }
    }
    catch (error) {
        console.error('Error loading financial statistics:', error)
    }
    finally {
        loadingFinancial.value = false
    }
}

async function loadBusinessStatistics() {
    try {
        const response = await fetch(route('admin.statistics.business-performance'))
        const data = await response.json()
        businessStats.value = data

        businessCards.value = [
            {
                title: 'Illustrations commandées',
                value: data.illustrations_stats?.total_commissioned || 0,
                subtitle: `${data.illustrations_stats?.completion_rate || 0}% terminées`,
                color: 'primary',
                icon: 'mdi-palette',
            },
            {
                title: 'Illustrations terminées',
                value: data.illustrations_stats?.total_completed || 0,
                color: 'success',
                icon: 'mdi-check-circle',
            },
            {
                title: 'Prix moyen illustration',
                value: data.average_illustration_price?.formatted_amount || '0 €',
                color: 'info',
                icon: 'mdi-currency-eur',
            },
            {
                title: 'Ratio print/digital',
                value: `${data.print_vs_digital_ratio?.print_percentage || 0}% / ${data.print_vs_digital_ratio?.digital_percentage || 0}%`,
                color: 'warning',
                icon: 'mdi-printer',
            },
        ]

        // Illustration types chart
        if (data.popular_illustration_types?.length) {
            const colors = colorSchemes.primary.slice(0, data.popular_illustration_types.length)
            const chartItems = data.popular_illustration_types.map((item: any, index: number) => ({
                label: item.type,
                value: item.count,
                color: colors[index],
            }))

            illustrationTypesChart.value = {
                labels: data.popular_illustration_types.map((item: any) => item.type),
                datasets: [{
                    data: data.popular_illustration_types.map((item: any) => item.count),
                    backgroundColor: colors,
                }],
                items: chartItems,
            }
        }

        // Print vs Digital chart
        if (data.print_vs_digital_ratio) {
            const totalIllustrations = data.print_vs_digital_ratio.print_count + data.print_vs_digital_ratio.digital_count

            if (totalIllustrations > 0) {
                const chartItems = [
                    { label: 'Print', value: data.print_vs_digital_ratio.print_count, color: colorSchemes.success[0] },
                    { label: 'Digital', value: data.print_vs_digital_ratio.digital_count, color: colorSchemes.info[0] },
                ]

                printDigitalChart.value = {
                    labels: ['Print', 'Digital'],
                    datasets: [{
                        data: [data.print_vs_digital_ratio.print_count, data.print_vs_digital_ratio.digital_count],
                        backgroundColor: [colorSchemes.success[0], colorSchemes.info[0]],
                    }],
                    items: chartItems,
                }
            }
            else {
                printDigitalChart.value = { labels: [], datasets: [], items: [] }
            }
        }
    }
    catch (error) {
        console.error('Error loading business statistics:', error)
    }
    finally {
        loadingBusiness.value = false
    }
}

async function loadProductsStatistics() {
    try {
        const response = await fetch(route('admin.statistics.products-comics'))
        const data = await response.json()
        productsStats.value = data

        productCards.value = [
            {
                title: 'Total produits',
                value: data.products_stats?.total_products || 0,
                subtitle: `${data.products_stats?.in_stock_products || 0} en stock`,
                color: 'primary',
                icon: 'mdi-package-variant',
            },
            {
                title: 'Produits en rupture',
                value: data.products_stats?.out_of_stock_products || 0,
                color: 'error',
                icon: 'mdi-alert-circle',
            },
            {
                title: 'Comics publiés',
                value: data.comics_stats?.published_comics || 0,
                subtitle: `${data.comics_stats?.total_comics || 0} total`,
                color: 'success',
                icon: 'mdi-book-open',
            },
            {
                title: 'Pages de comics',
                value: data.total_comic_pages || 0,
                color: 'info',
                icon: 'mdi-file-document',
            },
        ]

        // Comics chart
        if (data.comics_stats) {
            const totalComics = data.comics_stats.published_comics + data.comics_stats.unpublished_comics

            if (totalComics > 0) {
                const chartItems = [
                    { label: 'Publiés', value: data.comics_stats.published_comics, color: colorSchemes.success[0] },
                    { label: 'Non publiés', value: data.comics_stats.unpublished_comics, color: colorSchemes.warning[0] },
                ]

                comicsChart.value = {
                    labels: ['Publiés', 'Non publiés'],
                    datasets: [{
                        data: [data.comics_stats.published_comics, data.comics_stats.unpublished_comics],
                        backgroundColor: [colorSchemes.success[0], colorSchemes.warning[0]],
                    }],
                    items: chartItems,
                }
            }
            else {
                // No comics data, so no chart items
                comicsChart.value = { labels: [], datasets: [], items: [] }
            }
        }
    }
    catch (error) {
        console.error('Error loading products statistics:', error)
    }
    finally {
        loadingProducts.value = false
    }
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

onMounted(async () => {
    // Load all statistics in parallel
    await Promise.all([
        loadFinancialStatistics(),
        loadBusinessStatistics(),
        loadProductsStatistics(),
        loadCustomerStatistics(),
        loadOperationalStatistics(),
    ])
})
</script>

<template>
    <VContainer fluid>
        <h1 class="text-h4 mb-6">
            Tableau de bord
        </h1>

        <Financial />



        <!--
        <VCard class="mb-6">
            <VCardTitle class="d-flex align-center">
                <VIcon class="me-2">
                    mdi-chart-line
                </VIcon>
                Statistiques financières
            </VCardTitle>
            <VCardText>
                <VRow>
                    <VCol v-for="card in financialCards" :key="card.title" cols="12" sm="6" md="3">
                        <VCard :loading="loadingFinancial" :color="card.color" variant="tonal">
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
                    <VCol cols="12" md="6">
                        <VCard>
                            <VCardTitle>Commandes par statut</VCardTitle>
                            <VCardText>
                                <VSkeletonLoader v-if="loadingFinancial" type="image" />
                                <VPie
                                    v-else-if="orderStatusChart.items?.length > 0"
                                    :items="orderStatusChart.items"
                                    height="300"
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
        -->

        <!-- Business Performance -->
        <VCard class="mb-6">
            <VCardTitle class="d-flex align-center">
                <VIcon class="me-2">
                    mdi-palette
                </VIcon>
                Performance métier
            </VCardTitle>
            <VCardText>
                <VRow>
                    <VCol v-for="card in businessCards" :key="card.title" cols="12" sm="6" md="3">
                        <VCard :loading="loadingBusiness" :color="card.color" variant="tonal">
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
                    <VCol cols="12" md="6">
                        <VCard>
                            <VCardTitle>Types d'illustrations populaires</VCardTitle>
                            <VCardText>
                                <VSkeletonLoader v-if="loadingBusiness" type="image" />
<!--                                <VPie
                                    v-else-if="illustrationTypesChart.items?.length > 0"
                                    :items="illustrationTypesChart.items"
                                    height="300"
                                />-->
                                <div v-else class="text-center text-body-2 py-8">
                                    Aucune donnée disponible
                                </div>
                            </VCardText>
                        </VCard>
                    </VCol>
                    <VCol cols="12" md="6">
                        <VCard>
                            <VCardTitle>Print vs Digital</VCardTitle>
                            <VCardText>
                                <VSkeletonLoader v-if="loadingBusiness" type="image" />
<!--                                <VPie
                                    v-else-if="printDigitalChart.items?.length > 0"
                                    :items="printDigitalChart.items"
                                    height="300"
                                />-->
                                <div v-else class="text-center text-body-2 py-8">
                                    Aucune donnée disponible
                                </div>
                            </VCardText>
                        </VCard>
                    </VCol>
                </VRow>
            </VCardText>
        </VCard>

        <!-- Products & Comics -->
        <VCard class="mb-6">
            <VCardTitle class="d-flex align-center">
                <VIcon class="me-2">
                    mdi-package-variant
                </VIcon>
                Produits & Comics
            </VCardTitle>
            <VCardText>
                <VRow>
                    <VCol v-for="card in productCards" :key="card.title" cols="12" sm="6" md="3">
                        <VCard :loading="loadingProducts" :color="card.color" variant="tonal">
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
                    <VCol cols="12" md="6">
                        <VCard>
                            <VCardTitle>Comics publiés</VCardTitle>
                            <VCardText>
                                <VSkeletonLoader v-if="loadingProducts" type="image" />
<!--                                <VPie
                                    v-else-if="comicsChart.items?.length > 0"
                                    :items="comicsChart.items"
                                    height="300"
                                />-->
                                <div v-else class="text-center text-body-2 py-8">
                                    Aucune donnée disponible
                                </div>
                            </VCardText>
                        </VCard>
                    </VCol>
                    <VCol cols="12" md="6">
                        <VCard v-if="productsStats.low_stock_alerts?.length > 0">
                            <VCardTitle class="text-error">
                                Alertes stock faible
                            </VCardTitle>
                            <VCardText>
                                <VList>
                                    <VListItem
                                        v-for="product in productsStats.low_stock_alerts"
                                        :key="product.id"
                                        :title="product.name"
                                        :subtitle="`Stock: ${product.stock}`"
                                    >
                                        <template #prepend>
                                            <VIcon color="error">
                                                mdi-alert
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
