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
                v-for="column in columns"
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
import { computed } from 'vue'
import { Button } from '@/components/ui/button'
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import { ChevronDown, Settings2 } from 'lucide-vue-next'

const props = defineProps<{
    table: any
}>()

// Computed to handle potential undefined
const columns = computed(() =>
    props.table.getAllColumns?.().filter(
        (column) => column.getCanHide() && column.columnDef.meta?.hideable !== false
    ) || []
)

// Complete mapping of column IDs to user-friendly labels
const getColumnLabel = (columnId: string) => {
    const columnLabels: Record<string, string> = {
        'select': 'Selección',
        'dni': 'DNI',
        'NombreCompleto': 'Nombre completo',
        'fecha_inicio': 'Inicio',
        'fecha_vencimiento': 'Vencimiento',
        'capital': 'Capital',
        'numero_cuotas': 'Cuotas',
        'estado_cliente': 'Estado',
        'recomendacion': 'Recomendación',
        'tasa_interes_diario': 'Interés Diario'
    }
    return columnLabels[columnId] || columnId
}
</script>
