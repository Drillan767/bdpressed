import { defineAuth } from '@aws-amplify/backend'
import { postConfirmation } from './post-authentication/resource'

export const auth = defineAuth({
    loginWith: {
        email: {
            verificationEmailStyle: 'CODE',
            verificationEmailSubject: 'Vérification de votre adresse email',
            verificationEmailBody(createCode) {
                return `
                    Bonjour !
                    <br />
                    <br />
                    Veuillez renseigner le code suivant pour activer votre compte :
                    <br />
                    ${createCode()}
                    <br />
                `
            },
        },
    },
    groups: ['USERS', 'ADMINS'],
    triggers: {
        postConfirmation,
    },
    access: allow => [
        allow.resource(postConfirmation).to(['addUserToGroup']),
    ],
    userAttributes: {
        'custom:role': {
            dataType: 'String',
            mutable: true,
        },
    },
})
