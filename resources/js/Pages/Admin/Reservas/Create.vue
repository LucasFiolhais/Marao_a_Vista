<script setup>
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import http from '@/http'

const form = ref({
  user_id: '',
  alojamento_id: '',
  checkin: '',
  checkout: '',
  hospedes: 1,
  observacoes: '',
})

const users = ref([])
const alojamentos = ref([])

const errors = ref({})
const saving = ref(false)
const loading = ref(true)

const fetchOptions = async () => {
  loading.value = true
  try {
    // depende do teu API admin já ter estes endpoints (como tens para utilizadores/alojamentos)
    const [usersRes, alojRes] = await Promise.all([
      http.get('/admin/options/utilizadores'),
      http.get('/admin/options/alojamentos')
    ])

    users.value = usersRes.data || []
    alojamentos.value = alojRes.data || []
  } catch (error) {
    console.error('Erro ao carregar opções:', error?.response?.status, error?.response?.data || error)
  } finally {
    loading.value = false 
  }
}

const submit = async () => {
  saving.value = true
  errors.value = {}

  try {
    await http.post('/admin/reservas', {
      user_id: form.value.user_id,
      alojamento_id: form.value.alojamento_id,
      checkin: form.value.checkin,
      checkout: form.value.checkout,
      hospedes: form.value.hospedes,
      observacoes: form.value.observacoes,
    })

    router.visit('/admin/reservas')
  } catch (error) {
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
    } else {
      console.error('Erro ao criar reserva:', error?.response?.status, error?.response?.data || error)
    }
  } finally {
    saving.value = false
  }
}

onMounted(fetchOptions)
</script>

<template>
  <AdminLayout title="Criar Reserva">
    <div class="max-w-3xl mx-auto bg-white shadow rounded-lg p-6">
      <div class="flex justify-between mb-4">
        <h1 class="text-xl font-bold">Criar Reserva</h1>
        <button class="text-sm text-gray-600" @click="router.visit('/admin/reservas')">
          ← Voltar
        </button>
      </div>

      <div v-if="loading" class="text-sm text-gray-500">
        A carregar dados...
      </div>

      <form v-else class="space-y-4" @submit.prevent="submit">
        <!-- Cliente -->
        <div>
          <select v-model="form.user_id" class="w-full border px-3 py-2 rounded">
            <option value="">Selecionar cliente</option>
            <option v-for="u in users" :key="u.id" :value="u.id">
              {{ u.name }} ({{ u.email }})
            </option>
          </select>
          <p v-if="errors.user_id" class="text-sm text-red-600 mt-1">{{ errors.user_id[0] }}</p>
        </div>

        <!-- Alojamento -->
        <div>
          <select v-model="form.alojamento_id" class="w-full border px-3 py-2 rounded">
            <option value="">Selecionar alojamento</option>
            <option v-for="a in alojamentos" :key="a.id" :value="a.id">
              {{ a.titulo }}
            </option>
          </select>
          <p v-if="errors.alojamento_id" class="text-sm text-red-600 mt-1">
            {{ errors.alojamento_id[0] }}
          </p>
        </div>

        <!-- Datas -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <input v-model="form.checkin" type="date" class="w-full border px-3 py-2 rounded" />
            <p v-if="errors.checkin" class="text-sm text-red-600 mt-1">{{ errors.checkin[0] }}</p>
          </div>

          <div>
            <input v-model="form.checkout" type="date" class="w-full border px-3 py-2 rounded" />
            <p v-if="errors.checkout" class="text-sm text-red-600 mt-1">{{ errors.checkout[0] }}</p>
          </div>
        </div>

        <!-- Hóspedes -->
        <div>
          <input
            v-model.number="form.hospedes"
            type="number"
            min="1"
            class="w-full border px-3 py-2 rounded"
          />
          <p v-if="errors.hospedes" class="text-sm text-red-600 mt-1">{{ errors.hospedes[0] }}</p>
        </div>

        <!-- Observações -->
        <div>
          <textarea
            v-model="form.observacoes"
            rows="3"
            class="w-full border px-3 py-2 rounded"
            placeholder="Observações (opcional)"
          ></textarea>
          <p v-if="errors.observacoes" class="text-sm text-red-600 mt-1">
            {{ errors.observacoes[0] }}
          </p>
        </div>

        <!-- Botões -->
        <div class="flex justify-end gap-2 pt-4 border-t">
          <button type="button" class="border px-4 py-2 rounded" @click="router.visit('/admin/reservas')">
            Cancelar
          </button>
          <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">
            {{ saving ? 'A guardar...' : 'Criar Reserva' }}
          </button>
        </div>

        <p class="text-xs text-gray-500">
          Nota: o estado fica <b>pendente</b>. Passa a <b>confirmado</b> apenas após pagamento.
        </p>
      </form>
    </div>
  </AdminLayout>
</template>
