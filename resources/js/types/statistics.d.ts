export interface StatCard {
    title: string
    value: string | number
    subtitle?: string
    color?: string
    icon?: string
}

export interface ChartData {
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
