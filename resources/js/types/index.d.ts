import type { Config } from 'ziggy-js'

export interface User {
    id: number
    name: string
    email: string
    email_verified_at?: string
}

export interface ProductForm {
    name?: string
    quickDescription?: string
    description?: string
    price?: number
    weight?: number
    illustrations?: File[]
    promotedImage?: File | null
}

export interface AdminProductList {
    id: number,
    name: string
    price: number,
    created_at: string
    updated_at: string
}

export interface DataTableHeader {
    title: string
    align?: 'start' | 'center' | 'end'
    sortable?: boolean
    width?: string | number
    key: string
    nowrap?: boolean
    cellProps?: {
        class: string
    }
}


export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User
    }
    ziggy: Config & { location: string }
}
