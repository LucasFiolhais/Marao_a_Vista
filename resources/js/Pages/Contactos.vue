<template>
  <div class="min-h-screen bg-gray-50 text-gray-800">
    <Navbar />

    <main class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8 pt-24"> 
      
      <header class="text-center mb-12">
        <h1 class="text-5xl font-serif font-semibold text-gray-900 mb-2">CONTACTOS</h1>
        <p class="text-sm text-gray-600">Se tiver alguma dúvida ou precisar de ajuda, entre em contato conosco.</p>
      </header>
      
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-16">
        
        <div class="space-y-6">
          
          <div class="bg-white p-6 rounded-lg shadow-md flex items-start space-x-4">
            <svg class="w-8 h-8 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8m-1 12H4a2 2 0 01-2-2V6a2 2 0 012-2h16a2 2 0 012 2v12a2 2 0 01-2 2z"></path></svg>
            <div>
              <p class="text-xl font-semibold mb-1">Email</p>
              <p class="text-gray-500 text-sm">contact@example.com</p>
            </div>
          </div>
          
          <div class="bg-white p-6 rounded-lg shadow-md flex items-start space-x-4">
            <svg class="w-8 h-8 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
            <div>
              <p class="text-xl font-semibold mb-1">Telefone</p>
              <p class="text-gray-500 text-sm">+351 987 654 321</p>
            </div>
          </div>
          
          <!-- Card da Meteorologia -->
          <div class="bg-white p-6 rounded-lg shadow-md flex items-start space-x-4">
            <img 
              v-if="!weather.loading && !weather.error"
              :src="`https://openweathermap.org/img/wn/${weather.icone}@2x.png`"
              alt="Ícone do tempo"
              class="w-12 h-12"
            />

          <!-- Ícone de loading enquanto busca os dados -->
          <svg v-else class="w-8 h-8 text-gray-400 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" stroke-width="4" />
          </svg>

            <div>
              <p class="text-xl font-semibold mb-1">Temperatura Atual</p>

              <p v-if="weather.loading" class="text-gray-500 text-sm">
              A carregar dados meteorológicos...
              </p>

            <div v-else>
              <p class="text-2xl font-bold text-gray-800">
                {{ weather.temperatura }}°C
              </p>
              <p class="text-gray-600 text-sm">
                Humidade: {{ weather.descricao }}
              </p>
              <p class="text-gray-500 text-xs mt-1">
                Localidade: Poiares
              </p>
            </div>
          </div>
        </div>
      </div>
        
        <div class="bg-white p-3 rounded-lg shadow-md flex items-center justify-center">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d96075.14651993106!2d-7.926918321521399!3d41.19237886612182!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0xd3b4d52ed736209%3A0xb627b1bfb26c6889!2sAlojamento%20Local%20Mar%C3%A3o%20%C3%A0%20Vista!5e0!3m2!1spt-PT!2spt!4v1763477925175!5m2!1spt-PT!2spt" 
                width="100%" 
                height="384px" 
                class="w-full h-96 rounded-lg"
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
      </div>
      
      <hr class="border-gray-200 my-12" />
      
      <section class="text-center mb-12">
        <h2 class="text-4xl font-serif font-semibold text-gray-900 mb-2">FAQ</h2>
        <p class="text-sm text-gray-600">Perguntas frequentes para tirar suas dúvidas.</p>
      </section>
      
      <div class="space-y-4">
        <div 
          v-for="(item, index) in faqs" 
          :key="index"
          class="border border-gray-200 rounded-md cursor-pointer hover:bg-gray-100 transition duration-150 shadow-sm"
        >
          <div @click="toggleFAQ(index)" class="p-4 flex justify-between items-center">
            <span class="font-medium text-gray-700">{{ item.question }}</span>
            <svg :class="{'rotate-90': item.isOpen}" class="w-5 h-5 text-gray-500 transform transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
          </div>
          <div v-show="item.isOpen" class="px-4 pb-4 pt-0 text-gray-600 border-t border-gray-100">
            <p>{{ item.answer }}</p>
          </div>
        </div>
      </div>
      
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import Navbar from '../Components/NavBar.vue'; 

// --- Estado da FAQ ---
const faqs = ref([
  { question: 'Qual é o horário de atendimento?', answer: 'Nosso suporte está disponível de segunda a sexta, das 9h às 18h, horário de Lisboa.', isOpen: false },
  { question: 'Como faço para rastrear meu pedido?', answer: 'Você receberá um email com o código de rastreio assim que o pedido for enviado. O rastreamento pode levar até 24h para ser atualizado.', isOpen: false },
  { question: 'Posso devolver um produto?', answer: 'Sim, aceitamos devoluções em até 30 dias após o recebimento, desde que o produto esteja na condição original. Consulte nossa política de devolução para mais detalhes.', isOpen: false },
  { question: 'Quais métodos de pagamento são aceitos?', answer: 'Aceitamos cartões de crédito (Visa, MasterCard, Amex), PayPal e transferência bancária.', isOpen: false }
]);

// --- Função da FAQ ---
const toggleFAQ = (index) => {
  faqs.value[index].isOpen = !faqs.value[index].isOpen;
};



const weather = ref({
  loading: true,
  error: false,
  temperatura: null,
  descricao: "",
  cidade: "",
  icone: ""
});

onMounted(async () => {
  try {
    const res = await fetch("/api/public/meteo");
    const data = await res.json();

    weather.value = {
      loading: false,
      error: false,
      temperatura: data.temperatura,
      descricao: data.descricao,
      cidade: data.cidade,
      icone: data.icone
    };
  } catch (e) {
    weather.value.loading = false;
    weather.value.error = true;
  }
});

</script>

<style scoped>
/* Estilo para replicar a tipografia do título da imagem */
.font-serif {
  font-family: Georgia, serif; 
}
</style>