<script setup>
import { ref, onMounted } from "vue"
import { router, usePage, Link } from "@inertiajs/vue3"
import Navbar from "@/Components/NavBar.vue"
import axios from "@/axiosFrontend"

const page = usePage()
const reserva = ref(null)

const pagando = ref(false)
const erro = ref(null)
const sucesso = ref(null)

onMounted(async () => {
  const id = page.url.split("/").pop()

  const res = await axios.get(`/reservas/${id}`)
  reserva.value = res.data
})

const pagar = async () => {
  if (!reserva.value) return

  pagando.value = true
  erro.value = null
  sucesso.value = null

  try {
    const res = await axios.post(
      `/pagamentos/checkout/${reserva.value.id}`
    )

    console.log("Pagamento criado:", res.data)

    if (res.data.payment_url) {
      window.location.href = res.data.payment_url
    } else {
      sucesso.value = "Pagamento criado com sucesso."
    }

  } catch (e) {
    erro.value = "Erro ao criar pagamento."
    console.error(e.response?.data || e)
  } finally {
    pagando.value = false
  }
}
</script>

<template>
  <Navbar />

  <div
    v-if="reserva"
    class="max-w-3xl mx-auto mt-28 px-4"
  >
    <h1 class="text-3xl font-bold text-dark mb-6">
      Checkout da Reserva
    </h1>

    <div class="bg-white shadow rounded-lg p-6">
      <h2 class="text-xl font-semibold text-dark">
        {{ reserva.alojamento.titulo }}
      </h2>

      <p class="text-gray-600">
        {{ reserva.checkin }} → {{ reserva.checkout }}
      </p>

      <p class="mt-4 text-lg font-bold text-dark">
        Total: {{ reserva.total }} €
      </p>

      <div class="mt-6">
        <button
          @click="pagar"
          :disabled="pagando"
          class="bg-accent text-dark px-6 py-2 rounded-md font-semibold hover:bg-yellow-300 disabled:opacity-50"
        >
          Pagar com MBWay
        </button>
      </div>

      <p v-if="sucesso" class="mt-4 text-green-600 font-medium">
        {{ sucesso }}
      </p>
      <p v-if="erro" class="mt-4 text-red-600 font-medium">
        {{ erro }}
      </p>
    </div>
  </div>

  <!-- opcional: loading -->
  <div v-else class="text-center mt-40 text-gray-500">
    A carregar reserva…
  </div>
</template>

<style scoped>
.text-dark {
  color: #616160;
}
</style>
