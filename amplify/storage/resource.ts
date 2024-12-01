import { defineStorage } from '@aws-amplify/backend'

export const storage = defineStorage({
    name: 'media',
    access: allow => ({
        'products/*': [
            allow.groups(['ADMIN']).to(['read', 'write', 'delete']),
            allow.groups(['USER']).to(['read']),
            allow.guest.to(['read']),
        ],
    }),
})
