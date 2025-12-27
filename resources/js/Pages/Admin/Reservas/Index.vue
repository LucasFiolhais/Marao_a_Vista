<script setup>
import { ref, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import http from '@/http'

const reservas = ref([])
const pagination = ref({})
const loading = ref(true)

// filtros
const search = ref('')
const estado = ref('todas')
const alojamentoId = ref('')
const perPage = ref(10)

// dados para filtros
const alojamentos = ref([])
const estadosDisponiveis = ref([])

const fetchReservas = async (page = 1) => {
  loading.value = true
  try {
    const res = await http.get('/admin/reservas', {
      params: {
        page,
        per_page: perPage.value,
        search: search.value || null,
        estado: estado.value || null,
        alojamento_id: alojamentoId.value || null,
      },
    })

    const payload = res.data || {}

    // ✅ o teu backend devolve: { reservas: paginator, alojamentos: [], estados: [] }
    reservas.value = payload?.reservas?.data || []
    pagination.value = payload?.reservas || {}

    alojamentos.value = payload?.alojamentos || []
    estadosDisponiveis.value = payload?.estados || []
  } catch (error) {
    console.error('Erro ao carregar reservas:', error?.response?.status, error?.response?.data || error)
    reservas.value = []
    pagination.value = {}
  } finally {
    loading.value = false
  }
}

const aplicarFiltros = async () => {
  await fetchReservas(1)
}

const limparFiltros = async () => {
  search.value = ''
  estado.value = 'todas'
  alojamentoId.value = ''
  perPage.value = 10
  await fetchReservas(1)
}

const cancelarReserva = async (id) => {
  if (!confirm('Cancelar esta reserva?')) return
  try {
    await http.patch(`/admin/reservas/${id}/cancelar`)
    await fetchReservas(pagination.value.current_page || 1)
  } catch (error) {
    console.error('Erro ao cancelar reserva:', error?.response?.status, error?.response?.data || error)
  }
}

const apagarReserva = async (id) => {
  if (!confirm('Eliminar esta reserva?')) return
  try {
    await http.delete(`/admin/reservas/${id}`)
    await fetchReservas(pagination.value.current_page || 1)
  } catch (error) {
    console.error('Erro ao eliminar reserva:', error?.response?.status, error?.response?.data || error)
  }
}

const badgeEstadoClass = (e) => {
  if (e === 'confirmado') return 'bg-green-100 text-green-800'
  if (e === 'pendente') return 'bg-yellow-100 text-yellow-800'
  if (e === 'cancelado') return 'bg-red-100 text-red-800'
  return 'bg-gray-100 text-gray-800'
}

onMounted(() => fetchReservas())
</script>

<template>
  <AdminLayout title="Gestão de Reservas">
    <div class="flex justify-between items-center mb-4">
      <div class="flex gap-2">
        <input
          v-model="search"
          class="border px-3 py-2 rounded w-64"
          placeholder="Pesquisar por ID ou referência"
          @keyup.enter="aplicarFiltros"
        />

        <select v-model="estado" class="border px-3 py-2 rounded pr-10 rounded min-w-[180px]" @change="aplicarFiltros">
          <option value="todas">Todos os estados</option>
          <option v-for="e in estadosDisponiveis" :key="e" :value="e">
            {{ e }}
          </option>
        </select>

        <select v-model="alojamentoId" class="border px-3 py-2 rounded pr-10 rounded min-w-[180px]" @change="aplicarFiltros">
          <option value="">Todos os alojamentos</option>
          <option v-for="a in alojamentos" :key="a.id" :value="a.id">
            {{ a.titulo }}
          </option>
        </select>

        <select v-model.number="perPage" class="border px-3 py-2 rounded pr-10 rounded min-w-[180px]" @change="aplicarFiltros">
          <option :value="10">10</option>
          <option :value="25">25</option>
          <option :value="50">50</option>
        </select>

        <button class="border px-4 py-2 rounded" @click="limparFiltros">
          Limpar
        </button>
      </div>

      <Link
        :href="route('admin.reservas.create')"
        class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700"
      >
        Criar Reserva
      </Link>
    </div>

    <div v-if="loading">A carregar...</div>

    <div v-else>
      <table class="min-w-full bg-white rounded shadow">
        <thead>
          <tr class="bg-gray-200 text-left">
            <th class="p-3">ID</th>
            <th class="p-3">Referência</th>
            <th class="p-3">Cliente</th>
            <th class="p-3">Alojamento</th>
            <th class="p-3">Datas</th>
            <th class="p-3 text-center">Estado</th>
            <th class="p-3 text-right">Total</th>
            <th class="p-3 text-center w-64">Ações</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="r in reservas" :key="r.id" class="border-b">
            <td class="p-3">{{ r.id }}</td>
            <td class="p-3">{{ r.referencia || '-' }}</td>

            <td class="p-3">
              <div class="text-sm">
                <div class="font-medium">{{ r.user?.name || '—' }}</div>
                <div class="text-gray-500">{{ r.user?.email || '' }}</div>
              </div>
            </td>

            <td class="p-3">{{ r.alojamento?.titulo || '—' }}</td>

            <td class="p-3">
              <div class="text-sm">
                <div>{{ r.checkin }} → {{ r.checkout }}</div>
                <div class="text-gray-500">{{ r.hospedes }} hóspede(s)</div>
              </div>
            </td>

            <td class="p-3 text-center">
              <span class="inline-block text-xs px-2 py-1 rounded" :class="badgeEstadoClass(r.estado)">
                {{ r.estado }}
              </span>
            </td>

            <td class="p-3 text-right">
              {{ Number(r.total || 0).toFixed(2) }} €
            </td>

            <td class="p-3">
              <div class="flex justify-center gap-2">
                <Link
                  :href="route('admin.reservas.edit', r.id)"
                  class="bg-yellow-500 text-white px-3 py-1 rounded"
                >
                  Editar
                </Link>

                <button
                  v-if="r.estado !== 'cancelado'"
                  class="bg-red-600 text-white px-3 py-1 rounded"
                  @click="cancelarReserva(r.id)"
                >
                  Cancelar
                </button>

                <button
                  class="bg-gray-800 text-white px-3 py-1 rounded"
                  @click="apagarReserva(r.id)"
                >
                  Apagar
                </button>
              </div>
            </td>
          </tr>

          <tr v-if="!reservas.length">
            <td class="p-6 text-center text-gray-500" colspan="8">
              Sem reservas para mostrar.
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Paginação simples -->
      <div class="flex justify-between items-center mt-4">
        <div class="text-sm text-gray-600">
          Página {{ pagination.current_page || 1 }} de {{ pagination.last_page || 1 }}
        </div>

        <div class="flex gap-2">
          <button
            class="border px-3 py-1 rounded disabled:opacity-50"
            :disabled="!pagination.prev_page_url"
            @click="fetchReservas((pagination.current_page || 1) - 1)"
          >
            ← Anterior
          </button>
          <button
            class="border px-3 py-1 rounded disabled:opacity-50"
            :disabled="!pagination.next_page_url"
            @click="fetchReservas((pagination.current_page || 1) + 1)"
          >
            Seguinte →
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
