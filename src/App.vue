<script setup lang="ts">
import { onMounted } from 'vue'
import useAuthStore from './stores/authStore';

const { setAuthWatch } = useAuthStore()

onMounted(async () => setAuthWatch())

</script>

<template>
  <router-view />
</template>
