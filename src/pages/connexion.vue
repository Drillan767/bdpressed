<script setup lang="ts">
import LoginForm from '@/components/auth/LoginForm.vue'
import RegisterOTP from '@/components/auth/RegisterOTP.vue'
import useToast from '@/composables/toast'
import AuthLayout from '@/layouts/AuthLayout.vue'
import { ref } from 'vue'

const { showSuccess, showInfo } = useToast()

const loginForm = ref<InstanceType<typeof LoginForm>>()
const loginStatus = ref<'form' | 'otp'>('form')
const loginEmail = ref('')

definePage({
    meta: {
        title: 'Connexion',
        requiresAuth: false,
    },
})

function handleStepChange(email: string) {
    showInfo('Code de confirmation renvoyé')
    loginEmail.value = email
    loginStatus.value = 'otp'
}

function handleSuccess() {
    showSuccess('Email validé, vous pouvez vous connecter 🎉')
    loginStatus.value = 'form'
    loginForm.value?.submit()
}
</script>

<template>
    <AuthLayout>
        <VTabsWindow v-model="loginStatus">
            <VTabsWindowItem value="form">
                <LoginForm
                    ref="loginForm"
                    v-model="loginEmail"
                    @change-step="handleStepChange"
                />
            </VTabsWindowItem>
            <VTabsWindowItem value="otp">
                <RegisterOTP
                    :email="loginEmail"
                    @back="loginStatus = 'form'"
                    @success="handleSuccess"
                />
            </VTabsWindowItem>
        </VTabsWindow>
    </AuthLayout>
</template>
