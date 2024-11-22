import { defineAuth } from '@aws-amplify/backend'

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
    userAttributes: {
        'custom:role': {
            dataType: 'String',
            mutable: true,
        },
    },
})
