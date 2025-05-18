<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import { FilterMatchMode } from '@primevue/core/api';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import Image from 'primevue/image';
import Button from 'primevue/button';
import axios from 'axios';
import Select from 'primevue/select';
import ToggleButton from 'primevue/togglebutton';
import MultiSelect from 'primevue/multiselect';
import DeleteCliente from './DeleteCliente.vue';
import EditCliente from './UpdateCliente.vue';

const dt = ref();
const clientes = ref([]);
const clientesTransformados = ref([]);
const selectedClientes = ref();
const loading = ref(false);
const totalRecords = ref(0);
const perPage = ref(15);
const currentPage = ref(1);
const searchValue = ref('');
const searchTimeout = ref(null);
const deleteProductDialog = ref(false);
const product = ref({});
const severityColors = ['success', 'info', 'warning', 'danger', 'secondary', 'contrast'];
const severityMap = ref({});

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS }
});

const balanceFrozen = ref(false);

const estadoClienteOptions = ref([
    { name: 'TODOS', value: '' },
    { name: 'PAGA', value: 1 },
    { name: 'MOROSOS', value: 2 },
    { name: 'TERMINARON', value: 4 },
]);

const estadoTipoClienteOptions = ref([]);

const editClienteDialog = ref(false);
const selectedClienteId = ref(null);
const selectedEstadoCliente = ref(null);
const selectedTipoCliente = ref(null);

const optionalColumns = ref([
    { field: 'direccion', header: 'Dirección' },
    { field: 'centro_trabajo', header: 'Centro de Trabajo' },
    { field: 'foto', header: 'Imagen' },
    { field: 'recomendacion', header: 'Recomendación' }
]);

const selectedColumns = ref([]);

function editCliente(cliente) {
    selectedClienteId.value = cliente.id;
    editClienteDialog.value = true;
}

function handleClienteUpdated() {
    loadClientes();
}

onMounted(() => {
    loadClientes();
});

const props = defineProps({
    refresh: {
        type: Number,
        required: true
    }
});

watch(() => props.refresh, () => {
    loadClientes();
});

const transformarDatos = (data) => {
    return data.map(cliente => {
        const prestamo = cliente.prestamos && cliente.prestamos.length > 0 ? cliente.prestamos[0] : {};

        const cuotaKeys = prestamo.cuotas ? Object.keys(prestamo.cuotas) : [];
        const cuota = cuotaKeys.length > 0 ? prestamo.cuotas[cuotaKeys[0]] : {};

        return {
            ...cliente,
            fecha_inicio: prestamo.fecha_inicio || '',
            fecha_vencimiento: prestamo.fecha_vencimiento || '',
            tasa_interes_diario: prestamo.tasa_interes || '',
            capital_inicial: prestamo.capital || '',
            numero_cuotas: prestamo.numero_cuotas || 0,
            recomendacion: prestamo.recomendacion || '',
            fecha_Inicio_pago_mes: cuota.fecha_inicio || '',
            fecha_vencimientos: cuota.fecha_vencimientos || '',
            capital_del_mes: cuota.monto_capital_pagar || 0,
            capital_actual: cuota.capital_actual || 0,
            interes_actual: cuota.interes_actual || 0,
            interes_total: cuota.interes_totales || 0,
            total: cuota.totales || 0,
            estado_cliente: prestamo.Estado
        };
    });
};

const loadClientes = async () => {
    loading.value = true;
    try {
        const params = {
            page: currentPage.value,
            per_page: perPage.value,
            search: searchValue.value,
        };
        if (selectedEstadoCliente.value) {
            params.estado_cliente = selectedEstadoCliente.value.value;
        }
        if (selectedTipoCliente.value) {
            params.tipoCliente_id = selectedTipoCliente.value.value;
        }

        const response = await axios.get('/cliente', { params });

        clientes.value = response.data.data;
        clientesTransformados.value = transformarDatos(response.data.data);
        totalRecords.value = response.data.meta.total;
    } catch (error) {
        console.error('Error al cargar clientes:', error);
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar los clientes', life: 3000 });
    } finally {
        loading.value = false;
    }
};

function confirmDeleteProduct(cliente) {
    product.value = cliente;
    deleteProductDialog.value = true;
}

