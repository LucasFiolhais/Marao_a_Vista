<script setup>
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import backend from '@/axiosBackend'   // 👈 usar o axiosBackend com baseURL /admin/api

const props = defineProps({
  id: {
    type: [Number, String],
    required: true,
  },
})

// roles disponíveis (nomes Spatie)
const availableRoles = ['cliente', 'admin']

const form = ref({
  name: '',
  email: '',
  password: '',
  roles: [], // 👈 array de roles
})

const errors = ref({})
const loading = ref(true)
const saving = ref(false)

const loadUser = async () => {
  loading.value = true
  errors.value = {}

  try {
    // ANTES: axios.get(`/admin/utilizadores/${props.id}`)
    // AGORA: vai para /admin/api/utilizadores/{id}
    const res = await backend.get(`/utilizadores/${props.id}`)

    form.value.name = res.data.name
    form.value.email = res.data.email
    form.value.roles = Array.isArray(res.data.roles) ? res.data.roles : []
  } catch (error) {
    console.error('Erro ao carregar utilizador:', error)
  } finally {
    loading.value = false
  }
}

const submit = async () => {
  saving.value = true
  errors.value = {}

  try {
    const payload = {
      name: form.value.name,
      email: form.value.email,
      roles: form.value.roles, // 👈 envia array de roles
    }

    if (form.value.password) {
      payload.password = form.value.password
    }

    // ANTES: axios.put(`/admin/utilizadores/${props.id}`, payload)
    // AGORA: /admin/api/utilizadores/{id}
    await backend.put(`/utilizadores/${props.id}`, payload)

    router.visit(route('admin.utilizadores'))
  } catch (error) {
    console.error('Erro ao atualizar utilizador:', error)
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors || {}
    }
  } finally {
    saving.value = false
  }
}

onMounted(loadUser)
</script>

<template>
  <AdminLayout title="Editar Utilizador">
    <div v-if="loading">A carregar utilizador...</div>

    <form
      v-else
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
        <label class="block font-semibold">Password (opcional)</label>
        <input
          v-model="form.password"
          type="password"
          class="w-full border p-2 rounded"
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
        {{ saving ? 'A guardar...' : 'Guardar' }}
      </button>
    </form>
  </AdminLayout>
</template>
