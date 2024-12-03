import { a } from '@aws-amplify/backend'

export default a.model({
    id: a.id(),
    orderId: a.id().required(),
    order: a.belongsTo('Order', 'orderId'),
    orderDetail: a.hasMany('OrderDetail', 'orderItemId'),
})
    .authorization(allow => [
        allow.group('ADMIN'),
        allow.group('USER').to(['read']),
        allow.ownerDefinedIn('orderId'),
    ])

/*
CREATE TABLE order_items (
    id SERIAL PRIMARY KEY,
    order_id INTEGER NOT NULL,
    order_detail_id INTEGER,
    custom_illustration_id INTEGER,
    FOREIGN KEY (order_id) REFERENCES orders(id),
    FOREIGN KEY (order_detail_id) REFERENCES order_details(id),
    FOREIGN KEY (custom_illustration_id) REFERENCES custom_illustrations(id),
    CHECK (
        (order_detail_id IS NULL AND custom_illustration_id IS NOT NULL) OR
        (order_detail_id IS NOT NULL AND custom_illustration_id IS NULL)
    )
);
*/
