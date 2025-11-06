<template>
  <div>
    <h1>Perfil do Usuário</h1>
    
    <!-- Informações do Usuário -->
    <div>
      <h2>Informações Pessoais</h2>
      <p><strong>Nome:</strong> {{ user.name }}</p>
      <p><strong>Email:</strong> {{ user.email }}</p>
      
      <!-- Formulário para edição de dados -->
      <h3>Editar Perfil</h3>
      <form @submit.prevent="updateProfile">
        <input v-model="user.name" type="text" placeholder="Nome" required />
        <input v-model="user.email" type="email" placeholder="Email" required />
        <button type="submit">Salvar Alterações</button>
      </form>
    </div>

    <!-- Reservas do Usuário -->
    <div>
      <h2>Minhas Reservas</h2>
      <ul>
        <li v-for="reserva in reservas" :key="reserva.id">
          <p>Alojamento: {{ reserva.alojamento_nome }}</p>
          <p>Data Início: {{ reserva.start_date }}</p>
          <p>Data Fim: {{ reserva.end_date }}</p>
          <p>Status: {{ reserva.status }}</p>
        </li>
      </ul>
    </div>
  </div>
</template>

<script>
import axios from '../axios';

export default {
  data() {
    return {
      user: {
        name: '',
        email: '',
      },
      reservas: [],
    };
  },
  mounted() {
    this.fetchUserProfile();
    this.fetchUserReservations();
  },
  methods: {
    // Função para buscar os dados do usuário
    async fetchUserProfile() {
      try {
        const response = await axios.get('/user'); // Assumindo que existe uma rota que retorna os dados do usuário autenticado
        this.user = response.data;
      } catch (error) {
        console.error('Erro ao carregar perfil', error);
      }
    },
    
    // Função para atualizar os dados do usuário
    async updateProfile() {
      try {
        await axios.put('/user', {
          name: this.user.name,
          email: this.user.email,
        });
        alert('Perfil atualizado com sucesso!');
      } catch (error) {
        console.error('Erro ao atualizar perfil', error);
      }
    },

    // Função para buscar as reservas do usuário
    async fetchUserReservations() {
      try {
        const response = await axios.get('/reservas');
        this.reservas = response.data;
      } catch (error) {
        console.error('Erro ao carregar reservas', error);
      }
    },
  },
};
</script>

<style scoped>
/* Estilos para a página de perfil */
h1 {
  font-size: 2rem;
  margin-bottom: 20px;
}

form input {
  margin-bottom: 10px;
  padding: 8px;
  width: 100%;
  max-width: 400px;
}

button {
  background-color: #9faea0;
  color: white;
  padding: 10px 20px;
  border: none;
  cursor: pointer;
}

button:hover {
  background-color: #616160;
}
</style>