function handleClientDeleted() {
    loadClientes();
}

watch(() => filters.value.global.value, (newValue) => {
    if (searchTimeout.value) {
        clearTimeout(searchTimeout.value);
    }

    searchTimeout.value = setTimeout(() => {
        searchValue.value = newValue || '';
        currentPage.value = 1;
        loadClientes();
    }, 500);
});

watch(() => selectedEstadoCliente.value, () => {
    currentPage.value = 1;
    loadClientes();
});

watch(() => selectedTipoCliente.value, () => {
    currentPage.value = 1;
    loadClientes();
});

const onPage = (event) => {
    currentPage.value = event.page + 1;
    perPage.value = event.rows;
    loadClientes();
};

const onPerPageChange = (event) => {
    perPage.value = event.rows;
    currentPage.value = 1;
    loadClientes();
};

const isColumnSelected = (fieldName) => {
    return selectedColumns.value.some(col => col.field === fieldName);
};

function color(tipo) {
    if (!severityMap.value[tipo]) {
        const index = Object.keys(severityMap.value).length % severityColors.length;
        severityMap.value[tipo] = severityColors[index];
    }
    return severityMap.value[tipo];
}

function getStatusLabel(status) {
    switch (status) {
        case 1:
            return 'success';
        case 2:
            return 'danger';
        case 4:
            return 'contrast';
        case 3:
        case '':
        case null:
        case undefined:
            return 'warn';
        default:
            return null;
    }
}

function getStatusText(status) {
    switch (status) {
        case 1:
            return 'PAGA';
        case 2:
            return 'MOROSO';
        case 4:
            return 'TERMINADO';
        case 3:
        case '':
        case null:
        case undefined:
            return 'PENDIENTE';
        default:
            return 'Desconocido';
    }
}

const formatCurrency = (value) => {
    if (value === null || value === undefined) return '';
    return new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: 'PEN',
        minimumFractionDigits: 2
    }).format(value);
};

onMounted(async () => {
    try {
        const response = await axios.get('/cliente/tipos');
        estadoTipoClienteOptions.value = response.data.data.map(tc => ({
            label: tc.nombre,
            value: tc.id
        }));
    } catch (error) {
        console.error('Error al cargar tipos de cliente:', error);
    }
});
</script>

