import { a } from '@aws-amplify/backend'

export default a.model({
    id: a.id().required(),
    orderItemId: a.id().required(),
    productId: a.id().required(),
    quantity: a.integer().required(),
    price: a.float().required(),
    product: a.belongsTo('Product', 'productId'),
    orderItem: a.belongsTo('OrderItem', 'orderItemId'),
})
    .authorization(allow => [
        allow.group('ADMIN'),
        allow.group('USER').to(['read']),
        allow.ownerDefinedIn('orderItemId'),
    ])

/*

CREATE TABLE order_details (
    id SERIAL PRIMARY KEY,
    order_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL,
    item_price DECIMAL(10,2),
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);
*/
