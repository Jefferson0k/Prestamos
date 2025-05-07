<template>
    <Toolbar>
        <template #start>
            <Button label="New" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
        </template>
        <template #center>
        </template>
    </Toolbar>

    <Dialog v-model:visible="clienteDialog" :style="{ width: '1500px' }" header="Registro de Pagos" :modal="true">
        <div class="flex flex-col gap-6">
            <div>
                <label for="inventoryStatus" class="block font-bold mb-3">Cliente <span
                        class="text-red-500">*</span></label>
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
                    <Button icon="pi pi-search" severity="info" :disabled="!clienteSeleccionado" tooltip="Cargar cuotas"
                        @click="cargarCuotas(clienteSeleccionado)" />
                </div>
            </div>
        </div>
        <br>
        <Fieldset v-if="prestamo?.cliente">
            <template #legend>
                <div class="flex items-center pl-2">
                    <Avatar :image="prestamo.cliente.foto" shape="circle" />
                    <span class="font-bold p-2">{{ prestamo.cliente.nombre }}</span>
                </div>
            </template>
            <div class="flex flex-wrap gap-6 py-4">
                <div class="flex-1"><strong>Nombre:</strong> {{ prestamo.cliente.nombre }}</div>
                <div class="flex-1"><strong>DNI:</strong> {{ prestamo.cliente.dni }}</div>
                <div class="flex-1"><strong>Capital:</strong>
                    <Tag severity="success" value="Success">S/ {{ prestamo.cliente.capital }}</Tag>
                </div>
            </div>
            <div class="flex flex-wrap gap-6">
                <div class="flex-1"><strong>Inicio:</strong> {{ prestamo.cliente.Fecha_Inicio }}</div>
                <div class="flex-1"><strong>Vencimiento:</strong> {{ prestamo.cliente.Fecha_Vencimiento }}</div>
                <div class="flex-1"><strong>I. Diario:</strong>
                    <Tag severity="info" value="Info">{{ prestamo.cliente.tasa_interes_diario }} %</Tag>
                </div>
            </div>
            <h3 class="text-lg font-semibold mt-6 mb-2 border-b pb-1">Datos del recomendado</h3>
            <div class="flex flex-wrap gap-6 py-4">
                <div class="flex-1"><strong>Nombre:</strong> {{ prestamo.cliente.recomendado }}</div>
                <div class="flex-1"><strong>DNI:</strong> {{ prestamo.cliente.Dnirecomendado }}</div>
                <div class="flex-1"></div>

            </div>
        </Fieldset>
        <br>
        <DataTable v-if="prestamo?.cliente" ref="dt" v-model:selection="selectedCuotas" :value="cuotas" dataKey="id"
            :paginator="true" :rows="20" :filters="filters"
            paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
            :rowsPerPageOptions="[20, 10, 5]" :loading="loading"
            currentPageReportTemplate="Showing {first} to {last} of {totalRecords} cuotas" class="p-datatable-sm">
            <template #header>
                <div class="flex flex-wrap gap-2 items-center justify-between">
                    <h4 class="m-0">Pagos <Tag severity="success" value="Success">{{ prestamo.cantidad_cuotas }}</Tag>
                    </h4>
                    <IconField>
                        <InputIcon>
                            <i class="pi pi-search" />
                        </InputIcon>
                        <InputText v-model="filters['global'].value" placeholder="Buscar..." />
                    </IconField>
                </div>
            </template>
            <Column selectionMode="multiple" style="width: 1rem" :exportable="false"></Column>
            <Column field="numero_cuota" header="N° Cuota" sortable style="min-width: 6rem"></Column>
            <Column field="capital" header="Capital" sortable style="min-width: 6rem"></Column>
            <Column field="fecha_inicio" header="Inicio" sortable style="min-width: 4rem"></Column>
            <Column field="fecha_vencimiento" header="Vencimiento" sortable style="min-width: 4rem"></Column>
            <Column field="dias" header="Días Interes" sortable style="min-width: 4rem"></Column>
            <Column field="interes" header="Tasa de Interes Diario" sortable style="min-width: 4rem"></Column>
            <Column field="monto_interes_pagar" header="Monto Interes Pagar" sortable style="min-width: 4rem"></Column>
            <Column field="monto_capital_pagar" header="Monto Capital Pagar" sortable style="min-width: 4rem"></Column>
            <Column field="saldo_capital" header="Saldo Capital" sortable style="min-width: 4rem"></Column>
            <Column field="monto_total_pagar" header="Capital mas Interes" sortable style="min-width: 4rem"></Column>
            <Column field="estado" header="Estado" sortable style="min-width: 6rem">
                <template #body="slotProps">
                    <Tag severity="warn">{{ slotProps.data.estado }}</Tag>
                </template>
            </Column>
        </DataTable>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
            <Button label="Pagar" icon="pi pi-check" :disabled="!selectedCuotas.length" />
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
import Fieldset from 'primevue/fieldset';
import Avatar from 'primevue/avatar';
import Image from 'primevue/image';

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
        const response = await axios.get(url);

        if (response.data) {
            const data = response.data;

            prestamo.value = data;
            cuotas.value = data.cuotas || [];

            toast.add({
                severity: "success",
                summary: "Éxito",
                detail: `Cuotas del cliente cargadas (${cuotas.value.length})`,
                life: 3000
            });
        } else {
            cuotas.value = [];
            prestamo.value = null;

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