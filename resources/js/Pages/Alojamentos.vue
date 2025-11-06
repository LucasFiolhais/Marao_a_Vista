<template>
  <div class="min-h-screen bg-white">
    <!-- Header -->
    <header class="bg-primary text-white py-4">
      <div class="max-w-7xl mx-auto px-6">
        <h1 class="text-3xl font-semibold">{{ alojamento.titulo }}</h1>
      </div>
    </header>

    <!-- Detalhes do Alojamento -->
    <section class="py-8 px-6 md:px-20">
      <div class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-10">
        <div>
          <img :src="alojamento.imagem" alt="Alojamento" class="w-full h-96 object-cover rounded-lg shadow-md" />
        </div>
        <div class="space-y-4">
          <p class="text-lg">{{ alojamento.descricao }}</p>
          <p class="text-xl font-semibold text-accent">{{ alojamento.preco }}€/noite</p>
          
          <!-- Formulário de Reserva -->
          <div class="mt-8">
            <h2 class="text-2xl font-semibold">Fazer Reserva</h2>
            <form @submit.prevent="reservar">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label for="dataInicio" class="block text-sm">Data de Início</label>
                  <input type="date" id="dataInicio" v-model="reserva.dataInicio" class="w-full mt-1 p-2 border rounded" required />
                </div>
                <div>
                  <label for="dataFim" class="block text-sm">Data de Fim</label>
                  <input type="date" id="dataFim" v-model="reserva.dataFim" class="w-full mt-1 p-2 border rounded" required />
                </div>
              </div>
              <button type="submit" class="mt-4 bg-accent text-dark font-semibold px-6 py-3 rounded-lg shadow hover:bg-yellow-300 transition">
                Reservar Agora
              </button>
            </form>
          </div>
        </div>
      </div>
    </section>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axiosInstance from '../axios'; // Importa a configuração do Axios
import { useRoute, useRouter } from 'vue-router'; // Para obter parâmetros de rota

// Armazenar dados do alojamento e do formulário de reserva
const alojamento = ref({});
const reserva = ref({
  dataInicio: '',
  dataFim: '',
});

// Obter o ID do alojamento da URL
const route = useRoute();
const router = useRouter();
const alojamentoId = route.params.id; // O parâmetro 'id' que vem da URL

// Função para buscar os dados do alojamento
const fetchAlojamento = async () => {
  try {
    const response = await axiosInstance.get(`/alojamentos/${alojamentoId}`);
    alojamento.value = response.data; // Salva os dados do alojamento
  } catch (error) {
    console.error('Erro ao buscar alojamento', error);
  }
};

// Função para fazer a reserva
const reservar = async () => {
  try {
    const response = await axiosInstance.post('/reservas', {
      alojamento_id: alojamentoId,
      data_inicio: reserva.value.dataInicio,
      data_fim: reserva.value.dataFim,
    });
    alert('Reserva feita com sucesso!');
    router.push('/reservas'); // Redireciona para a página de reservas
  } catch (error) {
    console.error('Erro ao fazer reserva', error);
    alert('Erro ao fazer a reserva. Tente novamente.');
  }
};

// Buscar dados do alojamento quando o componente for montado
onMounted(() => {
  fetchAlojamento();
});
</script>

<style scoped>
.bg-primary {
  background-color: #9faea0;
}

.text-accent {
  color: #e6e019;
}

.text-dark {
  color: #616160;
}
</style>