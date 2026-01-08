# Marão à Vista - Plataforma de Gestão de Alojamento Local

`Sobre o Projeto`

O **Marão à Vista** é uma aplicação web completa desenvolvida para modernizar a gestão de unidades de alojamento local na região do Marão. O projeto integra uma forte componente de **comunicação visual** (fotografia) com uma engenharia de software focada na **autonomia do gestor** e na facilidade de reserva para o hóspede.

`Objetivos Técnicos`<br>
**Arquitetura Moderna:** Implementar uma aplicação Single Page (SPA) utilizando **Vue 3** e **Inertia.js** para uma experiência de utilizador fluida.

**Gestão em Tempo Real:** Criar um sistema de reservas dinâmico que comunica instantaneamente com o motor de base de dados para evitar conflitos de datas.

**Integração de Pagamentos:** Implementar um fluxo de checkout profissional e seguro utilizando a **Easypay Sandbox**.

**Contentorização:** Garantir que o ambiente de desenvolvimento e produção é idêntico através de **Docker**.

`Funcionalidades Principais` 

**Motor de Reservas:** Calendário interativo com verificação de disponibilidade em tempo real.

**Checkout Integrado:** Processamento de pagamentos com gestão de *webhooks* para atualização automática do estado da reserva.

**Painel Administrativo (Back-office):** Gestão total de alojamentos, utilizadores, preços e relatórios de ocupação.

**Consumo de APIs Externas:**  <br> -**Meteo API:** Previsão meteorológica local integrada para os hóspedes. <br>

-**Conversão de Moeda:** Atualização automática de taxas para clientes internacionais.

**Design Responsivo:** Interface otimizada com **Tailwind CSS** para todos os dispositivos (Mobile, Tablet, Desktop).

`Stack Tecnológica`

**Backend:** Laravel (PHP)

**Frontend:** Vue 3, Inertia.js, Tailwind CSS

**Base de Dados:** MySQL

**Infraestrutura:** Docker & Docker Compose (Nginx, PHP, MySQL)
