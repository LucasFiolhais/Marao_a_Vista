<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import http from '@/http'

const props = defineProps({
  id: { type: [Number, String], required: true },
})

const availableRoles = ref([])

const form = ref({
  name: '',
  email: '',
  password: '',
  roles: [],
  is_approved: false,
  nif: '',
  phone: '',
})

const errors = ref({})
const loading = ref(true)
const saving = ref(false)
const success = ref('')
const generalError = ref('')

const canFillExtra = computed(() => !!form.value.is_approved)

watch(() => form.value.is_approved, (val) => {
  if (!val) {
    form.value.nif = ''
    form.value.phone = ''
  }
})

const loadUser = async () => {
  loading.value = true
  errors.value = {}
  success.value = ''
  generalError.value = ''

  try {
    const res = await http.get(`/admin/utilizadores/${props.id}`)

    form.value.name = res.data.name ?? ''
    form.value.email = res.data.email ?? ''
    form.value.roles = Array.isArray(res.data.roles) ? res.data.roles : []
    form.value.is_approved = !!res.data.is_approved
    form.value.nif = res.data.nif ?? ''
    form.value.phone = res.data.phone ?? ''
  } catch (error) {
    generalError.value = 'Erro ao carregar utilizador.'
    console.error(error?.response?.status, error?.response?.data || error)
  } finally {
    loading.value = false
  }
}

const submit = async () => {
  saving.value = true
  errors.value = {}
  success.value = ''
  generalError.value = ''

  try {
    const payload = {
      name: form.value.name,
      email: form.value.email,
      roles: form.value.roles,
      is_approved: form.value.is_approved,
      nif: canFillExtra.value ? (form.value.nif || null) : null,
      phone: canFillExtra.value ? (form.value.phone || null) : null,
    }

    if (form.value.password) payload.password = form.value.password

    await http.put(`/admin/utilizadores/${props.id}`, payload)

    success.value = 'Guardado com sucesso!'
    router.visit(route('admin.utilizadores'))
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
    } else {
      generalError.value = 'Erro ao guardar alterações.'
      console.error(error?.response?.status, error?.response?.data || error)
    }
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  try {
    const rolesRes = await http.get('/admin/roles')
    availableRoles.value = rolesRes.data
  } catch (e) {
    console.error('Erro a carregar roles:', e?.response?.status, e?.response?.data || e)
  }
  await loadUser()
})
</script>

<template>
  <AdminLayout title="Editar Utilizador">
    <div v-if="loading">A carregar utilizador...</div>

    <div v-else class="max-w-lg mx-auto">
      <div v-if="success" class="mb-4 bg-green-100 text-green-800 p-3 rounded">{{ success }}</div>
      <div v-if="generalError" class="mb-4 bg-red-100 text-red-800 p-3 rounded">{{ generalError }}</div>

      <form @submit.prevent="submit" class="bg-white p-6 shadow rounded space-y-4">
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
          <label class="block font-semibold">Password (opcional)</label>
          <input v-model="form.password" type="password" class="w-full border p-2 rounded" />
          <div v-if="errors.password" class="text-red-600 text-sm mt-1">{{ errors.password[0] }}</div>
        </div>

        <div class="flex items-center gap-2">
          <input id="approved" type="checkbox" v-model="form.is_approved" />
          <label for="approved" class="font-semibold">Conta validada</label>
        </div>
        <div v-if="errors.is_approved" class="text-red-600 text-sm">{{ errors.is_approved[0] }}</div>

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
          {{ saving ? 'A guardar...' : 'Guardar' }}
        </button>
      </form>
    </div>
  </AdminLayout>
</template>
