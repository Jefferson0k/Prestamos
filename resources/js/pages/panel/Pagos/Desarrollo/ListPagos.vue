<script setup>
import { FilterMatchMode } from '@primevue/core/api';
import { ref, watch } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import ColumnGroup from 'primevue/columngroup';
import Row from 'primevue/row';
import Tag from 'primevue/tag';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
import InputNumber from 'primevue/inputnumber';
import Button from 'primevue/button';

const toast = useToast();
const dt = ref();
const selectedProducts = ref();
const isLoading = ref(false);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS }
});
defineProps({
    cuotas: Array
});
const calcularCuota = async (cuota) => {
    try {
        // Enviar los datos al servidor para calcular el monto
        const response = await axios.post(`/cuota/pagar/${cuota.prestamo_id}`, {
            monto_capital_pagar: cuota.monto_capital_pagar
        });

        // Aquí, se asume que el servidor devuelve los datos actualizados de la cuota
        const updatedCuotas = response.data.data;

        // Actualizamos las cuotas en la tabla
        cuotasSeleccionadas.value = updatedCuotas;

        toast.add({
            severity: 'success',
            summary: 'Cálculo Exitoso',
            detail: 'Cuota calculada y datos actualizados',
            life: 3000,
        });
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudo calcular la cuota',
            life: 3000,
        });
    }
};


</script>

<template>
    <DataTable ref="dt" v-model:selection="selectedProducts" :value="cuotas" dataKey="id" :paginator="true" :rows="10"
        :filters="filters" :loading="isLoading"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        :rowsPerPageOptions="[5, 10, 25]"
        currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} cuotas" responsiveLayout="scroll"
        class="p-datatable-sm">
        <template #header>
            <div class="flex flex-wrap gap-2 items-center justify-between">
                <h4 class="m-0">Historial de Pagos</h4>
                <IconField>
                    <InputIcon>
                        <i class="pi pi-search" />
                    </InputIcon>
                    <InputText v-model="filters['global'].value" placeholder="Buscar..." />
                </IconField>
            </div>
        </template>
        <Column selectionMode="multiple" style="width: 1rem" :exportable="false"></Column>
        <Column field="estado" header="Estado" sortable style="min-width: 6rem">
            <template #body="slotProps">
                <Tag :severity="slotProps.data.estado === 'Pendiente' ? 'warn' : 'success'">{{ slotProps.data.estado }}
                </Tag>
            </template>
        </Column>
        <Column field="numero_cuota" header="N° Cuota" sortable style="min-width: 8rem"></Column>
        <Column field="capital" header="Capital" sortable style="min-width: 6rem"></Column>
        <Column field="fecha_inicio" header="Inicio" sortable style="min-width: 7rem"></Column>
        <Column field="fecha_vencimiento" header="Vencimiento" sortable style="min-width: 7rem"></Column>
        <Column field="dias" header="Días Interes" sortable style="min-width: 8rem"></Column>
        <Column field="interes" header="Tasa de Interes Diario" sortable style="min-width: 13rem"></Column>
        <Column field="monto_interes_pagar" header="Monto Interes Pagar" sortable style="min-width: 12rem"></Column>
        <Column header="Monto Capital Pagar" sortable style="min-width: 12rem">
            <template #body="{ data }">
                <InputNumber 
                    v-model="data.monto_capital_pagar" 
                    :disabled="!(
                        data.fecha_inicio && data.fecha_inicio !== '00-00-0000' &&
                        (!data.fecha_vencimiento || data.fecha_vencimiento === '00-00-0000')
                    )"
                    inputId="minmaxfraction" 
                    :minFractionDigits="2"
                    :maxFractionDigits="5" 
                    fluid 
                />
            </template>
        </Column>
        <Column field="saldo_capital" header="Saldo Capital" sortable style="min-width: 9rem"></Column>
        <Column field="monto_total_pagar" header="Capital mas Interes" sortable style="min-width: 12rem"></Column>
        <Column header="" style="min-width: 4rem">
            <template #body="slotProps">
                <Button 
                    icon="pi pi-calculator" 
                    rounded 
                    outlined 
                    severity="info"
                    :disabled="!(
                        slotProps.data.fecha_inicio && slotProps.data.fecha_inicio !== '00-00-0000' &&
                        (!slotProps.data.fecha_vencimiento || slotProps.data.fecha_vencimiento === '00-00-0000')
                    )"
                    @click="calcularCuota(slotProps.data)" 
                />
            </template>
        </Column>
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
</template>

<style scoped></style>