<script setup>
import { ref, onMounted, watch } from 'vue'
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import http from '@/http'

const alojamentos = ref([])
const pagination = ref({
  current_page: 1,
  last_page: 1,
  next_page_url: null,
  prev_page_url: null,
})

const loading = ref(false)
const errorMsg = ref('')
const deletingId = ref(null)

const q = ref('')

// 👇 índice da foto atual por alojamento (key = alojamento.id)
const fotoIndex = ref({})

const normalizeApiUrl = (url) => {
  if (!url) return null
  return url.replace(/^.*\/api/, '')
}

const setDefaultFotoIndex = (items) => {
  // garante que cada alojamento começa na foto 0
  const map = { ...fotoIndex.value }
  items.forEach((a) => {
    if (map[a.id] === undefined) map[a.id] = 0
  })
  fotoIndex.value = map
}

const fetchAlojamentos = async (url = null) => {
  loading.value = true
  errorMsg.value = ''

  try {
    if (url) {
      const endpoint = normalizeApiUrl(url)
      const res = await http.get(endpoint)

      const items = Array.isArray(res.data?.data) ? res.data.data : []
      alojamentos.value = items
      setDefaultFotoIndex(items)

      pagination.value = {
        current_page: res.data.current_page ?? 1,
        last_page: res.data.last_page ?? 1,
        next_page_url: res.data.next_page_url ?? null,
        prev_page_url: res.data.prev_page_url ?? null,
      }
      return
    }

    const res = await http.get('/admin/alojamentos', {
      params: { q: q.value || undefined },
    })

    const items = Array.isArray(res.data?.data) ? res.data.data : []
    alojamentos.value = items
    setDefaultFotoIndex(items)

    pagination.value = {
      current_page: res.data.current_page ?? 1,
      last_page: res.data.last_page ?? 1,
      next_page_url: res.data.next_page_url ?? null,
      prev_page_url: res.data.prev_page_url ?? null,
    }
  } catch (e) {
    console.error(e)
    errorMsg.value = 'Erro ao carregar alojamentos.'
    alojamentos.value = []
  } finally {
    loading.value = false
  }
}

const deleteAlojamento = async (id) => {
  if (!confirm('Eliminar este alojamento?')) return

  deletingId.value = id
  try {
    await http.delete(`/admin/alojamentos/${id}`)
    await fetchAlojamentos()
  } catch (e) {
    console.error(e)
    alert('Erro ao eliminar alojamento.')
  } finally {
    deletingId.value = null
  }
}

const getFotos = (a) => Array.isArray(a?.fotos) ? a.fotos : []

const getFotoAtualUrl = (a) => {
  const fotos = getFotos(a)
  if (fotos.length > 0) {
    const idx = fotoIndex.value[a.id] ?? 0
    return fotos[idx]?.url ?? null
  }
  // fallback para capa
  return a.foto_capa_url ?? null
}

const prevFoto = (a) => {
  const fotos = getFotos(a)
  if (fotos.length <= 1) return

  const current = fotoIndex.value[a.id] ?? 0
  fotoIndex.value = {
    ...fotoIndex.value,
    [a.id]: (current - 1 + fotos.length) % fotos.length,
  }
}

const nextFoto = (a) => {
  const fotos = getFotos(a)
  if (fotos.length <= 1) return

  const current = fotoIndex.value[a.id] ?? 0
  fotoIndex.value = {
    ...fotoIndex.value,
    [a.id]: (current + 1) % fotos.length,
  }
}

const goFoto = (a, idx) => {
  const fotos = getFotos(a)
  if (!fotos.length) return
  fotoIndex.value = { ...fotoIndex.value, [a.id]: idx }
}

watch(q, () => fetchAlojamentos())
onMounted(() => fetchAlojamentos())
</script>

