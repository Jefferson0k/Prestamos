<template>
    <Toolbar>
        <template #start>
            <Button label="New" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
        </template>
        <template #center>
        </template>
    </Toolbar>
    
    <Dialog v-model:visible="clienteDialog" :style="{ width: '800px' }" header="Registro de Pagos" :modal="true">
        <div class="flex flex-col gap-6">
            <div>
                <label for="inventoryStatus" class="block font-bold mb-3">Cliente <span class="text-red-500">*</span></label>
                <div class="flex gap-2">
                    <Select v-model="clienteSeleccionado" :options="clientes" editable optionLabel="label"
                            optionValue="value" showClear placeholder="Buscar clientes..." @input="buscarclientes"
                            class="w-full">
                        <template #option="{ option }">
                            <div>
                                <strong>{{ option.label }}</strong>
                            </div>
                        </template>
                        <template #empty>Clientes no encontrado.</template>
                    </Select>
                    <Button icon="pi pi-search" severity="info"
                            :disabled="!clienteSeleccionado" tooltip="Cargar cuotas" @click="cargarCuotas(clienteSeleccionado)"/>
                </div>
            </div>

            <div v-if="prestamo" class="p-3 rounded">
                <h3 class="text-lg font-bold mb-2">Información del Préstamo</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div><strong>DNI:</strong> {{ prestamo.dni }}</div>
                    <div><strong>Cliente:</strong> {{ prestamo.NombreCompleto }}</div>
                    <div><strong>Capital:</strong> {{ prestamo.capital }}</div>
                    <div><strong>N° Cuotas:</strong> {{ prestamo.numero_cuotas }}</div>
                    <div><strong>Fecha Inicio:</strong> {{ prestamo.fecha_inicio }}</div>
                    <div><strong>Fecha Vencimiento:</strong> {{ prestamo.fecha_vencimiento }}</div>
                    <div><strong>Tasa Interés Diario:</strong> {{ prestamo.tasa_interes_diario }}%</div>
                    <div><strong>Estado:</strong> {{ prestamo.estado_cliente }}</div>
                </div>
            </div>
        </div>
        
        <DataTable 
            ref="dt" 
            v-model:selection="selectedCuotas" 
            :value="cuotas" 
            dataKey="id" 
            :paginator="true"
            :rows="10" 
            :filters="filters"
            paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
            :rowsPerPageOptions="[5, 10, 25]"
            :loading="loading"
            currentPageReportTemplate="Showing {first} to {last} of {totalRecords} cuotas"
            class="p-datatable-sm">
            <template #header>
                <div class="flex flex-wrap gap-2 items-center justify-between">
                    <h4 class="m-0">Pagos</h4>
                    <IconField>
                        <InputIcon>
                            <i class="pi pi-search" />
                        </InputIcon>
                        <InputText v-model="filters['global'].value" placeholder="Buscar..." />
                    </IconField>
                </div>
            </template>
            <Column selectionMode="multiple" style="width: 3rem" :exportable="false"></Column>
            <Column field="numero_cuota" header="N° Cuota" sortable style="min-width: 8rem"></Column>
            <Column field="monto_total" header="Monto" sortable style="min-width: 5rem"></Column>
            <Column field="dias" header="Días" sortable style="min-width: 4rem"></Column>
            <Column field="fecha_inicio" header="F. Inicio" sortable style="min-width: 13rem"></Column>
            <Column field="fecha_vencimiento" header="F. Vencimiento" sortable style="min-width: 13rem"></Column>
            <Column field="estado" header="Estado" sortable style="min-width: 8rem">
                <template #body="slotProps">
                    <Tag severity="warn">{{ slotProps.data.estado }}</Tag>
                </template>
                </Column>
        </DataTable>
        
        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
            <Button label="Pagar" icon="pi pi-check"/>
        </template>
    </Dialog>
</template>

<script setup>
import { ref } from 'vue';
import Toolbar from 'primevue/toolbar';
import Select from 'primevue/select';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import { FilterMatchMode } from '@primevue/core/api';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';

const toast = useToast();
const clienteSeleccionado = ref(null);
const clienteDialog = ref(false);
const pago = ref({});
const submitted = ref(false);
const clientes = ref([]);
const cuotas = ref([]);
const selectedCuotas = ref([]);
const prestamo = ref(null);
const loading = ref(false);

let debounceTimeout = null;

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS }
});

function openNew() {
    pago.value = {};
    submitted.value = false;
    clienteDialog.value = true;
}

function hideDialog() {
    clienteDialog.value = false;
    submitted.value = false;
}

async function cargarCuotas(clienteId) {
    if (!clienteId) {
        cuotas.value = [];
        prestamo.value = null;
        return;
    }
    
    loading.value = true;
    try {
        const url = `/prestamo/${clienteId}/Cuotas`;
        console.log(`Realizando petición a: ${url}`);
        
        const response = await axios.get(url);
        console.log('Respuesta recibida:', response.data);
        
        if (response.data && response.data.data) {
            prestamo.value = response.data.data;
            cuotas.value = response.data.data.cuotas || [];
            console.log(`Cuotas cargadas: ${cuotas.value.length}`);
            
            toast.add({
                severity: "success",
                summary: "Éxito",
                detail: `Cuotas del cliente cargadas (${cuotas.value.length})`,
                life: 3000
            });
        } else {
            cuotas.value = [];
            prestamo.value = null;
            console.log('No se encontraron datos de cuotas');
            
            toast.add({
                severity: "info",
                summary: "Información",
                detail: "No se encontraron cuotas para este cliente",
                life: 3000
            });
        }
    } catch (error) {
        console.error('Error al cargar cuotas:', error);
        toast.add({
            severity: "error",
            summary: "Error",
            detail: `Error al cargar las cuotas: ${error.message}`,
            life: 3000
        });
        cuotas.value = [];
        prestamo.value = null;
    } finally {
        loading.value = false;
    }
}

const buscarclientes = async (evento) => {
    clearTimeout(debounceTimeout);
    const textoIngresado = evento.target?.value?.trim() || "";
    if (!textoIngresado) {
        clientes.value = [];
        return;
    }
    debounceTimeout = setTimeout(async () => {
        try {
            const response = await axios.get("/prestamo/cliente", {
                params: {
                    search: textoIngresado
                },
            });
            clientes.value = response.data.data.map((cliente) => ({
                label: `${cliente.label}`,
                value: cliente.id,
            }));
        } catch (error) {
            toast.add({
                severity: "error",
                summary: "Error",
                detail: "Error al buscar clientes",
                life: 3000
            });
        }
    }, 500);
};
</script>
