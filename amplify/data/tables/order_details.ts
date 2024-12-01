import { a } from '@aws-amplify/backend'

export default a.model({
    id: a.id(),
    order: a.belongsTo('Order', 'orderId'),
    product: a.belongsTo('Product', 'productId'),
    quantity: a.integer().required(),
    price: a.float().required(),
    createdAt: a.datetime().required(),
    updatedAt: a.datetime().required(),
})

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
