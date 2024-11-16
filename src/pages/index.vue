<script setup lang="ts">

</script>

<template>
    <div>
        <h1>Hello World</h1>
        <RouterLink :to="{ name: 'auth.login' }">
            Login
        </RouterLink><br>
        <RouterLink :to="{ name: 'auth.register' }">
            Register
        </RouterLink><br>
        <RouterLink :to="{ name: 'admin' }">
            Admin / User
        </RouterLink>
    </div>
</template>
