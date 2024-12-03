import { a } from '@aws-amplify/backend'

export default a.model({
    id: a.id(),
    email: a.string().required(),
    fullName: a.string().required(),
    subject: a.string().required(),
    message: a.string().required(),
    createdAt: a.datetime().required(),
})
    .authorization(allow => [
        allow.group('ADMIN'),
        allow.guest().to(['create']),
    ])
