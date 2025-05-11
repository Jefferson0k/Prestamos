<script setup>
import { ref, onMounted,watch } from 'vue';
import Button from 'primevue/button';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Tag from 'primevue/tag';
import axios from 'axios';
import { debounce } from 'lodash';
import DeleteUsuario from './DeleteUsuario.vue';
import updateUsuario from './updateUsuario.vue';

const dt = ref();
const users = ref([]);
const selectedUsers = ref();
const loading = ref(false);
const globalFilterValue = ref('');
const deleteProductDialog = ref(false);
const product = ref({});
const selectedUsuarioId = ref(null);

const updateUsuarioDialog = ref(false);

const props = defineProps({
    refresh: {
        type: Number,
        required: true
    }
});
watch(() => props.refresh, () => {
    loadUsers();
});
function editusuario(usuario) {
    selectedUsuarioId.value = usuario.id;
    updateUsuarioDialog.value = true;
}

function handleUsuarioUpdated() {
    loadUsers();
}
function confirmDeleteProduct(usuario) {
    product.value = usuario;
    deleteProductDialog.value = true;
}
const pagination = ref({
    currentPage: 1,
    perPage: 15,
    total: 0
});

const filters = ref({
    status: null,
    online: null
});
function handleUserDeleted() {
    loadUsers();
}
const loadUsers = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/usuarios', {
            params: {
                page: pagination.value.currentPage,
                per_page: pagination.value.perPage,
                search: globalFilterValue.value,
                status: filters.value.status,
                online: filters.value.online
            }
        });

        users.value = response.data.data;
        pagination.value.currentPage = response.data.meta.current_page;
        pagination.value.total = response.data.meta.total;
    } catch (error) {
        console.error('Error cargando usuarios:', error);
    } finally {
        loading.value = false;
    }
};

const onPage = (event) => {
    pagination.value.currentPage = event.page + 1;
    pagination.value.perPage = event.rows;
    loadUsers();
};

const getSeverity = (value) => {
    if (value === true || value === '1') return 'success';
    if (value === false || value === '0') return 'danger';
    return null;
};

const onGlobalSearch = debounce(() => {
    pagination.value.currentPage = 1;
    loadUsers();
}, 500);

onMounted(() => {
    loadUsers();
});
</script>

<template>
    <DataTable ref="dt" v-model:selection="selectedUsers" :value="users" dataKey="id" :paginator="true"
        :rows="pagination.perPage" :totalRecords="pagination.total" :loading="loading" :lazy="true" @page="onPage"
        :rowsPerPageOptions="[15, 20, 25]"
        paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
        currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} usuarios">

        <template #header>
            <div class="flex flex-wrap gap-2 items-center justify-between">
                <h4 class="m-0">Usuarios</h4>
                <div class="flex flex-wrap gap-2">
                    <IconField>
                        <InputIcon>
                            <i class="pi pi-search" />
                        </InputIcon>
                        <InputText v-model="globalFilterValue" @input="onGlobalSearch" placeholder="Buscar..." />
                    </IconField>
                    <Button icon="pi pi-refresh" outlined rounded aria-label="Refresh" @click="loadUsers" />
                </div>
            </div>
        </template>

        <Column selectionMode="multiple" style="width: 1rem" :exportable="false"></Column>
        <Column field="role" header="Rol" sortable style="min-width: 4rem"></Column>
        <Column field="username" header="Usuario" sortable style="min-width: 12rem"></Column>
        <Column field="dni" header="DNI" sortable style="min-width: 4rem"></Column>
        <Column field="name1" header="Nombres y Apellidos" sortable style="min-width: 32rem"></Column>
        <Column field="email" header="Email" sortable style="min-width: 25rem"></Column>
        <Column field="creacion" header="Creación" sortable style="min-width: 13rem"></Column>
        <Column field="online" header="Online" sortable style="min-width: 9rem">
            <template #body="{ data }">
                <Tag :value="data.online ? 'En línea' : 'Sin conexión'" :severity="getSeverity(data.online)" />
            </template>
        </Column>
        <Column field="status" header="Estado" sortable style="min-width: 4rem">
            <template #body="{ data }">
                <Tag :value="data.status ? 'Activo' : 'Inactivo'" :severity="getSeverity(data.status)" />
            </template>
        </Column>
        <Column :exportable="false" style="min-width: 8rem">
            <template #body="slotProps">
                <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editusuario(slotProps.data)"/>
                <Button icon="pi pi-trash" outlined rounded severity="danger" @click="confirmDeleteProduct(slotProps.data)" />
            </template>
        </Column>
    </DataTable>
    <DeleteUsuario
        v-model:visible="deleteProductDialog"
        :product="product"
        @deleted="handleUserDeleted"
    />
    <updateUsuario
        v-model:visible="updateUsuarioDialog"
        :UsuarioId="selectedUsuarioId"
        @updated="handleUsuarioUpdated"
    />
</template>
