<script setup lang="ts">
import { Plus, ChevronsUpDown, Check, Info } from 'lucide-vue-next';
import { Button } from "@/components/ui/button";
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { Textarea } from "@/components/ui/textarea";
import { Combobox, ComboboxAnchor, ComboboxEmpty, ComboboxGroup, ComboboxInput, ComboboxItem, ComboboxItemIndicator, ComboboxList, ComboboxTrigger } from '@/components/ui/combobox';
import { cn } from '@/lib/utils';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { RangeCalendar } from '@/components/ui/range-calendar';
import InputError from '@/components/InputError.vue';
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { AddPrestamoEmits } from './typsPrestamos/AddPrestamos.types';
import { useAddPrestamo } from './typsPrestamos/useAddPrestamo';

const emit = defineEmits<AddPrestamoEmits>();
const {
    isOpen,
    loading,
    clientes,
    selectedCliente,
    errors,
    dateRange,
    capital,
    numeroCuotas,
    tasaInteresDiario,
    recomendacion,
    estadoCliente,
    today,
    recommendationCharLimit,
    remainingChars,
    isDateDisabled,
    fetchClientes,
    handleSubmit
} = useAddPrestamo(emit);
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