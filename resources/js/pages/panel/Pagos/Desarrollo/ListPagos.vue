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

const toast = useToast();
const dt = ref();
const products = ref([]);
const selectedProducts = ref();
const isLoading = ref(false);

const prestamoData = ref({
    tasa_interes_diario: '',
    capital: '',
    numero_cuotas: '',
    NombreCompleto: '',
    fecha_inicio: '',
    fecha_vencimiento: ''
});

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS }
});

const props = defineProps({
    clienteId: {
        type: [Number, String, null],
        default: null
    }
});
watch(() => props.clienteId, async (id) => {

    products.value = [];
    resetPrestamoData();

    if (id && (Number.isInteger(Number(id)) || typeof id === 'string')) {
        isLoading.value = true;
        try {
            const response = await axios.get(`/prestamo/${id}/Cuotas`);

            if (response.data && response.data.data) {
                const data = response.data.data;

                prestamoData.value = {
                    tasa_interes_diario: data.tasa_interes_diario || '',
                    capital: data.capital || '',
                    numero_cuotas: data.numero_cuotas || '',
                    NombreCompleto: data.NombreCompleto || '',
                    fecha_inicio: data.fecha_inicio || '',
                    fecha_vencimiento: data.fecha_vencimiento || ''
                };

                if (data.cuotas && Array.isArray(data.cuotas)) {
                    products.value = data.cuotas.map(cuota => ({
                        ...cuota,
                        monto_interes: Number(cuota.monto_interes || 0),
                        monto_capital: Number(cuota.monto_capital || 0),
                        saldo_capital: Number(cuota.saldo_capital || 0),
                        monto_total: Number(cuota.monto_total || 0),
                        capital: Number(cuota.capital || 0)
                    }));
                } else {
                    products.value = [];
                }
            } else {
                products.value = [];
            }

            if (products.value.length === 0) {
                toast.add({
                    severity: 'info',
                    summary: 'Información',
                    detail: 'No hay cuotas disponibles para este cliente',
                    life: 3000
                });
            }
        } catch (error) {
            console.error('Error al cargar las cuotas:', error);
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: 'No se pudieron cargar las cuotas del cliente',
                life: 3000
            });
        } finally {
            isLoading.value = false;
        }
    }
}, { immediate: true });

function resetPrestamoData() {
    prestamoData.value = {
        tasa_interes_diario: '',
        capital: '',
        numero_cuotas: '',
        NombreCompleto: '',
        fecha_inicio: '',
        fecha_vencimiento: ''
    };
}
</script>

<template>
    <DataTable ref="dt" v-model:selection="selectedProducts" :value="products" dataKey="id" :paginator="true" :rows="10"
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
        <Column selectionMode="multiple" style="width: 3rem" :exportable="false"></Column>
        <Column field="estado" header="Estado" sortable style="min-width: 6rem">
            <template #body="{ data }">
                <Tag :severity="data.estado === 'Pendiente' ? 'danger' : 'success'">{{ data.estado }}</Tag>
            </template>
        </Column>
        <Column field="numero_cuota" header="N°Cuotas" sortable style="min-width: 5rem"></Column>
        <Column field="capital" header="Capital" sortable style="min-width: 5rem"></Column>
        <Column field="fecha_inicio" header="F. Inicio" sortable style="min-width: 13rem"></Column>
        <Column field="fecha_vencimiento" header="F. Pago" sortable style="min-width: 13rem"></Column>
        <Column field="dias" header="D. Interes" sortable style="min-width: 8rem"></Column>
        <Column field="monto_interes" header="M. I. Pagar" sortable style="min-width: 10rem"></Column>
        <Column field="monto_capital" header="M. C. Pagar" sortable style="min-width: 10rem"></Column>
        <Column field="saldo_capital" header="S. Capital" sortable style="min-width: 10rem"></Column>
        <Column field="monto_total" header="M. C. MÁS I. A Pagar" sortable style="min-width: 15rem"></Column>
        <ColumnGroup type="footer">
            <Row>
                <Column footer="Totales:" :colspan="7" footerStyle="text-align:right; font-weight: bold;" />
                <Column footer="00.00" footerStyle="font-weight: bold;" />
                <Column footer="00.00" footerStyle="font-weight: bold;" />
                <Column footer="00.00" footerStyle="font-weight: bold;" />
                <Column footer="00.00" footerStyle="font-weight: bold;" />
            </Row>
        </ColumnGroup>
    </DataTable>
</template>

<style scoped></style>