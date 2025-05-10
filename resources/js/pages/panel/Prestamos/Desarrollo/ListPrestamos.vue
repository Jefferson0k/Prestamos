<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import MultiSelect from 'primevue/multiselect';
import Tag from 'primevue/tag';
import Button from 'primevue/button';
import axios from 'axios';
import Talonario from './Talonario.vue';
import DeletePrestamo from './DeletePrestamo.vue';
import UpdatPrestamos from './UpdatPrestamos.vue';

const dt = ref();
const prestamos = ref([]);
const prestamo = ref ([]);
const selectedPrestamos = ref();
const searchTerm = ref('');
const debouncedSearchTerm = ref('');
const searchTimeout = ref(null);
const loading = ref(false);
const totalRecords = ref(0);
const perPage = ref(15);
const currentPage = ref(1);
const selectedEstado = ref('');
const showPrintDialog = ref(false);
const prestamosId = ref(null);
const deletePrestamoDialog = ref(false);
const updatePrestamoDialog = ref(false);
const selectedPrestamoId = ref(null);

const estadoOptions = ref([
    { label: 'TODOS', value: '' },
    { label: 'PAGA', value: 1 },
    { label: 'MOROSO', value: 2 },
    { label: 'PENDIENTE', value: 3 },
    { label: 'FINALIZADO', value: 4 },
]);
const props = defineProps({
    refresh: {
        type: Number,
        required: true
    }
});
watch(() => props.refresh, () => {
    loadPrestamos();
});
const optionalColumns = ref([
    { field: 'recomendacion', header: 'Recomendación', width: '30rem' }
]);
const selectedColumns = ref([]);

function editPrestamo(prestamo) {
    selectedPrestamoId.value = prestamo.id;
    updatePrestamoDialog.value = true;
}
function handlePrestamoUpdated() {
    loadPrestamos();
}
const loadPrestamos = async (page = 1, itemsPerPage = perPage.value, search = debouncedSearchTerm.value, estado = selectedEstado.value) => {
    try {
        loading.value = true;
        const response = await axios.get('/prestamo', {
            params: {
                page: page,
                per_page: itemsPerPage,
                search: search,
                estado_cliente: estado
            }
        });

        prestamos.value = response.data.data;
        totalRecords.value = response.data.meta.total;
        currentPage.value = response.data.meta.current_page;
    } catch (error) {
        console.error('Error al cargar préstamos:', error);
    } finally {
        loading.value = false;
    }
};
function handleUserDeleted() {
    loadPrestamos();
}

function confirmDeletePrestamo(selectedPrestamo) {
    prestamo.value = selectedPrestamo;
    deletePrestamoDialog.value = true;
}

const refreshData = () => {
    loadPrestamos(currentPage.value, perPage.value, debouncedSearchTerm.value, selectedEstado.value);
};

watch(searchTerm, (newValue) => {
    if (searchTimeout.value) {
        clearTimeout(searchTimeout.value);
    }

    searchTimeout.value = setTimeout(() => {
        debouncedSearchTerm.value = newValue;
        currentPage.value = 1;
        loadPrestamos(1, perPage.value, newValue, selectedEstado.value);
    }, 600);
});

watch(selectedEstado, (newValue) => {
    currentPage.value = 1;
    loadPrestamos(1, perPage.value, debouncedSearchTerm.value, newValue);
});

function getStatusLabel(estado_cliente) {
    switch (estado_cliente) {
        case 'PAGA':
            return 'success';
        case 'MOROSO':
            return 'danger';
        case 'PENDIENTE':
            return 'warn';    
        case 'FINALIZADO':
            return 'contrast'; 
        default:
            return null;
    }
}

const onPage = (event) => {
    currentPage.value = event.page + 1;
    loadPrestamos(currentPage.value, event.rows, debouncedSearchTerm.value, selectedEstado.value);
};

const clearFilters = () => {
    searchTerm.value = '';
    debouncedSearchTerm.value = '';
    selectedEstado.value = '';
    currentPage.value = 1;
    loadPrestamos();
};

const printprestamo = async (prestamo) => {
    try {
        prestamosId.value = prestamo.id;
        showPrintDialog.value = true;
    } catch (error) {
        console.error('Error al preparar impresión A4:', error);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudo preparar la impresión A4',
            life: 3000
        });
    }
};

