import { a } from '@aws-amplify/backend'

export default a.model({
    id: a.id(),
    email: a.string().required(),
    firstName: a.string(),
    lastName: a.string(),
    address: a.string(),
    address2: a.string(),
    city: a.string(),
    zipCode: a.string(),
    country: a.string(),
    phoneNumber: a.string(),
    createdAt: a.timestamp(),
    updatedAt: a.timestamp(),
})
    .authorization(allow => [allow.publicApiKey()])
