<template>
    <Toolbar class="mb-6">
        <template #start>
            <Button label="New" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
        </template>
        <template #end>
        </template>
    </Toolbar>

    <Dialog v-model:visible="usuarioDialog" :style="{ width: '600px' }" header="Registro de usuarios" :modal="true">
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-9">
                    <label for="dni" class="block font-bold mb-3">DNI <span class="text-red-500">*</span></label>
                    <InputText id="dni" v-model.trim="usuario.dni" required autofocus fluid
                        :invalid="(submitted && !usuario.dni) || serverErrors.dni" maxlength="8"
                        @keydown.enter="consultarusuarioPorDNI" />
                    <small v-if="submitted && !usuario.dni" class="text-red-500">El DNI es obligatorio.</small>
                    <small v-else-if="submitted && usuario.dni.length !== 8" class="text-red-500">El DNI debe tener 8
                        dígitos.</small>
                    <small v-else-if="serverErrors.dni" class="text-red-500">{{ serverErrors.dni[0] }}</small>
                </div>
                <div class="col-span-3">
                    <label for="status" class="block font-bold mb-2">Estado <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-3">
                        <Checkbox v-model="usuario.status" :binary="true" inputId="status" />
                        <Tag :value="usuario.status ? 'Con Acceso' : 'Sin Acceso'"
                            :severity="usuario.status ? 'success' : 'danger'" />
                        <small v-if="submitted && !usuario.status" class="text-red-500">El estado es
                            obligatorio.</small>
                        <small v-else-if="serverErrors.status" class="text-red-500">{{ serverErrors.status[0] }}</small>
                    </div>
                </div>
            </div>

            <div>
                <label for="name" class="block font-bold mb-3">Nombres <span class="text-red-500">*</span></label>
                <InputText id="name" v-model.trim="usuario.name" required maxlength="100" disabled fluid />
                <small v-if="submitted && !usuario.name" class="text-red-500">El nombre es obligatorio.</small>
                <small v-else-if="submitted && usuario.name && usuario.name.length < 2" class="text-red-500">El
                    nombre
                    debe tener al menos 2 caracteres.</small>
                <small v-else-if="serverErrors.name" class="text-red-500">{{ serverErrors.name[0] }}</small>

            </div>

            <div>
                <label for="apellidos" class="block font-bold mb-3">Apellidos <span
                        class="text-red-500">*</span></label>
                <InputText id="apellidos" v-model.trim="usuario.apellidos" required maxlength="100" disabled fluid />
                <small v-if="submitted && !usuario.apellidos" class="text-red-500">Los apellidos son
                    obligatorios.</small>
                <small v-else-if="submitted && usuario.apellidos && usuario.apellidos.length < 2"
                    class="text-red-500">Los
                    apellidos deben tener al menos 2 caracteres.</small>
                <small v-else-if="serverErrors.apellidos" class="text-red-500">{{ serverErrors.apellidos[0] }}</small>
            </div>

            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-6">
                    <label for="nacimiento" class="block font-bold mb-3">Fecha de nacimiento <span
                            class="text-red-500">*</span></label>
                    <InputText v-model="usuario.nacimiento" required maxlength="100" disabled fluid />
                    <small v-if="submitted && !usuario.nacimiento" class="text-red-500">Los apellidos son
                        obligatorios.</small>
                    <small v-else-if="serverErrors.nacimiento" class="text-red-500">{{ serverErrors.nacimiento[0]
                    }}</small>
                </div>
                <div class="col-span-6">
                    <label for="username" class="block font-bold mb-3">Usuario <span
                            class="text-red-500">*</span></label>
                    <InputText v-model="usuario.username" disabled fluid />
                </div>
            </div>

            <div>
                <label for="email" class="block font-bold mb-3">Email <span class="text-red-500">*</span></label>
                <InputText id="email" v-model.trim="usuario.email" required maxlength="150" fluid />
                <small v-if="submitted && !usuario.email" class="text-red-500">El correo electrónico es
                    obligatorio.</small>
                <small v-else-if="submitted && usuario.email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(usuario.email)"
                    class="text-red-500">El correo electrónico debe ser válido.</small>
                <small v-else-if="serverErrors.email" class="text-red-500">{{ serverErrors.email[0] }}</small>
            </div>

            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-6">
                    <label for="password" class="block font-bold mb-3">Contraseña <small
                            class="text-red-500">*</small></label>
                    <Password v-model="usuario.password" toggleMask placeholder="contraseña" fluid :feedback="false"
                        inputId="password" />
                    <small v-if="submitted && !usuario.password" class="text-red-500">La Contraseña es
                        obligatorio.</small>
                    <small v-else-if="submitted && usuario.password && usuario.password.length < 8"
                        class="text-red-500">La
                        Contraseña debe tener al menos 8 caracteres.</small>
                    <small v-else-if="serverErrors.password" class="text-red-500">{{ serverErrors.password[0] }}</small>
                </div>
                <div class="col-span-6">
                    <label for="role" class="block font-bold mb-3">Rol <span class="text-red-500">*</span></label>
                    <Select v-model="usuario.role_id" :options="roles" optionLabel="name" optionValue="id"
                        placeholder="Seleccione un rol" fluid />
                    <small v-if="submitted && !usuario.role" class="text-red-500">El rol es obligatorio.</small>
                    <small v-else-if="serverErrors.role" class="text-red-500">{{ serverErrors.role[0] }}</small>
                </div>
            </div>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
            <Button label="Guardar" icon="pi pi-check" @click="guardarUsuario" />
        </template>
    </Dialog>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Dialog from 'primevue/dialog';
