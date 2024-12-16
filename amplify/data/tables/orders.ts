import { a } from '@aws-amplify/backend'

export default a.model({
    id: a.id().required(),
    userId: a.id().required(),
    useSameAddress: a.boolean(),
    title: a.string().required(),
    type: a.enum(['ILLUSTRATION', 'PRODUCT']),
    status: a.enum([
        'CANCELLED',
        'NEW',
        'ILLUSTRATION_DEPOSIT_PENDING',
        'ILLUSTRATION_DEPOSIT_PAID',
        'PENDING_CLIENT_REVIEW',
        'IN_PROGRESS',
        'PAYMENT_PENDING',
        'PAID',
        'TO_SHIP',
        'DONE',
    ]),
    price: a.float().required(),
    trackingId: a.string(),
    user: a.belongsTo('User', 'userId'),
    illustation: a.hasOne('Illustration', 'orderId'),
    orderItem: a.hasOne('OrderItem', 'orderId'),
    shippingAddress: a.hasOne('Address', 'shippingAddressId'),
    billingAddress: a.hasOne('Address', 'billingAddressId'),
    additionalInfos: a.string(),
    updatedAt: a.datetime().required(),
    createdAt: a.datetime().required(),
})
    .authorization(allow => [
        allow.group('ADMIN'),
        allow.ownerDefinedIn('userId'),
        allow.group('USER').to(['create']),

        // allow.group('USER').to(['read']),
        // allow.guest().to(['read']),
    ])
