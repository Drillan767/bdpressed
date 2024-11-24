import { a } from '@aws-amplify/backend'

export default a.model({
    id: a.id(),
    name: a.string().required(),
    articles: a.hasMany('ProductTag', 'tagId'),
})
    .authorization(allow => [allow.publicApiKey()])
