import type { OrderStatus } from '@/types/enums'
import type { ChartData, FinancialApiData, StatCard } from '@/types/statistics'

// Translation mappings
const ORDER_STATUS_TRANSLATIONS: Record<OrderStatus, string> = {
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
    HALF_BODY: 'Portrait demi-corps',
    PORTRAIT: 'Portrait',
    COUPLE: 'Couple',
    GROUP: 'Groupe',
    PET: 'Animal',
    LANDSCAPE: 'Paysage',
    // Add more as needed
}

// Color schemes
const COLOR_SCHEMES = {
    primary: ['#1976D2', '#42A5F5', '#90CAF9', '#E3F2FD'],
    success: ['#388E3C', '#66BB6A', '#A5D6A7', '#E8F5E8'],
    warning: ['#F57C00', '#FFB74D', '#FFCC02', '#FFF8E1'],
    error: ['#D32F2F', '#EF5350', '#FFCDD2', '#FFEBEE'],
    info: ['#0288D1', '#29B6F6', '#81D4FA', '#E1F5FE'],
    mixed: ['#1976D2', '#388E3C', '#F57C00', '#D32F2F', '#7B1FA2', '#00796B', '#E64A19', '#455A64'],
}

export function useStatistics() {
    /**
     * Transform complete financial statistics from API response
     */
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
            labels: [],
            colors: [],
            series: [],
            centerValue: data.total_commands,
        }

        const totalOrders = Object.values(data.orders_by_status).reduce((sum: number, count: any) => sum + count, 0)

        if (totalOrders > 0) {
            const colors = COLOR_SCHEMES.mixed.slice(0, Object.keys(data.orders_by_status).length)
            Object.entries(data.orders_by_status).forEach(([status, count], index) => {
                chart.labels.push(ORDER_STATUS_TRANSLATIONS[status] || status)
                chart.colors.push(colors[index])
                chart.series.push(count)
            })
        }

        // Transform chart data
        // let chart: ChartData = { labels: [], datasets: [], items: [] }
        /*
        if (data.orders_by_status) {
            const totalOrders = Object.values(data.orders_by_status).reduce((sum: number, count: any) => sum + count, 0)

            if (totalOrders > 0) {
                const colors = COLOR_SCHEMES.mixed.slice(0, Object.keys(data.orders_by_status).length)
                const chartItems = Object.entries(data.orders_by_status).map(([status, count], index) => ({
                    label: ORDER_STATUS_TRANSLATIONS[status] || status,
                    value: count as number,
                    color: colors[index],
                }))

                chart = {
                    labels: chartItems.map(item => item.label),
                    datasets: [{
                        data: chartItems.map(item => item.value),
                        backgroundColor: colors,
                    }],
                    items: chartItems,
                }
            }
        } */

        return { cards, chart }
    }

    /**
     * Transform complete business performance statistics from API response
     */
    function transformBusinessStatistics(data: any) {
        // Transform cards data
        const cards: StatCard[] = [
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

        // Transform illustration types chart
        let illustrationTypesChart: ChartData = { labels: [], datasets: [], items: [] }
        if (data.popular_illustration_types?.length) {
            const colors = COLOR_SCHEMES.primary.slice(0, data.popular_illustration_types.length)
            const chartItems = data.popular_illustration_types.map((item: any, index: number) => ({
                label: ILLUSTRATION_TYPE_TRANSLATIONS[item.type] || item.type,
                value: item.count,
                color: colors[index],
            }))

            illustrationTypesChart = {
                labels: chartItems.map(item => item.label),
                datasets: [{
                    data: chartItems.map(item => item.value),
                    backgroundColor: colors,
                }],
                items: chartItems,
            }
        }

        // Transform print vs digital chart
        let printDigitalChart: ChartData = { labels: [], datasets: [], items: [] }
        if (data.print_vs_digital_ratio) {
            const total = data.print_vs_digital_ratio.print_count + data.print_vs_digital_ratio.digital_count

            if (total > 0) {
                const chartItems = [
                    { label: 'Print', value: data.print_vs_digital_ratio.print_count, color: COLOR_SCHEMES.success[0] },
                    { label: 'Digital', value: data.print_vs_digital_ratio.digital_count, color: COLOR_SCHEMES.info[0] },
                ]

                printDigitalChart = {
                    labels: ['Print', 'Digital'],
                    datasets: [{
                        data: [data.print_vs_digital_ratio.print_count, data.print_vs_digital_ratio.digital_count],
                        backgroundColor: [COLOR_SCHEMES.success[0], COLOR_SCHEMES.info[0]],
                    }],
                    items: chartItems,
                }
            }
        }

        return { cards, illustrationTypesChart, printDigitalChart }
    }

    /**
     * Transform complete products & comics statistics from API response
     */
    function transformProductsStatistics(data: any) {
        // Transform cards data
        const cards: StatCard[] = [
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

        // Transform comics chart
        let comicsChart: ChartData = { labels: [], datasets: [], items: [] }
        if (data.comics_stats) {
            const total = data.comics_stats.published_comics + data.comics_stats.unpublished_comics

            if (total > 0) {
                const chartItems = [
                    { label: 'Publiés', value: data.comics_stats.published_comics, color: COLOR_SCHEMES.success[0] },
                    { label: 'Non publiés', value: data.comics_stats.unpublished_comics, color: COLOR_SCHEMES.warning[0] },
                ]

                comicsChart = {
                    labels: ['Publiés', 'Non publiés'],
                    datasets: [{
                        data: [data.comics_stats.published_comics, data.comics_stats.unpublished_comics],
                        backgroundColor: [COLOR_SCHEMES.success[0], COLOR_SCHEMES.warning[0]],
                    }],
                    items: chartItems,
                }
            }
        }

        return { cards, comicsChart, lowStockAlerts: data.low_stock_alerts }
    }

    /**
     * Transform complete customer analytics from API response
     */
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
     */
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

    return {
        // Main transformation functions
        transformFinancialStatistics,
        transformBusinessStatistics,
        transformProductsStatistics,
        transformCustomerStatistics,
        transformOperationalStatistics,

        // Constants if needed elsewhere
        COLOR_SCHEMES,
    }
}
