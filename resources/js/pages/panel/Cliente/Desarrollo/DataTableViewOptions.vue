<template>
  <DropdownMenu>
    <DropdownMenuTrigger asChild>
      <Button variant="outline"  class="flex h-8">
        <Settings2 class="mr-2 h-4 w-4" />
        Ver
        <ChevronDown class="ml-2 h-4 w-4" />
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent align="end" class="w-[170px]">
      <DropdownMenuLabel>Toggle columns</DropdownMenuLabel>
      <DropdownMenuSeparator />
      <DropdownMenuCheckboxItem v-for="column in table.getAllColumns().filter(
        (column) => column.getCanHide()
      )" :key="column.id" :checked="column.getIsVisible()"
        @update:checked="(value) => column.toggleVisibility(!!value)">
        {{ getColumnLabel(column.id) }}
      </DropdownMenuCheckboxItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>

<script setup lang="ts">
import { Table } from '@tanstack/vue-table';
import { Button } from '@/components/ui/button';
import {
  DropdownMenu,
  DropdownMenuCheckboxItem,
  DropdownMenuContent,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { ChevronDown, Settings2 } from 'lucide-vue-next';

const props = defineProps<{
  table: Table<any>;
}>();

// Complete mapping of column IDs to user-friendly labels
const getColumnLabel = (columnId: string) => {
  const columnLabels: Record<string, string> = {
    'dni': 'DNI',
    'foto': 'Foto',
    'nombre_completo': 'Nombre completo',
    'celular': 'Celular',
    'direccion': 'Dirección',
    'centro_trabajo': 'Centro de Trabajo',
    'estado': 'Estado',
    'fecha_inicio': 'Inicio',
    'fecha_vencimiento': 'Vencimiento',
    'tasa_interes_diario': 'Interés Diario',
    'capital_inicial': 'Capital Inicial',
    'capital_del_mes': 'Capital del Mes',
    'capital_actual': 'Capital Actual',
    'interes_actual': 'Interés Actual',
    'interes_total': 'Interés Total',
    'total': 'Total',
    'numero_cuotas': 'Nº de Cuotas',
    'recomendacion': 'Recomendación'
  };
  return columnLabels[columnId] || columnId;
};
</script>