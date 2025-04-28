<template>
  <DropdownMenu>
    <DropdownMenuTrigger asChild>
      <Button variant="outline" class="flex items-center space-x-2">
          <FilterIcon class="h-4 w-4" />
          <span>Filtrar</span>
        </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent align="end" class="w-[170px]">
      <DropdownMenuLabel>Selecciona</DropdownMenuLabel>
      <DropdownMenuSeparator />
      <DropdownMenuCheckboxItem v-for="estado in estadosCliente" :key="estado.value"
        :checked="selectedFilter === estado.value" @update:checked="(value) => applyFilter(estado.value, value)">
        {{ estado.label }}
      </DropdownMenuCheckboxItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>

<script setup lang="ts">
import { ref, defineEmits } from 'vue';
import { Button } from '@/components/ui/button';
import {
  DropdownMenu,
  DropdownMenuCheckboxItem,
  DropdownMenuContent,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { FilterIcon, ArrowUpDown } from 'lucide-vue-next';

const emit = defineEmits(['filter-change']);
const selectedFilter = ref<string | null>(null);

const estadosCliente = [
  { value: 'activo', label: 'Activo' },
  { value: 'inactivo', label: 'Inactivo' },
  { value: 'moroso', label: 'Moroso' },
  { value: 'al_dia', label: 'Al día' }
];

const applyFilter = (estadoValue: string, isChecked: boolean) => {
  selectedFilter.value = isChecked ? estadoValue : null;
  emit('filter-change', selectedFilter.value);
};
</script>
