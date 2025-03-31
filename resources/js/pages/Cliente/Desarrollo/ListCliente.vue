<template>
    <div class="grid grid-cols-1gap-6">
        <Toaster />
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <Input 
                    placeholder="Buscar clientes..." 
                    class="h-8 w-[250px]" 
                    v-model="searchQuery"
                    @input="onSearchChange" 
                />
            </div>
            <div class="flex items-center gap-2">
                <Button variant="outline" size="sm" @click="refreshData">
                    <RefreshCcw class="h-4 w-4" />
                    Actualizar
                </Button>
                <DataTableViewOptions :table="table" />
                <AddCliente @cliente-added="fetchClientes" />
            </div>
        </div>
        <div class="rounded-md border overflow-x-auto">
            <Table>
                <TableHeader>
                    <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                        <TableHead 
                            v-for="header in headerGroup.headers" 
                            :key="header.id"
                            :class="[
                                header.column.getCanSort() ? 'cursor-pointer select-none' : '',
                                getColumnWidthClass(header.column.id)
                            ]"
                            @click="header.column.getCanSort() ? header.column.toggleSorting() : null"
                        >
                            <div class="flex items-center justify-between space-x-2" v-if="!header.isPlaceholder">
                                <component
                                    :is="() => h(FlexRender, {
                                        render: header.column.columnDef.header,
                                        props: header.getContext()
                                    })"
                                />
                                <div v-if="header.column.getCanSort()">
                                    <ArrowUpDown class="h-4 w-4" v-if="!header.column.getIsSorted()" />
                                    <ArrowUp class="h-4 w-4" v-else-if="header.column.getIsSorted() === 'asc'" />
                                    <ArrowDown class="h-4 w-4" v-else />
                                </div>
                            </div>
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template v-if="table.getRowModel().rows?.length">
                        <TableRow 
                            v-for="row in table.getRowModel().rows"
                            :key="row.id"
                            :data-state="row.getIsSelected() && 'selected'"
                            class="cursor-pointer hover:bg-muted/50"
                            @click="openClienteDetails(row.original)"
                        >
                            <TableCell 
                                v-for="cell in row.getVisibleCells()" 
                                :key="cell.id"
                                :class="getColumnWidthClass(cell.column.id)"
                            >
                                <div class="truncate">
                                    <component
                                        :is="() => h(FlexRender, {
                                            render: cell.column.columnDef.cell,
                                            props: cell.getContext()
                                        })"
                                    />
                                </div>
                            </TableCell>
                        </TableRow>
                    </template>
                    <template v-else>
                        <TableRow>
                            <TableCell :colspan="columns.length" class="text-center">
                                No hay resultados.
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>

        <div class="flex items-center justify-between space-x-2 py-4">
            <div class="flex-1 text-sm text-muted-foreground">
                Mostrando <span class="font-medium">{{ table.getRowModel().rows.length }}</span> de <span class="font-medium">{{ filteredData.length }}</span> clientes
            </div>
            <div class="flex items-center space-x-4">
                <!-- Selección de filas por página -->
                <div class="flex items-center space-x-2">
                    <p class="text-sm font-medium">Filas por página</p>
                    <Select v-model="pageSize" @update:modelValue="table.setPageSize(Number($event))">
                        <SelectTrigger class="h-8 w-[70px]">
                            <SelectValue :placeholder="pageSizeString" />
                        </SelectTrigger>
                        <SelectContent side="top">
                            <SelectItem v-for="size in [5, 10, 20, 50]" :key="size" :value="size">{{ size }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- No mostrar si no hay suficientes elementos para paginar -->
                <template v-if="totalPageCount > 0">
                    <!-- Indicador de página actual -->
                    <p class="text-sm font-medium">
                        Página {{ table.getState().pagination.pageIndex + 1 }} de {{ totalPageCount }}
                    </p>

                    <!-- Controles de paginación -->
                    <div class="flex items-center space-x-2">
                        <Button 
                            variant="outline" 
                            class="hidden h-8 w-8 p-0 lg:flex" 
                            :disabled="!table.getCanPreviousPage()"
                            @click="table.setPageIndex(0)"
                        >
                            <DoubleArrowLeftIcon class="h-4 w-4" />
                        </Button>
                        <Button 
                            variant="outline" 
                            class="h-8 w-8 p-0" 
                            :disabled="!table.getCanPreviousPage()"
                            @click="table.previousPage()"
                        >
                            <ChevronLeftIcon class="h-4 w-4" />
                        </Button>
                        <Button 
                            variant="outline" 
                            class="h-8 w-8 p-0" 
                            :disabled="!table.getCanNextPage()"
                            @click="table.nextPage()"
                        >
                            <ChevronRightIcon class="h-4 w-4" />
                        </Button>
                        <Button 
                            variant="outline" 
                            class="hidden h-8 w-8 p-0 lg:flex" 
                            :disabled="!table.getCanNextPage()"
                            @click="table.setPageIndex(table.getPageCount() - 1)"
                        >
                            <DoubleArrowRightIcon class="h-4 w-4" />
                        </Button>
                    </div>
                </template>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch, h } from 'vue';
