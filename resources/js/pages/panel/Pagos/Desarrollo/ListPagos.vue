<script setup>
import { FilterMatchMode } from '@primevue/core/api';
import { ref, watch, defineProps, defineEmits, computed } from 'vue';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import ColumnGroup from 'primevue/columngroup';
import Row from 'primevue/row';
import Tag from 'primevue/tag';
import { useToast } from 'primevue/usetoast';
import InputNumber from 'primevue/inputnumber';
import Button from 'primevue/button';

const toast = useToast();
const dt = ref();
const selectedProducts = ref([]);
const isLoading = ref(false);

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS }
});

const props = defineProps({
    cuotas: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['abrir-dialogo', 'monto-cambio', 'imprimir-comprobante', 'abrir-actualizacion']);

const puedeEditar = (cuota) => {
    const cuotasConFechasValidas = props.cuotas.filter(c =>
        c.fecha_inicio !== '00-00-0000' &&
        c.fecha_vencimiento !== '00-00-0000'
    );

    const ultima = cuotasConFechasValidas.at(-1);
    return ultima?.id === cuota.id;
};

const limpiarNumero = (valor) => {
    if (!valor) return 0;
    return parseFloat(valor.toString().replace(/[^\d.-]+/g, '')) || 0;
};

const calcularTotales = () => {
    if (!props.cuotas || props.cuotas.length === 0) return {
        montoInteres: 0,
        montoCapital: 0,
        montoTotal: 0
    };

    return props.cuotas.reduce((totales, cuota) => {
        totales.montoInteres += limpiarNumero(cuota.monto_interes_pagar);
        totales.montoCapital += limpiarNumero(cuota.monto_capital_pagar);
        totales.montoTotal += limpiarNumero(cuota.monto_total_pagar);
        return totales;
    }, { montoInteres: 0, montoCapital: 0, montoTotal: 0 });
};

// Computed para mejor rendimiento
const totales = computed(() => calcularTotales());

// Formato amigable para miles
const formatear = (numero) => new Intl.NumberFormat('es-PE', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
}).format(numero);

const handleMontoChange = (cuotaId, newMonto) => {
    emit('monto-cambio', { cuotaId, newMonto });
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
                <h4 class="m-0">Cronograma</h4>
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
                <Tag :severity="slotProps.data.estado === 'Pendiente' ? 'warn' :
                    slotProps.data.estado === 'Cancelado' ? 'info' :
                        slotProps.data.estado === 'Pagado' ? 'success' :
                            'danger'">
                    {{ slotProps.data.estado }}
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
                <InputNumber v-model="data.monto_capital_pagar" disabled inputId="minmaxfraction" :minFractionDigits="2"
                    :maxFractionDigits="5" @blur="handleMontoChange(data.id, data.monto_capital_pagar)" />
            </template>
        </Column>
        <Column field="saldo_capital" header="Saldo Capital" sortable style="min-width: 9rem"></Column>
        <Column field="monto_total_pagar" header="Capital más Interes" sortable style="min-width: 12rem"></Column>
        <Column :exportable="false" style="min-width: 12rem">
            <template #body="slotProps">
                <Button icon="pi pi-dollar" outlined rounded severity="success" class="mr-2"
                    :disabled="slotProps.data.fecha_inicio === '00-00-0000' || slotProps.data.fecha_vencimiento !== '00-00-0000'"
                    @click="$emit('abrir-dialogo', slotProps.data.id)" />
                <Button icon="pi pi-pencil" outlined rounded class="mr-2"
                    :disabled="!puedeEditar(slotProps.data)" @click="$emit('abrir-actualizacion', slotProps.data.id)" />
                <Button icon="pi pi-print" outlined rounded severity="help" class="mr-2"
                    :disabled="['Cancelado', 'Pendiente'].includes(slotProps.data.estado)"
                    @click="$emit('imprimir-comprobante', slotProps.data.id)" />
            </template>
        </Column>

        <!-- Totales en el pie -->
        <ColumnGroup type="footer">
            <Row>
                <Column footer="Totales:" :colspan="8" footerStyle="text-align:right; font-weight: bold;" />
                <Column :footer="formatear(totales.montoInteres)" footerStyle="font-weight: bold;" />
                <Column :footer="formatear(totales.montoCapital)" footerStyle="font-weight: bold;" />
                <Column footer="" footerStyle="font-weight: bold;" />
                <Column :footer="formatear(totales.montoTotal)" footerStyle="font-weight: bold;" />
            </Row>
        </ColumnGroup>
    </DataTable>
</template>

<style scoped>
/* Puedes agregar estilos si es necesario */
</style>
