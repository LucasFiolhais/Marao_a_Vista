<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import backend from '@/axiosBackend'

// Roles disponíveis (nomes dos roles do Spatie)
const availableRoles = ['cliente', 'admin']

const form = ref({
  name: '',
  email: '',
  password: '',
  roles: ['cliente'], // default: cliente
})

const errors = ref({})
const saving = ref(false)

const submit = async () => {
  saving.value = true
  errors.value = {}

  try {
    // ANTES: axios.post('/admin/utilizadores', {...})
    // AGORA: /admin/api/utilizadores (por causa do baseURL)
    await backend.post('/utilizadores', {
      name: form.value.name,
      email: form.value.email,
      password: form.value.password,
      roles: form.value.roles, // 👈 enviar array de roles
    })

    router.visit(route('admin.utilizadores'))
  } catch (error) {
    console.error('Erro ao criar utilizador:', error)
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors || {}
    }
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <AdminLayout title="Criar Utilizador">
    <form
      @submit.prevent="submit"
      class="max-w-lg mx-auto bg-white p-6 shadow rounded space-y-4"
    >
      <div>
        <label class="block font-semibold">Nome</label>
        <input v-model="form.name" class="w-full border p-2 rounded" required />
        <div v-if="errors.name" class="text-red-600 text-sm mt-1">
          {{ errors.name[0] }}
        </div>
      </div>

      <div>
        <label class="block font-semibold">Email</label>
        <input
          v-model="form.email"
          type="email"
          class="w-full border p-2 rounded"
          required
        />
        <div v-if="errors.email" class="text-red-600 text-sm mt-1">
          {{ errors.email[0] }}
        </div>
      </div>

      <div>
        <label class="block font-semibold">Password</label>
        <input
          v-model="form.password"
          type="password"
          class="w-full border p-2 rounded"
          required
        />
        <div v-if="errors.password" class="text-red-600 text-sm mt-1">
          {{ errors.password[0] }}
        </div>
      </div>

      <!-- Roles (Spatie) -->
      <div>
        <label class="block font-semibold mb-1">Papéis (roles)</label>

        <div class="flex flex-wrap gap-3">
          <label
            v-for="role in availableRoles"
            :key="role"
            class="inline-flex items-center space-x-2"
          >
            <input
              type="checkbox"
              :value="role"
              v-model="form.roles"
            />
            <span class="capitalize">{{ role }}</span>
          </label>
        </div>

        <div v-if="errors.roles" class="text-red-600 text-sm mt-1">
          {{ errors.roles[0] }}
        </div>
      </div>

      <button
        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-50"
        :disabled="saving"
      >
        {{ saving ? 'A criar...' : 'Criar' }}
      </button>
    </form>
  </AdminLayout>
</template>
