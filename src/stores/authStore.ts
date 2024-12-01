import { AuthError, fetchAuthSession, fetchUserAttributes } from 'aws-amplify/auth'
import { Hub } from 'aws-amplify/utils'
import { defineStore } from 'pinia'
import { computed, ref } from 'vue'

interface AuthUser {
    email: string
    role: 'admin' | 'user'
}

const useAuthStore = defineStore('auth', () => {
    const loadingUser = ref(false)
    const currentUser = ref<AuthUser | undefined>()

    const isAuthenticated = computed(() => !!currentUser.value)

    async function setAuthWatch() {
        await retrieveUser()

        Hub.listen('auth', ({ payload }) => {
            if (payload.event === 'signedIn') {
                retrieveUser()
            }

            if (payload.event === 'signedOut') {
                currentUser.value = undefined
            }
        })
    }

    async function retrieveUser() {
        loadingUser.value = true
        try {
            const session = await fetchAuthSession()

            if (session) {
                const email = session.tokens?.idToken?.payload.email as string
                const rolesList = session.tokens?.accessToken?.payload['cognito:groups']

                if (!rolesList || !Array.isArray(rolesList) || rolesList.length === 0) {
                    currentUser.value = {
                        email,
                        role: 'user',
                    }

                    return
                }

                const role = rolesList.includes('ADMIN') ? 'admin' : 'user'

                currentUser.value = {
                    email,
                    role,
                }
            }
        }
        catch (error) {
            if (error instanceof AuthError) {
                if (error.name === 'UserUnAuthenticatedException') {
                    currentUser.value = undefined
                }
            }
        }
        finally {
            loadingUser.value = false
        }
    }

    async function ensureLoggedIn() {
        if (currentUser.value) {
            return true
        }

        try {
            await fetchUserAttributes()
            return true
        }
        catch {
            return false
        }
    }

    return {
        isAuthenticated,
        currentUser,
        loadingUser,
        setAuthWatch,
        ensureLoggedIn,
    }
}, {
    persist: {
        pick: ['currentUser'],
    },
})

export default useAuthStore
