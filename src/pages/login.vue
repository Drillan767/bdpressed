<script setup lang="ts">
import { ref } from 'vue'
import { signIn } from 'aws-amplify/auth'

const email = ref('')
const password = ref('')

const submit = async () => {
  const result = await signIn({
    username: email.value,
    password: password.value,
    options: {
        authFlowType: 'USER_PASSWORD_AUTH'
    }
  })

  console.log(result)
}
</script>

<template>
  <div>
    <h1>Login</h1>

    <form>
        <label for="email">Email</label>
        <input type="email" id="email" v-model="email" />
        <br />
        <label for="password">Password</label>
        <input type="password" id="password" v-model="password" />
        <br />
        <button @click.prevent="submit">Login</button>
    </form>

    <RouterLink to="/">Home</RouterLink><br />
    <RouterLink to="/admin">Admin</RouterLink>
  </div>
</template>