import type { Config } from 'ziggy-js'

export interface User {
    id: number
    name: string
    email: string
    email_verified_at?: string
}

export interface FileProperty {
    path: string
    type: string
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
    id: number
    name: string
    slug: string
    weight: number
    price: number
    created_at: string
    updated_at: string
}

export interface AdminProduct extends AdminProductList {
    id: number
    quickDescription: string
    description: string
    promotedImage: string
    illustrations: FileProperty[]
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

export interface Catalog {
    id: number
    name: string
    slug: string
    price: number
    weight: number
    promotedImage: string
    quickDescription: string
}

export interface CartItem {
    id: number
    name: string
    price: number
    quantity: number
    weight: number
    illustration: string
}

export interface Address {
    firstName: string
    lastName: string
    street: string
    street2: string
    city: string
    zipCode: string
    country: string
    billingAddressId?: number
    shippingAddressId?: number
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User
    }
    ziggy: Config & { location: string }
}
