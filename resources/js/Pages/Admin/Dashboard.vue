<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import DropdownLink from "@/Components/DropdownLink.vue";

</script>

<template>
    <h1>Administration, gg.</h1>
    <Link
        :href="route('logout')"
        method="post"
        as="button"
    >
        Déconnexion
    </Link>
</template>
