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
