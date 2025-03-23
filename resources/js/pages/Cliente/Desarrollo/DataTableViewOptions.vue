<template>
    <DropdownMenu>
      <DropdownMenuTrigger asChild>
        <Button variant="outline" size="sm" class="ml-auto hidden h-8 lg:flex">
          <Settings2 class="mr-2 h-4 w-4" />
          Ver
          <ChevronDown class="ml-2 h-4 w-4" />
        </Button>
      </DropdownMenuTrigger>
      <DropdownMenuContent align="end" class="w-[170px]">
        <DropdownMenuLabel>Toggle columns</DropdownMenuLabel>
        <DropdownMenuSeparator />
        <DropdownMenuCheckboxItem
          v-for="column in table.getAllColumns().filter(
            (column) => column.getCanHide()
          )"
          :key="column.id"
          :checked="column.getIsVisible()"
          @update:checked="(value) => column.toggleVisibility(!!value)"
        >
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

  // Function to get user-friendly column labels
  const getColumnLabel = (columnId: string) => {
    const columnLabels: Record<string, string> = {
      'dni': 'DNI',
      'foto': 'Foto',
      'nombres': 'Nombres',
      'apellidos': 'Apellidos',
      'celular': 'Celular',
      'direccion': 'Dirección',
      'centro_trabajo': 'Centro de Trabajo',
      'estado': 'Estado',
    };

    return columnLabels[columnId] || columnId;
  };
  </script>
