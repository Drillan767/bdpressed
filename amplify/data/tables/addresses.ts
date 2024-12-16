import { a } from '@aws-amplify/backend'

export default a.model({
    id: a.id(),
    shippingAddressId: a.id(),
    billingAddressId: a.id(),
    firstName: a.string(),
    lastName: a.string(),
    address: a.string(),
    address2: a.string(),
    city: a.string(),
    zipCode: a.string(),
    country: a.string(),
    phoneNumber: a.string(),
    billing: a.belongsTo('Order', 'billingAddressId'),
    shipping: a.belongsTo('Order', 'shippingAddressId'),
})
    .authorization(allow => [
        allow.guest().to(['create']),
        allow.group('ADMIN'),
    ])
