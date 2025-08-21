<script setup lang="ts">
import VisitorsLayout from '@/Layouts/VisitorsLayout.vue'
import { useHead } from '@vueuse/head'

defineOptions({ layout: VisitorsLayout })

useHead({
    title: 'Boutique fermée',
})
</script>

<template>
    <VContainer>
        <VRow class="justify-center">
            <VCol cols="12" md="8" lg="6">
                <VCard class="pa-8" elevation="2">
                    <template #text>
                        <VEmptyState
                            icon="mdi-store-off"
                            title="La boutique est désactivée pour l'instant"
                            text="Vous pouvez toujours vous connecter pour voir où en est votre dernière commande si vous le souhaitez !"
                        >
                            <template #actions>
                                <VBtn
                                    href="/"
                                    variant="flat"
                                    color="primary"
                                    class="me-4"
                                >
                                    <VIcon start icon="mdi-home" />
                                    Retour à l'accueil
                                </VBtn>

                                <VBtn
                                    href="/contact"
                                    variant="outlined"
                                    color="primary"
                                >
                                    <VIcon start icon="mdi-email" />
                                    Me contacter
                                </VBtn>
                            </template>
                        </VEmptyState>
                    </template>
                </VCard>
            </VCol>
        </VRow>
    </VContainer>
</template>
