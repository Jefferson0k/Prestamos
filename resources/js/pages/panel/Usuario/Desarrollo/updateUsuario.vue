<script setup>
import { ref, watch } from 'vue';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import axios from 'axios';
import { useToast } from 'primevue/usetoast';
import Tag from 'primevue/tag';
import Checkbox from 'primevue/checkbox';

const props = defineProps({
    visible: Boolean,
    UsuarioId: Number
});
const emit = defineEmits(['update:visible', 'updated']);

const toast = useToast();
const user = ref({});
const loading = ref(false);

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
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo cargar el usuario', life: 3000 });
        console.error(error);
    } finally {
        loading.value = false;
    }
};

const updateUser = async () => {
    try {
        await axios.put(`/usuarios/${props.UsuarioId}`, {
            dni: user.value.dni,
            name: user.value.name,
            apellidos: user.value.apellidos,
            nacimiento: user.value.nacimiento,
            email: user.value.email,
            username: user.value.username,
            status: user.value.status
        });

        toast.add({ severity: 'success', summary: 'Actualizado', detail: 'Usuario actualizado correctamente', life: 3000 });
        emit('update:visible', false);
        emit('updated');
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No se pudo actualizar el usuario', life: 3000 });
        console.error(error);
    }
};
</script>

<template>
    <Dialog v-model:visible="props.visible" header="Editar Usuario" modal :closable="true" :closeOnEscape="true"
        :style="{ width: '600px' }">
        <div class="flex flex-col gap-6">
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-9">
                    <label for="dni" class="block font-bold mb-3">DNI <span class="text-red-500">*</span></label>
                    <InputText v-model="user.dni" maxlength="8" required="true" autofocus fluid />
                </div>
                <div class="col-span-3">
                    <label for="status" class="block font-bold mb-2">Estado <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-3">
                        <Checkbox v-model="user.status" binary inputId="status" />
                        <Tag :value="user.status ? 'Con Acceso =)' : 'Sin Acceso =(' " :severity="user.status ? 'success' : 'danger'" />
                    </div>
                </div>
            </div>
            <div>
                <label for="name" class="block font-bold mb-3">Nombre completo <span
                        class="text-red-500">*</span></label>
                <InputText v-model="user.name" required="true" disabled maxlength="100" fluid />
            </div>
            <div>
                <label for="apellidos" class="block font-bold mb-3">Apellidos <span
                        class="text-red-500">*</span></label>
                <InputText v-model="user.apellidos" required="true" disabled maxlength="100" fluid />
            </div>
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-6">
                    <label for="nacimiento" class="block font-bold mb-3">Fecha de nacimiento <span
                            class="text-red-500">*</span></label>
                    <InputText v-model="user.nacimiento" required="true" maxlength="100" fluid disabled />
                </div>
                <div class="col-span-6">
                    <label for="username" class="block font-bold mb-3">Usuario <span
                            class="text-red-500">*</span></label>
                    <InputText v-model="user.username" fluid disabled/>
                </div>
            </div>
            <div>
                <label for="email" class="block font-bold mb-3">Email <span class="text-red-500">*</span></label>
                <InputText v-model="user.email" maxlength="150" fluid />
            </div>
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="emit('update:visible', false)" />
            <Button label="Guardar" icon="pi pi-check" @click="updateUser" :loading="loading" />
        </template>
    </Dialog>
</template>
