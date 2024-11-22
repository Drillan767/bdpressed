import { defineStorage } from '@aws-amplify/backend'

export const storage = defineStorage({
    name: 'media',
    access: allow => ({
        'products/*': [
            allow.guest.to(['read']),
            allow.authenticated.to(['read']),
            allow.entity('identity').to(['read', 'write', 'delete']),
        ],

    }),
})
