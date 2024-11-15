<script setup lang="ts">
import { signIn } from 'aws-amplify/auth'
import { ref } from 'vue'

const email = ref('')
const password = ref('')

async function submit() {
    const result = await signIn({
        username: email.value,
        password: password.value,
        options: {
            authFlowType: 'USER_PASSWORD_AUTH',
        },
    })

    console.log(result)
}
</script>

<template>
    <div>
        <h1>Login</h1>

        <form>
            <label for="email">Email</label>
            <input id="email" v-model="email" type="email">
            <br>
            <label for="password">Password</label>
            <input id="password" v-model="password" type="password">
            <br>
            <button @click.prevent="submit">
                Login
            </button>
        </form>

        <RouterLink to="/">
            Home
        </RouterLink><br>
        <RouterLink to="/admin">
            Admin
        </RouterLink>
    </div>
</template>
