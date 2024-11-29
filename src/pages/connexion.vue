<script setup lang="ts">
import LoginForm from '@/components/auth/LoginForm.vue'
import RegisterOTP from '@/components/auth/RegisterOTP.vue'
import useToast from '@/composables/toast'
import { useHead } from '@vueuse/head'
import { ref } from 'vue'

useHead({
    title: 'Connexion',
})

const { showSuccess, showInfo } = useToast()

const loginForm = ref<InstanceType<typeof LoginForm>>()
const loginStatus = ref<'form' | 'otp'>('form')
const loginEmail = ref('')

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
    <VContainer>
        <VRow>
            <VCol class="d-flex justify-center">
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
            </VCol>
        </VRow>
    </VContainer>
</template>
