import { a } from '@aws-amplify/backend'

export default a.model({
    productId: a.id().required(),
    tagId: a.id().required(),
    product: a.belongsTo('Product', 'productId'),
    tag: a.belongsTo('Tag', 'tagId'),
})
    .authorization(allow => [allow.publicApiKey()])
