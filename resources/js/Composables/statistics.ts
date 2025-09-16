import type {
    BusinessApiData,
    ChartData,
    CustomersAnalyticsApiData,
    FinancialApiData,
    StatCard,
    StocksApiData,
} from '@/types/statistics'
import useGradientGenerator from '@/Composables/colorGradient'

// Translation mappings
const ORDER_STATUS_TRANSLATIONS: Record<string, string> = {
    NEW: 'Nouveau',
    IN_PROGRESS: 'En cours',
    PENDING_PAYMENT: 'Paiement en attente',
    PAID: 'Payé',
    TO_SHIP: 'À expédier',
    SHIPPED: 'Expédié',
    DONE: 'Terminé',
    CANCELLED: 'Annulé',
}

const ILLUSTRATION_TYPE_TRANSLATIONS: Record<string, string> = {
    FULL_LENGTH: 'Portrait en pied',
    BUST: 'Buste',
    PORTRAIT: 'Portrait',
    COUPLE: 'Couple',
    GROUP: 'Groupe',
    ANIMAL: 'Animaux',
    LANDSCAPE: 'Paysage',
    // Add more as needed
}

function useStatistics() {
    const { generateColors } = useGradientGenerator()

    function transformFinancialStatistics(data: FinancialApiData) {
        // Transform cards data
        const cards: StatCard[] = [
            {
                subtitle: 'Chiffre d\'affaires total',
                title: data.total_revenue,
                color: 'success',
                icon: 'mdi-currency-eur',
            },
            {
                subtitle: 'Valeur moyenne des commandes',
                title: data.average_order_value,
                color: 'info',
                icon: 'mdi-chart-line',
            },
            {
                subtitle: 'Commandes récentes (7j)',
                title: data.last_week.toString() || '0',
                color: 'warning',
                icon: 'mdi-calendar-week',
            },
            {
                subtitle: 'Commandes récentes (30j)',
                title: data.last_month.toString() || '0',
                color: 'secondary',
                icon: 'mdi-calendar-month',
            },
        ]

        const chart: ChartData = {
            items: [],
            centerText: data.total_commands.toString(),
            centerLabel: 'Commandes',
            showLegend: true,
        }

        const colors = generateColors(Object.keys(data.orders_by_status).length)

        if (data.total_commands > 0) {
            Object.entries(data.orders_by_status).forEach(([status, count], index) => {
                chart.items.push({
                    id: index + 1,
                    title: ORDER_STATUS_TRANSLATIONS[status] || status,
                    value: count,
                    color: colors[index],
                })
            })
        }

        return { cards, chart }
    }

    function transformBusinessStatistics(data: BusinessApiData) {
        const cards: StatCard[] = [
            {
                title: data.illustrations_stats?.total_commissioned.toString() || '0',
                subtitle: 'Illustrations commandées',
                color: 'primary',
                icon: 'mdi-palette',
            },
            {
                title: data.illustrations_stats?.total_completed.toString() || '0',
                subtitle: 'Illustrations terminées',
                color: 'success',
                icon: 'mdi-check-circle',
            },
            {
                title: data.average_illustration_price || '0 €',
                subtitle: 'Prix moyen illustration',
                color: 'info',
                icon: 'mdi-currency-eur',
            },
        ]

        const typeColors = generateColors(data.popular_illustration_types.length)
        const completionColors = generateColors(2)

        const charts: ChartData[] = [
            // Types
            {
                title: 'Types d\'illustrations',
                items: data.popular_illustration_types.map((item, index) => ({
                    id: index + 1,
                    title: ILLUSTRATION_TYPE_TRANSLATIONS[item.type],
                    value: item.count,
                    color: typeColors[index],
                })),
            },
            // Completion
            {

                title: 'Statut des illustrations',
                centerLabel: 'Prix moyen d\'une illustration',
                centerText: data.average_illustration_price,
                items: [
                    {
                        id: 1,
                        color: completionColors[0],
                        value: data.illustrations_stats.total_commissioned - data.illustrations_stats.total_completed,
                        title: 'Illustrations en cours',
                    },
                    {
                        id: 2,
                        color: completionColors[1],
                        value: data.illustrations_stats.total_completed,
                        title: 'Total complétées',
                    },
                ],
            },
            // Repartition
            {
                title: 'Commandes digitales / imprimées',
                items: [
                    {
                        id: 1,
                        color: completionColors[0],
                        value: data.print_vs_digital_ratio.print_count,
                        title: 'Illustrations imprimées',
                    },
                    {
                        id: 2,
                        color: completionColors[1],
                        value: data.print_vs_digital_ratio.digital_count,
                        title: 'Illustrations digitales',
                    },
                ],
            },
        ]

        return { cards, charts }
    }

    function transformStocksStatistics(data: StocksApiData) {
        const topSellersColor = generateColors(data.top_sellers.length)

        const chart = {
            title: 'Meilleures ventes',
            items: data.top_sellers.map((item, i) => ({
                id: item.id,
                color: topSellersColor[i],
                value: item.total_sold,
                title: item.name,
            })),
        }

        const warningList = data.low_stock_alerts
        const outOfStock = data.out_of_stock

        return {
            chart,
            warningList,
            outOfStock,
        }
    }

    function transformCustomerStatistics(data: CustomersAnalyticsApiData) {
        const cards: StatCard[] = [
             {
                title: data.user_stats.total_registered_users.toString(),
                subtitle: 'Clients inscrits',
                color: 'secondary',
                icon: 'mdi-account-check',
            },
            {
                title: data.user_stats.repeat_customers.toString(),
                subtitle: 'Clients avec plusieurs commandes',
                color: 'success',
                icon: 'mdi-account-heart',
            },
            {
                title: data.user_stats.guest_orders.toString(),
                subtitle: 'Nombre de clients sans compte',
                color: 'warning',
                icon: 'mdi-account-question',
            }
        ]

        return {
            cards,
            list: data.top_customers,
        }
    }

    /**
     * Transform complete customer analytics from API response
    function transformCustomerStatistics(data: any) {
        const cards: StatCard[] = [
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

        return { cards, topCustomers: data.top_customers }
    }

    /**
     * Transform complete operational statistics from API response
    function transformOperationalStatistics(data: any) {
        const cards: StatCard[] = [
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

        return {
            cards,
            pendingIllustrations: data.pending_illustrations,
            ordersNeedingTracking: data.orders_needing_tracking,
            todaysOrders: data.todays_orders,
        }
    }
     */

    return {
        transformFinancialStatistics,
        transformBusinessStatistics,
        transformStocksStatistics,
        transformCustomerStatistics,
        // transformProductsStatistics,
        // transformCustomerStatistics,
        // transformOperationalStatistics,
    }
}

export default useStatistics
