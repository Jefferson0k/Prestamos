<template>
  <div>
    <Dialog v-model:visible="dialogVisible" modal header="Registrar Pago" :style="{ width: '40vw' }"
      :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
      <div class="flex flex-column gap-3">
        <InputNumber v-model="montoCapitalPagar" inputId="monto" :minFractionDigits="2" mode="decimal"
          placeholder="0.00" class="w-full" />
      </div>
      <template #footer>
        <Button label="Cancelar" icon="pi pi-times" text @click="cerrarDialogo" />
        <Button label="Guardar" icon="pi pi-check" @click="guardarPago" :loading="guardando" :disabled="montoCapitalPagar === null" />
      </template>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import InputNumber from 'primevue/inputnumber'
import axios from 'axios'
import { defineProps, defineEmits } from 'vue';
import { useToast } from 'primevue/usetoast';

const toast = useToast();

const props = defineProps({
  cuotaId: {
    type: [Number, null],
    default: 0,
  },
  visible: {
    type: Boolean,
    default: false,
  }
});

const emit = defineEmits(['update:visible', 'pago-guardado'])

const montoCapitalPagar = ref(null);
const guardando = ref(false);

const dialogVisible = ref(false);

watch(() => props.visible, (newValue) => {
  dialogVisible.value = newValue;
  if (newValue) {
    montoCapitalPagar.value = null;
  }
});

watch(() => dialogVisible.value, (newValue) => {
  emit('update:visible', newValue);
});

const cerrarDialogo = () => {
  emit('update:visible', false);
}

const guardarPago = async () => {
  if (montoCapitalPagar.value === null) {
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'Debe ingresar un monto',
      life: 3000
    });
    return;
  }
  
  try {
    guardando.value = true;
    const response = await axios.post('/cuota', {
      cuota_id: props.cuotaId,
      monto_capital_pagar: montoCapitalPagar.value
    });
    
    toast.add({
      severity: 'success',
      summary: 'Éxito',
      detail: 'Pago registrado correctamente',
      life: 3000
    });
    
    emit('update:visible', false);
    
    setTimeout(() => {
      emit('pago-guardado', { success: true, data: response.data });
    }, 100);
    
  } catch (error) {
    console.error('Error al registrar pago:', error.response?.data || error);
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: error.response?.data?.message || 'Error al registrar el pago',
      life: 3000
    });
  } finally {
    guardando.value = false;
  }
}
</script>