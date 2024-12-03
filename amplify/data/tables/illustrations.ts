import { a } from '@aws-amplify/backend'

export default a.model({
    id: a.id(),
    orderId: a.id(),
    type: a.enum(['BUST', 'FULL_LENGTH', 'ANIMAL']),
    nbHumans: a.integer().required(),
    nbAnimals: a.integer().required(),
    nbItems: a.integer().required(),
    pose: a.enum(['SIMPLE', 'COMPLEX']),
    background: a.enum(['UNIFIED', 'GRADIENT', 'SIMPLE', 'COMPLEX']),
    price: a.float().required(),
    status: a.enum(['PENDING', 'IN_PROGRESS', 'COMPLETED']),
    description: a.string().required(),
    order: a.belongsTo('Order', 'orderId'),
})
    .authorization(allow => [
        allow.group('ADMIN'),
        allow.group('USER').to(['read']),
        allow.ownerDefinedIn('orderId'),
    ])
