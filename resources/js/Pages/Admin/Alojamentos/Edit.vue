<script setup>
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import http from '@/http'

const props = defineProps({
  id: Number,
})

const loading = ref(false)
const saving = ref(false)
const deleting = ref(false)

const form = ref({
  titulo: '',
  descricao: '',
  preco_noite: '',
})

const errors = ref({})

const fotosExistentes = ref([]) // [{id, url}...]
const fotosInput = ref(null)
const uploadingFotos = ref(false)
const deletingFotoId = ref(null)

const loadAlojamento = async () => {
  loading.value = true
  errors.value = {}

  try {
    const res = await http.get(`/admin/alojamentos/${props.id}`)

    form.value.titulo = res.data?.titulo ?? ''
    form.value.descricao = res.data?.descricao ?? ''
    form.value.preco_noite = res.data?.preco_noite ?? ''

    // Ajusta conforme o teu backend devolve as fotos.
    // Se devolver "fotos": [{id, url}, ...]
    fotosExistentes.value = Array.isArray(res.data?.fotos) ? res.data.fotos : []
  } catch (e) {
    console.error(e)
    alert('Erro ao carregar alojamento.')
  } finally {
    loading.value = false
  }
}

const submit = async () => {
  saving.value = true
  errors.value = {}

  try {
    await http.put(`/admin/alojamentos/${props.id}`, {
      titulo: form.value.titulo,
      descricao: form.value.descricao,
      preco_noite: form.value.preco_noite,
    })

    // recarrega (para garantir estado alinhado)
    await loadAlojamento()
    alert('Alojamento atualizado com sucesso.')
  } catch (error) {
    console.error('Erro ao atualizar alojamento:', error)
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
    } else {
      alert('Erro ao atualizar alojamento.')
    }
  } finally {
    saving.value = false
  }
}

const uploadFotos = async () => {
  if (!fotosInput.value || !fotosInput.value.files.length) return

  uploadingFotos.value = true
  errors.value = {}

  try {
    const formData = new FormData()
    Array.from(fotosInput.value.files).forEach((file) => {
      formData.append('fotos[]', file)
    })

    await http.post(`/admin/alojamentos/${props.id}/fotos`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    fotosInput.value.value = '' // limpa input
    await loadAlojamento()
  } catch (error) {
    console.error('Erro ao enviar fotos:', error)
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
    } else {
      alert('Erro ao enviar fotos.')
    }
  } finally {
    uploadingFotos.value = false
  }
}

const deleteFoto = async (fotoId) => {
  if (!confirm('Eliminar esta foto?')) return

  deletingFotoId.value = fotoId
  try {
    await http.delete(`/admin/alojamentos/fotos/${fotoId}`)
    await loadAlojamento()
  } catch (e) {
    console.error(e)
    alert('Erro ao eliminar foto.')
  } finally {
    deletingFotoId.value = null
  }
}

const deleteAlojamento = async () => {
  if (!confirm('Tens a certeza que queres eliminar este quarto?')) return

  deleting.value = true
  try {
    await http.delete(`/admin/alojamentos/${props.id}`)
    router.visit(route('admin.alojamentos'))
  } catch (e) {
    console.error(e)
    alert('Erro ao eliminar quarto.')
  } finally {
    deleting.value = false
  }
}

onMounted(() => loadAlojamento())
</script>

<template>
  <AdminLayout title="Editar Quarto">
    <div v-if="loading" class="py-6">A carregar...</div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- FORM -->
      <form
        @submit.prevent="submit"
        class="bg-white p-6 shadow rounded space-y-4"
      >
        <div>
          <label class="block font-semibold">Título</label>
          <input v-model="form.titulo" class="w-full border p-2 rounded" required />
          <div v-if="errors.titulo" class="text-red-600 text-sm mt-1">
            {{ errors.titulo[0] }}
          </div>
        </div>

        <div>
          <label class="block font-semibold">Descrição</label>
          <textarea v-model="form.descricao" class="w-full border p-2 rounded" required />
          <div v-if="errors.descricao" class="text-red-600 text-sm mt-1">
            {{ errors.descricao[0] }}
          </div>
        </div>

        <div>
          <label class="block font-semibold">Preço/noite (€)</label>
          <input
            v-model="form.preco_noite"
            type="number"
            step="0.01"
            class="w-full border p-2 rounded"
            required
          />
          <div v-if="errors.preco_noite" class="text-red-600 text-sm mt-1">
            {{ errors.preco_noite[0] }}
          </div>
        </div>

        <div class="flex gap-3">
          <button
            class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 disabled:opacity-50"
            :disabled="saving"
          >
            {{ saving ? 'A guardar...' : 'Guardar' }}
          </button>

          <button
            type="button"
            class="px-4 py-2 border rounded hover:bg-gray-50"
            @click="router.visit(route('admin.alojamentos'))"
          >
            Voltar
          </button>

          <button
            type="button"
            class="ml-auto px-4 py-2 rounded bg-red-600 text-white hover:bg-red-700 disabled:opacity-50"
            :disabled="deleting"
            @click="deleteAlojamento"
          >
            {{ deleting ? 'A eliminar...' : 'Eliminar quarto' }}
          </button>
        </div>
      </form>

      <!-- FOTOS -->
      <div class="bg-white p-6 shadow rounded space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="font-semibold text-lg">Fotos</h2>
        </div>

        <div>
          <label class="block font-semibold mb-1">Adicionar fotos</label>
          <input type="file" multiple ref="fotosInput" />
          <div class="flex items-center gap-3 mt-2">
            <button
              class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50"
              :disabled="uploadingFotos"
              type="button"
              @click="uploadFotos"
            >
              {{ uploadingFotos ? 'A enviar...' : 'Enviar fotos' }}
            </button>

            <p class="text-xs text-gray-500">Pode selecionar várias imagens.</p>
          </div>

          <div v-if="errors['fotos.*']" class="text-red-600 text-sm mt-2">
            {{ errors['fotos.*'][0] }}
          </div>
          <div v-if="errors.fotos" class="text-red-600 text-sm mt-2">
            {{ errors.fotos[0] }}
          </div>
        </div>

        <div v-if="fotosExistentes.length === 0" class="text-gray-500">
          Ainda não há fotos.
        </div>

        <div v-else class="grid grid-cols-2 md:grid-cols-3 gap-3">
          <div
            v-for="f in fotosExistentes"
            :key="f.id"
            class="border rounded overflow-hidden"
          >
            <div class="aspect-square bg-gray-50 overflow-hidden">
              <img
                v-if="f.url"
                :src="f.url"
                alt="Foto"
                class="w-full h-full object-cover"
              />
              <div v-else class="p-2 text-xs text-gray-500">
                Sem URL (ajusta o backend para devolver url)
              </div>
            </div>

            <div class="p-2 flex items-center justify-between">
              <span class="text-xs text-gray-600">#{{ f.id }}</span>

              <button
                class="text-red-600 text-sm hover:underline disabled:opacity-50"
                type="button"
                :disabled="deletingFotoId === f.id"
                @click="deleteFoto(f.id)"
              >
                {{ deletingFotoId === f.id ? 'A eliminar...' : 'Eliminar' }}
              </button>
            </div>
          </div>
        </div>

        
      </div>
    </div>
  </AdminLayout>
</template>
