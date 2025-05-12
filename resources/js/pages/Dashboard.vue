<script setup>
import AppLayout from '@/layout/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import Password from './settings/Password.vue';
import Message from 'primevue/message';
import Button from 'primevue/button';

const page = usePage();
const mustReset = page.props.mustReset;
// Obtener el usuario autenticado
const user = page.props.auth.user;

// Función para determinar el saludo
const getGreeting = () => {
  const hours = new Date().getHours();
  if (hours < 12) {
    return 'Buenos días';
  } else if (hours < 18) {
    return 'Buenas tardes';
  } else {
    return 'Buenas noches';
  }
};

// Mensajes motivacionales
const motivationalMessages = [
  "¡Estás haciendo un gran trabajo, sigue así!",
  "¡Hoy es un buen día para alcanzar tus metas!",
  "¡La perseverancia te lleva lejos, nunca pares!",
  "¡Cada paso te acerca más a tu éxito!"
];

// Escoger un mensaje aleatorio y almacenarlo en una variable reactiva
const motivationalMessage = getMotivationalMessage();

// Función para obtener un mensaje aleatorio
function getMotivationalMessage() {
  const randomIndex = Math.floor(Math.random() * motivationalMessages.length);
  return motivationalMessages[randomIndex];
}
</script>

<template>
  <Head title="Dashboard" />
  <div v-if="mustReset">
    <div>
      <Password />
    </div>
  </div>
  <AppLayout v-else>
    <div class="card">
      <!-- Saludo y nombre completo del usuario - Orden corregido: primero nombre, luego apellidos -->
      <h2>{{ getGreeting() }}, {{ user.name }}!</h2>
      <br>
      <!-- Mensaje motivacional con PrimeVue Message - Uso correcto del binding con ":" -->
      <Message severity="info">{{ motivationalMessage }}</Message>
      
      <!-- Botón para realizar alguna acción -->
    </div>
  </AppLayout>
</template>