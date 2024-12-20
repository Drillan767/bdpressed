import type { SchemaType } from '@root/amplify/data/resource'

export interface ProductForm {
    name?: string
    quickDescription?: string
    description?: string
    price?: number
    illustrations?: File[]
    promotedImage?: File | null
}

export interface EditProductForm extends ProductForm {
    id: string
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
    id: string
    name: string
    slug: string
    price: number
    promotedImage: string
    quickDescription: string
}

export interface CartItem {
    id: string
    name: string
    price: number
    quantity: number
    illustration: string
}

export type VisitorProduct = Omit<SchemaType<'Product'>, 'illustrations'> & {
    illustrations: {
        path: string
        type: string
    }[]
}
