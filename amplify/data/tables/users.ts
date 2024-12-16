import { a } from '@aws-amplify/backend'

export default a.model({
    id: a.id(),
    email: a.string().required(),
    role: a.enum(['admin', 'user']),
    orders: a.hasMany('Order', 'userId'),
    createdAt: a.datetime().required(),
    updatedAt: a.datetime().required(),
})
    .authorization(allow => [
        allow.group('ADMIN'),
        allow.ownerDefinedIn('email'),
    ])
