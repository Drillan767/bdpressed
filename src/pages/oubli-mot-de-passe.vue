<script setup lang="ts">
import ForgotPasswordForm from '@/components/auth/ForgotPasswordForm.vue'
import NewPasswordForm from '@/components/auth/NewPasswordForm.vue'
import AuthLayout from '@/layouts/AuthLayout.vue'
import { useHead } from '@vueuse/head'
import { ref } from 'vue'
import { useRouter } from 'vue-router'

definePage({
    meta: {
        requiresAuth: false,
    },
})

useHead({
    title: 'Mot de passe oublié',
})

const router = useRouter()

const forgotPasswordStatus = ref<'form' | 'otp'>('form')
const forgotPasswordEmail = ref('')
const showSuccess = ref(false)

function handleSuccess() {
    router.push('/connexion')
        .then(() => showSuccess.value = true)
}
</script>

<template>
    <AuthLayout>
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
        <VSnackbar
            v-model="showSuccess"
            color="success"
            icon="mdi-check-circle-outline"
            timeout="3000"
            text="Mot de passe modifié avec succès"
        />
    </AuthLayout>
</template>
