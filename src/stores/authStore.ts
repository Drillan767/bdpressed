import { ref } from 'vue'
import { defineStore } from 'pinia'
import { Hub } from 'aws-amplify/utils'
import { fetchAuthSession, fetchUserAttributes } from 'aws-amplify/auth'

const useAuthStore = defineStore('auth', () => {
    const isAuthenticated = ref(false)
    const currentRole = ref<'admin' | 'user' | undefined>()

    async function setAuthWatch() {

        const session = await fetchAuthSession()

        if (session.tokens?.idToken) {
            isAuthenticated.value = true
        }

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

    async function retrieveRole() {
        const userAttributes = await fetchUserAttributes()
        const role = userAttributes['custom:role']
        if (role === 'admin' || role === 'user') {
            currentRole.value = role
        }
    }

  return {
    isAuthenticated,
    setAuthWatch,
  }
})

export default useAuthStore