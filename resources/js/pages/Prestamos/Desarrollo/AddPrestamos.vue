<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import axios from 'axios'
import { Plus, ChevronsUpDown, Check, Info } from 'lucide-vue-next'
import { Button } from "@/components/ui/button"
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Textarea } from "@/components/ui/textarea"
import { Combobox, ComboboxAnchor, ComboboxEmpty, ComboboxGroup, ComboboxInput, ComboboxItem, ComboboxItemIndicator, ComboboxList, ComboboxTrigger } from '@/components/ui/combobox'
import { cn } from '@/lib/utils'
import { CalendarDate } from '@internationalized/date'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import { RangeCalendar } from '@/components/ui/range-calendar'
import InputError from '@/components/InputError.vue'
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

interface Cliente {
    id: number
    label: string
    value: number
}

interface ClienteResponse {
    data: Cliente[]
    pagination: {
        current_page: number
        last_page: number
        next_page_url: string
    }
}

const isOpen = ref(false)
const loading = ref(false)
const clientes = ref<Cliente[]>([])
const selectedCliente = ref<Cliente | null>(null)
const errors = ref<Record<string, string[]>>({})

const today = new Date()

// State for date range with start and end dates
const dateRange = ref({
    start: new CalendarDate(
        today.getFullYear(),
        today.getMonth() + 1,
        today.getDate()
    ),
    end: null as CalendarDate | null
})

const capital = ref<number | null>(null)
const numeroCuotas = ref<number | null>(null)
const tasaInteresDiario = ref<number | null>(null)
const recomendacion = ref<string>('')
const estadoCliente = ref<number>(1)

// Character count for recommendation
const recommendationCharLimit = 255
const remainingChars = computed(() => {
    return recommendationCharLimit - (recomendacion.value?.length || 0)
})

// Función para deshabilitar fechas pasadas
const isDateDisabled = (date: CalendarDate) => {
  const currentDate = new CalendarDate(
    today.getFullYear(),
    today.getMonth() + 1,
    today.getDate()
  )
  return date.compare(currentDate) < 0
}

// Fetch clients from backend
const fetchClientes = async (query = '', page = 1) => {
    try {
        const { data } = await axios.get<ClienteResponse>('/prestamo/cliente', {
            params: { search: query, page }
        })
        clientes.value = data.data
    } catch (error) {
        console.error('Error al cargar clientes:', error)
    }
}

const handleSubmit = async () => {
    // Reset previous errors
    errors.value = {}
    loading.value = true

    // Prepare submission data
    const submissionData = {
        cliente_id: selectedCliente.value?.value,
        fecha_inicio: dateRange.value.start?.toString(),
        fecha_vencimiento: dateRange.value.end?.toString(),
        capital: capital.value,
        numero_cuotas: numeroCuotas.value,
        tasa_interes_diario: tasaInteresDiario.value,
        estado_cliente: estadoCliente.value,
        recomendacion: recomendacion.value
    }

    try {
        const response = await axios.post('/prestamo', submissionData)

        // Reset form and close dialog on success
        isOpen.value = false
        resetForm()
    } catch (error: any) {
        // Handle backend validation errors
        if (error.response?.data?.errors) {
            errors.value = error.response.data.errors
        }
    } finally {
        loading.value = false
    }
}

// Reset form method
const resetForm = () => {
    selectedCliente.value = null
    dateRange.value = {
        start: new CalendarDate(
            today.getFullYear(),
            today.getMonth() + 1,
            today.getDate()
        ),
        end: null
    }
    capital.value = null
    numeroCuotas.value = null
    tasaInteresDiario.value = null
    recomendacion.value = ''
    estadoCliente.value = 1
    errors.value = {}
}

// Lifecycle hook
onMounted(() => {
    fetchClientes()
})

