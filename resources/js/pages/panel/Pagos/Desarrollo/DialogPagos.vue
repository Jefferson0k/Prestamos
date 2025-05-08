<template>
    <div>
      <Button icon="pi pi-wallet" label="Pagar" outlined severity="success" class="mr-2" @click="openDialog" />
  
      <Dialog v-model:visible="visible" modal header="Registrar Pago" :style="{ width: '40vw' }"
        :breakpoints="{ '1199px': '75vw', '575px': '90vw' }">
        
        <div class="flex flex-column gap-3">
          <label for="monto">Monto a pagar (Capital)</label>
          <InputNumber v-model="montoCapitalPagar" inputId="monto" :minFractionDigits="2" mode="decimal" placeholder="0.00" class="w-full" />
        </div>
  
        <template #footer>
          <Button label="Cancelar" icon="pi pi-times" text @click="visible = false" />
          <Button label="Guardar" icon="pi pi-check" @click="guardarPago" :disabled="!montoCapitalPagar" />
        </template>
      </Dialog>
    </div>
  </template>
  
  <script setup>
  import { ref } from 'vue'
  import Button from 'primevue/button'
  import Dialog from 'primevue/dialog'
  import InputNumber from 'primevue/inputnumber'
  import axios from 'axios'
  
  const visible = ref(false)
  const montoCapitalPagar = ref(null)
  
  // Este debe ser el ID de la cuota (puedes pasarlo como prop)
  const cuotaId = 44// Reemplaza con el valor real
  
  const openDialog = () => {
    montoCapitalPagar.value = null
    visible.value = true
  }
  
  const guardarPago = async () => {
    try {
      const response = await axios.post('/cuota', {
        cuota_id: cuotaId,
        monto_capital_pagar: montoCapitalPagar.value
      })
  
      console.log('Pago registrado:', response.data)
      visible.value = false
      // Emitir evento o refrescar datos si es necesario
    } catch (error) {
      console.error('Error al registrar pago:', error.response?.data || error)
    }
  }
  </script>
  