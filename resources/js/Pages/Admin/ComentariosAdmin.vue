<script setup>
import { ref, onMounted } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import http from '@/http'

const comentarios = ref([])
const pagination = ref(null)
const loading = ref(false)
const statusFilter = ref('todos') // todos | pendentes | aprovados

const respostaTexto = ref('')
const comentarioSelecionado = ref(null)
const erroResposta = ref('')

const setStatusFilter = (value) => {
  if (statusFilter.value === value) return
  statusFilter.value = value
  fetchComentarios()
}

const normalizeUrl = (url) => {
  if (!url) return null
  if (url.startsWith('http')) {
    const u = new URL(url)
    return u.pathname + u.search
  }
  return url
}

const fetchComentarios = async (url = null) => {
  loading.value = true
  try {
    const res = await http.get(normalizeUrl(url) || '/admin/comentarios', {
      params: { status: statusFilter.value },
    })

    const payload = res.data || {}

    // suporta:
    // { data: [...] }
    // { comentarios: { data: [...] } }
    const paginator = payload.comentarios || payload

    comentarios.value = Array.isArray(paginator.data)
      ? paginator.data
      : []

    pagination.value = {
      current_page: paginator.current_page ?? 1,
      last_page: paginator.last_page ?? 1,
      next_page_url: paginator.next_page_url ?? null,
      prev_page_url: paginator.prev_page_url ?? null,
    }
  } catch (e) {
    console.error('Erro ao carregar comentários', e)
    comentarios.value = []
    pagination.value = null
  } finally {
    loading.value = false
  }
}

const aprovar = async (comentario) => {
  await http.post(`/admin/comentarios/${comentario.id}/aprovar`)
  fetchComentarios()
}

const apagar = async (comentario) => {
  if (!confirm('Tens a certeza que queres apagar este comentário?')) return
  await http.delete(`/admin/comentarios/${comentario.id}`)
  fetchComentarios()
}

const abrirResposta = (comentario) => {
  comentarioSelecionado.value = comentario
  respostaTexto.value = comentario.resposta_admin || ''
  erroResposta.value = ''
}

const enviarResposta = async () => {
  if (!comentarioSelecionado.value) return

  try {
    await http.post(
      `/admin/comentarios/${comentarioSelecionado.value.id}/responder`,
      { resposta_admin: respostaTexto.value }
    )

    comentarioSelecionado.value = null
    respostaTexto.value = ''
    fetchComentarios()
  } catch {
    erroResposta.value = 'Erro ao guardar a resposta.'
  }
}

onMounted(() => fetchComentarios())
</script>

<template>
  <AdminLayout title="Gestão de Comentários">
    <!-- Tabs -->
    <div class="flex gap-2 mb-4">
      <button
        v-for="s in ['todos','pendentes','aprovados']"
        :key="s"
        class="px-4 py-2 rounded border"
        :class="statusFilter === s
          ? 'bg-indigo-600 text-white'
          : 'bg-white text-gray-700 hover:bg-gray-100'"
        @click="setStatusFilter(s)"
      >
        {{ s.charAt(0).toUpperCase() + s.slice(1) }}
      </button>
    </div>

    <div v-if="loading">A carregar...</div>

    <div v-else>
      <div v-if="!comentarios.length" class="text-gray-500">
        Nenhum comentário encontrado.
      </div>

      <div
        v-for="comentario in comentarios"
        :key="comentario.id"
        class="bg-white shadow rounded p-4 mb-3"
      >
        <div class="flex justify-between mb-2">
          <div>
            <p class="font-semibold">
              {{ comentario.user?.name || 'Utilizador' }}
              <span class="text-xs text-gray-500">
                ({{ comentario.user?.email || '-' }})
              </span>
            </p>

            <p class="text-sm text-gray-500">
              {{ comentario.alojamento?.titulo || '-' }}
            </p>
          </div>

          <span
            class="px-3 py-1 rounded text-xs"
            :class="comentario.aprovado
              ? 'bg-green-100 text-green-700'
              : 'bg-yellow-100 text-yellow-700'"
          >
            {{ comentario.aprovado ? 'Aprovado' : 'Pendente' }}
          </span>
        </div>
        <!-- Título -->
          <p v-if="comentario.titulo" class="font-semibold mb-1">
          Titulo: {{ comentario.titulo }}
        </p>

        <p class="text-sm text-gray-500 mb-2">
          Rating: {{ comentario.rating != null ? `${comentario.rating}/5` : '-' }}
        </p>

        <p class="mb-3">Texto: {{ comentario.texto }}</p>

        <div v-if="comentario.resposta_admin" class="bg-gray-50 p-3 mb-3">
          <strong>Resposta:</strong> {{ comentario.resposta_admin }}
        </div>

        <div class="flex gap-2">
          <button
            v-if="!comentario.aprovado"
            @click="aprovar(comentario)"
            class="bg-green-600 text-white px-3 py-1 rounded"
          >
            Aprovar
          </button>

          <button
            @click="abrirResposta(comentario)"
            class="bg-blue-600 text-white px-3 py-1 rounded"
          >
            Responder
          </button>

          <button
            @click="apagar(comentario)"
            class="bg-red-600 text-white px-3 py-1 rounded"
          >
            Apagar
          </button>
        </div>
      </div>

      <!-- Paginação -->
      <div
        v-if="pagination && (pagination.prev_page_url || pagination.next_page_url)"
        class="flex justify-between mt-4"
      >
        <button
          :disabled="!pagination.prev_page_url"
          @click="fetchComentarios(pagination.prev_page_url)"
        >
          ← Anterior
        </button>

        <span>
          Página {{ pagination.current_page }} de {{ pagination.last_page }}
        </span>

        <button
          :disabled="!pagination.next_page_url"
          @click="fetchComentarios(pagination.next_page_url)"
        >
          Seguinte →
        </button>
      </div>
    </div>

    <!-- Modal resposta -->
    <div v-if="comentarioSelecionado" class="fixed inset-0 bg-black/40 flex items-center justify-center">
      <div class="bg-white p-4 rounded w-full max-w-lg">
        <textarea v-model="respostaTexto" rows="4" class="w-full border rounded" />
        <p v-if="erroResposta" class="text-red-600">{{ erroResposta }}</p>
        <div class="flex justify-end gap-2 mt-2">
          <button @click="comentarioSelecionado = null">Cancelar</button>
          <button @click="enviarResposta" class="bg-indigo-600 text-white px-3 py-1 rounded">
            Guardar
          </button>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
