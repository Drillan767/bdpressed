export interface ProductForm {
    name?: string;
    quickDescription?: string;
    description?: string;
    price?: number;
    images?: File[];
    promotedImage?: File | null;
}
