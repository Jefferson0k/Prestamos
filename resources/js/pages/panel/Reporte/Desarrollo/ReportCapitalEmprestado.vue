<template>
    <div class="space-y-4">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <Tag :value="`Total: ${capitalTotal}`" severity="success" class="text-lg px-3 py-2" />

            <div class="p-inputgroup w-36">
                <IconField>
                    <InputIcon>
                        <i class="pi pi-calendar" />
                    </InputIcon>
                    <InputNumber v-model="selectedYear" :min="2020" :max="currentYear" inputId="yearInput"
                        :useGrouping="false" placeholder="Año" aria-label="Año de reporte" fluid />
                </IconField>
            </div>
        </div>
        <div v-if="loading" class="flex justify-center py-6">
            <ProgressSpinner />
        </div>
        <div v-else>
            <Chart v-if="barData && barOptions" type="bar" :data="barData" :options="barOptions" class="mb-6" />
            <DataTable :value="capitalPorMes" responsiveLayout="scroll" dataKey="id" :paginator="true" :rows="10"
                :filters="filters"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} registros">
                <template #header>
                    <div class="flex flex-wrap gap-2 items-center justify-between">
                        <h4 class="m-0 text-lg font-semibold">Capital Mensual</h4>
                        <IconField>
                            <InputIcon><i class="pi pi-search" /></InputIcon>
                            <InputText v-model="filters['global'].value" placeholder="Buscar..." />
                        </IconField>
                    </div>
                </template>
                <Column selectionMode="multiple" style="width: 3rem" :exportable="false" />
                <Column field="mes_nombre" header="MES" />
                <Column field="total_capital_formatted" header="CAPITAL" />
                <Column field="porcentaje" header="PORCENTAJE (%)" />
            </DataTable>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import axios from "axios";

import Chart from "primevue/chart";
import InputNumber from "primevue/inputnumber";
import ProgressSpinner from "primevue/progressspinner";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import InputText from "primevue/inputtext";
import Tag from "primevue/tag";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import { FilterMatchMode } from "@primevue/core/api";
import { useLayout } from "@/layout/composables/layout";
import { useToast } from "primevue/usetoast";

const { getPrimary, getSurface, isDarkTheme } = useLayout();

const toast = useToast();
const currentYear = new Date().getFullYear();
const selectedYear = ref(currentYear);
const loading = ref(false);
const capitalTotal = ref(0);
const capitalPorMes = ref([]);
const barData = ref(null);
const barOptions = ref(null);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS }
});

const fetchData = async () => {
    loading.value = true;
    try {
        const response = await axios.get(`/reporte/capital/${selectedYear.value}`);
        const data = response.data;
        capitalTotal.value = data.capital_total_formatted;
        capitalPorMes.value = data.por_mes;
        updateChartData();
    } catch (error) {
        console.error("Error al obtener datos:", error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar los datos.', life: 3000 });
    } finally {
        loading.value = false;
    }
};

const updateChartData = () => {
    const documentStyle = getComputedStyle(document.documentElement);
    const primaryColor = documentStyle.getPropertyValue("--p-primary-500");
    const primaryLightColor = documentStyle.getPropertyValue("--p-primary-200");

    barData.value = {
        labels: capitalPorMes.value.map(item => item.mes_nombre),
        datasets: [
            {
                label: "Capital Prestado",
                backgroundColor: primaryColor,
                borderColor: primaryColor,
                data: capitalPorMes.value.map(item => parseFloat(item.total_capital))
            },
            {
                label: "Porcentaje (%)",
                backgroundColor: primaryLightColor,
                borderColor: primaryLightColor,
                data: capitalPorMes.value.map(item => item.porcentaje),
                yAxisID: "y1"
            }
        ]
    };

    barOptions.value = {
        plugins: {
            legend: {
                labels: {
                    color: documentStyle.getPropertyValue("--text-color")
                }
            },
            tooltip: {
                callbacks: {
                    label: context =>
                        context.datasetIndex === 0
                            ? `Capital: S/ ${context.raw}`
                            : `Porcentaje: ${context.raw}%`
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: documentStyle.getPropertyValue("--surface-border") },
                ticks: { color: documentStyle.getPropertyValue("--text-color") }
            },
            y1: {
                beginAtZero: true,
                position: "right",
                grid: { drawOnChartArea: false },
                ticks: {
                    color: documentStyle.getPropertyValue("--text-color"),
                    callback: value => value + "%"
                }
            },
            x: {
                grid: { color: documentStyle.getPropertyValue("--surface-border") },
                ticks: { color: documentStyle.getPropertyValue("--text-color") }
            }
        }
    };
};

onMounted(fetchData);
watch(selectedYear, fetchData);
watch([getPrimary, getSurface, isDarkTheme], () => {
    if (capitalPorMes.value.length > 0) updateChartData();
});
</script>