// Optional: Watch for date range changes (for debugging or additional logic)
watch(dateRange, (newValue) => {
  console.log('Date range updated:', newValue)
}, { deep: true })
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button variant="outline" size="sm">
                <Plus class="h-4 mr-2" />
                Crear Nuevo Préstamo
            </Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-[800px] grid-rows-[auto_1fr_auto] p-0 max-h-[90dvh]">
            <DialogHeader class="p-6 pb-0">
                <DialogTitle class="text-lg font-semibold">Registro de Préstamos</DialogTitle>
                <DialogDescription class="text-sm text-muted-foreground">
                    Introduzca los datos del préstamo y seleccione el cliente.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="handleSubmit" class="flex flex-col">
                <div class="grid grid-cols-2 gap-4 py-4 overflow-y-auto px-6 max-h-[60vh]">
                    <!-- Cliente Selection -->
                    <div class="grid gap-2 col-span-2">
                        <Label for="cliente">Cliente <span class="text-red-500">*</span></Label>
                        <Combobox v-model="selectedCliente" :items="clientes" by="value" class="w-full">
                            <ComboboxAnchor class="w-full">
                                <div class="relative w-full max-w-full items-center">
                                    <ComboboxInput :display-value="(val) => val?.label ?? ''"
                                        placeholder="Selecciona un cliente..."
                                        @input="fetchClientes($event.target.value)" class="w-full" />
                                    <ComboboxTrigger
                                        class="absolute end-0 top-1/2 -translate-y-1/2 flex items-center justify-center px-3">
                                        <ChevronsUpDown class="size-4 text-muted-foreground" />
                                    </ComboboxTrigger>
                                </div>
                            </ComboboxAnchor>
                            <ComboboxList class="w-full">
                                <ComboboxEmpty class="w-full">No se encontraron clientes.</ComboboxEmpty>
                                <ComboboxGroup class="w-full">
                                    <ComboboxItem v-for="cliente in clientes" :key="cliente.value" :value="cliente"
                                        class="w-full">
                                        {{ cliente.label }}
                                        <ComboboxItemIndicator>
                                            <Check :class="cn('w-full')" />
                                        </ComboboxItemIndicator>
                                    </ComboboxItem>
                                </ComboboxGroup>
                            </ComboboxList>
                        </Combobox>
                        <InputError :message="errors['cliente_id']?.[0]" />
                    </div>

                    <!-- Fecha de Inicio y Vencimiento -->
                    <div class="grid gap-2">
                        <Label>Fechas de Préstamo <span class="text-red-500">*</span></Label>
                        <Popover>
                            <PopoverTrigger as-child>
                                <Button variant="outline" class="w-full justify-start text-left">
                                    <template v-if="dateRange.start && dateRange.end">
                                        {{ dateRange.start.day }}/{{ dateRange.start.month }}/{{ dateRange.start.year }} -
                                        {{ dateRange.end.day }}/{{ dateRange.end.month }}/{{ dateRange.end.year }}
                                    </template>
                                    <template v-else-if="dateRange.start">
                                        {{ dateRange.start.day }}/{{ dateRange.start.month }}/{{ dateRange.start.year }}
                                    </template>
                                    <template v-else>
                                        Selecciona fechas
                                    </template>
                                </Button>
                            </PopoverTrigger>
                            <PopoverContent class="w-auto p-0">
                                <RangeCalendar
                                    v-model="dateRange"
                                    :is-date-disabled="isDateDisabled"
                                    :month="today.getMonth() + 1"
                                    :year="today.getFullYear()"
                                />
                            </PopoverContent>
                        </Popover>
                        <div class="space-y-2">
                            <InputError :message="errors['fecha_inicio']?.[0]" />
                            <InputError :message="errors['fecha_vencimiento']?.[0]" />
                        </div>
                    </div>

                    <!-- Capital -->
                    <div class="grid gap-2">
                        <Label for="capital">Capital <span class="text-red-500">*</span></Label>
                        <Input id="capital" type="number" v-model.number="capital" placeholder="Monto del préstamo"
                            min="0" step="0.01" />
                        <InputError :message="errors['capital']?.[0]" />
                    </div>

                    <!-- Número de Cuotas -->
                    <div class="grid gap-2">
                        <Label for="numeroCuotas">Número de Cuotas <span class="text-red-500">*</span></Label>
                        <Input id="numeroCuotas" type="number" v-model.number="numeroCuotas"
                            placeholder="Número de cuotas" min="1" />
                        <InputError :message="errors['numero_cuotas']?.[0]" />
                    </div>

                    <!-- Tasa de Interés Diario -->
                    <div class="grid gap-2">
                        <Label for="tasaInteresDiario">Tasa de Interés Diario (%) <span
                                class="text-red-500">*</span></Label>
                        <Input id="tasaInteresDiario" type="number" v-model.number="tasaInteresDiario"
                            placeholder="Tasa de interés diaria" min="0" step="0.01" />
                        <InputError :message="errors['tasa_interes_diario']?.[0]" />
                    </div>

                    <!-- Estado del Cliente -->
                    <div class="grid gap-2 col-span-2">
                        <Label for="estadoCliente">Estado del Cliente <span
                            class="text-red-500">*</span></Label>
                        <Select v-model="estadoCliente">
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Seleccionar estado" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem :value="1">Paga</SelectItem>
                                    <SelectItem :value="2">Moroso</SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors['estado_cliente']?.[0]" />
                    </div>

                    <!-- Recomendación -->
                    <div class="grid gap-2 col-span-2">
                        <Label for="recomendacion">Recomendación <span
                            class="text-red-500">*</span></Label>
                        <div class="relative">
                            <Textarea
                                id="recomendacion"
                                v-model="recomendacion"
                                placeholder="Notas o recomendaciones adicionales"
                                :maxlength="recommendationCharLimit"
                            />
                            <div class="text-xs text-muted-foreground absolute bottom-2 right-2 flex items-center">
                                <Info class="w-4 h-4 mr-1" />
                                {{ remainingChars }} caracteres restantes
                            </div>
                        </div>
                        <InputError :message="errors['recomendacion']?.[0]" />
                    </div>
                </div>

                <!-- Submit Button -->
                <DialogFooter class="p-6 pt-0">
                    <Button type="submit" :disabled="loading" class="w-full">
                        {{ loading ? "Guardando..." : "Guardar Préstamo" }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
