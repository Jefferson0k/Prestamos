<template>
    <Dialog v-model:visible="prestamoDialog" :style="{ width: '450px' }" header="Actualizar Préstamo" :modal="true">
        <div class="flex flex-col gap-6">
            <div>
                <label for="inventoryStatus" class="block font-bold mb-3">Cliente <span
                        class="text-red-500">*</span></label>
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
                    <InputNumber v-model="prestamo.capital" mode="currency" currency="PEN" locale="es-PE"
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
                <Select v-model="prestamo.estado_cliente" :options="statuses" optionLabel="label"
                    placeholder="Seleccione un estado" fluid></Select>
            </div>
            <div>
                <label for="recoemdado" class="block font-bold mb-3">Recomendado <span
                        class="text-red-500">*</span></label>
                <Select v-model="clienteRecomendado" :options="recomendos" editable optionLabel="label"
                    optionValue="value" showClear placeholder="Buscar clientes que lo recomendo"
                    @input="buscarclientesRecomendado" class="w-full">
                    <template #option="{ option }">
                        <div>
                            <strong>{{ option.label }}</strong>
                        </div>
                    </template>
                    <template #empty>Clientes no encontrado.</template>
                </Select>
            </div>
        </div>

        <template #footer>
            <Button label="Cancel" icon="pi pi-times" text @click="hideDialog" />
            <Button label="Save" icon="pi pi-check" @click="saveProduct" />
        </template>
    </Dialog>
</template>
<script setup>
import { ref } from 'vue';
import Button from 'primevue/button';
import Toolbar from 'primevue/toolbar';
import Dialog from 'primevue/dialog';
import Select from 'primevue/select';
import InputNumber from 'primevue/inputnumber';
import axios from 'axios';
import DatePicker from 'primevue/datepicker';
import { useToast } from 'primevue/usetoast';

const toast = useToast();
const prestamoDialog = ref(false);
const product = ref({});
const selectedProducts = ref();
const submitted = ref(false);
const clienteSeleccionado = ref(null);
const clienteRecomendado = ref(null);
const clientes = ref([]);
const recomendos = ref([]);
const dates = ref();
const prestamo = ref([]);
const statuses = ref([
    { label: 'PAGA', value: 1 },
    { label: 'MOROSO', value: 2 },
]);

const emit = defineEmits(['prestamoAgregado']);

function openNew() {
    product.value = {};
    submitted.value = false;
    prestamoDialog.value = true;
}

function hideDialog() {
    prestamoDialog.value = false;
    submitted.value = false;
}
</script>