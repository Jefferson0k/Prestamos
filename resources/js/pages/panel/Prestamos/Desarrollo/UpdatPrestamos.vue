<template>
    <Dialog v-model:visible="dialogVisible" :style="{ width: '450px' }" header="Actualizar Préstamo" :modal="true">
        <div class="flex flex-col gap-6">
            <div>
                <label for="inventoryStatus" class="block font-bold mb-3">Cliente <span
                        class="text-red-500">*</span></label>
                        <InputText type="text" v-model="prestamo.NombreCompleto" fluid disabled/>
            </div>
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-6">
                    <label for="inicioVencimiento" class="block font-bold mb-3">Inicio / Vencimiento <span
                            class="text-red-500">*</span></label>
                    <DatePicker v-model="dates" selectionMode="range" dateFormat="dd/mm/yy" :manualInput="false" />
                </div>
                <div class="col-span-6">
                    <label for="capital" class="block font-bold mb-3">Capital <span
                            class="text-red-500">*</span></label>
                    <InputNumber v-model="prestamo.capital" mode="currency" currency="PEN" disabled locale="es-PE"
                        :useGrouping="true" :minFractionDigits="2" fluid />
                </div>
            </div>
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-6">
                    <label for="ncuotas" class="block font-bold mb-3">N° Cuotas <span
                            class="text-red-500">*</span></label>
                    <InputNumber v-model="prestamo.numero_cuotas" integeronly fluid />
                </div>
                <div class="col-span-6">
                    <label for="interesdiario" class="block font-bold mb-3">Tasa de interes diario (%) <span
                            class="text-red-500">*</span></label>
                    <InputNumber v-model="prestamo.tasa_interes_diario" prefix="%" integeronly fluid />
                </div>
            </div>
            <div>
                <label for="estado" class="block font-bold mb-3">Estado del cliente <span
                        class="text-red-500">*</span></label>
                <Select v-model="prestamo.estado_cliente" 
                        :options="statuses" 
                        optionLabel="label" 
                        :class="{ 'p-invalid': submitted && !prestamo.estado_cliente }"
                        placeholder="Seleccione un estado" 
                        fluid />
            </div>
            <div>
                <label for="recoemdado" class="block font-bold mb-3">Recomendado <span
                        class="text-red-500">*</span></label>
                <InputText type="text" v-model="prestamo.recomendacion" fluid disabled/>
            </div>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="dialogVisible = false" />
            <Button label="Save" icon="pi pi-check" :loading="loading" @click="saveUpdate" />
        </template>
    </Dialog>
</template>
<script setup>
import { ref,watch } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Select from 'primevue/select';
import InputNumber from 'primevue/inputnumber';
import axios from 'axios';
import DatePicker from 'primevue/datepicker';
import InputText from 'primevue/inputtext';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const submitted = ref(false);
const loading = ref(false);
const dates = ref();
const prestamo = ref([]);

const statuses = ref([
    { label: 'PAGA', value: 1 },
    { label: 'MOROSO', value: 2 },
]);

const props = defineProps({
    visible: Boolean,
    PrestamoId: Number
});

const emit = defineEmits(['update:visible', 'updated']);

const dialogVisible = ref(props.visible);
watch(() => props.visible, (val) => dialogVisible.value = val);
watch(dialogVisible, (val) => emit('update:visible', val));

watch(() => props.visible, (newVal) => {
    if (newVal && props.PrestamoId) {
        fetchPrestamos();
    }
});

const fetchPrestamos = async () => {
    loading.value = true;
    try {
        const response = await axios.get(`/prestamo/${props.PrestamoId}`);
        const data = response.data.data;
        const fechaInicio = new Date(data.finicio);
        const fechaVencimiento = new Date(data.fvencimiento);
        const estadoObj = statuses.value.find(s => s.value === parseInt(data.estado));        
        prestamo.value = {
            ...data,
            capital: parseFloat(data.capital),
            cliente_id: data.cliente_id,
            recomendado_id: data.recomendado_id,
            tasa_interes_diario: parseFloat(data.tasa_interes_diario),
            numero_cuotas: parseInt(data.numero_cuotas),
            estado_cliente: estadoObj
        };        
        dates.value = [fechaInicio, fechaVencimiento];
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo cargar el préstamo', life: 3000 });
        console.error(error);
    } finally {
        loading.value = false;
    }
};

const saveUpdate = async () => {
    submitted.value = true;
    
    loading.value = true;
    
    try {
        // Formatear fechas a 'd-m-Y'
        const fechaInicio = dates.value[0].toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        }).replace(/\//g, '-');
        
        const fechaVencimiento = dates.value[1].toLocaleDateString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        }).replace(/\//g, '-');
        
        // Preparar datos para enviar
        const updateData = {
            cliente_id: prestamo.value.cliente_id,
            fecha_inicio: fechaInicio,
            fecha_vencimiento: fechaVencimiento,
            capital: prestamo.value.capital,
            numero_cuotas: prestamo.value.numero_cuotas,
            tasa_interes_diario: prestamo.value.tasa_interes_diario,
            recomendado_id: prestamo.value.recomendado_id,
            estado_cliente: prestamo.value.estado_cliente.value || prestamo.value.estado_cliente,
        };

        
        const response = await axios.put(`/prestamo/${props.PrestamoId}`, updateData);
        
        toast.add({ severity: 'success', summary: 'Éxito', detail: 'Préstamo actualizado correctamente', life: 3000 });
        dialogVisible.value = false;
        emit('updated');
    } catch (error) {
        let errorMessage = 'Error al actualizar el préstamo';
        if (error.response && error.response.data && error.response.data.message) {
            errorMessage = error.response.data.message;
        }
        toast.add({ severity: 'error', summary: 'Error', detail: errorMessage, life: 3000 });
        console.error(error);
    } finally {
        loading.value = false;
    }
};
</script>