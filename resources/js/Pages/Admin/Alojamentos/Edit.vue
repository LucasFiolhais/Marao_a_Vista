<script setup>
import { ref, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import backend from '@/axiosBackend'

const props = defineProps({
  id: {
    type: [Number, String],
    required: true,
  },
})

const form = ref({
  titulo: '',
  descricao: '',
  preco_noite: '',
})

const fotos = ref([]) // {id, url}
const novaFotos = ref(null)

const errors = ref({})
const loading = ref(true)
const saving = ref(false)
const uploading = ref(false)

const loadAlojamento = async () => {
  loading.value = true
  errors.value = {}

  try {
    const res = await backend.get(`/alojamentos/${props.id}`)
    form.value.titulo = res.data.titulo
    form.value.descricao = res.data.descricao
    form.value.preco_noite = res.data.preco_noite
    fotos.value = res.data.fotos || []
  } catch (error) {
    console.error('Erro ao carregar alojamento:', error)
  } finally {
    loading.value = false
  }
}

const submit = async () => {
  saving.value = true
  errors.value = {}

  try {
    await backend.put(`/alojamentos/${props.id}`, form.value)
    router.visit(route('admin.alojamentos'))
  } catch (error) {
    console.error('Erro ao atualizar alojamento:', error)
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors || {}
    }
  } finally {
    saving.value = false
  }
}

const uploadFotos = async () => {
  if (!novaFotos.value || !novaFotos.value.files.length) return

  uploading.value = true

  try {
    const formData = new FormData()
    Array.from(novaFotos.value.files).forEach(file => {
      formData.append('fotos[]', file)
    })

    const res = await backend.post(
      `/alojamentos/${props.id}/fotos`,
      formData,
      {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
      }
    )

    // juntar as novas fotos à lista
    fotos.value.push(...res.data.fotos)
    novaFotos.value.value = '' // limpar input
  } catch (error) {
    console.error('Erro ao enviar fotos:', error)
  } finally {
    uploading.value = false
  }
}

const apagarFoto = async (fotoId) => {
  if (!confirm('Eliminar esta foto?')) return

  try {
    await backend.delete(`/alojamentos/fotos/${fotoId}`)
    fotos.value = fotos.value.filter(f => f.id !== fotoId)
  } catch (error) {
    console.error('Erro ao eliminar foto:', error)
  }
}

onMounted(loadAlojamento)
</script>

<template>
  <AdminLayout title="Editar Alojamento">
    <div v-if="loading">A carregar alojamento...</div>

    <form
      v-else
      @submit.prevent="submit"
      class="max-w-3xl mx-auto bg-white p-6 shadow rounded space-y-6"
    >
      <!-- dados básicos -->
      <div class="space-y-4">
        <div>
          <label class="block font-semibold">Título</label>
          <input
            v-model="form.titulo"
            class="w-full border p-2 rounded"
            required
          />
          <div v-if="errors.titulo" class="text-red-600 text-sm mt-1">
            {{ errors.titulo[0] }}
          </div>
        </div>

        <div>
          <label class="block font-semibold">Descrição</label>
          <textarea
            v-model="form.descricao"
            class="w-full border p-2 rounded"
            required
          ></textarea>
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
      </div>

      <!-- gestão de fotos -->
      <div class="border-t pt-4">
        <h2 class="font-semibold mb-2">Fotos do alojamento</h2>

        <div class="mb-3">
          <input
            type="file"
            multiple
            ref="novaFotos"
            @change="uploadFotos"
          />
          <p class="text-xs text-gray-500 mt-1">
            Pode selecionar várias imagens de uma vez.
          </p>
        </div>

        <div v-if="uploading" class="text-sm text-gray-500 mb-2">
          A enviar fotos...
        </div>

        <div
          v-if="fotos.length"
          class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3"
        >
          <div
            v-for="foto in fotos"
            :key="foto.id"
            class="relative group"
          >
            <img
              :src="foto.url"
              alt="Foto do alojamento"
              class="w-full h-32 object-cover rounded"
            />

            <button
              type="button"
              class="absolute top-1 right-1 bg-red-600 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition"
              @click="apagarFoto(foto.id)"
            >
              X
            </button>
          </div>
        </div>

        <div v-else class="text-sm text-gray-400 italic">
          Nenhuma foto ainda.
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
