import type { IllustrationStatus, OrderStatus, PaymentType, StatusTriggers } from '@/types/enums'
import type { Config } from 'ziggy-js'

export interface Money {
    cents: number
    euros: number
    formatted: string
}

export type StatusChange<T extends 'illustration' | 'order'> = {
    id: number
    triggered_by: StatusTriggers
    reason?: string
    metadata?: Record<string, any>
    user_id?: number
    created_at: string
    updated_at: string
} & (T extends 'illustration'
    ? {
            illustration_id: number
            from_status: IllustrationStatus
            to_status: IllustrationStatus
        }
    : {
            order_id: number
            from_status: OrderStatus
            to_status: OrderStatus
        }
    )

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
    price: Money
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
    price: Money
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
    total: Money
    final_amount: number
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
    price: Money
    quantity: number
    product: {
        id: number
        name: string
        promotedImage: string
        price: Money
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

interface Illustration {
    id: number
    type: 'BUST' | 'FULL_LENGTH' | 'ANIMAL'
    nbHumans: number
    nbAnimals: number
    pose: 'SIMPLE' | 'COMPLEX'
    background: 'SIMPLE' | 'GRADIENT' | 'COMPLEX'
    price: Money
    status: IllustrationStatus
    description: string
    created_at: string
    updated_at: string
    order_id: number
    addTracking: boolean
    print: boolean
    trackingNumber: string | null
}

export interface OrderDetail {
    id: number
    additionalInfos: string
    reference: string
    total: Money
    status: OrderStatus
    created_at: string
    updated_at: string
    useSameAddress: boolean
    shipmentFees: Money
    details: Detail[]
    guest_id: number | null
    illustrationsList: IllustrationSpecs[]
    illustrations: Illustration[]
    status_changes: StatusChange<'order'>[]
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

export interface ComicPage {
    id: number
    order: number
    image: string
    comic_id: number
}

export interface Comic {
    id: number
    title: string
    slug: string
    description: string
    preview: string
    is_published: boolean
    instagram_url: string
    pages: ComicPage[]
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

export interface OrderPayment {
    id: number
    amount: string
    paid_at: string | null
    stripe_fee: number
    description: string | null
    stripe_payment_intent_id: string
    stripe_payment_link: string | null
}

export interface UserPaymentHistory {
    id: number
    title: string
    type: PaymentType
    amount: string
    status: string
    paid_at: string | null
    payment_link: string | null
    is_illustration: boolean
}