const handleClosePrestamo = () => {
    showPrintDialog.value = false;
    prestamosId.value = null;
};

onMounted(() => {
    loadPrestamos();
});
defineExpose({ loadPrestamos });
</script>

<template>
    <DataTable ref="dt" v-model:selection="selectedPrestamos" :value="prestamos" dataKey="id" :paginator="true"
        :loading="loading" responsiveLayout="scroll" :rows="perPage" :rowsPerPageOptions="[5, 10, 15, 25]"
        :totalRecords="totalRecords" :lazy="true" @page="onPage" selectionMode="multiple"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} Préstamos" class="p-datatable-sm">

        <template #header>
            <div class="flex flex-wrap gap-2 items-center justify-between">
                <div class="flex align-items-center gap-2">
                    <h4 class="m-0">Préstamos</h4>
                </div>
                <div class="flex flex-wrap gap-2">
                    <IconField>
                        <InputIcon>
                            <i class="pi pi-search" />
                        </InputIcon>
                        <InputText v-model="searchTerm" placeholder="Buscar..." />
                    </IconField>
                    <Select v-model="selectedEstado" :options="estadoOptions" optionLabel="label" optionValue="value"
                        placeholder="Filtrar por estado" class="w-48" />
                    <MultiSelect v-model="selectedColumns" :options="optionalColumns" optionLabel="header"
                        display="chip" placeholder="Seleccionar Columnas" />
                    <Button icon="pi pi-filter-slash" label="Limpiar" severity="secondary" text @click="clearFilters" />
                    <Button icon="pi pi-refresh" outlined rounded aria-label="Refresh" @click="refreshData" />
                </div>
            </div>
        </template>
        <Column field="dni" header="DNI" sortable style="min-width: 4rem"></Column>
        <Column field="NombreCompleto" header="Nombre y Apellido" sortable style="min-width: 25rem">
        </Column>
        <Column field="fecha_inicio" header="Fecha de inicio" sortable style="min-width: 14rem"></Column>
        <Column field="fecha_vencimiento" header="Fecha de vencimiento" sortable style="min-width: 14rem">
        </Column>
        <Column field="capital" header="Capital" sortable style="min-width: 5rem"></Column>
        <Column field="numero_cuotas" header="N° Cuotas" sortable style="min-width: 8rem"></Column>
        <Column field="estado_cliente" header="Estado" sortable style="min-width: 8rem">
            <template #body="slotProps">
                <Tag :value="slotProps.data.estado_cliente" :severity="getStatusLabel(slotProps.data.estado_cliente)" />
            </template>
        </Column>
        <Column field="tasa_interes_diario" header="Tasa de interés diario" sortable style="min-width: 13em"></Column>
        <Column v-for="col of selectedColumns" :key="col.field" :field="col.field" :header="col.header"
            :style="{ 'min-width': col.width }" sortable></Column>
            <Column :exportable="false" style="min-width: 12rem">
                <template #body="slotProps">
                    <Button
                        icon="pi pi-pencil"
                        outlined
                        rounded
                        class="mr-2"
                        :disabled="slotProps.data.estado_cliente === 'FINALIZADO'"
                        @click="editPrestamo(slotProps.data)"
                    />
                    <Button
                        icon="pi pi-trash"
                        outlined
                        rounded
                        severity="danger"
                        class="mr-2"
                        :disabled="slotProps.data.estado_cliente === 'FINALIZADO'"
                        @click="confirmDeletePrestamo(slotProps.data)"
                    />
                    <Button
                        icon="pi pi-print"
                        outlined
                        rounded
                        severity="help"
                        class="rl-2"
                        @click="printprestamo(slotProps.data)"
                    />
                </template>
            </Column>
    </DataTable>
    <Talonario v-if="showPrintDialog" :prestamosId="prestamosId" v-model:visible="showPrintDialog" @close="handleClosePrestamo" />
    <DeletePrestamo
        v-model:visible="deletePrestamoDialog"
        :prestamo="prestamo"
        @deleted="handleUserDeleted"
    />
    <UpdatPrestamos
        v-model:visible="updatePrestamoDialog"
        :PrestamoId="selectedPrestamoId"
        @updated="handlePrestamoUpdated"
    />
</template>