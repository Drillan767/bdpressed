<script setup lang="ts">
import useToast from '@/Composables/toast'
import VisitorsLayout from '@/Layouts/VisitorsLayout.vue'
import { router, useForm } from '@inertiajs/vue3'
import { useHead } from '@vueuse/head'
import { watch } from 'vue'
import { useDisplay } from 'vuetify'

defineOptions({ layout: VisitorsLayout })

const props = defineProps<{
    status?: string
}>()

const { smAndDown } = useDisplay()
const { showSuccess } = useToast()
const form = useForm({})

function submit() {
    form.post(route('verification.send'))
}

watch(() => props.status, (value) => {
    if (value === 'verification-link-sent') {
        showSuccess('Un nouvel email vous a été envoyé à l\'adresse que vous avez indiqué !')
    }
})

useHead({
    title: 'Vérifiez votre email',
})
</script>

<template>
    <VContainer>
        <VRow>
            <VCol class="d-flex justify-center">
                <VForm>
                    <VCard
                        :width="smAndDown ? '100%' : '560'"
                        prepend-icon="mdi-login"
                        title="Vérifiez votre email"
                    >
                        <template #text>
                            <p>
                                Merci de votre inscription ! Avant de continuer, est-ce que je peux vous demander
                                de vérifier votre adresse email en cliquant sur le lien que je vous ai envoyé ? 👉👈
                            </p>
                            <p>
                                SI vous n'avez rien reçu, je peux vous en renvoyer un autre sans problème ! ✨
                            </p>
                        </template>
                        <template #actions>
                            <VRow no-gutters>
                                <VCol class="d-flex justify-space-between">
                                    <VBtn
                                        @click="router.post(route('logout'))"
                                    >
                                        Déconnexion
                                    </VBtn>
                                    <VBtn
                                        variant="flat"
                                        @click="submit"
                                    >
                                        Renvoyer un mail
                                    </VBtn>
                                </VCol>
                            </VRow>
                        </template>
                    </VCard>
                </VForm>
            </VCol>
        </VRow>
    </VContainer>
<!--    <GuestLayout>
        <Head title="Email Verification" />

        <div class="mb-4 text-sm text-gray-600">
            Thanks for signing up! Before getting started, could you verify your
            email address by clicking on the link we just emailed to you? If you
            didn't receive the email, we will gladly send you another.
        </div>

        <div
            v-if="verificationLinkSent"
            class="mb-4 text-sm font-medium text-green-600"
        >
            A new verification link has been sent to the email address you
            provided during registration.
        </div>

        <form @submit.prevent="submit">
            <div class="mt-4 flex items-center justify-between">
                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Resend Verification Email
                </PrimaryButton>

                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                >
                    Log Out
                </Link>
            </div>
        </form>
    </GuestLayout> -->
</template>
