<script setup>
import { ref, watch, onMounted } from 'vue';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
import Tag from 'primevue/tag';
import Checkbox from 'primevue/checkbox';
import Password from 'primevue/password';

const props = defineProps({
    visible: Boolean,
    UsuarioId: Number
});
const emit = defineEmits(['update:visible', 'updated']);

const toast = useToast();
const user = ref({});
const password = ref('');
const loading = ref(false);
const originalEmail = ref('');
const originalUsername = ref('');

const dialogVisible = ref(props.visible);
watch(() => props.visible, (val) => dialogVisible.value = val);
watch(dialogVisible, (val) => emit('update:visible', val));

watch(() => props.visible, (newVal) => {
    if (newVal && props.UsuarioId) {
        fetchUser();
    }
});

const fetchUser = async () => {
    loading.value = true;
    try {
        const response = await axios.get(`/usuarios/${props.UsuarioId}`);
        user.value = response.data.user;
        originalEmail.value = response.data.user.email;
        originalUsername.value = response.data.user.username;
        user.value.status = response.data.user.status === true ||
            response.data.user.status === 1 ||
            response.data.user.status === 'activo' ? true : false;
        password.value = '';
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo cargar el usuario', life: 3000 });
        console.error(error);
    } finally {
        loading.value = false;
    }
};

const updateUser = async () => {
    try {
        const statusValue = user.value.status === true;
        const userData = {
            dni: user.value.dni,
            name: user.value.name,
            apellidos: user.value.apellidos,
            nacimiento: user.value.nacimiento,
            email: user.value.email,
            username: user.value.username,
            status: statusValue
        };
        if (password.value && password.value.trim() !== '') {
            userData.password = password.value;
        }
        await axios.put(`/usuarios/${props.UsuarioId}`, userData);
        toast.add({ severity: 'success', summary: 'Actualizado', detail: 'Usuario actualizado correctamente', life: 3000 });
        dialogVisible.value = false;
        emit('updated');
    } catch (error) {
        if (error.response && error.response.data && error.response.data.errors) {
            const errorsData = error.response.data.errors;
            let errorMessage = '';
            for (const field in errorsData) {
                if (errorsData.hasOwnProperty(field)) {
                    errorMessage += `${errorsData[field][0]}\n`;
                }
            }
            toast.add({ severity: 'error', summary: 'Error de validación', detail: errorMessage, life: 5000 });
        } else {
            toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo actualizar el usuario', life: 3000 });
        }
        console.error(error);
    }
};

const buscarPorDni = async () => {
    if (user.value.dni.length !== 8) {
        toast.add({
            severity: 'warn',
            summary: 'Advertencia',
            detail: 'El DNI debe tener 8 dígitos.',
            life: 3000
        })
        return
    }

    try {
        const response = await axios.get(`/consulta/${user.value.dni}`)
        const data = response.data

        if (data.success && data.data && data.data.nombre_completo) {
            const nombres = data.data.nombres ?? ''
            const apePat = data.data.apellido_paterno ?? ''
            const apeMat = data.data.apellido_materno ?? ''
            const nacimiento = data.data.fecha_nacimiento ?? ''

            user.value.name = nombres
            user.value.apellidos = `${apePat} ${apeMat}`
            user.value.nacimiento = nacimiento
            user.value.username = generarUsername(nombres, apePat, apeMat, nacimiento)
        } else {
            toast.add({
                severity: 'warn',
                summary: 'Advertencia',
                detail: 'No se encontraron datos para este DNI.',
                life: 3000
            })
        }
    } catch (error) {
        console.error(error)
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudo consultar el DNI.',
            life: 3000
        })
    }
}

const generarUsername = (nombres, apePat, apeMat, nacimiento) => {
    const normalizar = (texto) => {
        return texto
            ?.replace(/ñ/g, 'n')
            .replace(/Ñ/g, 'n')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase() || '';
    };

    const partesNombre = normalizar(nombres).trim().split(/\s+/);
    const inicialNombre = partesNombre[0]?.charAt(0) || '';

    const apellido = normalizar(apePat).replace(/\s+/g, '');
    const segundoApellido = normalizar(apeMat).replace(/\s+/g, '').slice(0, 2) || '';
    const dia = nacimiento?.split('/')?.[0]?.padStart(2, '0') || '00';

    return `${inicialNombre}${apellido}${segundoApellido}${dia}`.toUpperCase();
};
</script>

<template>
    <Dialog v-model:visible="dialogVisible" header="Editar Usuario" modal :closable="true" :closeOnEscape="true"
        :style="{ width: '600px' }">
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-9">
                    <label for="dni" class="block font-bold mb-3">DNI <span class="text-red-500">*</span></label>
                    <InputText v-model="user.dni" maxlength="8" required @keyup.enter="buscarPorDni" fluid />
                </div>
                <div class="col-span-3">
                    <label for="status" class="block font-bold mb-2">Estado <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-3">
                        <Checkbox v-model="user.status" :binary="true" inputId="status" />
                        <Tag :value="user.status ? 'Con Acceso' : 'Sin Acceso'"
                            :severity="user.status ? 'success' : 'danger'" />
                    </div>
                </div>
            </div>

            <div>
                <label for="name" class="block font-bold mb-3">Nombre completo <span
                        class="text-red-500">*</span></label>
                <InputText v-model="user.name" required disabled maxlength="100" fluid />
            </div>

            <div>
                <label for="apellidos" class="block font-bold mb-3">Apellidos <span
                        class="text-red-500">*</span></label>
                <InputText v-model="user.apellidos" required disabled maxlength="100" fluid />
            </div>

            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-6">
                    <label for="nacimiento" class="block font-bold mb-3">Fecha de nacimiento <span
                            class="text-red-500">*</span></label>
                    <InputText v-model="user.nacimiento" required maxlength="100" fluid disabled />
                </div>
                <div class="col-span-6">
                    <label for="username" class="block font-bold mb-3">Usuario <span
                            class="text-red-500">*</span></label>
                    <InputText v-model="user.username" fluid disabled />
                </div>
            </div>

            <div>
                <label for="email" class="block font-bold mb-3">Email <span class="text-red-500">*</span></label>
                <InputText v-model="user.email" maxlength="150" fluid />
            </div>

            <div>
                <label for="password" class="block font-bold mb-3">Contraseña <small>(Dejar vacío para mantener la
                        actual)</small></label>
                <Password v-model="password" toggleMask placeholder="Nueva contraseña" :feedback="false"
                    inputId="password" fluid />
            </div>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="dialogVisible = false" />
            <Button label="Guardar" icon="pi pi-check" @click="updateUser" :loading="loading" />
        </template>
    </Dialog>
</template>