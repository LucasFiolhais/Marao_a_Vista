<template>
  <div>
    <h1>Comentários</h1>
    <ul>
      <li v-for="comentario in comentarios" :key="comentario.id">
        <p>{{ comentario.texto }}</p>
      </li>
    </ul>
    <form @submit.prevent="addComment">
      <textarea v-model="newComment" placeholder="Deixe seu comentário" required></textarea>
      <button type="submit">Adicionar Comentário</button>
    </form>
  </div>
</template>

<script>
import axios from '../axios';

export default {
  data() {
    return {
      comentarios: [],
      newComment: '',
    };
  },
  mounted() {
    this.fetchComments();
  },
  methods: {
    async fetchComments() {
      const alojamentoId = 1; // Substitua pelo ID real do alojamento
      try {
        const response = await axios.get(`/alojamentos/${alojamentoId}/comentarios`);
        this.comentarios = response.data;
      } catch (error) {
        console.error('Erro ao buscar comentários', error);
      }
    },
    async addComment() {
      const alojamentoId = 1; // Substitua pelo ID real do alojamento
      try {
        await axios.post(`/comentarios`, {
          texto: this.newComment,
          alojamento_id: alojamentoId,
        });
        this.newComment = ''; // Limpa o campo de comentário
        this.fetchComments(); // Recarrega os comentários
      } catch (error) {
        console.error('Erro ao adicionar comentário', error);
      }
    },
  },
};
</script>