<script setup lang="ts">
import { ref } from 'vue'
import { signUp, confirmSignUp } from 'aws-amplify/auth'

const email = ref('')
const password = ref('')

const submit = async () => {
  const result = await signUp({
    username: 'jd.levarato@gmail.com',
    password: password.value,
  })

  console.log(result)
}

const confirm = async () => {
  const { isSignUpComplete, nextStep } = await confirmSignUp({
    username: 'jd.levarato@gmail.com',
    confirmationCode: '919892',
  })

  console.log(isSignUpComplete, nextStep)
}
</script>

<template>
  <div>
    <h1>Register</h1>

    <form>
        <label for="email">Email</label>
        <input type="email" id="email" v-model="email" />
        <br />
        <label for="password">Password</label>
        <input type="password" id="password" v-model="password" />
        <br />
        <button @click.prevent="submit">Register</button>
    </form>

    <button @click.prevent="confirm">Confirmer</button>

    <RouterLink to="/">Home</RouterLink><br />
    <RouterLink to="/admin">Admin</RouterLink>
  </div>
</template>