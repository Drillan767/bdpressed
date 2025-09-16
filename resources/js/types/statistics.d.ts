import type { OrderStatus } from '@/types/enums'

interface Item {
    id: number
    title: string
    value: number
    color: string
}

export interface StatCard {
    title: string
    subtitle: string
    color: string
    icon: string
}

export interface ChartData {
    items: Item[]
    title?: string
    centerText?: string
    centerLabel?: string
    showLegend?: boolean
}

export interface FinancialApiData {
    total_revenue: string
    average_order_value: string
    last_week: number
    last_month: number
    total_commands: number
    orders_by_status: Record<OrderStatus, number>
}

interface BusinessApiData {
    illustrations_stats: {
        total_commissioned: number
        total_completed: number
    }
    popular_illustration_types: {
        type: string
        count: number
    }[]
    average_illustration_price: string
    print_vs_digital_ratio: {
        print_count: number
        digital_count: number
    }
}

interface StocksApiData {
    top_sellers: {
        id: number
        name: string
        total_sold: number
    }[]
    low_stock_alerts: {
        id: number
        slug: string
        name: string
        stock: number
    }[]
    out_of_stock: {
        id: number
        slug: string
        name: string
    }[]
}

interface CustomersAnalyticsApiData {
    user_stats: {
        total_registered_users: number
        guest_orders: number
        repeat_customers: number
    }
    top_customers: {
        email: string
        total_spent: string
        order_count: string
    }[]
}
