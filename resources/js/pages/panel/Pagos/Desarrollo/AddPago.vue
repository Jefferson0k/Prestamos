<template>
    <Toolbar>
        <template #start>
            <Button :label="botonClienteLabel" :icon="botonClienteIcono" :severity="botonClienteSeverity"
                :variant="botonClienteVariant" class="mr-2" @click="openNew" />
        </template>
        <template #center>

        </template>
        <template #end>
            <Button icon="pi pi-sign-out" label="Salir" outlined severity="danger" class="mr-2" />
        </template>
    </Toolbar>
    <Dialog v-model:visible="clienteDialog" :style="{ width: '1100px' }" header="Registro de Pagos" :modal="true">
        <div class="flex flex-col gap-6">
            <div>
                <label for="inventoryStatus" class="block font-bold mb-3">
                    Cliente <span class="text-red-500">*</span>
                </label>
                <div class="flex gap-2">
                    <Select v-model="clienteSeleccionado" :options="clientes" editable optionLabel="label"
                        optionValue="value" showClear placeholder="Buscar clientes..." @input="buscarclientes"
                        class="w-full" :disabled="inputsDeshabilitados">
                        <template #option="{ option }">
                            <div>
                                <strong>{{ option.label }}</strong>
                            </div>
                        </template>
                        <template #empty>Clientes no encontrado.</template>
                    </Select>
                    <Button icon="pi pi-search" severity="info" :disabled="!clienteSeleccionado || inputsDeshabilitados"
                        tooltip="Cargar cuotas" @click="cargarCuotas(clienteSeleccionado)" />
                </div>
            </div>
        </div>
        <br />
        <div v-if="estaCargandoCuotas">
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
        <div v-else-if="prestamos.length === 0">
            <p class="text-center">No se encontraron cuotas para este cliente.</p>
        </div>
        <div v-else>
            <br>
            <MultiSelect v-model="selectedCities" display="chip" :options="cities" optionLabel="name" filter
                placeholder="Selecciona estados" :maxSelectedLabels="4" class="w-full md:w-80" fluid />
            <br>
            <div v-for="(prestamo, index) in prestamos" :key="index">
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
                            <template v-if="prestamo.estado_cliente === 4">
                                <span class="text-gray-500">Finalizado</span>
                            </template>
                            <template v-else>
                                <SelectButton :options="['Pagar Aqui']"
                                    :modelValue="accionSeleccionada === prestamo.idPrestamo ? 'Pagar Aqui' : null"
                                    @change="() => pagarPrestamo(prestamo.idPrestamo)" />
                            </template>
                        </div>

                    </div>
                </Fieldset>
            </div>
        </div>
        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
        </template>
    </Dialog>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
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
import MultiSelect from 'primevue/multiselect';

const toast = useToast();
const clienteSeleccionado = ref(null);
const clienteDialog = ref(false);
const submitted = ref(false);
const clientes = ref([]);
const prestamos = ref([]);
const estaCargandoCuotas = ref(false);
const accionSeleccionada = ref(null);
const inputsDeshabilitados = ref(false);
const selectedCities = ref([
    { name: 'TODOS', code: 0 }
]);

const emit = defineEmits(['ver-cuotas']);

const cities = ref([
    { name: 'TODOS', code: 0 },
    { name: 'PAGA', code: 1 },
    { name: 'MOROSO', code: 2 },
    { name: 'FINALIZADO', code: 4 }
]);

const estadoTexto = {
    1: 'Paga',
    2: 'Mora',
    4: 'Finalizado'
};

const estadoColor = {
    1: 'success',
    2: 'danger',
    4: 'contrast'
};

function openNew() {
    submitted.value = false;
    clienteDialog.value = true;
}

function hideDialog() {
    clienteDialog.value = false;
    submitted.value = false;
}

function pagarPrestamo(idPrestamo) {
    if (accionSeleccionada.value === idPrestamo) return;
    accionSeleccionada.value = idPrestamo;

    inputsDeshabilitados.value = true;
    clienteDialog.value = false;

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

watch(selectedCities, async (newValues, oldValues) => {
    prestamos.value = [];
    if (clienteSeleccionado.value) {
        await cargarCuotas(clienteSeleccionado.value);
    }
});

const cargarCuotas = async (clienteId) => {
    if (!clienteId) return;

    estaCargandoCuotas.value = true;
    prestamos.value = [];

    try {
        const estadosSeleccionados = selectedCities.value.map(e => e.code);
        const params = {};
        if (estadosSeleccionados.length > 0 && !estadosSeleccionados.includes(0)) {
            params.estado = estadosSeleccionados.join(','); 
        }
        const response = await axios.get(`/prestamo/${clienteId}/Cuotas`, { params });
        prestamos.value = response.data.clientes;
    } catch (error) {
        console.error('Error al cargar las cuotas:', error);
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

const botonClienteLabel = computed(() => {
    if (accionSeleccionada.value && clienteSeleccionado.value) {
        const cliente = clientes.value.find(c => c.value === clienteSeleccionado.value);
        return cliente ? cliente.label : 'Cliente seleccionado';
    }
    return 'Nuevo Pago';
});

const botonClienteIcono = computed(() => {
    return (accionSeleccionada.value && clienteSeleccionado.value) ? 'pi pi-user' : 'pi pi-plus';
});

const botonClienteSeverity = computed(() => {
    return (accionSeleccionada.value && clienteSeleccionado.value) ? 'contrast' : 'secondary';
});

const botonClienteVariant = computed(() => {
    return (accionSeleccionada.value && clienteSeleccionado.value) ? 'text' : undefined;
});

</script>