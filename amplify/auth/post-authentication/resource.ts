import { defineFunction } from '@aws-amplify/backend'

export const postConfirmation = defineFunction({
    name: 'post-confirmation',

    environment: {
        USER_GROUP: 'USER',
        ADMIN_GROUP: 'ADMIN',
    },
})
