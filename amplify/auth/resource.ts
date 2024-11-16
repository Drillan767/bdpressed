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
})

/*   signIn: {
    redirectSignOut: '/',
    redirectSignIn: '/',
    redirectSignUp: '/',
    redirectResetPassword: '/',
    redirectCustomSignIn: '/',
    redirectCustomSignOut: '/',
    responseType: 'code',
    scopes: ['email', 'openid', 'profile'],
    triggerCustomSignIn: false,
    authProviders: ['UserPool'],
  },
  signOut: {
    redirectSignOut: '/',
    responseType: 'code',
    authProviders: ['UserPool'],
  },
  signUp: {
    redirectSignUp: '/',
    responseType: 'code',
    authProviders: ['UserPool'],
  },
  confirmSignUp: {
    redirectSignUp: '/',
    responseType: 'code',
    authProviders: ['UserPool'],
  }, */
