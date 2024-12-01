import { a } from '@aws-amplify/backend'

export default a.model({
    id: a.id(),
    user: a.belongsTo('User', 'userId'),
    status: a.enum(['pending', 'delivered', 'cancelled']),
    price: a.float().required(),
    createdAt: a.datetime().required(),
    updatedAt: a.datetime().required(),
})

/*
CREATE TABLE orders (
    id SERIAL PRIMARY KEY,
    customer_id INTEGER NOT NULL,
    total_price DECIMAL(10,2),
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id)
);
*/
