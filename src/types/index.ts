export interface ProductForm {
    name?: string
    quickDescription?: string
    description?: string
    price?: number
    images?: File[]
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