import {
    useVueTable,
    getCoreRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    getFilteredRowModel,
    FlexRender
} from '@tanstack/vue-table';

// Tipos y utilitarios
import { Cliente, SortingState, VisibilityState, ColumnFiltersState } from './typsClientes/typesCliente';
import { getColumnWidthClass } from './typsClientes/columnUtils';
import { createColumns } from './typsClientes/columns';

// Componentes UI
import { Toaster } from '@/components/ui/toast';
import { useToast } from '@/components/ui/toast';
import AddCliente from './AddCliente.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import DataTableViewOptions from './DataTableViewOptions.vue';

// Iconos
import { RefreshCcw, ArrowUpDown, ArrowUp, ArrowDown } from 'lucide-vue-next';
import { ChevronRightIcon, ChevronLeftIcon, DoubleArrowLeftIcon, DoubleArrowRightIcon } from "@radix-icons/vue";

// Estado
const { toast } = useToast();
const clientes = ref<Cliente[]>([]);
const isLoading = ref(true);
const searchQuery = ref('');
const selectedCliente = ref<Cliente | null>(null);
const isClienteDetailsOpen = ref(false);
const pageSize = ref(10);
const currentPage = ref(0);

// Computed properties
const pageSizeString = computed(() => String(pageSize.value));

// Estados de la tabla
const sorting = ref<SortingState>([]);
const columnVisibility = ref<VisibilityState>({
    'direccion': false,
    'centro_trabajo': false,
    'recomendacion': false,
    'foto': false,
    'celular': false,
});
const columnFilters = ref<ColumnFiltersState>([]);

// Manejadores de clientes
const viewClienteDetails = (cliente: Cliente) => {
    selectedCliente.value = cliente;
    isClienteDetailsOpen.value = true;
};

const editCliente = (cliente: Cliente) => {
    toast({
        title: "Función en desarrollo",
        description: `Editar cliente: ${cliente.nombres} ${cliente.apellidos}`,
    });
};

const deleteCliente = (cliente: Cliente) => {
    toast({
        title: "Función en desarrollo",
        description: `Eliminar cliente: ${cliente.nombres} ${cliente.apellidos}`,
        variant: "destructive",
    });
};

const openClienteDetails = (cliente: Cliente) => {
    selectedCliente.value = cliente;
    isClienteDetailsOpen.value = true;
};

// Definición de columnas
const columns = createColumns({
    viewClienteDetails,
    editCliente,
    deleteCliente
});

