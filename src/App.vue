<script setup lang="ts">

</script>

<template>
    <RouterView />
    <p>
        <RouterLink to="/login">
            Login
        </RouterLink>
    </p>
    <p>
        <RouterLink to="/administration">
            Administration
        </RouterLink>
    </p>
</template>

<style scoped>
.logo {
  height: 6em;
  padding: 1.5em;
  will-change: filter;
  transition: filter 300ms;
}
.logo:hover {
  filter: drop-shadow(0 0 2em #646cffaa);
}
.logo.vue:hover {
  filter: drop-shadow(0 0 2em #42b883aa);
}
</style>
