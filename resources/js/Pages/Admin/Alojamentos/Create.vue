<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import http from '@/http'

const form = ref({
  titulo: '',
  descricao: '',
  preco_noite: '',
})

const errors = ref({})
const saving = ref(false)
const fotosInput = ref(null)

const submit = async () => {
  saving.value = true
  errors.value = {}

  try {
    const formData = new FormData()
    formData.append('titulo', form.value.titulo)
    formData.append('descricao', form.value.descricao)
    formData.append('preco_noite', form.value.preco_noite)

    if (fotosInput.value && fotosInput.value.files.length) {
      Array.from(fotosInput.value.files).forEach((file) => {
        formData.append('fotos[]', file)
      })
    }

    await http.post('/admin/alojamentos', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })

    router.visit(route('admin.alojamentos'))
  } catch (error) {
    console.error('Erro ao criar quarto:', error)
    if (error.response?.status === 422) {
      errors.value = error.response.data.errors || {}
    } else {
      alert('Erro ao criar quarto.')
    }
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <AdminLayout title="Criar Quarto">
    <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- DADOS -->
      <div class="bg-white p-6 shadow rounded space-y-4">
        <h2 class="font-semibold text-lg">Dados do quarto</h2>

        <div>
          <label class="block font-semibold mb-1">Título</label>
          <input
            v-model="form.titulo"
            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-200"
            required
          />
          <div v-if="errors.titulo" class="text-red-600 text-sm mt-1">
            {{ errors.titulo[0] }}
          </div>
        </div>

        <div>
          <label class="block font-semibold mb-1">Descrição</label>
          <textarea
            v-model="form.descricao"
            rows="6"
            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-200"
            required
          />
          <div v-if="errors.descricao" class="text-red-600 text-sm mt-1">
            {{ errors.descricao[0] }}
          </div>
        </div>

        <div>
          <label class="block font-semibold mb-1">Preço/noite (€)</label>
          <input
            v-model="form.preco_noite"
            type="number"
            step="0.01"
            class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-200"
            required
          />
          <div v-if="errors.preco_noite" class="text-red-600 text-sm mt-1">
            {{ errors.preco_noite[0] }}
          </div>
        </div>

        <!-- BOTÕES (mesmo estilo do edit) -->
        <div class="pt-2 flex gap-3">
          <button
            class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 disabled:opacity-50"
            :disabled="saving"
          >
            {{ saving ? 'A criar...' : 'Criar' }}
          </button>

          <button
            type="button"
            class="px-4 py-2 border rounded hover:bg-gray-50"
            @click="router.visit(route('admin.alojamentos'))"
          >
            Voltar
          </button>
        </div>
      </div>

      <!-- FOTOS -->
      <div class="bg-white p-6 shadow rounded space-y-4">
        <h2 class="font-semibold text-lg">Fotos</h2>

        <div>
          <label class="block font-semibold mb-1">Adicionar fotos</label>

          <input
            type="file"
            multiple
            ref="fotosInput"
            class="block w-full text-sm text-gray-700
                   file:mr-4 file:py-2 file:px-4
                   file:rounded file:border-0
                   file:text-sm file:font-semibold
                   file:bg-gray-100 file:text-gray-700
                   hover:file:bg-gray-200"
          />

          <p class="text-xs text-gray-500 mt-2">
            Pode selecionar várias imagens de uma vez.
          </p>

          <div v-if="errors['fotos.*']" class="text-red-600 text-sm mt-2">
            {{ errors['fotos.*'][0] }}
          </div>
          <div v-if="errors.fotos" class="text-red-600 text-sm mt-2">
            {{ errors.fotos[0] }}
          </div>
        </div>

        <!-- Pequena dica visual (igual vibe do edit) -->
        <div class="text-xs text-gray-500">
          As fotos serão guardadas e a <strong>primeira</strong> será usada como capa no Index.
        </div>
      </div>
    </form>
  </AdminLayout>
</template>
