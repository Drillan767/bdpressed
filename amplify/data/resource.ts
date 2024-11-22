import { a, type ClientSchema, defineData } from '@aws-amplify/backend'
import products from './tables/products'
import users from './tables/users'

const schema = a.schema({
    User: users,
    Product: products,
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
