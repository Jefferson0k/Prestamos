<script setup>
import { watch, ref, onMounted } from 'vue'
import Dialog from 'primevue/dialog'
import InputNumber from 'primevue/inputnumber'
import Button from 'primevue/button'
import axios from 'axios'
import { useToast } from 'primevue/usetoast'

const toast = useToast()
const props = defineProps({
    visible: Boolean,
    cuota: Object
})

const emit = defineEmits(['update:visible'])
const localVisible = ref(props.visible)
const value1 = ref(0)
const loading = ref(false)
const interesData = ref(null)

watch(() => props.visible, (val) => {
    localVisible.value = val
    if (val && props.cuota) {
        fetchInteresDetails()
    }
})

watch(() => props.cuota, (newCuota) => {
    if (newCuota && localVisible.value) {
        fetchInteresDetails()
    }
})

watch(localVisible, (val) => {
    if (!val) {
        emit('update:visible', false)
    }
})

const fetchInteresDetails = async () => {
    if (!props.cuota || !props.cuota.id) return

    loading.value = true
    try {
        const response = await axios.get(`/cuota/${props.cuota.id}/show/intereses`)
        interesData.value = response.data.data

        // Inicializa el input con el valor de la API
        if (interesData.value) {
            value1.value = parseFloat(interesData.value.monto_interes_pagar.replace(/,/g, '') || 0)
        }

        console.log('Interés data:', interesData.value)
    } catch (error) {
        console.error('Error al obtener detalles de interés:', error)
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudieron cargar los detalles del interés',
            life: 3000
        })
    } finally {
        loading.value = false
    }
}

const hideDialog = () => {
    emit('update:visible', false)
}

const aplicarCambios = async () => {
    if (!interesData.value || !interesData.value.id) return

    loading.value = true
    try {
        await axios.post(`/cuota/${interesData.value.id}/update/interes`, {
            monto_interes_pagar: value1.value
        })

        toast.add({
            severity: 'success',
            summary: 'Éxito',
            detail: 'El monto de interés ha sido actualizado',
            life: 3000
        })

        hideDialog()
    } catch (error) {
        console.error('Error al actualizar el interés:', error)
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: 'No se pudo actualizar el monto de interés',
            life: 3000
        })
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <Dialog v-model:visible="localVisible" :style="{ width: '500px' }" modal header="Editar Interés">
        <div v-if="loading" class="flex justify-center p-4">
            <i class="pi pi-spin pi-spinner text-xl"></i>
        </div>

        <div v-else-if="interesData" class="flex flex-col gap-6">

            <div class="grid grid-cols-2 gap-2">
                <label class="block font-bold mb-3">Fecha Inicio: {{ interesData.fecha_inicio }}</label>
                <label class="block font-bold mb-3">Día: {{ interesData.dias }} | Cobrar: {{ interesData.dias_calculados
                    }}</label>
                <label class="block font-bold mb-3">Interés a Pagar: {{ interesData.monto_interes_pagar }}</label>
                <label class="block font-bold mb-3">Capital mas Interes: {{ interesData.monto_total_pagar }}</label>
            </div>

            <div>
                <label for="Capal_Pagar" class="block font-bold mb-3">Nuevo monto interés a pagar <span
                        class="text-red-500">*</span></label>
                <InputNumber v-model="value1" inputId="withoutgrouping" :useGrouping="false" class="w-full" />
            </div>
        </div>

        <div v-else class="p-4 text-red-500">
            No se pudieron cargar los datos de interés.
        </div>

        <template #footer>
            <Button label="Cancelar" icon="pi pi-times" text @click="hideDialog" />
            <Button label="Aplicar" icon="pi pi-check" :loading="loading" @click="aplicarCambios"
                :disabled="!interesData" />
        </template>
    </Dialog>
</template>