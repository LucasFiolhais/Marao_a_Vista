<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import http from '@/http'

const availableRoles = ref([])

const form = ref({
  name: '',
  email: '',
  password: '',
  roles: ['cliente'],
  is_approved: false,
  nif: '',
  phone: '',
})

const errors = ref({})
const saving = ref(false)
const success = ref('')
const generalError = ref('')

const canFillExtra = computed(() => !!form.value.is_approved)

// ✅ Se desmarcar aprovado, limpamos logo no front
watch(() => form.value.is_approved, (val) => {
  if (!val) {
    form.value.nif = ''
    form.value.phone = ''
  }
})

onMounted(async () => {
  try {
    const res = await http.get('/admin/roles')
    availableRoles.value = res.data
  } catch (e) {
    console.error('Erro a carregar roles:', e?.response?.status, e?.response?.data || e)
  }
})

const submit = async () => {
  saving.value = true
  errors.value = {}
  success.value = ''
  generalError.value = ''

  try {
    await http.post('/admin/utilizadores', {
      name: form.value.name,
      email: form.value.email,
      password: form.value.password,
      roles: form.value.roles,

      is_approved: form.value.is_approved,

      // só manda se aprovado; senão manda null
      nif: canFillExtra.value ? (form.value.nif || null) : null,
      phone: canFillExtra.value ? (form.value.phone || null) : null,
    })

    success.value = 'Utilizador criado com sucesso!'
    router.visit(route('admin.utilizadores'))
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
    } else {
      generalError.value = 'Ocorreu um erro ao criar o utilizador.'
      console.error(error?.response?.status, error?.response?.data || error)
    }
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <AdminLayout title="Criar Utilizador">
    <div v-if="success" class="max-w-lg mx-auto mb-4 bg-green-100 text-green-800 p-3 rounded">
      {{ success }}
    </div>
    <div v-if="generalError" class="max-w-lg mx-auto mb-4 bg-red-100 text-red-800 p-3 rounded">
      {{ generalError }}
    </div>

    <form @submit.prevent="submit" class="max-w-lg mx-auto bg-white p-6 shadow rounded space-y-4">
      <div>
        <label class="block font-semibold">Nome</label>
        <input v-model="form.name" class="w-full border p-2 rounded" required />
        <div v-if="errors.name" class="text-red-600 text-sm mt-1">{{ errors.name[0] }}</div>
      </div>

      <div>
        <label class="block font-semibold">Email</label>
        <input v-model="form.email" type="email" class="w-full border p-2 rounded" required />
        <div v-if="errors.email" class="text-red-600 text-sm mt-1">{{ errors.email[0] }}</div>
      </div>

      <div>
        <label class="block font-semibold">Password</label>
        <input v-model="form.password" type="password" class="w-full border p-2 rounded" required />
        <div v-if="errors.password" class="text-red-600 text-sm mt-1">{{ errors.password[0] }}</div>
      </div>

      <!-- ✅ Aprovação -->
      <div class="flex items-center gap-2">
        <input id="approved" type="checkbox" v-model="form.is_approved" />
        <label for="approved" class="font-semibold">Conta validada</label>
      </div>
      <div v-if="errors.is_approved" class="text-red-600 text-sm">{{ errors.is_approved[0] }}</div>

      <!-- ✅ NIF/Telemóvel só se aprovado -->
      <div>
        <label class="block font-semibold">NIF</label>
        <input
          v-model="form.nif"
          class="w-full border p-2 rounded"
          :disabled="!canFillExtra"
          :class="!canFillExtra ? 'bg-gray-100 cursor-not-allowed' : ''"
          placeholder="Só ao validar"
        />
        <div v-if="errors.nif" class="text-red-600 text-sm mt-1">{{ errors.nif[0] }}</div>
      </div>

      <div>
        <label class="block font-semibold">Telemóvel</label>
        <input
          v-model="form.phone"
          class="w-full border p-2 rounded"
          :disabled="!canFillExtra"
          :class="!canFillExtra ? 'bg-gray-100 cursor-not-allowed' : ''"
          placeholder="Só ao validar"
        />
        <div v-if="errors.phone" class="text-red-600 text-sm mt-1">{{ errors.phone[0] }}</div>
      </div>

      <!-- Roles dinâmicas -->
      <div>
        <label class="block font-semibold mb-1">Papéis (roles)</label>
        <div class="flex flex-wrap gap-3">
          <label v-for="role in availableRoles" :key="role" class="inline-flex items-center space-x-2">
            <input type="checkbox" :value="role" v-model="form.roles" />
            <span class="capitalize">{{ role }}</span>
          </label>
        </div>
        <div v-if="errors.roles" class="text-red-600 text-sm mt-1">{{ errors.roles[0] }}</div>
      </div>

      <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-50" :disabled="saving">
        {{ saving ? 'A criar...' : 'Criar' }}
      </button>
    </form>
  </AdminLayout>
</template>
