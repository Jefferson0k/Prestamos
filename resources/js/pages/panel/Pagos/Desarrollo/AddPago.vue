<template>
    <Toolbar>
        <template #start>
            <Button label="New" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
        </template>
        <template #center>
            
        </template>
        <template #end>
            <DialogPagos/>
            <Button icon="pi pi-print" label="Imprimir" outlined severity="help" class="mr-2"/>
            <Button icon="pi pi-sign-out" label="Salir" outlined severity="danger" class="mr-2" />
        </template>
    </Toolbar>
    <Dialog v-model:visible="clienteDialog" :style="{ width: '1100px' }" header="Registro de Pagos" :modal="true"
        @keydown.esc="handleCloseModal">

        <div class="flex flex-col gap-6">
            <div>
                <label for="inventoryStatus" class="block font-bold mb-3">
                    Cliente <span class="text-red-500">*</span>
                </label>
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
        <br />
        <div v-if="estaCargandoCuotas || prestamos.length === 0">
            <div class="rounded border border-surface-200 dark:border-surface-700 p-6 bg-surface-0 dark:bg-surface-900">
                <ul class="m-0 p-0 list-none">
                    <li v-for="i in 4" :key="i" class="mb-4">
                        <div class="flex">
                            <Skeleton shape="circle" size="4rem" class="mr-2" />
                            <div class="self-center flex-1">
                                <Skeleton width="100%" class="mb-2" />
                                <Skeleton width="75%" />
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <div v-else v-for="(prestamo, index) in prestamos" :key="index">
            <Fieldset>
                <template #legend>
                    <div class="flex items-center pl-2">
                        <Avatar :image="prestamo.foto" shape="circle" />
                        <span class="font-bold p-2">{{ prestamo.nombre }}</span>
                        <Tag :value="estadoTexto[prestamo.estado_cliente]"
                            :severity="estadoColor[prestamo.estado_cliente]" />
                    </div>
                </template>

                <div class="flex flex-wrap gap-6 py-4">
                    <div class="flex-1"><strong>Nombre:</strong> {{ prestamo.nombre }}</div>
                    <div class="flex-1"><strong>DNI:</strong> {{ prestamo.dni }}</div>
                    <div class="flex-1">
                        <strong>Capital:</strong>
                        <Tag severity="success" :value="'S/ ' + prestamo.capital"></Tag>
                    </div>
                </div>
                <div class="flex flex-wrap gap-6">
                    <div class="flex-1"><strong>Inicio:</strong> {{ prestamo.Fecha_Inicio }}</div>
                    <div class="flex-1"><strong>Vencimiento:</strong> {{ prestamo.Fecha_Vencimiento }}</div>
                    <div class="flex-1">
                        <strong>I. Diario:</strong>
                        <Tag severity="info" :value="prestamo.tasa_interes_diario + '%'" />
                    </div>
                </div>

                <h3 class="text-lg font-semibold mt-6 mb-2 border-b pb-1">Datos del recomendado</h3>
                <div class="flex flex-wrap gap-6 py-4">
                    <div class="flex-1"><strong>Nombre:</strong> {{ prestamo.recomendado }}</div>
                    <div class="flex-1"><strong>DNI:</strong> {{ prestamo.Dnirecomendado }}</div>
                    <div class="flex-1">
                        <SelectButton :options="['Pagar Aqui']" :disabled="prestamo.estado_cliente === 4"
                            :modelValue="accionSeleccionada === prestamo.idPrestamo ? 'Pagar Aqui' : null"
                            @change="() => pagarPrestamo(prestamo.idPrestamo)" />
                    </div>
                </div>
            </Fieldset>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
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
import Tag from 'primevue/tag';
import Fieldset from 'primevue/fieldset';
import Avatar from 'primevue/avatar';
import SelectButton from 'primevue/selectbutton';
import Skeleton from 'primevue/skeleton';
import DialogPagos from './DialogPagos.vue';

const toast = useToast();
const clienteSeleccionado = ref(null);
const clienteDialog = ref(false);
const submitted = ref(false);
const clientes = ref([]);
const prestamos = ref([]);
const estaCargandoCuotas = ref(false);
const accionSeleccionada = ref(null);

const emit = defineEmits(['ver-cuotas']);

const estadoTexto = {
    1: 'Paga',
    2: 'Mora',
    4: 'Finalizado'
};

const estadoColor = {
    1: 'info',
    2: 'warning',
    4: 'success'
};

function openNew() {
    submitted.value = false;
    clienteDialog.value = true;
}

function hideDialog() {
    clienteDialog.value = false;
    submitted.value = false;
    clienteSeleccionado.value = null;
    accionSeleccionada.value = null;
}

function handleCloseModal() {
    clienteDialog.value = false;
    submitted.value = false;
    clienteSeleccionado.value = null;
    accionSeleccionada.value = null;
}

function pagarPrestamo(idPrestamo) {
    if (accionSeleccionada.value === idPrestamo) return;
    accionSeleccionada.value = idPrestamo;
    emit('ver-cuotas', idPrestamo);
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

const cargarCuotas = async (clienteId) => {
    if (!clienteId) return;

    estaCargandoCuotas.value = true;
    prestamos.value = [];

    try {
        const response = await axios.get(`/prestamo/${clienteId}/Cuotas`);
        prestamos.value = response.data.clientes;
    } catch (error) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Error al cargar las cuotas",
            life: 3000
        });
    } finally {
        estaCargandoCuotas.value = false;
    }
};

let debounceTimeout = null;
</script>