import { a } from '@aws-amplify/backend'

export default a.model({
    id: a.id(),
    user: a.belongsTo('User', 'userId'),
    type: a.enum(['bust', 'fullLength', 'animal']),
    nbHumans: a.integer().required(),
    nbAnimals: a.integer().required(),
    nbItems: a.integer().required(),
    pose: a.enum(['simple', 'complex']),
    background: a.enum(['unified', 'gradient', 'simple', 'complex']),
    price: a.float().required(),
    status: a.enum(['pending', 'in-progress', 'completed']),
    description: a.string(),
    createdAt: a.datetime().required(),
    updatedAt: a.datetime().required(),
})
