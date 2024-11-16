import { AuthError, fetchUserAttributes, getCurrentUser } from 'aws-amplify/auth'
import { Hub } from 'aws-amplify/utils'
import { defineStore } from 'pinia'
import { ref } from 'vue'

const useAuthStore = defineStore('auth', () => {
    const isAuthenticated = ref(false)
    const currentRole = ref<'admin' | 'user' | undefined>()

    async function setAuthWatch() {
        await retrieveUser()

        Hub.listen('auth', ({ payload }) => {
            if (payload.event === 'signedIn') {
                isAuthenticated.value = true
                retrieveRole()
            }

            if (payload.event === 'signedOut') {
                isAuthenticated.value = false
                currentRole.value = undefined
            }
        })
    }

    async function retrieveUser() {
        try {
            const currentUser = await getCurrentUser()
            if (currentUser) {
                isAuthenticated.value = true
                retrieveRole()
            }
        }
        catch (error) {
            if (error instanceof AuthError) {
                if (error.name === 'UserUnAuthenticatedException') {
                    isAuthenticated.value = false
                    currentRole.value = undefined
                }
            }
        }
    }

    async function retrieveRole() {
        const userAttributes = await fetchUserAttributes()
        const role = userAttributes['custom:role']
        if (role === 'admin' || role === 'user') {
            currentRole.value = role
        }
    }

    return {
        isAuthenticated,
        currentRole,
        setAuthWatch,
    }
})

export default useAuthStore
