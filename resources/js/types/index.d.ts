import type { Config } from 'ziggy-js'

export enum OrderStatus {
    NEW = 'NEW',
    ILLUSTRATION_DEPOSIT_PENDING = 'ILLUSTRATION_DEPOSIT_PENDING',
    ILLUSTRATION_DEPOSIT_PAID = 'ILLUSTRATION_DEPOSIT_PAID',
    PENDING_CLIENT_REVIEW = 'PENDING_CLIENT_REVIEW',
    IN_PROGRESS = 'IN_PROGRESS',
    PAYMENT_PENDING = 'PAYMENT_PENDING',
    PAID = 'PAID',
    TO_SHIP = 'TO_SHIP',
    SHIPPED = 'SHIPPED',
    DONE = 'DONE',
    CANCELLED = 'CANCELLED',
}

export interface User {
    id: number
    name: string
    email: string
    instagram?: string
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
    stock?: number
    price?: number
    weight?: number
    illustrations?: File[]
    promotedImage?: File | null
}

export interface AdminProductList {
    id: number
    name: string
    slug: string
    stock: number
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
    stock: number
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
    stock: number
    weight: number
    illustration: string
}

export interface AddressFields {
    firstName: string
    lastName: string
    street: string
    street2: string
    city: string
    zipCode: string
    country: string
}

export interface Address extends AddressFields {
    id: number
    default_billing: boolean
    default_shipping: boolean
}

export interface OrderStep1Form {
    email: string
    guest: boolean
    instagram?: string
    password?: string
    password_confirmation?: string
    additionalInfos: string
}

export interface OrderStep2Form {
    useSameAddress: boolean
    shippingAddress: AddressForm
    billingAddress?: AddressForm
}

export interface OrderIndex {
    id: number
    reference: string
    status: OrderStatus
    total: number
    guest: {
        id: number
        email: string
    } | null
    user: {
        id: number
        email: string
    } | null
    created_at: string
    updated_at: string
}

interface Detail {
    id: number
    price: number
    quantity: number
    product: {
        id: number
        name: string
        promotedImage: string
        slug: string
        weigh: number
    }
}

export interface OrderDetail {
    id: number
    additionalInfos: string
    reference: string
    total: number
    status: OrderStatus
    created_at: string
    updated_at: string
    useSameAddress: boolean
    stripeFees: number
    shipmentFees: number
    details: Detail[]
    guest: {
        email: string
        shipping_address: Address
        billing_address: Address | null
    } | null

    user: {
        email: string
        shipping_address: Address
        billing_address: Address | null
        instagram: string | null
    } | null
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User
    }
    ziggy: Config & { location: string }
}
