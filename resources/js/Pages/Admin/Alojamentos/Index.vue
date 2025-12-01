<script setup>
import { ref, onMounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import backend from '@/axiosBackend'

const alojamentos = ref([])
const pagination = ref({})
const loading = ref(true)

// índice da foto atual por alojamento: { [id]: number }
const currentPhotoIndex = ref({})

const fetchAlojamentos = async (page = 1) => {
  loading.value = true

  try {
    const res = await backend.get('/alojamentos', { params: { page } })
    alojamentos.value = res.data.data
    pagination.value = res.data
  } catch (error) {
    console.error('Erro ao carregar alojamentos:', error)
  } finally {
    loading.value = false
  }
}

const deleteAlojamento = async (id) => {
  if (!confirm('Eliminar alojamento?')) return

  try {
    await backend.delete(`/alojamentos/${id}`)
    await fetchAlojamentos(pagination.value.current_page || 1)
  } catch (error) {
    console.error('Erro ao eliminar alojamento:', error)
  }
}

// devolve a foto atual (ou null se não houver)
const getCurrentPhoto = (alojamento) => {
  const fotos = alojamento.fotos || []
  if (!fotos.length) return null

  const index = currentPhotoIndex.value[alojamento.id] ?? 0
  return fotos[index]
}

const nextPhoto = (alojamento) => {
  const fotos = alojamento.fotos || []
  if (!fotos.length) return

  const actual = currentPhotoIndex.value[alojamento.id] ?? 0
  const next = (actual + 1) % fotos.length
  currentPhotoIndex.value = {
    ...currentPhotoIndex.value,
    [alojamento.id]: next,
  }
}

const prevPhoto = (alojamento) => {
  const fotos = alojamento.fotos || []
  if (!fotos.length) return

  const actual = currentPhotoIndex.value[alojamento.id] ?? 0
  const prev = (actual - 1 + fotos.length) % fotos.length
  currentPhotoIndex.value = {
    ...currentPhotoIndex.value,
    [alojamento.id]: prev,
  }
}

onMounted(() => fetchAlojamentos())
</script>

<template>
  <AdminLayout title="Gestão de Alojamentos">
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-2xl font-bold">Alojamentos</h1>

      <Link
        :href="route('admin.alojamentos.create')"
        class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700"
      >
        Criar Alojamento
      </Link>
    </div>

    <div v-if="loading">A carregar...</div>

    <div v-else>
      <div
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6"
      >
        <div
          v-for="a in alojamentos"
          :key="a.id"
          class="bg-white rounded-xl shadow overflow-hidden flex flex-col"
        >
          <!-- Área da imagem maior -->
          <div class="relative h-56 md:h-64 bg-gray-200 flex items-center justify-center overflow-hidden">
            <template v-if="(a.fotos && a.fotos.length) || a.foto_principal">
              <img
                :src="getCurrentPhoto(a)?.url || a.foto_principal"
                alt="Foto do alojamento"
                class="w-full h-full object-cover"
              />

              <!-- setas -->
              <template v-if="a.fotos && a.fotos.length > 1">
                <button
                  type="button"
                  class="absolute left-2 top-1/2 -translate-y-1/2 bg-black/50 text-white rounded-full w-8 h-8 flex items-center justify-center text-lg"
                  @click.stop="prevPhoto(a)"
                >
                  ‹
                </button>

                <button
                  type="button"
                  class="absolute right-2 top-1/2 -translate-y-1/2 bg-black/50 text-white rounded-full w-8 h-8 flex items-center justify-center text-lg"
                  @click.stop="nextPhoto(a)"
                >
                  ›
                </button>

                <!-- indicador de posição -->
                <div class="absolute bottom-2 right-2 bg-black/60 text-white text-xs px-2 py-1 rounded">
                  {{ (currentPhotoIndex[a.id] ?? 0) + 1 }}/{{ a.fotos.length }}
                </div>
              </template>
            </template>

            <span v-else class="text-gray-500 text-sm">
              Sem foto
            </span>
          </div>

          <!-- Conteúdo do card -->
          <div class="p-4 flex-1 flex flex-col">
            <h2 class="font-semibold text-lg mb-1">
              {{ a.titulo }}
            </h2>

            <p class="text-sm text-gray-600 mb-3 line-clamp-4">
              {{ a.descricao }}
            </p>

            <div class="mt-auto flex items-center justify-between pt-2 border-t">
              <span class="font-bold text-indigo-700">
                {{ a.preco_noite }} €
                <span class="text-sm font-normal text-gray-500">/ noite</span>
              </span>

              <div class="flex gap-2">
                <Link
                  :href="route('admin.alojamentos.edit', a.id)"
                  class="bg-yellow-500 text-white text-sm px-3 py-1 rounded"
                >
                  Editar
                </Link>

                <button
                  class="bg-red-600 text-white text-sm px-3 py-1 rounded"
                  @click="deleteAlojamento(a.id)"
                >
                  Eliminar
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- (se quiseres depois, podemos pôr paginação visual aqui) -->
    </div>
  </AdminLayout>
</template>
