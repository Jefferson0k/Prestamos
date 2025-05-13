<template>
    <Toolbar class="mb-6">
        <template #start>
            <Button label="New" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
            <Button label="Delete" icon="pi pi-trash" severity="secondary" @click="confirmDeleteSelected"
                :disabled="!selectedClientes || !selectedClientes.length" />
        </template>

        <template #end>
            <Button label="Export" icon="pi pi-upload" severity="secondary" @click="exportCSV($event)" />
        </template>
    </Toolbar>
    <Dialog v-model:visible="clienteDialog" :style="{ width: '450px' }" header="Registro de clientes" :modal="true">
        <div class="flex flex-col gap-6">
            <div>
                <label for="dni" class="block font-bold mb-3">DNI <span class="text-red-500">*</span></label>
                <InputText id="dni" v-model.trim="cliente.dni" required="true" autofocus
                    :invalid="(submitted && !cliente.dni) || serverErrors.dni" maxlength="8" fluid
                    @keydown="consultarClientePorDNI" />
                <small v-if="submitted && !cliente.dni" class="text-red-500">El DNI es obligatorio.</small>
                <small v-else-if="submitted && cliente.dni && cliente.dni.length !== 8" class="text-red-500">El DNI debe
                    tener 8 dígitos.</small>
                <small v-else-if="serverErrors.dni" class="text-red-500">{{ serverErrors.dni[0] }}</small>
            </div>

            <div>
                <label for="nombre" class="block font-bold mb-3">Nombres <span class="text-red-500">*</span></label>
                <InputText id="nombre" v-model.trim="cliente.nombre" required="true"
                    :invalid="submitted && !cliente.nombre" maxlength="100" fluid disabled />
                <small v-if="submitted && !cliente.nombre" class="text-red-500">El nombre es obligatorio.</small>
                <small v-else-if="submitted && cliente.nombre && cliente.nombre.length < 2" class="text-red-500">El
                    nombre
                    debe tener al menos 2 caracteres.</small>
                <small v-else-if="serverErrors.nombre" class="text-red-500">{{ serverErrors.nombre[0] }}</small>
            </div>
            <div>
                <label for="apellidos" class="block font-bold mb-3">Apellidos <span
                        class="text-red-500">*</span></label>
                <InputText id="apellidos" v-model.trim="cliente.apellidos" required="true"
                    :invalid="submitted && !cliente.apellidos" maxlength="100" fluid disabled />
                <small v-if="submitted && !cliente.apellidos" class="text-red-500">Los apellidos son
                    obligatorios.</small>
                <small v-else-if="submitted && cliente.apellidos && cliente.apellidos.length < 2"
                    class="text-red-500">Los
                    apellidos deben tener al menos 2 caracteres.</small>
                <small v-else-if="serverErrors.apellidos" class="text-red-500">{{ serverErrors.apellidos[0] }}</small>
            </div>
            <div>
                <label for="direccion" class="block font-bold mb-3">Dirección <span
                        class="text-red-500">*</span></label>
                <Textarea id="direccion" v-model="cliente.direccion" required="true" rows="3" cols="20"
                    :invalid="submitted && !cliente.direccion" maxlength="255" fluid disabled />
                <small v-if="submitted && !cliente.direccion" class="text-red-500">La dirección es obligatoria.</small>
                <small v-else-if="serverErrors.direccion" class="text-red-500">{{ serverErrors.direccion[0] }}</small>
            </div>
            <div>
                <label for="telefono" class="block font-bold mb-3">Teléfono <span class="text-red-500">*</span></label>
                <InputText id="telefono" v-model.trim="cliente.telefono" required="true"
                    :invalid="submitted && !cliente.telefono" maxlength="15" fluid />
                <small v-if="submitted && !cliente.telefono" class="text-red-500">El teléfono es obligatorio.</small>
                <small v-else-if="submitted && cliente.telefono && !/^\d+$/.test(cliente.telefono)"
                    class="text-red-500">El
                    teléfono debe contener solo números.</small>
                <small v-else-if="submitted && cliente.telefono && cliente.telefono.length < 9" class="text-red-500">El
                    teléfono debe tener al menos 9 dígitos.</small>
                <small v-else-if="serverErrors.telefono" class="text-red-500">{{ serverErrors.telefono[0] }}</small>
            </div>
            <div>
                <label for="correo" class="block font-bold mb-3">Email <span class="text-red-500">*</span></label>
                <InputText id="correo" v-model.trim="cliente.correo" required="true"
                    :invalid="submitted && !cliente.correo" maxlength="150" fluid />
                <small v-if="submitted && !cliente.correo" class="text-red-500">El correo electrónico es
                    obligatorio.</small>
                <small v-else-if="submitted && cliente.correo && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(cliente.correo)"
                    class="text-red-500">El correo electrónico debe ser una dirección válida.</small>
                <small v-else-if="serverErrors.correo" class="text-red-500">{{ serverErrors.correo[0] }}</small>
            </div>
            <div>
                <label for="centro_trabajo" class="block font-bold mb-3">Centro de trabajo <span
                        class="text-red-500">*</span></label>
                <Textarea id="centro_trabajo" v-model="cliente.centro_trabajo" required="true" rows="3" cols="20"
                    :invalid="submitted && !cliente.centro_trabajo" maxlength="150" fluid />
                <small v-if="submitted && !cliente.centro_trabajo" class="text-red-500">El centro de trabajo es
                    obligatorio.</small>
                <small v-else-if="serverErrors.centro_trabajo" class="text-red-500">{{ serverErrors.centro_trabajo[0]
                    }}</small>
            </div>
            <div>
                <label for="foto" class="block font-bold mb-3">Foto <span class="text-red-500">*</span></label>
                <FileUpload name="foto" url="/api/upload" @upload="onTemplatedUpload($event)" :multiple="false"
                    accept="image/jpeg,image/jpg,image/png" :maxFileSize="2097152" @select="onSelectedFiles">
                    <template #header="{ chooseCallback, uploadCallback, clearCallback, files }">
                        <div class="flex flex-wrap justify-between items-center flex-1 gap-4">
                            <div class="flex gap-2">
                                <Button @click="chooseCallback()" icon="pi pi-images" rounded outlined
                                    severity="secondary"></Button>
                                <Button @click="uploadEvent(uploadCallback)" icon="pi pi-cloud-upload" rounded outlined
                                    severity="success" :disabled="!files || files.length === 0"></Button>
                                <Button @click="clearCallback()" icon="pi pi-times" rounded outlined severity="danger"
                                    :disabled="!files || files.length === 0"></Button>
                            </div>
                            <ProgressBar :value="totalSizePercent" :showValue="false"
                                class="md:w-20rem h-1 w-full md:ml-auto">
                                <span class="whitespace-nowrap">{{ totalSize }}B / 2MB</span>
                            </ProgressBar>
                        </div>
                    </template>
                    <template #content="{ files, uploadedFiles, removeUploadedFileCallback, removeFileCallback }">
                        <div class="flex flex-col gap-8 pt-4">
                            <div v-if="files.length > 0">
                                <h5>Pendiente</h5>
                                <div class="flex flex-wrap gap-4">
                                    <div v-for="(file, index) of files" :key="file.name + file.type + file.size"
                                        class="p-8 rounded-border flex flex-col border border-surface items-center gap-4">
                                        <div>
                                            <img role="presentation" :alt="file.name" :src="file.objectURL" width="100"
                                                height="50" />
                                        </div>
                                        <span
                                            class="font-semibold text-ellipsis max-w-60 whitespace-nowrap overflow-hidden">{{
                                            file.name }}</span>
                                        <div>{{ formatSize(file.size) }}</div>
                                        <Badge value="Pendiente" severity="warn" />
                                        <Button icon="pi pi-times"
                                            @click="onRemoveTemplatingFile(file, removeFileCallback, index)" outlined
                                            rounded severity="danger" />
                                    </div>
                                </div>
                            </div>

                            <div v-if="uploadedFiles.length > 0">
                                <h5>Completado</h5>
                                <div class="flex flex-wrap gap-4">
                                    <div v-for="(file, index) of uploadedFiles" :key="file.name + file.type + file.size"
                                        class="p-8 rounded-border flex flex-col border border-surface items-center gap-4">
                                        <div>
                                            <img role="presentation" :alt="file.name" :src="file.objectURL" width="100"
                                                height="50" />
                                        </div>
                                        <span
                                            class="font-semibold text-ellipsis max-w-60 whitespace-nowrap overflow-hidden">{{
                                            file.name }}</span>
                                        <div>{{ formatSize(file.size) }}</div>
                                        <Badge value="Completado" class="mt-4" severity="success" />
                                        <Button icon="pi pi-times" @click="removeUploadedFileCallback(index)" outlined
                                            rounded severity="danger" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                    <template #empty>
                        <div class="flex items-center justify-center flex-col">
                            <i class="pi pi-cloud-upload !border-2 !rounded-full !p-8 !text-4xl !text-muted-color" />
                            <p class="mt-6 mb-0">Arrastre y suelte archivos aquí para cargar.</p>
                            <small v-if="submitted && !cliente.foto" class="text-red-500">La foto es
                                obligatoria.</small>
                            <small v-else-if="serverErrors.foto" class="text-red-500">{{ serverErrors.foto[0] }}</small>
                        </div>
                    </template>
                </FileUpload>
            </div>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
            <Button label="Guardar" icon="pi pi-check" @click="saveCliente" />
        </template>
    </Dialog>
    <Dialog v-model:visible="deleteClientesDialog" :style="{ width: '450px' }" header="Confirmar" :modal="true">
        <div class="flex items-center gap-4">
            <i class="pi pi-exclamation-triangle !text-3xl" />
            <span v-if="cliente">¿Está seguro de que desea eliminar los clientes seleccionados?</span>
        </div>
        <template #footer>
            <Button label="No" icon="pi pi-times" text @click="deleteClientesDialog = false" />
            <Button label="Sí" icon="pi pi-check" text @click="deleteSelectedClientes" />
        </template>
    </Dialog>
