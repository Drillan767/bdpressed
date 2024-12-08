import { a } from '@aws-amplify/backend'

export default a.model({
    id: a.id().required(),
    name: a.string().required(),
    slug: a.string().required(),
    quickDescription: a.string().required(),
    description: a.string().required(),
    price: a.float().required(),
    illustrations: a.string().required().array().required(),
    promotedImage: a.string().required(),
    tags: a.hasMany('ProductTag', 'productId'),
    orderDetails: a.hasMany('OrderDetail', 'productId'),
    createdAt: a.datetime().required(),
    updatedAt: a.datetime().required(),
})
    .secondaryIndexes(index => [
        index('slug'),
    ])
    .authorization(allow => [
        allow.group('ADMIN'),
        allow.guest(),
        // allow.group('ADMIN'),
        // allow.group('USER').to(['read']),
        // allow.authenticated.to(['read']),
        // allow.().to(['read', 'list']),
    ])