import Toolbar from 'primevue/toolbar';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import Checkbox from 'primevue/checkbox';
import Tag from 'primevue/tag';
import Password from 'primevue/password';
import { useToast } from 'primevue/usetoast';
import { defineEmits } from 'vue';
import Select from 'primevue/select';

const toast = useToast();
const roles = ref([]);
const submitted = ref(false);
const usuarioDialog = ref(false);
const serverErrors = ref({});
const emit = defineEmits(['usuario-agregado']);

const usuario = ref({
    dni: '',
    name: '',
    apellidos: '',
    nacimiento: '',
    email: '',
    username: '',
    password: '',
    status: true,
    role_id: null,
});

function openNew() {
    submitted.value = false;
    usuarioDialog.value = true;
}

function hideDialog() {
    usuarioDialog.value = false;
    submitted.value = false;
}

function consultarusuarioPorDNI() {
    const dni = usuario.value.dni;
    if (dni && dni.length === 8) {
        axios.get(`/consulta/${dni}`)
            .then(response => {
                const data = response.data;
                if (data.success && data.data) {
                    const name = data.data.nombres || '';
                    const apellido_paterno = data.data.apellido_paterno || '';
                    const apellido_materno = data.data.apellido_materno || '';
                    const nacimiento = data.data.fecha_nacimiento || '';

                    usuario.value.name = name;
                    usuario.value.apellidos = `${apellido_paterno} ${apellido_materno}`.trim();
                    usuario.value.nacimiento = nacimiento;

                    usuario.value.username = generarUsername(name, apellido_paterno, apellido_materno, nacimiento);
                } else {
                    toast.add({ severity: 'warn', summary: 'No encontrado', detail: 'No se encontraron datos para este DNI', life: 3000 });
                }
            })
            .catch(() => {
                toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo consultar el DNI', life: 3000 });
            });
    }
}

function generarUsername(nombre, apellidoPaterno, apellidoMaterno, nacimiento) {
    const normalizar = (texto) => {
        return texto
            ?.replace(/ñ/g, 'n')
            .replace(/Ñ/g, 'n')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase() || '';
    };

    const primeraLetraNombre = normalizar(nombre)?.charAt(0);
    const primerApellido = normalizar(apellidoPaterno)?.split(' ')[0];
    const segundoApellido = normalizar(apellidoMaterno)?.split(' ')[0]?.substring(0, 2);
    const diaNacimiento = nacimiento?.split('/')?.[0]?.padStart(2, '0') || '00';

    return `${primeraLetraNombre}${primerApellido}${segundoApellido}${diaNacimiento}`.toUpperCase();
}

function guardarUsuario() {
    submitted.value = true;
    serverErrors.value = {};

    axios.post('/usuarios', usuario.value)
        .then(response => {
            toast.add({ severity: 'success', summary: 'Éxito', detail: 'Usuario registrado', life: 3000 });
            hideDialog();
            emit('usuario-agregado');
        })
        .catch(error => {
            if (error.response && error.response.status === 422) {
                const errors = error.response.data.errors;
                serverErrors.value = {
                    dni: errors.dni,
                    nombre: errors.name,
                    apellidos: errors.apellidos,
                    nacimiento: errors.nacimiento,
                    correo: errors.email,
                    password: errors.password,
                };
            }
        });
}

onMounted(() => {
    axios.get('/rol')
        .then(response => {
            roles.value = response.data.data;
        })
        .catch(() => {
            toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudieron cargar los roles', life: 3000 });
        });
});
</script>