</template>
<script setup>
import { ref } from 'vue';
import axios from 'axios';
import Dialog from 'primevue/dialog';
import Textarea from 'primevue/textarea';
import Toolbar from 'primevue/toolbar';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import FileUpload from 'primevue/fileupload';
import { useToast } from "primevue/usetoast";
import { usePrimeVue } from 'primevue/config';
import ProgressBar from 'primevue/progressbar';
import Badge from 'primevue/badge';
import { defineEmits } from 'vue';

const clientes = ref([]);
const cliente = ref({});
const submitted = ref(false);
const selectedClientes = ref();
const serverErrors = ref({});
const emit = defineEmits(['cliente-agregado']);
function openNew() {
    cliente.value = {};
    submitted.value = false;
    clienteDialog.value = true;
}

function hideDialog() {
    clienteDialog.value = false;
    submitted.value = false;
}
const clienteDialog = ref(false);
const deleteClientesDialog = ref(false);

function deleteSelectedClientes() {
    clientes.value = clientes.value.filter((val) => !selectedClientes.value.includes(val));
    deleteClientesDialog.value = false;
    selectedClientes.value = null;
    toast.add({ severity: 'success', summary: 'Exitoso', detail: 'Clientes eliminados', life: 3000 });
}
function confirmDeleteSelected() {
    deleteClientesDialog.value = true;
}

