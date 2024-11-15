<script setup lang="ts">
import { confirmSignUp, signUp } from 'aws-amplify/auth'
import { ref } from 'vue'

const email = ref('')
const password = ref('')

async function submit() {
    const result = await signUp({
        username: 'jd.levarato@gmail.com',
        password: 'P@ssw0rd',
        options: {
            userAttributes: {
                'custom:role': 'admin',
            },
        },
    })

    console.log(result)
}

async function confirm() {
    const { isSignUpComplete, nextStep } = await confirmSignUp({
        username: 'jd.levarato@gmail.com',
        confirmationCode: '123456',
    })

    console.log(isSignUpComplete, nextStep)
}
</script>

<template>
    <div>
        <h1>Register</h1>

        <form>
            <label for="email">Email</label>
            <input id="email" v-model="email" type="email">
            <br>
            <label for="password">Password</label>
            <input id="password" v-model="password" type="password">
            <br>
            <button @click.prevent="submit">
                Register
            </button>
        </form>

        <button @click.prevent="confirm">
            Confirmer
        </button>

        <RouterLink to="/">
            Home
        </RouterLink><br>
        <RouterLink to="/admin">
            Admin
        </RouterLink>
    </div>
</template>
