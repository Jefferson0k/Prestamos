<script setup>
import { ref, onMounted, watch } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import axios from 'axios';
import Button from 'primevue/button';
import Select from 'primevue/select';

const cuotas = ref([]);
const loading = ref(false);
const selectedEstadoPrestamo = ref('');
const selectedCuotas = ref([]);
const dt = ref();

const props = defineProps({
    idPrestamo: {
        type: Number,
        required: true
    },
    refresh: {
        type: Number,
        default: 0
    }
});

const pagination = ref({
    currentPage: 1,
    perPage: 15,
    total: 0
});

const estadoPrestamoOptions = ref([
    { name: 'TODOS', value: '' },
    { name: 'PENDIENTES', value: 'Pendiente' },
    { name: 'PAGADOS', value: 'Pagado' },
]);

watch(() => props.refresh, () => {
    loadCuotas();
});

watch(() => selectedEstadoPrestamo.value, () => {
    pagination.value.currentPage = 1;
    loadCuotas();
});

const getEstadoSeverity = (estado) => {
    if (estado === 'Pendiente') return 'warn';
    if (estado === 'Pagado') return 'success';
    return 'info';
};

const loadCuotas = async () => {
    if (!props.idPrestamo || loading.value) return;

    loading.value = true;
    try {
        const response = await axios.get(`/cuota/${props.idPrestamo}`, {
            params: {
                page: pagination.value.currentPage,
                per_page: pagination.value.perPage,
                estado: selectedEstadoPrestamo.value
            }
        });

        cuotas.value = response.data.data;
        pagination.value.currentPage = response.data.meta.current_page;
        pagination.value.total = response.data.meta.total;
    } catch (error) {
        console.error('Error al cargar cuotas:', error);
    } finally {
        loading.value = false;
    }
};

const onPage = (event) => {
    pagination.value.currentPage = event.page + 1;
    pagination.value.perPage = event.rows;
    loadCuotas();
};

onMounted(() => {
    loadCuotas();
});
</script>

<template>
    <DataTable ref="dt" :value="cuotas" v-model:selection="selectedCuotas" :loading="loading" dataKey="id"
        :paginator="true" :rows="pagination.perPage" :totalRecords="pagination.total" @page="onPage" :lazy="true"
        :rowsPerPageOptions="[15, 10, 5]" scrollable scrollHeight="425px" responsiveLayout="scroll" class="p-datatable-sm"
        currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} cuotas"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown">
        <template #header>
            <div class="flex flex-wrap gap-2 items-center justify-between">
                <h4 class="m-0">Cuotas del Préstamo</h4>
                <div class="flex flex-wrap gap-2">
                    <Select v-model="selectedEstadoPrestamo" :options="estadoPrestamoOptions" optionLabel="name"
                        optionValue="value" placeholder="Seleccionar Estado" class="w-full md:w-auto" />
                    <Button icon="pi pi-refresh" outlined rounded aria-label="Refresh" @click="loadCuotas" />
                </div>
            </div>
        </template>

        <Column selectionMode="multiple" headerStyle="width: 3rem" />
        <Column field="numero_cuota" header="N° Cuota" sortable />
        <Column field="capital" header="Capital" sortable />
        <Column field="fecha_inicio" header="Inicio" sortable />
        <Column field="fecha_vencimiento" header="Vencimiento" sortable />
        <Column field="dias" header="Días" sortable />
        <Column field="tasa_interes_diario" header="I. Diario (%)" sortable />
        <Column field="interes" header="Interés" sortable />
        <Column field="monto_interes_pagar" header="A pagar interés" sortable />
        <Column field="monto_capital_paga" header="Capital pagado" sortable />
        <Column field="saldo_capital" header="Saldo capital" sortable />
        <Column field="monto_capital_mas_interes_a_pagar" header="Total a pagar" sortable />
        <Column field="estado" header="Estado" sortable>
            <template #body="{ data }">
                <Tag :value="data.estado" :severity="getEstadoSeverity(data.estado)" />
            </template>
        </Column>
    </DataTable>
</template>