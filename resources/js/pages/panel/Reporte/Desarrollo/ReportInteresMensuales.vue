<template>
    <div class="card">
        <Chart type="line" :data="chartData" :options="chartOptions" class="h-[30rem]" />
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import Chart from 'primevue/chart';
import axios from 'axios'; // Asegúrate de tener Axios instalado

// Variables reactivas
const chartData = ref();
const chartOptions = ref();

// Cargar datos desde Laravel
onMounted(async () => {
    const response = await axios.get('/reporte/intereses/mensuales'); // Ruta definida en Laravel
    const data = response.data;

    // Procesamos los datos recibidos para el gráfico
    chartData.value = setChartData(data);
    chartOptions.value = setChartOptions();
});

// Función para configurar los datos del gráfico
const setChartData = (data) => {
    const documentStyle = getComputedStyle(document.documentElement);

    // Los datos que recibimos son un total de intereses, así que solo mostramos eso
    const labels = ['Intereses Mensuales'];
    const intereses = [parseFloat(data.total_intereses)];

    return {
        labels: labels,
        datasets: [
            {
                label: 'Total de Intereses del Mes',
                data: intereses,
                fill: false,
                borderColor: documentStyle.getPropertyValue('--p-cyan-500'),
                tension: 0.4
            }
        ]
    };
};

// Función para configurar las opciones del gráfico
const setChartOptions = () => {
    const documentStyle = getComputedStyle(document.documentElement);
    const textColor = documentStyle.getPropertyValue('--p-text-color');
    const textColorSecondary = documentStyle.getPropertyValue('--p-text-muted-color');
    const surfaceBorder = documentStyle.getPropertyValue('--p-content-border-color');

    return {
        maintainAspectRatio: false,
        aspectRatio: 0.6,
        plugins: {
            legend: {
                labels: {
                    color: textColor
                }
            }
        },
        scales: {
            x: {
                ticks: {
                    color: textColorSecondary
                },
                grid: {
                    color: surfaceBorder
                }
            },
            y: {
                ticks: {
                    color: textColorSecondary
                },
                grid: {
                    color: surfaceBorder
                }
            }
        }
    };
};
</script>
