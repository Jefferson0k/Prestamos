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

// Nuevas variables para los diálogos de confirmación
const showConfirmDialog = ref(false);
const showEditInterestDialog = ref(false);
const interesData = ref(null);
const loadingInteres = ref(false);
const cuotaGuardadaId = ref(null);
const capitalInteresCalculado = ref(0);

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

const emit = defineEmits(['update:visible', 'pago-agregado', 'editar-interes']);

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
    cuotaGuardadaId.value = null;
    interesData.value = null;
    capitalInteresCalculado.value = 0;
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

        const response = await axios.post('/cuota', datosPago);

        // Guardar el ID de la cuota para usarlo en la consulta de interés
        cuotaGuardadaId.value = response.data.id || props.cuotasSeleccionadas[0].id;

        toast.add({
            severity: 'success',
            summary: 'Éxito',
            detail: 'Pago registrado correctamente',
            life: 3000
        });

        emit('pago-agregado');

        // Mostrar el diálogo de confirmación en lugar de cerrar directamente
        showConfirmDialog.value = true;

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

// Función para manejar la respuesta "Sí" en el primer diálogo
const confirmarEditarInteres = async () => {
    showConfirmDialog.value = false;
    await obtenerInteresActual();
    showEditInterestDialog.value = true;
};

// Función para obtener el interés actual de la API
const obtenerInteresActual = async () => {
    if (!cuotaGuardadaId.value) {
        console.warn('No hay ID de cuota guardada para obtener interés');
        return;
    }

    loadingInteres.value = true;

    try {
        const response = await axios.get(`/cuota/${cuotaGuardadaId.value}/show/Edicion/intereses`);

        // Según la estructura que proporcionaste
        interesData.value = response.data.data;

        // Inicializar el capital_interes calculado
        if (interesData.value) {
            capitalInteresCalculado.value = parseFloat(interesData.value.capital_interes || 0);
        }

        console.log('Datos de interés obtenidos:', interesData.value);

    } catch (error) {
        console.error('Error al obtener el interés:', error);

        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudo obtener los datos del interés',
            life: 3000
        });

        // Valor por defecto en caso de error
        interesData.value = {
            id: cuotaGuardadaId.value,
            monto_capital_pagar: "0.00",
            monto_interes_pagar: "0.00",
            capital_interes: "0.00",
            state: false
        };
        capitalInteresCalculado.value = 0;
    } finally {
        loadingInteres.value = false;
    }
};

// Función para calcular el capital + interés en tiempo real
const calcularCapitalInteres = () => {
    if (interesData.value) {
        const capital = parseFloat(interesData.value.monto_capital_pagar || 0);
        const interes = parseFloat(interesData.value.monto_interes_pagar || 0);
        capitalInteresCalculado.value = capital + interes;
    }
};

// Watch para calcular automáticamente cuando cambia el interés
watch(() => interesData.value?.monto_interes_pagar, (newValue) => {
    if (newValue !== undefined) {
        calcularCapitalInteres();
    }
});

// Función para manejar la respuesta "No" en el primer diálogo
const rechazarEditarInteres = () => {
    showConfirmDialog.value = false;
    hideDialog();
};

// Función para cerrar el diálogo de editar interés
const cerrarEditarInteres = () => {
    showEditInterestDialog.value = false;
    interesData.value = null;
    cuotaGuardadaId.value = null;
    capitalInteresCalculado.value = 0;
    hideDialog();
};

// Función para aplicar los cambios del interés
// Función para aplicar los cambios del interés
const aplicarCambios = async () => {
    if (!interesData.value || !interesData.value.id) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No hay datos de interés para actualizar',
            life: 3000
        });
        return;
    }

    loadingInteres.value = true;

    try {
        await axios.post(`/cuota/${interesData.value.id}/update/interes/modificado`, {
            monto_interes_pagar: interesData.value.monto_interes_pagar,
            monto_capital_mas_interes_a_pagar: capitalInteresCalculado.value // Agregar este campo
        });

        toast.add({
            severity: 'success',
            summary: 'Éxito',
            detail: 'El monto de interés ha sido actualizado',
            life: 3000
        });

        cerrarEditarInteres();

    } catch (error) {
        console.error('Error al actualizar el interés:', error);

        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudo actualizar el monto de interés',
            life: 3000
        });
    } finally {
        loadingInteres.value = false;
    }
};
</script>

