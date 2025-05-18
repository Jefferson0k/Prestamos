<template>
  <Toolbar class="mb-6">
    <template #start>
      <Button label="Nuevo Tipo Cliente" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
    </template>
  </Toolbar>

  <Dialog v-model:visible="tipoClienteDialog" :style="{ width: '600px' }" header="Registro de Tipo Cliente"
    :modal="true" :closable="true" :closeOnEscape="true">
    <div class="flex flex-col gap-6">
      <div class="grid grid-cols-12 gap-4">
        <div class="col-span-9">
          <label for="nombre" class="block font-bold mb-3">Nombre <span class="text-red-500">*</span></label>
          <InputText id="nombre" v-model.trim="tipoCliente.nombre" required maxlength="100" fluid
            :class="{ 'p-invalid': serverErrors.nombre }" />
          <small v-if="submitted && !tipoCliente.nombre" class="text-red-500">El nombre es obligatorio.</small>
          <small v-else-if="submitted && tipoCliente.nombre && tipoCliente.nombre.length < 2" class="text-red-500">
            El nombre debe tener al menos 2 caracteres.
          </small>
          <small v-else-if="serverErrors.nombre" class="text-red-500">{{ serverErrors.nombre[0] }}</small>
        </div>

        <div class="col-span-3">
          <label for="estado" class="block font-bold mb-2">Estado <span class="text-red-500">*</span></label>
          <Select v-model="tipoCliente.estado" :options="estadoOptions" optionLabel="label" optionValue="value"
            placeholder="Selecciona estado" :class="{ 'p-invalid': serverErrors.estado }" />
          <small v-if="submitted && !tipoCliente.estado" class="text-red-500">El estado es obligatorio.</small>
          <small v-else-if="serverErrors.estado" class="text-red-500">{{ serverErrors.estado[0] }}</small>
        </div>
      </div>
    </div>

    <template #footer>
      <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
      <Button label="Guardar" icon="pi pi-check" @click="guardarTipoCliente" :loading="loading" />
    </template>
  </Dialog>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import Toolbar from 'primevue/toolbar';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import InputText from 'primevue/inputtext';
import { useToast } from 'primevue/usetoast';
import { defineEmits } from 'vue';
import Select from 'primevue/select';

const toast = useToast();
const submitted = ref(false);
const loading = ref(false);
const tipoClienteDialog = ref(false);
const serverErrors = ref({});
const emit = defineEmits(['tipo-cliente-agregado']);

const estadoOptions = [
  { label: 'Activo', value: 'activo' },
  { label: 'Inactivo', value: 'inactivo' },
];

const tipoCliente = ref({
  nombre: '',
  estado: 'activo',  // valor por defecto acorde al enum
});

function resetTipoCliente() {
  tipoCliente.value = {
    nombre: '',
    estado: 'activo',
  };
  serverErrors.value = {};
  submitted.value = false;
  loading.value = false;
}

function openNew() {
  resetTipoCliente();
  tipoClienteDialog.value = true;
}

function hideDialog() {
  tipoClienteDialog.value = false;
  resetTipoCliente();
}

async function guardarTipoCliente() {
  submitted.value = true;
  serverErrors.value = {};
  loading.value = true;

  try {
    await axios.post('/tipo-cliente', tipoCliente.value);
    toast.add({ severity: 'success', summary: 'Éxito', detail: 'Tipo cliente registrado', life: 3000 });
    hideDialog();
    emit('tipo-cliente-agregado');
  } catch (error) {
    if (error.response && error.response.status === 422) {
      serverErrors.value = error.response.data.errors || {};
    } else {
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: 'No se pudo registrar el tipo cliente',
        life: 3000,
      });
    }
  } finally {
    loading.value = false;
  }
}
</script>
