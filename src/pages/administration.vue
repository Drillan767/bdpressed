<script setup lang="ts">
import { signOut } from 'aws-amplify/auth'

const logout = () => signOut()
</script>

<template>
    <div>
        <h1>Administration</h1>
        <VBtn @click="logout">
            Déconnexion
        </VBtn>
    </div>
</template>
