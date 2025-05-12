<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Message from 'primevue/message';

const numeroVencidas = ref(null);
const error = ref(null);

// Llamada a la API cuando el componente se monta
onMounted(async () => {
    try {
        const response = await axios.get('reporte/prestamos/estado');
        numeroVencidas.value = response.data.numeroVencidas;
        error.value = null;  // Limpiar error si la consulta es exitosa
    } catch (err) {
        error.value = 'Error al obtener el número de cuotas vencidas';
        numeroVencidas.value = null;
    }
});
</script>

<template>
    <div class="p-4">
        <!-- Mostrar el número de cuotas vencidas usando PrimeVue Message -->
        <Message>{{ numeroVencidas }}</Message>
        <!-- Mostrar error usando PrimeVue Message -->
    </div>
</template>
