import { a } from '@aws-amplify/backend'

export default a.model({
    id: a.id().required(),
    userId: a.id().required(),
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

/*
CREATE TABLE orders (
    id SERIAL PRIMARY KEY,
    customer_id INTEGER NOT NULL,
    total_price DECIMAL(10,2),
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);
*/
