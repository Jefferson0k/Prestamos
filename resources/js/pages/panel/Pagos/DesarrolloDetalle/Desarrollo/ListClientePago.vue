<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Tag from 'primevue/tag';
import axios from 'axios';
import Button from 'primevue/button';
import Select from 'primevue/select';
import AddClientePago from './AddClientePago.vue';
import { useToast } from 'primevue/usetoast';
import Toolbar from 'primevue/toolbar';
import InputNumber from 'primevue/inputnumber';
import ColumnGroup from 'primevue/columngroup';
import Row from 'primevue/row';
import DialogInteres from './DialogInteres.vue';

const toast = useToast();
const cuotas = ref([]);
const loading = ref(false);
const selectedEstadoPrestamo = ref('Pendiente');
const selectedCuotas = ref(null);
const pagoDialog = ref(false);
const dt = ref();
const visible = ref(false);

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
    { name: 'PARCIAL', value: 'Parcial' },
]);

watch(() => props.refresh, () => {
    loadCuotas();
});

watch(() => selectedEstadoPrestamo.value, () => {
    pagination.value.currentPage = 1;
    loadCuotas();
});

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

const hasSelectedCuotas = computed(() => {
    return selectedCuotas.value !== null;
});

const validateSelection = () => {
    if (!selectedCuotas.value) {
        return false;
    }
    return true;
};

watch(() => selectedCuotas.value, (newValue) => {
    if (newValue === null) {
        return;
    }

}, { deep: true });

const openPagoDialog = () => {
    if (!hasSelectedCuotas.value) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'Debe seleccionar una cuota para registrar un pago',
            life: 3000
        });
        return;
    }

    if (!validateSelection()) return;

    pagoDialog.value = true;
};

const onPagoAgregado = () => {
    pagoDialog.value = false;
    selectedCuotas.value = null;
    loadCuotas();
};
const selectedCuota = ref(null)

function abrirDialogo(cuota) {
    selectedCuota.value = cuota
    visible.value = true
}
</script>

<template>
    <div>
        <Toolbar class="mb-6">
            <template #start>
                <Button label="Nuevo Pago" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openPagoDialog"
                    :disabled="!hasSelectedCuotas" />
            </template>
        </Toolbar>

        <DataTable ref="dt" :value="cuotas" v-model:selection="selectedCuotas" :loading="loading" dataKey="id"
            :paginator="true" :rows="pagination.perPage" :totalRecords="pagination.total" @page="onPage" :lazy="true"
            :rowsPerPageOptions="[15, 10, 5]" scrollable scrollHeight="359px" responsiveLayout="scroll"
            class="p-datatable-sm" currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} cuotas"
            paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
            selectionMode="single">
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
            <Column selectionMode="single" style="width: 1rem" :exportable="false"></Column>
            <Column field="estado" header="Estado" sortable style="min-width: 6rem">
                <template #body="slotProps">
                    <Tag :severity="slotProps.data.estado === 'Pendiente' ? 'warn' :
                        slotProps.data.estado === 'Cancelado' ? 'info' :
                            slotProps.data.estado === 'Pagado' ? 'success' :
                                slotProps.data.estado === 'Parcial' ? 'info' :
                                    'danger'">
                        {{ slotProps.data.estado }}
                    </Tag>
                </template>
            </Column>
            <Column field="numero_cuota" header="N° Cuota" sortable style="min-width: 8rem"></Column>
            <Column field="capital" header="Capital" sortable style="min-width: 6rem"></Column>
            <Column field="fecha_inicio" header="Inicio" sortable style="min-width: 7rem"></Column>
            <Column field="fecha_vencimiento" header="Vencimiento" sortable style="min-width: 7rem"></Column>
            <Column field="dias" header="Días Interes" sortable style="min-width: 8rem"></Column>
            <Column field="interes" header="Tasa de Interes Diario" sortable style="min-width: 13rem"></Column>
            <Column header="Monto Interes Pagar" sortable style="min-width: 13rem">
                <template #body="{ data }">
                    <div class="flex justify-between items-center">
                        <span>{{ data.monto_interes_pagar }}</span>
                        <Button icon="pi pi-check" size="small" aria-label="Editar interés"
                            @click="abrirDialogo(data)" />
                    </div>
                </template>
            </Column>
            <Column header="Monto Capital Pagar" sortable style="min-width: 12rem">
                <template #body="{ data }">
                    <InputNumber v-model="data.monto_capital_pagar" disabled inputId="minmaxfraction"
                        :minFractionDigits="2" :maxFractionDigits="5" />
                </template>
            </Column>
            <Column field="saldo_capital" header="Saldo Capital" sortable style="min-width: 9rem"></Column>
            <Column field="monto_total_pagar" header="Capital mas Interes" sortable style="min-width: 12rem"></Column>
            <ColumnGroup type="footer">
                <Row>
                    <Column footer="Totales:" :colspan="8" footerStyle="text-align:right; font-weight: bold;" />
                    <Column footer="00.00" footerStyle="font-weight: bold;" />
                    <Column footer="00.00" footerStyle="font-weight: bold;" />
                    <Column footer="" footerStyle="font-weight: bold;" />
                    <Column footer="00.00" footerStyle="font-weight: bold;" />
                </Row>
            </ColumnGroup>
        </DataTable>
        <DialogInteres :visible="visible" :cuota="selectedCuota" @update:visible="visible = $event" />
        <AddClientePago v-model:visible="pagoDialog" :cuotasSeleccionadas="selectedCuotas ? [selectedCuotas] : []"
            @pago-agregado="onPagoAgregado" />
    </div>
</template>