function saveCliente() {
    submitted.value = true;
    serverErrors.value = {};

    if (!cliente.value.dni || cliente.value.dni.length !== 8 || !/^\d+$/.test(cliente.value.dni)) return;
    if (!cliente.value.nombre || cliente.value.nombre.length < 2) return;
    if (!cliente.value.apellidos || cliente.value.apellidos.length < 2) return;
    if (!cliente.value.telefono || !/^\d+$/.test(cliente.value.telefono) || cliente.value.telefono.length < 9) return;
    if (!cliente.value.direccion) return;
    if (!cliente.value.correo || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(cliente.value.correo)) return;
    if (!cliente.value.centro_trabajo) return;

    const formData = new FormData();
    formData.append('dni', cliente.value.dni);
    formData.append('nombre', cliente.value.nombre);
    formData.append('apellidos', cliente.value.apellidos);
    formData.append('telefono', cliente.value.telefono);
    formData.append('direccion', cliente.value.direccion);
    formData.append('correo', cliente.value.correo);
    formData.append('centro_trabajo', cliente.value.centro_trabajo);

    if (cliente.value.foto) {
        formData.append('foto', cliente.value.foto);
    }
    axios.post('/cliente', formData, {
        headers: {
            'Content-Type': 'multipart/form-data',
        }
    })
        .then(response => {
            const data = response.data;
            if (data.cliente) {
                clientes.value.push(data.cliente);
                toast.add({ severity: 'success', summary: 'Exitoso', detail: data.message, life: 3000 });
                clienteDialog.value = false;
                cliente.value = {
                    dni: '',
                    nombre: '',
                    apellidos: '',
                    telefono: '',
                    direccion: '',
                    correo: '',
                    centro_trabajo: '',
                    foto: '',
                };
                emit('cliente-agregado');
            }
        })
        .catch(error => {
            if (error.response && error.response.status === 422) {
                serverErrors.value = error.response.data.errors;
            } else {
                console.error(error);
                toast.add({ severity: 'error', summary: 'Error', detail: 'Ocurrió un error al guardar el cliente', life: 3000 });
            }
        });
}

