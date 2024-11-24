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
