import { a } from '@aws-amplify/backend'

export default a.model({
    id: a.id().required(),
    name: a.string().required(),
    slug: a.string().required().default(''),
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
    .authorization(allow => [
        allow.group('ADMIN'),
        allow.group('USER').to(['read']),
        allow.guest().to(['read']),
    ])