<template>
    <DataTable ref="dt" v-model:selection="selectedClientes" :value="clientesTransformados" dataKey="id"
        :paginator="true" :loading="loading" :filters="filters"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        :rowsPerPageOptions="[15, 10, 5]" :rows="perPage" :totalRecords="totalRecords" :lazy="true" @page="onPage"
        @update:rows="onPerPageChange" currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} clientes"
        responsiveLayout="scroll" scrollHeight="800px" selectionMode="multiple" class="p-datatable-sm" scrollable>
        <template #header>
            <div class="flex flex-wrap gap-2 items-center justify-between">
                <div class="flex items-center gap-2">
                    <h4 class="m-0">Administración de Clientes</h4>
                </div>

                <div class="flex flex-wrap gap-2">
                    <IconField>
                        <InputIcon>
                            <i class="pi pi-search" />
                        </InputIcon>
                        <InputText v-model="filters['global'].value" placeholder="Buscar cliente..." />
                    </IconField>

                    <Select v-model="selectedEstadoCliente" :options="estadoClienteOptions" optionLabel="name"
                        placeholder="Estado del cliente" class="w-full md:w-auto" />
                    <Select v-model="selectedTipoCliente" :options="estadoTipoClienteOptions" optionLabel="label"
                        placeholder="Tipo Clientes" class="w-full md:w-auto" />

                    <MultiSelect v-model="selectedColumns" :options="optionalColumns" optionLabel="header"
                        display="chip" placeholder="Seleccionar Columnas" />
                    <ToggleButton v-model="balanceFrozen" onIcon="pi pi-lock" offIcon="pi pi-lock-open"
                        onLabel="Fijo" offLabel="Total" />
                    <Button icon="pi pi-refresh" outlined rounded aria-label="Refresh" @click="loadClientes" />
                </div>
            </div>
        </template>
        <Column field="dni" header="DNI" sortable style="min-width: 4rem" frozen class="font-bold"></Column>
        <Column field="nombre_completo" header="Nombre y Apellidos" sortable style="min-width: 30rem"></Column>

        <Column v-if="isColumnSelected('direccion')" field="direccion" header="Dirección" sortable
            style="min-width: 41rem">
        </Column>
        <Column v-if="isColumnSelected('centro_trabajo')" field="centro_trabajo" header="Centro de Trabajo" sortable
            style="min-width: 30rem"></Column>

        <Column field="celular" header="Celular" sortable style="min-width: 8rem"></Column>

        <Column v-if="isColumnSelected('foto')" header="Imagen">
            <template #body="slotProps">
                <Image v-if="slotProps.data.foto" :src="slotProps.data.foto" class="rounded" alt="Foto del cliente"
                    preview width="50" style="width: 64px" />
                <span v-else>-</span>
            </template>
        </Column>

        <Column field="fecha_inicio" header="F. I. Contrato" sortable style="min-width: 12rem"></Column>
        <Column field="fecha_vencimiento" header="F. V. Contrato" sortable style="min-width: 12rem"></Column>
        <Column field="tasa_interes_diario" header="T. Interés Diario" sortable style="min-width: 12rem">
            <template #body="slotProps">
                {{ slotProps.data.tasa_interes_diario }}%
            </template>
        </Column>
        <Column field="capital_inicial" header="Capital Inicial" sortable style="min-width: 12rem">
            <template #body="slotProps">
                {{ formatCurrency(slotProps.data.capital_inicial) }}
            </template>
        </Column>
        <Column field="numero_cuotas" header="N° Cuotas" sortable style="min-width: 8rem"></Column>
        <Column field="nomTipoCliente" header="Tipo" sortable style="min-width: 10rem">
            <template #body="slotProps">
                <Tag :value="slotProps.data.nomTipoCliente" :severity="color(slotProps.data.nomTipoCliente)" />
            </template>
        </Column>
        <Column field="estado_cliente" header="Estado" sortable style="min-width: 10rem">
            <template #body="slotProps">
                <Tag :value="getStatusText(slotProps.data.estado_cliente)"
                    :severity="getStatusLabel(slotProps.data.estado_cliente)" />
            </template>
        </Column>
        <Column v-if="isColumnSelected('recomendacion')" field="recomendacion" header="Recomendación" sortable
            style="min-width: 35rem"></Column>

        <Column field="fecha_Inicio_pago_mes" header="I. P. mes" sortable style="min-width: 12rem"></Column>
        <Column field="fecha_vencimientos" header="V. P. por mes" sortable style="min-width: 12rem"></Column>
        <Column field="capital_del_mes" header="Capital del mes" sortable style="min-width: 12rem">
            <template #body="slotProps">
                {{ formatCurrency(slotProps.data.capital_actual) }}
            </template>
        </Column>
        <Column field="capital_actual" header="Capital Actual" sortable style="min-width: 12rem">
            <template #body="slotProps">
                {{ formatCurrency(slotProps.data.capital_actual) }}
            </template>
        </Column>
        <Column field="interes_actual" header="Interés Actual" sortable style="min-width: 12rem">
            <template #body="slotProps">
                {{ formatCurrency(slotProps.data.interes_actual) }}
            </template>
        </Column>
        <Column field="interes_total" header="Interés Total" sortable style="min-width: 12rem">
            <template #body="slotProps">
                {{ formatCurrency(slotProps.data.interes_total) }}
            </template>
        </Column>
        <Column field="total" header="Total" sortable style="min-width: 12rem" alignFrozen="right"
            :frozen="balanceFrozen" frozen class="font-bold">
            <template #body="slotProps">
                {{ formatCurrency(slotProps.data.total) }}
            </template>
        </Column>
        <Column :exportable="false" style="min-width: 8rem">
            <template #body="slotProps">
                <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editCliente(slotProps.data)" />
                <Button icon="pi pi-trash" outlined rounded severity="danger"
                    @click="confirmDeleteProduct(slotProps.data)" />
            </template>
        </Column>
    </DataTable>
    <DeleteCliente v-model:visible="deleteProductDialog" :product="product" @deleted="handleClientDeleted" />
    <EditCliente v-model:visible="editClienteDialog" :clienteId="selectedClienteId" @updated="handleClienteUpdated" />
</template>