<template>
  <AdminLayout title="Gestão de Quartos">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
      <div class="flex-1 max-w-xl">
        <input
          v-model="q"
          type="text"
          placeholder="Pesquisar por título ou ID..."
          class="w-full border rounded-lg px-3 py-2"
        />
      </div>

      <Link
        :href="route('admin.alojamentos.create')"
        class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 text-center"
      >
        Criar Quarto
      </Link>
    </div>

    <div v-if="errorMsg" class="mb-4 p-3 rounded bg-red-50 text-red-700">
      {{ errorMsg }}
    </div>

    <div v-if="loading" class="py-8">A carregar...</div>

    <div v-else>
      <div v-if="alojamentos.length === 0" class="text-gray-500 bg-white p-6 rounded shadow">
        Sem alojamentos.
      </div>

      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        <div
          v-for="a in alojamentos"
          :key="a.id"
          class="bg-white rounded-xl shadow overflow-hidden border"
        >
          
          <div class="relative h-60 bg-gray-100"> 
            <img
              v-if="getFotoAtualUrl(a)"
              :src="getFotoAtualUrl(a)"
              class="w-full h-full object-cover"
              alt="Foto"
            />

            <!-- Setas (só aparecem se houver +1 foto) -->
            <button
              v-if="getFotos(a).length > 1"
              type="button"
              class="absolute left-2 top-1/2 -translate-y-1/2 bg-black/40 text-white w-9 h-9 rounded-full hover:bg-black/60 flex items-center justify-center"
              @click="prevFoto(a)"
              aria-label="Foto anterior"
            >
              ‹
            </button>

            <button
              v-if="getFotos(a).length > 1"
              type="button"
              class="absolute right-2 top-1/2 -translate-y-1/2 bg-black/40 text-white w-9 h-9 rounded-full hover:bg-black/60 flex items-center justify-center"
              @click="nextFoto(a)"
              aria-label="Foto seguinte"
            >
              ›
            </button>

            <!-- Dots -->
            <div
              v-if="getFotos(a).length > 1"
              class="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-1.5 bg-black/25 px-2 py-1 rounded-full"
            >
              <button
                v-for="(f, idx) in getFotos(a)"
                :key="f.id ?? idx"
                type="button"
                class="w-2 h-2 rounded-full"
                :class="(fotoIndex[a.id] ?? 0) === idx ? 'bg-white' : 'bg-white/50'"
                @click="goFoto(a, idx)"
                aria-label="Ir para foto"
              />
            </div>
          </div>

          <div class="p-4 space-y-2">
            <div class="flex items-start justify-between gap-3">
              <div>
                <h3 class="font-semibold text-lg leading-tight">
                  {{ a.titulo }}
                </h3>
                <p class="text-xs text-gray-500">ID: {{ a.id }}</p>
              </div>

              <div class="text-right">
                <div class="font-semibold">
                  {{ Number(a.preco_noite ?? 0).toFixed(2) }} €
                </div>
                <div class="text-xs text-gray-500">/ noite</div>
              </div>
            </div>

            <p class="text-sm text-gray-600 line-clamp-2">
              {{ a.descricao }}
            </p>

            <div class="pt-3 flex items-center justify-between">
              <Link
                :href="route('admin.alojamentos.edit', a.id)"
                class="text-indigo-600 hover:underline"
              >
                Editar
              </Link>

              <button
                class="text-red-600 hover:underline disabled:opacity-50"
                :disabled="deletingId === a.id"
                @click="deleteAlojamento(a.id)"
              >
                {{ deletingId === a.id ? 'A eliminar...' : 'Eliminar' }}
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="flex items-center justify-between mt-6">
        <button
          class="px-3 py-2 border rounded disabled:opacity-50"
          :disabled="!pagination.prev_page_url"
          @click="fetchAlojamentos(pagination.prev_page_url)"
        >
          Anterior
        </button>

        <div class="text-sm text-gray-600">
          Página {{ pagination.current_page }} / {{ pagination.last_page }}
        </div>

        <button
          class="px-3 py-2 border rounded disabled:opacity-50"
          :disabled="!pagination.next_page_url"
          @click="fetchAlojamentos(pagination.next_page_url)"
        >
          Próxima
        </button>
      </div>
    </div>
  </AdminLayout>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
