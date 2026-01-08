# Marão à Vista - Plataforma de Gestão de Alojamento Local

<img width="1919" height="884" alt="Captura de ecrã 2026-01-08 194238" src="https://github.com/user-attachments/assets/00289316-83cc-4732-9d6d-2646cdd6e21e" /> <br>

<img width="1636" height="897" alt="Captura de ecrã 2026-01-08 194259" src="https://github.com/user-attachments/assets/97a9389f-8aa4-422e-ba38-ed9ecfc597d3" /> <br>

<img width="1341" height="917" alt="Captura de ecrã 2026-01-08 194308" src="https://github.com/user-attachments/assets/178c12dc-b33a-40c4-a306-e92d95f63121" /> <br>

<img width="1165" height="912" alt="Captura de ecrã 2026-01-08 194317" src="https://github.com/user-attachments/assets/a36d5a24-6161-4fc5-898c-2a34a4d58ae7" /> <br>


`Sobre o Projeto`

O **Marão à Vista** é uma aplicação web completa desenvolvida para modernizar a gestão de unidades de alojamento local na região do Marão. O projeto integra uma forte componente de **comunicação visual** (fotografia) com uma engenharia de software focada na **autonomia do gestor** e na facilidade de reserva para o hóspede.

`Objetivos Técnicos`<br>
**Arquitetura Moderna:** Implementar uma aplicação utilizando **Vue 3** e **Inertia.js** para uma experiência de utilizador fluida.

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

