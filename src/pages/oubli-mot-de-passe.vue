<script setup lang="ts">
import ForgotPasswordForm from '@/components/auth/ForgotPasswordForm.vue'
import NewPasswordForm from '@/components/auth/NewPasswordForm.vue'
import useToast from '@/composables/toast'
import { useHead } from '@vueuse/head'
import { ref } from 'vue'
import { useRouter } from 'vue-router'

useHead({
    title: 'Mot de passe oublié',
})

const router = useRouter()
const { showSuccess } = useToast()

const forgotPasswordStatus = ref<'form' | 'otp'>('form')
const forgotPasswordEmail = ref('')

function handleSuccess() {
    router.push('/connexion')
        .then(() => showSuccess('Mot de passe modifié avec succès'))
}
</script>

<template>
    <VContainer>
        <VRow>
            <VCol class="d-flex justify-center">
                <VTabsWindow v-model="forgotPasswordStatus">
                    <VTabsWindowItem value="form">
                        <ForgotPasswordForm
                            v-model="forgotPasswordEmail"
                            @success="forgotPasswordStatus = 'otp'"
                        />
                    </VTabsWindowItem>
                    <VTabsWindowItem value="otp">
                        <NewPasswordForm
                            v-model="forgotPasswordEmail"
                            @success="handleSuccess"
                        />
                    </VTabsWindowItem>
                </VTabsWindow>
            </VCol>
        </VRow>
    </VContainer>
</template>
