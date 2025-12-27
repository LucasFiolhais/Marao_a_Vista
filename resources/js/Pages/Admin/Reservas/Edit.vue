<script setup>
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import http from '@/http'

const props = defineProps({
  id: {
    type: [Number, String],
    required: true,
  },
})

const form = ref({
  user_id: '',
  alojamento_id: '',
  checkin: '',
  checkout: '',
  hospedes: 1,
  estado: '',
  observacoes: '',
  referencia: '',
  total: 0,
})

const users = ref([])
const alojamentos = ref([])

const loading = ref(true)
const saving = ref(false)
const errors = ref({})

const fetchOptions = async () => {
  const [usersRes, alojRes] = await Promise.all([
    http.get('/admin/options/utilizadores'),
      http.get('/admin/options/alojamentos')
    ])

    users.value = usersRes.data || []
    alojamentos.value = alojRes.data || []
}

const fetchReserva = async () => {
  const res = await http.get(`/admin/reservas/${props.id}`)
  const r = res.data

  form.value = {
    user_id: r.user_id,
    alojamento_id: r.alojamento_id,
    checkin: r.checkin,
    checkout: r.checkout,
    hospedes: r.hospedes,
    estado: r.estado,
    observacoes: r.observacoes || '',
    referencia: r.referencia || '',
    total: r.total || 0,
  }
}

const submit = async () => {
  saving.value = true
  errors.value = {}

  try {
    await http.put(`/admin/reservas/${props.id}`, {
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
      console.error('Erro ao atualizar reserva:', error?.response?.status, error?.response?.data || error)
    }
  } finally {
    saving.value = false
  }
}

const cancelarReserva = async () => {
  if (!confirm('Cancelar esta reserva?')) return
  try {
    await http.patch(`/admin/reservas/${props.id}/cancelar`)
    await fetchReserva() // refresca estado
  } catch (error) {
    console.error('Erro ao cancelar reserva:', error?.response?.status, error?.response?.data || error)
  }
}

onMounted(async () => {
  try {
    await fetchOptions()
    await fetchReserva()
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <AdminLayout title="Editar Reserva">
    <div class="max-w-3xl mx-auto bg-white shadow rounded-lg p-6">
      <div class="flex justify-between mb-4">
        <h1 class="text-xl font-bold">Editar Reserva #{{ props.id }}</h1>
        <button class="text-sm text-gray-600" @click="router.visit('/admin/reservas')">
          ← Voltar
        </button>
      </div>

      <div v-if="loading" class="text-sm text-gray-500">
        A carregar reserva...
      </div>

      <form v-else class="space-y-4" @submit.prevent="submit">
        <!-- Referência / Total (info) -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="text-xs text-gray-500">Referência</label>
            <input :value="form.referencia" disabled class="w-full bg-gray-100 border px-3 py-2 rounded" />
          </div>
          <div>
            <label class="text-xs text-gray-500">Total</label>
            <input
              :value="Number(form.total || 0).toFixed(2) + ' €'"
              disabled
              class="w-full bg-gray-100 border px-3 py-2 rounded"
            />
          </div>
        </div>

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

        <!-- Estado (read-only) -->
        <div>
          <label class="text-xs text-gray-500">Estado</label>
          <input :value="form.estado" disabled class="w-full bg-gray-100 border px-3 py-2 rounded" />
          <p class="text-xs text-gray-500 mt-1">
            O estado passa para <b>confirmado</b> automaticamente quando o pagamento é concluído.
          </p>
        </div>

        <!-- Observações -->
        <div>
          <textarea v-model="form.observacoes" rows="3" class="w-full border px-3 py-2 rounded"></textarea>
          <p v-if="errors.observacoes" class="text-sm text-red-600 mt-1">
            {{ errors.observacoes[0] }}
          </p>
        </div>

        <!-- Botões -->
        <div class="flex justify-between pt-4 border-t">
          <button
            v-if="form.estado !== 'cancelado'"
            type="button"
            class="bg-red-600 text-white px-4 py-2 rounded"
            @click="cancelarReserva"
          >
            Cancelar Reserva
          </button>

          <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded">
            {{ saving ? 'A guardar...' : 'Guardar Alterações' }}
          </button>
        </div>
      </form>
    </div>
  </AdminLayout>
</template>