// Datos filtrados
const filteredData = computed(() => {
    if (!searchQuery.value) return clientes.value;

    return clientes.value.filter(cliente => {
        const searchLower = searchQuery.value.toLowerCase();
        return (
            (cliente.nombres || '').toLowerCase().includes(searchLower) ||
            (cliente.apellidos || '').toLowerCase().includes(searchLower) ||
            (cliente.dni || '').includes(searchQuery.value) ||
            (cliente.celular || '').includes(searchQuery.value)
        );
    });
});

// Total de páginas
const totalPageCount = computed(() => {
    if (!filteredData.value.length) return 0;
    return Math.ceil(filteredData.value.length / pageSize.value);
});

// Inicialización de la tabla
const table = useVueTable({
    get data() {
        return filteredData.value;
    },
    columns,
    state: {
        get sorting() {
            return sorting.value;
        },
        get columnVisibility() {
            return columnVisibility.value;
        },
        get columnFilters() {
            return columnFilters.value;
        },
        get pagination() {
            return {
                pageIndex: currentPage.value,
                pageSize: pageSize.value,
            };
        },
    },
    onSortingChange: (updater) => {
        sorting.value = typeof updater === 'function' ? updater(sorting.value) : updater;
    },
    onColumnVisibilityChange: (updater) => {
        columnVisibility.value = typeof updater === 'function' ? updater(columnVisibility.value) : updater;
    },
    onColumnFiltersChange: (updater) => {
        columnFilters.value = typeof updater === 'function' ? updater(columnFilters.value) : updater;
    },
    onPaginationChange: (updater) => {
        const pagination = {
            pageIndex: currentPage.value,
            pageSize: pageSize.value,
        };
        const updated = typeof updater === 'function' ? updater(pagination) : updater;
        currentPage.value = updated.pageIndex;
        pageSize.value = updated.pageSize;
    },
    getCoreRowModel: getCoreRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getFilteredRowModel: getFilteredRowModel(),
    enableColumnResizing: true,
    enableRowSelection: true,
    debugTable: false,
    manualPagination: false,
});

// Watchers y manejadores de eventos
watch([searchQuery, clientes], () => {
    table.resetPageIndex(true);
}, { deep: true });

const fetchClientes = async () => {
    isLoading.value = true;
    try {
        const response = await fetch('/cliente');
        const result = await response.json();
        if (result && result.data) {
            clientes.value = result.data.map((cliente: any) => ({
                ...cliente,
                nombres: cliente.nombres || '',
                apellidos: cliente.apellidos || '',
                direccion: cliente.direccion || '',
                centro_trabajo: cliente.centro_trabajo || '',
                celular: cliente.celular || '',
                dni: cliente.dni || '',
                foto: cliente.foto || '',
                capital_del_mes: Number(cliente.capital_del_mes || 0),
                capital_actual: Number(cliente.capital_actual || 0),
                interes_actual: Number(cliente.interes_actual || 0),
                interes_total: Number(cliente.interes_total || 0),
                total: Number(cliente.total || 0)
            }));
        } else {
            console.error('Formato de respuesta inesperado:', result);
            toast({
                title: "Error",
                description: "El formato de la respuesta no es el esperado.",
                variant: "destructive",
            });
        }
    } catch (error) {
        console.error('Error al obtener clientes:', error);
        toast({
            title: "Error",
            description: "No se pudieron cargar los clientes. Verifica la conexión.",
            variant: "destructive",
        });
    } finally {
        isLoading.value = false;
    }
};

const refreshData = () => {
    fetchClientes();
    toast({
        title: "Actualizado",
        description: "Los datos de clientes han sido actualizados.",
    });
};

const onSearchChange = () => {
    table.resetPageIndex(true);
};

// Inicialización
onMounted(() => {
    fetchClientes();
});
</script>

<style scoped>
:deep(.truncate) {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

:deep(th), :deep(td) {
    padding: 0.5rem 0.75rem;
}

:deep(.table-container) {
    width: 100%;
    overflow-x: auto;
}
</style>