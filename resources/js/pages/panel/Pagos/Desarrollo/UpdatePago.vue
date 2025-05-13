<template>
  <div>
    <Dialog v-model:visible="dialogVisible" modal header="Actualizar Pago" :style="{ width: '40vw' }"
      :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
      <div class="flex flex-column gap-3">
        <InputNumber v-model="montoCapitalPagarActualizar" inputId="monto" :minFractionDigits="2" mode="decimal"
          placeholder="0.00" class="w-full" :max="capitalMaximo" />
        <small v-if="montoCapitalPagarActualizar > capitalMaximo" class="text-red-500">
          El monto no puede ser mayor al capital original ({{ capitalMaximo }}).
        </small>
      </div>
      <template #footer>
        <Button label="Cancelar" icon="pi pi-times" text @click="cerrarDialogo" />
        <Button label="Guardar" icon="pi pi-check"
          @click="guardarPago"
          :loading="cargando"
          :disabled="montoCapitalPagarActualizar === null || montoCapitalPagarActualizar < 0 || montoCapitalPagarActualizar > capitalMaximo"
        />
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
import { useToast } from 'primevue/usetoast'

const toast = useToast()

const props = defineProps({
  cuotaId: {
    type: Number,
    default: 0,
  },
  visible: {
    type: Boolean,
    default: false,
  }
})

const emit = defineEmits(['update:visible', 'pago-actualizado'])

const montoCapitalPagarActualizar = ref(null)
const capitalMaximo = ref(0)
const cargando = ref(false)
const dialogVisible = ref(false)

watch(() => props.visible, async (newValue) => {
  dialogVisible.value = newValue
  if (newValue && props.cuotaId) {
    try {
      const response = await axios.get(`/cuota/${props.cuotaId}/show`)
      const cuota = response.data.data[0]
      montoCapitalPagarActualizar.value = parseFloat(cuota.monto_capital_pagar)
      capitalMaximo.value = parseFloat(cuota.capital)
    } catch (error) {
      toast.add({
        severity: 'error',
        summary: 'Error',
        detail: 'No se pudo cargar la información de la cuota',
        life: 3000
      })
    }
  }
})

watch(() => dialogVisible.value, (newValue) => {
  emit('update:visible', newValue)
})

const cerrarDialogo = () => {
  emit('update:visible', false)
}

const guardarPago = async () => {
  if (montoCapitalPagarActualizar.value === null) {
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: 'Debe ingresar un monto',
      life: 3000
    });
    return;
  }
  
  try {
    cargando.value = true;
    const response = await axios.post('/cuota', {
      cuota_id: props.cuotaId,
      monto_capital_pagar: montoCapitalPagarActualizar.value
    });
    
    toast.add({
      severity: 'success',
      summary: 'Éxito',
      detail: 'Pago registrado correctamente',
      life: 3000
    });
    
    emit('pago-actualizado')
    cerrarDialogo()
  } catch (error) {
    console.error('Error al registrar pago:', error.response?.data || error);
    toast.add({
      severity: 'error',
      summary: 'Error',
      detail: error.response?.data?.message || 'Error al registrar el pago',
      life: 3000
    });
  } finally {
    cargando.value = false;
  }
}
</script>