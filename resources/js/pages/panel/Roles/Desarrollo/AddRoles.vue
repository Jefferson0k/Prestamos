<template>
    <Toolbar class="mb-6">
        <template #start>
            <Button label="New" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
        </template>
        <template #end>

        </template>
    </Toolbar>

    <Dialog v-model:visible="rolDialog" :style="{ width: '1000px' }" header="Roles y Permisos" :modal="true">
        <div class="flex flex-col gap-6">
            <div>
                <label for="name" class="block font-bold mb-3">Nombre <span class="text-red-500">*</span></label>
                <InputText id="name" v-model.trim="rol.name" required maxlength="100" fluid />
                <small v-if="submitted && !rol.name" class="text-red-500">El nombre es obligatorio.</small>
                <small v-else-if="submitted && rol.name && rol.name.length < 2" class="text-red-500">
                    El nombre debe tener al menos 2 caracteres.
                </small>
                <small v-else-if="serverErrors.name" class="text-red-500">{{ serverErrors.name[0] }}</small>
            </div>
            <div>
                <label class="block font-bold mb-3">Permisos <span class="text-red-500">*</span></label>

                <div v-if="loadingPermissions" class="text-center p-4">
                    <i class="pi pi-spin pi-spinner text-2xl"></i>
                    <p>Cargando permisos...</p>
                </div>

                <div v-else class="permisos-container">
                    <div v-for="(permisos, categoria) in permisosAgrupados" :key="categoria" class="mb-4">
                        <Fieldset :toggleable="true" class="shadow-sm">
                            <template #legend>
                                <div class="flex justify-between items-center w-full">
                                    <span class="font-bold">{{ categoria }}</span>
                                    <div class="fieldset-actions flex gap-2">
                                        <Button icon="pi pi-check-square" size="small" text
                                            @click.stop="seleccionarTodos(categoria)"
                                            v-tooltip="{ value: 'Seleccionar todos', showDelay: 1000, hideDelay: 300 }" />
                                        <Button icon="pi pi-times" severity="danger" size="small" text
                                            @click.stop="deseleccionarTodos(categoria)"
                                            v-tooltip="{ value: 'Deseleccionar todos', showDelay: 1000, hideDelay: 300 }"
                                            tooltipOptions="{ position: 'top' }" />
                                    </div>
                                </div>
                            </template>

                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                <div v-for="permiso of permisos" :key="permiso.id"
                                    class="flex items-center gap-2 p-2  rounded-md">
                                    <Checkbox v-model="permisosSeleccionados" :inputId="'permiso_' + permiso.id"
                                        :value="permiso.id" />
                                    <label :for="'permiso_' + permiso.id" class="cursor-pointer">
                                        {{ permiso.name }}
                                    </label>
                                </div>
                            </div>
                        </Fieldset>
                    </div>

                    <small v-if="submitted && permisosSeleccionados.length === 0" class="text-red-500 block mt-2">
                        Debe seleccionar al menos un permiso
                    </small>
                </div>
            </div>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
            <Button label="Guardar" icon="pi pi-check" @click="guardarRol" />
        </template>
    </Dialog>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import axios from 'axios';
import Dialog from 'primevue/dialog';
import Toolbar from 'primevue/toolbar';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Checkbox from 'primevue/checkbox';
import { useToast } from 'primevue/usetoast';
import { defineEmits } from 'vue';
import Fieldset from 'primevue/fieldset';

const toast = useToast();
const submitted = ref(false);
const rolDialog = ref(false);
const selectedrols = ref();
const serverErrors = ref({});
const emit = defineEmits(['rol-agregado']);
const rol = ref({
    name: '',
});

const permisos = ref([]);
const permisosSeleccionados = ref([]);
const loadingPermissions = ref(false);

async function obtenerPermisos() {
    loadingPermissions.value = true;
    try {
        const response = await axios.get('/rol/Permisos');
        permisos.value = response.data.permissions;
    } catch (error) {
        console.error('Error al obtener permisos:', error);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudieron cargar los permisos',
            life: 3000
        });
    } finally {
        loadingPermissions.value = false;
    }
}

const permisosAgrupados = computed(() => {
    const grupos = {};

    permisos.value.forEach(permiso => {
        const nombrePartes = permiso.name.split(' ');
        const categoria = nombrePartes.length > 1 ? nombrePartes[1] : "General";

        if (!grupos[categoria]) {
            grupos[categoria] = [];
        }

        grupos[categoria].push(permiso);
    });

    return grupos;
});

function seleccionarTodos(categoria) {
    const nuevosSeleccionados = [...permisosSeleccionados.value];
    const permisosCategoria = permisosAgrupados.value[categoria].map(p => p.id);
    permisosCategoria.forEach(id => {
        if (!nuevosSeleccionados.includes(id)) {
            nuevosSeleccionados.push(id);
        }
    });
    permisosSeleccionados.value = nuevosSeleccionados;
}

function deseleccionarTodos(categoria) {
    const permisosCategoria = permisosAgrupados.value[categoria].map(p => p.id);
    permisosSeleccionados.value = permisosSeleccionados.value.filter(
        id => !permisosCategoria.includes(id)
    );
}

function openNew() {
    rol.value = {
        name: '',
    };
    permisosSeleccionados.value = [];
    submitted.value = false;
    serverErrors.value = {};
    rolDialog.value = true;
}

function hideDialog() {
    rolDialog.value = false;
    submitted.value = false;
    serverErrors.value = {};
}

function guardarRol() {
    submitted.value = true;

    if (rol.value.name && rol.value.name.length >= 2 && permisosSeleccionados.value.length > 0) {
        axios.post('/rol', {
            name: rol.value.name,
            permissions: permisosSeleccionados.value
        })
            .then(response => {
                toast.add({
                    severity: 'success',
                    summary: 'Éxito',
                    detail: 'Rol guardado correctamente',
                    life: 3000
                });
                hideDialog();
                emit('rol-agregado');
            })
            .catch(error => {
                if (error.response && error.response.data && error.response.data.errors) {
                    serverErrors.value = error.response.data.errors;
                } else {
                    toast.add({
                        severity: 'error',
                        summary: 'Error',
                        detail: 'Error al guardar el rol',
                        life: 3000
                    });
                }
            });
    }
}

onMounted(() => {
    obtenerPermisos();
});
</script>

<style scoped>
/* Puedes añadir estilos adicionales aquí si es necesario */
</style>