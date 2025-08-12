<script setup lang="ts">
import VisitorsLayout from '@/Layouts/VisitorsLayout.vue'
import { useHead } from '@vueuse/head'

defineOptions({ layout: VisitorsLayout })

useHead({
    title: 'Merci !',
})
</script>

<template>
    <VCard class="bede-block">
        <VCardText class="bede-text">
            <h1 class="text-center mb-4">
                ¥ Merci pour votre paiement ! ¥
            </h1>

            <p>
                Vous avez reçu un email de recapitulatif, et j'ai reçu l'alerte qui me permettra d'envoyer
                la commande dans les plus brefs délais !
            </p>

            <p>
                N'hésitez surtout pas à venir me soliciter sur Instagram pour savoir où en est l'expedition !
            </p>

            <p>
                À très vite !
            </p>
            <div class="d-flex justify-center mt-4">
                <VBtn href="/">
                    Retourner à l'accueil
                </VBtn>
            </div>
        </VCardText>
    </VCard>
</template>
