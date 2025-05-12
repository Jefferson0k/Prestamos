<template>
    <div>
        <DataTable ref="dt" v-model:selection="selectedRol" :value="rol" dataKey="id" :paginator="true" :rows="10"
            :filters="filters"
            paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
            :rowsPerPageOptions="[5, 10, 25]"
            currentPageReportTemplate="Mostrando {first} a {last} de {totalRecords} roles">
            <template #header>
                <div class="flex flex-wrap gap-2 items-center justify-between">
                    <h4 class="m-0">Roles y Permisos</h4>
                    <IconField>
                        <InputIcon>
                            <i class="pi pi-search" />
                        </InputIcon>
                        <InputText v-model="filters['global'].value" placeholder="Buscar..." />
                    </IconField>
                </div>
            </template>

            <Column selectionMode="multiple" style="width: 3rem" :exportable="false"></Column>
            <Column field="name" header="Nombre" sortable style="min-width: 12rem"></Column>
            <Column field="created_at" header="Creación" sortable style="min-width: 16rem"></Column>
            <Column field="updated_at" header="Modificado" sortable style="min-width: 16rem"></Column>
            <Column :exportable="false" style="min-width: 8rem">
                <template #body="slotProps">
                    <template v-if="slotProps.data.name !== 'administrador'">
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editrol(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger"
                            @click="confirmDeleteRol(slotProps.data)" />
                    </template>
                    <template v-else>
                        <span>No editable</span>
                    </template>
                </template>
            </Column>

        </DataTable>

        <!-- Diálogos -->
        <DeleteRoles
            v-model:visible="deleteRolDialog"
            :rol="rolToDelete"
            @deleted="handleRolDeleted"
        />

        <UpdateRoles
            v-model:visible="updaterolDialog"
            :RolId="selectedrolId"
            @updated="handleRolUpdated"
        />
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import { FilterMatchMode } from '@primevue/core/api';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import IconField from 'primevue/iconfield';
import InputIcon from 'primevue/inputicon';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import DeleteRoles from './DeleteRoles.vue';
import UpdateRoles from './UpdateRoles.vue';

const dt = ref();
const rol = ref([]);
const selectedRol = ref();
const rolToDelete = ref(null);
const selectedrolId = ref(null);
const updaterolDialog = ref(false);
const deleteRolDialog = ref(false);

const filters = ref({
    'global': { value: null, matchMode: FilterMatchMode.CONTAINS },
});

const props = defineProps({
    refresh: {
        type: Number,
        required: true
    }
});

watch(() => props.refresh, () => {
    fetchRoles();
});

const fetchRoles = async () => {
    try {
        const response = await axios.get('/rol');
        rol.value = response.data.data;
    } catch (error) {
        console.error('Error al cargar los roles:', error);
    }
};

function editrol(rol) {
    selectedrolId.value = rol.id;
    updaterolDialog.value = true;
}

function confirmDeleteRol(selected) {
    rolToDelete.value = selected;
    deleteRolDialog.value = true;
}

function handleRolDeleted() {
    fetchRoles();
}

function handleRolUpdated() {
    fetchRoles();
}

onMounted(() => {
    fetchRoles();
});
</script>