const $primevue = usePrimeVue();
const toast = useToast();

const totalSize = ref(0);
const totalSizePercent = ref(0);
const files = ref([]);

const onRemoveTemplatingFile = (file, removeFileCallback, index) => {
    removeFileCallback(index);
    totalSize.value -= parseInt(formatSize(file.size));
    totalSizePercent.value = totalSize.value / 20.97;
    cliente.value.foto = null;
};

const onSelectedFiles = (event) => {
    files.value = event.files;
    if (files.value.length > 0) {
        cliente.value.foto = files.value[0];
        totalSize.value = parseInt(formatSize(files.value[0].size));
        totalSizePercent.value = totalSize.value / 20.97;
    }
};

const uploadEvent = (callback) => {
    totalSizePercent.value = totalSize.value / 20.97;
    toast.add({ severity: "info", summary: "Éxito", detail: "Archivo listo para enviar", life: 3000 });
};

const onTemplatedUpload = (event) => {
    toast.add({ severity: "info", summary: "Éxito", detail: "Archivo cargado", life: 3000 });
};

const formatSize = (bytes) => {
    const k = 1024;
    const dm = 3;
    const sizes = $primevue.config.locale.fileSizeTypes || ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

    if (bytes === 0) {
        return `0 ${sizes[0]}`;
    }

    const i = Math.floor(Math.log(bytes) / Math.log(k));
    const formattedSize = parseFloat((bytes / Math.pow(k, i)).toFixed(dm));

    return `${formattedSize} ${sizes[i]}`;
};
function consultarClientePorDNI(event) {
    if (event.key === 'Enter' && cliente.value.dni.length === 8) {
        axios.get(`/consulta/${cliente.value.dni}`)
            .then(response => {
                const data = response.data;
                if (data.success && data.data) {
                    cliente.value.nombre = data.data.nombres || '';
                    cliente.value.apellidos = `${data.data.apellido_paterno || ''} ${data.data.apellido_materno || ''}`.trim();
                    cliente.value.direccion = data.data.direccion_completa || '';
                } else {
                    toast.add({ severity: 'warn', summary: 'Advertencia', detail: 'No se encontraron datos para este DNI.', life: 3000 });
                }
            })
            .catch(error => {
                console.error(error);
                toast.add({ severity: 'error', summary: 'Error', detail: 'Error al consultar el DNI.', life: 3000 });
            });
    }
}
</script>