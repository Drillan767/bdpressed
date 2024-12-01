import type { Schema } from '@root/amplify/data/resource'
import { generateClient } from 'aws-amplify/data'
import { defineStore } from 'pinia'
import { ref } from 'vue'

interface UserForm {
    email: string
}

const useUserStore = defineStore('user', () => {
    const loadingUser = ref(false)
    const client = generateClient<Schema>()

    async function createUser(form: UserForm) {
        loadingUser.value = true
        try {
            await client.models.User.create({
                email: form.email,
                role: 'user',
                createdAt: new Date().toISOString(),
                updatedAt: new Date().toISOString(),
            })
        }
        catch (error) {
            console.error(error)
        }
        finally {
            loadingUser.value = false
        }
    }

    return {
        createUser,
        loadingUser,
    }
})

export default useUserStore