<template>
    <!-- Diálogo principal de registro de pago -->
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

    <!-- Diálogo de confirmación para editar interés -->
    <Dialog v-model:visible="showConfirmDialog" :style="{ width: '400px' }" header="Confirmar Acción" :modal="true"
        :closable="false">
        <div class="flex align-items-center gap-4 mb-4">
            <i class="pi pi-question-circle text-6xl text-primary"></i>
            <div>
                <h3 class="mb-2">¿Deseas editar el interés?</h3>
                <p class="text-color-secondary mb-0">Puedes modificar el monto del interés para esta cuota.</p>
            </div>
        </div>

        <template #footer>
            <Button label="No" icon="pi pi-times" text @click="rechazarEditarInteres" />
            <Button label="Sí" icon="pi pi-check" @click="confirmarEditarInteres" />
        </template>
    </Dialog>

    <!-- Diálogo para editar interés -->
    <Dialog v-model:visible="showEditInterestDialog" :style="{ width: '500px' }" header="Editar Interés" :modal="true"
        :closable="true" :closeOnEscape="true">
        <div class="flex flex-col gap-6">
            <!-- Encabezado -->
            <div class="text-center">
                <i class="pi pi-pencil text-6xl text-primary mb-3"></i>
                <h3 class="mb-2">Editar Monto de Interés</h3>
                <p class="text-color-secondary">Modifica el monto del interés para la cuota seleccionada.</p>
            </div>

            <!-- Información de la cuota -->
            <div v-if="props.cuotasSeleccionadas.length > 0" class="border-1 border-200 p-3 border-round">
                <div class="grid grid-cols-1 gap-3">
                    <div>
                        <label class="font-semibold text-sm text-color-secondary">Cuota ID:</label>
                        <p class="font-bold mb-0">{{ props.cuotasSeleccionadas[0].id }}</p>
                    </div>
                    <div>
                        <label class="font-semibold text-sm text-color-secondary">Fecha de Inicio:</label>
                        <p class="font-bold mb-0">{{ props.cuotasSeleccionadas[0].fecha_inicio }}</p>
                    </div>
                </div>
            </div>

            <!-- Mensaje de estado bloqueado -->
            <div v-if="interesData && !interesData.state" class="p-message p-message-warn">
                <div class="p-message-wrapper">
                    <div class="p-message-icon">
                        <i class="pi pi-exclamation-triangle"></i>
                    </div>
                    <div class="p-message-text">
                        <strong>Edición Bloqueada:</strong> Esta cuota ya fue modificada anteriormente y no se puede
                        editar
                        nuevamente.
                    </div>
                </div>
            </div>

            <!-- Campos de edición -->
            <div v-if="interesData" class="flex flex-col gap-4">
                <!-- Monto Capital a Pagar (Deshabilitado) -->
                <div>
                    <label for="capital_pagar" class="block font-bold mb-3">
                        Monto Capital a Pagar
                    </label>
                    <div class="p-inputgroup">
                        <InputNumber :model-value="parseFloat(interesData.monto_capital_pagar)" inputId="capital_pagar"
                            :useGrouping="false" fluid :disabled="true" :minFractionDigits="2" :maxFractionDigits="2"
                            class="p-disabled" />
                    </div>
                    <small class="text-color-secondary">Campo bloqueado - No editable</small>
                </div>

                <!-- Monto Interés a Pagar (Editable solo si state es true) -->
                <div>
                    <label for="interes_pagar" class="block font-bold mb-3">
                        Monto Interés a Pagar <span class="text-red-500" v-if="interesData.state">*</span>
                    </label>
                    <div class="p-inputgroup">
                        <InputNumber v-model="interesData.monto_interes_pagar" inputId="interes_pagar"
                            :useGrouping="false" fluid placeholder="Ingrese el monto del interés"
                            :disabled="loadingInteres || !interesData.state" :minFractionDigits="2"
                            :maxFractionDigits="2" :class="{ 'p-disabled': !interesData.state }" />
                    </div>
                    <small class="text-color-secondary" v-if="!interesData.state">
                        Campo bloqueado - No editable
                    </small>
                    <small class="text-color-secondary" v-else>
                        Este campo se puede modificar
                    </small>
                </div>

                <!-- Capital + Interés (Calculado automáticamente) -->
                <div>
                    <label for="capital_interes" class="block font-bold mb-3">
                        Capital + Interés (Total)
                    </label>
                    <div class="p-inputgroup">
                        <InputNumber :model-value="capitalInteresCalculado" inputId="capital_interes"
                            :useGrouping="false" fluid :disabled="true" :minFractionDigits="2" :maxFractionDigits="2"
                            class="p-disabled" />
                    </div>
                    <small class="text-color-secondary">
                        Calculado automáticamente: Capital ({{ interesData.monto_capital_pagar }}) + Interés ({{
                            interesData.monto_interes_pagar }})
                    </small>
                </div>
            </div>

            <!-- Loading state -->
            <div v-if="loadingInteres" class="text-center p-4">
                <i class="pi pi-spin pi-spinner text-4xl text-primary mb-3"></i>
                <p class="text-color-secondary">Cargando datos del interés...</p>
            </div>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="cerrarEditarInteres" :disabled="loadingInteres" />
            <Button label="Guardar Cambios" icon="pi pi-check" @click="aplicarCambios"
                :disabled="loadingInteres || !interesData?.state" :loading="loadingInteres" v-if="interesData?.state" />
        </template>
    </Dialog>
</template>