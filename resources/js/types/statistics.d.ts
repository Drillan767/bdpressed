import type { OrderStatus } from '@/types/enums'

export interface StatCard {
    title: string
    subtitle: string
    color: string
    icon: string
}

export interface ChartData {
    labels: string[]
    series: number[]
    colors: string[]
    centerValue: number
}

export interface FinancialApiData {
    total_revenue: string
    average_order_value: string
    last_week: number
    last_month: number
    total_commands: number
    orders_by_status: Record<OrderStatus, number>
}
