<script setup>
import { ref, watch } from 'vue';
import axios from 'axios';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { useToast } from 'primevue/usetoast';
import DatePicker from 'primevue/datepicker';
import InputNumber from 'primevue/inputnumber';

const toast = useToast();
const submitted = ref(false);
const loading = ref(false);
const serverErrors = ref({});
const date = ref(new Date());
const value1 = ref(0);
const value3 = ref(0);

const props = defineProps({
    visible: {
        type: Boolean,
        default: false
    },
    cuotasSeleccionadas: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:visible', 'pago-agregado']);

const calcularDias = () => {

    if (props.cuotasSeleccionadas && props.cuotasSeleccionadas.length > 0 && date.value) {
        const fechaInicioRaw = props.cuotasSeleccionadas[0].fecha_inicio;
        const fechaInicio = parseFechaDMY(fechaInicioRaw);
        const fechaPago = new Date(date.value);
        const diferenciaTiempo = fechaPago.getTime() - fechaInicio.getTime();
        const diasCalculados = Math.ceil(diferenciaTiempo / (1000 * 3600 * 24));

        value3.value = diasCalculados > 0 ? diasCalculados : 0;
    } else {
        console.warn('[calcularDias] No se pudo calcular días. Falta fecha o cuota.');
    }
};

function parseFechaDMY(fechaStr) {
    const [dia, mes, anio] = fechaStr.split('-');
    return new Date(`${anio}-${mes}-${dia}`);
}

const inicializarValores = () => {

    if (props.cuotasSeleccionadas && props.cuotasSeleccionadas.length > 0) {
        const cuotaSeleccionada = props.cuotasSeleccionadas[0];

        if (cuotaSeleccionada.capital) {
            value1.value = cuotaSeleccionada.capital;
        }

        if (cuotaSeleccionada.estado === 'Parcial' && cuotaSeleccionada.dias) {
            value3.value = cuotaSeleccionada.dias;
        } else {
            calcularDias();
        }
    } else {
        console.warn('[inicializarValores] No hay cuotas seleccionadas.');
    }
};


watch(() => props.visible, (newValue) => {
    if (newValue) {
        inicializarValores();
    }
});


watch(() => date.value, (newDate) => {
    if (props.cuotasSeleccionadas && props.cuotasSeleccionadas.length > 0 && props.cuotasSeleccionadas[0].estado !== 'Parcial') {
        calcularDias();
    }
});

const hideDialog = () => {
    emit('update:visible', false);
    resetPago();
};

function resetPago() {
    date.value = new Date();
    value1.value = 0;
    value3.value = 0;
    serverErrors.value = {};
    submitted.value = false;
    loading.value = false;
}

function formatDateToYMD(dateObj) {
    const year = dateObj.getFullYear();
    const month = String(dateObj.getMonth() + 1).padStart(2, '0');
    const day = String(dateObj.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
}

async function guardarPago() {
    submitted.value = true;

    if (!date.value) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Debe seleccionar una fecha de pago', life: 3000 });
        return;
    }


    if (!props.cuotasSeleccionadas || props.cuotasSeleccionadas.length === 0) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No hay cuota seleccionada', life: 3000 });
        return;
    }

    loading.value = true;

    try {
        const datosPago = {
            cuota_id: props.cuotasSeleccionadas[0].id,
            monto_capital_pagar: value1.value,
            fecha_pago: formatDateToYMD(date.value),
            dias: value3.value
        };

        await axios.post('/cuota', datosPago);

        toast.add({
            severity: 'success',
            summary: 'Éxito',
            detail: 'Pago registrado correctamente',
            life: 3000
        });

        emit('pago-agregado');
        hideDialog();
    } catch (error) {
        console.error('Error al guardar el pago:', error);

        if (error.response && error.response.data && error.response.data.errors) {
            serverErrors.value = error.response.data.errors;

            for (const key in serverErrors.value) {
                toast.add({
                    severity: 'error',
                    summary: 'Error',
                    detail: serverErrors.value[key][0],
                    life: 3000
                });
            }
        } else {
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'Ocurrió un error al registrar el pago',
                life: 3000
            });
        }
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <Dialog v-model:visible="props.visible" :style="{ width: '450px' }" header="Registro de Pago" :modal="true"
        :closable="true" :closeOnEscape="true" @update:visible="(val) => emit('update:visible', val)">
        <div class="flex flex-col gap-6">
            <div>
                <div v-if="props.cuotasSeleccionadas.length > 0">
                    <div class="grid grid-cols-2 gap-2">
                        <label class="block font-bold mb-3" fluid>Fecha de Inicio: {{
                            props.cuotasSeleccionadas[0].fecha_inicio }}</label>
                        <label class="block font-bold mb-3" fluid>Monto Interes: {{
                            props.cuotasSeleccionadas[0].monto_interes_pagar }}</label>

                    </div>
                </div>
            </div>
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-8">
                    <label for="fecha_vencimiento" class="block font-bold mb-3">Fecha de Pago <span
                            class="text-red-500">*</span></label>
                    <DatePicker v-model="date" dateFormat="dd/mm/yy" fluid showButtonBar
                        placeholder="Seleccione la fecha de pago" />
                </div>
                <div class="col-span-4">
                    <label for="fecha_vencimiento" class="block font-bold mb-3">Dias <span
                            class="text-red-500">*</span></label>
                    <InputNumber v-model="value3" inputId="withoutgrouping" :useGrouping="false" fluid
                        :readonly="true" />
                </div>
            </div>

            <div>
                <label for="Capal_Pagar" class="block font-bold mb-3">Capital a Pagar <span
                        class="text-red-500">*</span></label>
                <InputNumber v-model="value1" inputId="withoutgrouping" :useGrouping="false" fluid />
            </div>

            <div v-if="Object.keys(serverErrors).length > 0" class="p-message p-message-error">
                <ul>
                    <li v-for="(error, index) in serverErrors" :key="index">{{ error[0] }}</li>
                </ul>
            </div>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
            <Button label="Guardar" icon="pi pi-check" :loading="loading" @click="guardarPago" />
        </template>
    </Dialog>
</template>