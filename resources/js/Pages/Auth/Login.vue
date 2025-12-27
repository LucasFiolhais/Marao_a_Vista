<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import axiosInstance from '../../axiosFrontend'

defineProps({
  canResetPassword: Boolean,
  status: String,
})

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const loginError = ref('')

const submit = () => {
<<<<<<< HEAD
  form
    .transform(data => ({
      ...data,
      remember: form.remember ? 'on' : '',
    }))
    .post(route('login'), {
      onFinish: () => form.reset('password'),
      onError: (errors) => {
        loginError.value = errors.email || 'Credenciais inválidas'
      },
=======
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
        onSuccess: (response) => {
            // Garante que o window existe antes de usar localStorage
            if (typeof window !== 'undefined' && window.localStorage) {
                // Guardar token (caso estejas a usar API personalizada)
                localStorage.setItem('auth_token', response?.token ?? 'logged_in')
                authToken.value = response?.token ?? 'logged_in'
            }
            console.log('Login bem-sucedido', response)
        },
        onError: (errors) => {
        loginError.value = errors?.email || ''
      }
    })
}

// Função para logout
const logout = () => {
    if (typeof window !== 'undefined' && window.localStorage) {
        localStorage.removeItem('auth_token')
        authToken.value = null
    }

    console.log('✅ Logout local efetuado')

    // Logout via Laravel API (opcional)
    axiosInstance.post('/logout').then(() => {
        console.log('✅ Logout API efetuado')
    }).catch((error) => {
        console.error('❌ Erro ao fazer logout na API', error)
>>>>>>> e22edfc7231e05568e5d11c713872c32a40cb8d1
    })
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-secondary">
    <div
      class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1505691938895-1758d7feb511?auto=format&fit=crop&w=1350&q=80')] bg-cover bg-center opacity-30"
    />

    <div class="relative z-10 w-full max-w-md bg-white shadow-xl rounded-xl p-8 border border-primary">
      <h2 class="text-3xl font-semibold text-center mb-4 text-dark">
        Bem-vindo(a)
      </h2>

      <p class="text-center text-dark/70 mb-8">
        Entre para gerir ou reservar o seu alojamento
      </p>

      <div
        v-if="loginError"
        class="mb-4 rounded-md border border-red-300 bg-red-50 p-3 text-sm text-red-700"
      >
        {{ loginError }}
      </div>

      <form @submit.prevent="submit" class="space-y-5">
        <div>
          <label class="block text-dark mb-1">Email</label>
          <input
            v-model="form.email"
            type="email"
            class="w-full border border-primary rounded-md p-2"
          />
        </div>

        <div>
          <label class="block text-dark mb-1">Password</label>
          <input
            v-model="form.password"
            type="password"
            class="w-full border border-primary rounded-md p-2"
          />
        </div>

        <div class="flex items-center justify-between">
          <label class="flex items-center gap-2">
            <input type="checkbox" v-model="form.remember" />
            Lembrar-me
          </label>

          <a href="/forgot-password" class="text-sm underline">
            Esqueceu a password?
          </a>
        </div>

        <button
          type="submit"
          class="w-full py-2 bg-primary text-white rounded-md"
        >
          Entrar
        </button>
      </form>

      <p class="text-center mt-6">
        Ainda não tem conta?
        <a href="/register" class="font-semibold underline">
          Criar conta
        </a>
      </p>
    </div>
  </div>
</template>
