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
    type: 'item' | 'illustration'
    quantity: number
    stock: number
    weight: number
    illustration: string
}

export interface CartIllustration extends CartItem {
    type: 'illustration'
    illustrationSettings: {
        illustrationType: 'bust' | 'fl' | 'animal'
        addedHuman: number
        addedAnimal: number
        pose: 'simple' | 'complex'
        background: 'gradient' | 'simple' | 'complex'
        description: string
        print: boolean
        addTracking: boolean
    }
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

interface IllustrationRow {
    name: string
    price: string
}

interface IllustrationSpecs {
    type: IllustrationRow
    price: IllustrationRow
    nbHumans?: IllustrationRow
    nbAnimals?: IllustrationRow
    pose: IllustrationRow
    background: IllustrationRow
    print?: IllustrationRow
    addTracking?: IllustrationRow
}

interface IllustrationDetail {
    pose: 'SIMPLE' | 'COMPLEX'
    background: 'UNIFIED' | 'GRADIENT'
    print: boolean
    addTracking: boolean
    description: string
    price: number
    trackingNumber: string | null
    type: 'BUST' | 'FULL_LENGTH' | 'ANIMAL'
    nbHumans: number
    nbAnimals: number
    nbItems: number
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
    guest_id: number | null
    illustrationsList: IllustrationSpecs[]
    client: {
        email: string
        instagram: string | null
        shipping_address: Address
        billing_address: Address | null
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

export interface IllustrationSettings {
    bust_base: number
    bust_add_human: number
    bust_add_animal: number
    fl_base: number
    fl_add_human: number
    fl_add_animal: number
    animal_base: number
    animal_add_one: number
    animal_toy: number
    option_pose_simple: number
    option_pose_complex: number
    option_bg_gradient: number
    option_bg_simple: number
    option_bg_complex: number
    options_print: number
    options_add_tracking: number
}

export interface IllustrationDetailed {
    addedHuman: number
    addedAnimal: number
    pose: 'simple' | 'complex'
    background: 'gradient' | 'simple' | 'complex'
}

export interface IllustrationForm {
    illustrationType: 'bust' | 'fl' | 'animal'
    bustDetails: IllustrationDetailed
    fullDetails: IllustrationDetailed
    animalDetails: IllustrationDetailed & { addedToy: number }
    options: {
        print: boolean
        addTracking: boolean
        description: string
    }
}
