import { a, type ClientSchema, defineData } from '@aws-amplify/backend'
import contact from './tables/contact'
import illustrations from './tables/illustrations'
import order_details from './tables/order_details'
import order_items from './tables/order_items'
import orders from './tables/orders'
import products from './tables/products'
import productsTags from './tables/products_tags'
import tags from './tables/tags'
import users from './tables/users'

const schema = a.schema({
    User: users,
    Product: products,
    Tag: tags,
    ProductTag: productsTags,
    Order: orders,
    Illustration: illustrations,
    OrderItem: order_items,
    OrderDetail: order_details,
    Contact: contact,
})

export type Schema = ClientSchema <typeof schema>
export type SchemaType<T extends keyof Schema> = Schema[T]['type']

export const data = defineData({
    schema,
    authorizationModes: {
        defaultAuthorizationMode: 'apiKey',
        apiKeyAuthorizationMode: { expiresInDays: 30 },
    },
})
