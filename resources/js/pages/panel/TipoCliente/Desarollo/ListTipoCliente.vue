<script setup>
import { ref, onMounted, watch } from 'vue';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import Select from 'primevue/select';
import axios from 'axios';
import { debounce } from 'lodash';
import DeleteTipoCliente from './DeleteTipoCliente.vue';
import UpdateTipoCliente from './UpdateTipoCliente.vue';

const dt = ref();
const tiposCliente = ref([]);
const selectedTiposCliente = ref();
const loading = ref(false);
const globalFilterValue = ref('');
const deleteDialog = ref(false);
const tipoCliente = ref({});
const selectedTipoClienteId = ref(null);
const selectedEstado = ref(null);
const updateDialog = ref(false);
const currentPage = ref(1);

const props = defineProps({
    refresh: {
        type: Number,
        required: true
    }
});

watch(() => props.refresh, () => {
    loadTiposCliente();
});

watch(() => selectedEstado.value, () => {
    currentPage.value = 1;
    loadTiposCliente();
});

function editTipoCliente(cliente) {
    selectedTipoClienteId.value = cliente.id;
    updateDialog.value = true;
}

function confirmDelete(cliente) {
    tipoCliente.value = cliente;
    deleteDialog.value = true;
}

const estadoOptions = ref([
    { name: 'TODOS', value: '' },
    { name: 'ACTIVOS', value: 'activo' },
    { name: 'INACTIVOS', value: 'inactivo' },
]);

function handleTipoClienteUpdated() {
    loadTiposCliente();
}

function handleTipoClienteDeleted() {
    loadTiposCliente();
}

const pagination = ref({
    currentPage: 1,
    perPage: 15,
    total: 0
});

const loadTiposCliente = async () => {
    loading.value = true;
    try {
        const params = {
            page: pagination.value.currentPage,
            per_page: pagination.value.perPage,
            search: globalFilterValue.value,
            estado: selectedEstado.value?.value || '',
        };

        const response = await axios.get('/tipo-cliente', { params });

        tiposCliente.value = response.data.data;
        pagination.value.currentPage = response.data.meta.current_page;
        pagination.value.total = response.data.meta.total;
    } catch (error) {
        console.error('Error al cargar tipos de cliente:', error);
    } finally {
        loading.value = false;
    }
};

const onPage = (event) => {
    pagination.value.currentPage = event.page + 1;
    pagination.value.perPage = event.rows;
    loadTiposCliente();
};

const getSeverity = (estado) => {
    if (estado === 'activo') return 'success';
    if (estado === 'inactivo') return 'danger';
    return null;
};

const onGlobalSearch = debounce(() => {
    pagination.value.currentPage = 1;
    loadTiposCliente();
}, 500);

onMounted(() => {
    loadTiposCliente();
});
</script>

<template>
    <DataTable ref="dt" v-model:selection="selectedTiposCliente" :value="tiposCliente" dataKey="id"
        :paginator="true" :rows="pagination.perPage" :totalRecords="pagination.total" :loading="loading"
        :lazy="true" @page="onPage" :rowsPerPageOptions="[15, 20, 25]" scrollable scrollHeight="574px"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} tipos de cliente">

        <template #header>
            <div class="flex flex-wrap gap-2 items-center justify-between">
                <h4 class="m-0">TIPOS DE CLIENTE</h4>
                <div class="flex flex-wrap gap-2">
                    <IconField>
                        <InputIcon>
                            <i class="pi pi-search" />
                        </InputIcon>
                        <InputText v-model="globalFilterValue" @input="onGlobalSearch" placeholder="Buscar..." />
                    </IconField>
                    <Select v-model="selectedEstado" :options="estadoOptions" optionLabel="name"
                        placeholder="Estado" class="w-full md:w-auto" />
                    <Button icon="pi pi-refresh" outlined rounded aria-label="Refresh" @click="loadTiposCliente" />
                </div>
            </div>
        </template>

        <Column selectionMode="multiple" style="width: 1rem" :exportable="false" />
        <Column field="nombre" header="Nombre" sortable style="min-width: 13rem" />
        <Column field="creacion" header="Creación" sortable style="min-width: 13rem" />
        <Column field="actualizacion" header="Actualización" sortable style="min-width: 13rem" />
        <Column field="estado" header="Estado" sortable style="min-width: 6rem">
            <template #body="{ data }">
                <Tag :value="data.estado" :severity="getSeverity(data.estado)" />
            </template>
        </Column>
        <Column :exportable="false" style="min-width: 8rem">
            <template #body="slotProps">
                <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editTipoCliente(slotProps.data)" />
                <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDelete(slotProps.data)" />
            </template>
        </Column>
    </DataTable>

    <DeleteTipoCliente
        v-model:visible="deleteDialog"
        :tipoCliente="tipoCliente"
        @deleted="handleTipoClienteDeleted"
    />
    <UpdateTipoCliente
        v-model:visible="updateDialog"
        :tipoClienteId="selectedTipoClienteId"
        @updated="handleTipoClienteUpdated"
    />
</template>
