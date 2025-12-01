<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import backend from '@/axiosBackend'

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
      Array.from(fotosInput.value.files).forEach(file => {
        formData.append('fotos[]', file)
      })
    }

    await backend.post('/alojamentos', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })

    router.visit(route('admin.alojamentos'))
  } catch (error) {
    console.error('Erro ao criar alojamento:', error)
    if (error.response && error.response.status === 422) {
      errors.value = error.response.data.errors || {}
    }
  } finally {
    saving.value = false
  }
}
</script>

<template>
  <AdminLayout title="Criar Alojamento">
    <form
      @submit.prevent="submit"
      class="max-w-lg mx-auto bg-white p-6 shadow rounded space-y-4"
    >
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

      <div>
        <label class="block font-semibold mb-1">Fotos do alojamento</label>
        <input
          type="file"
          multiple
          ref="fotosInput"
        />
        <p class="text-xs text-gray-500 mt-1">
          Pode selecionar várias imagens de uma vez.
        </p>
        <div v-if="errors['fotos.*']" class="text-red-600 text-sm mt-1">
          {{ errors['fotos.*'][0] }}
        </div>
      </div>

      <button
        class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-50"
        :disabled="saving"
      >
        {{ saving ? 'A criar...' : 'Criar' }}
      </button>
    </form>
  </AdminLayout>
</template>