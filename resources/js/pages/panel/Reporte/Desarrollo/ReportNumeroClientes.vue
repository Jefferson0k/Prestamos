<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Message from 'primevue/message';

const numeroClientes = ref(null);
const error = ref(null);

// Llamada a la API cuando el componente se monta
onMounted(async () => {
    try {
        const response = await axios.get('/reporte/clientes/count');
        numeroClientes.value = response.data.numeroClientes;
        error.value = null;  // Limpiar error si la consulta es exitosa
    } catch (err) {
        error.value = 'Error al obtener el número de clientes';
        numeroClientes.value = null;
    }
});
</script>

<template>
    <div class="p-4">
        <!-- Usamos el componente Message de PrimeVue con clases personalizadas para el tamaño del texto -->
        <Message v-if="numeroClientes" severity="info" class="text-4xl p-5">
            Número de clientes: {{ numeroClientes }}
        </Message>
        <Message v-else-if="error" severity="error" class="text-2xl p-5">
            {{ error }}
        </Message>
    </div>
</template>

<style scoped>
/* Si deseas personalizar aún más el tamaño de los textos y el padding */
.text-4xl {
    font-size: 3rem; /* Tamaño más grande para el número de clientes */
    font-weight: bold;
}
.text-2xl {
    font-size: 1.5rem; /* Tamaño moderado para los mensajes de error */
}
.p-5 {
    padding: 2rem;
}
</style>
