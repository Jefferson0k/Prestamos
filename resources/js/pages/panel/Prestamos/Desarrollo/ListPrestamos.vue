<template>
    <Toaster />

    <!-- Tarjeta de controles principales -->
    <Card class="mb-4">
        <CardContent class="pt-6 pb-4">
            <div class="flex flex-wrap justify-between items-center">
                <div class="flex items-center gap-2">
                    <AddPrestamos @click="agregarNuevoCliente" />
                    <Button variant="secondary" @click="refreshData">
                        <RefreshCcw class="h-4 w-4 mr-2" />
                        Actualizar
                    </Button>
                </div>

                <!-- Botón de exportar al lado derecho -->
                <Button variant="destructive" class="mt-2 sm:mt-0">
                    <Download class="h-4 mr-2" />
                    Exportar
                </Button>
            </div>
        </CardContent>
    </Card>

    <!-- Tarjeta principal con la tabla -->
    <Card>
        <CardHeader class="pb-0">
            <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
                <div>
                    <CardTitle>Préstamos</CardTitle>
                    <CardDescription>Gestión de préstamos y financiamientos</CardDescription>
                </div>

                <!-- Controles de búsqueda y visualización -->
                <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                    <div class="relative w-full sm:w-auto">
                        <Search className="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                        <Input placeholder="Buscar préstamos..." class="h-9 w-full sm:w-[250px] pl-8"
                            v-model="searchQuery" @input="onSearchChange" />
                    </div>
                    <DataTableViewOptions :table="table" />
                </div>
            </div>
        </CardHeader>

        <CardContent class="pt-4">
            <div class="rounded-md border overflow-hidden">
                <div class="overflow-x-auto">
                    <Table class="w-full">
                        <TableHeader>
                            <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
                                <TableHead v-for="header in headerGroup.headers" :key="header.id" :class="[
                                    header.column.getCanSort() ? 'cursor-pointer select-none' : '',
                                    getColumnWidthClass(header.column.id)
                                ]" @click="header.column.getCanSort() ? header.column.toggleSorting() : null">
                                    <div class="flex items-center justify-between space-x-2"
                                        v-if="!header.isPlaceholder">
                                        <component :is="() => h(FlexRender, {
                                            render: header.column.columnDef.header,
                                            props: header.getContext()
                                        })" />
                                        <div v-if="header.column.getCanSort()">
                                            <ArrowUpDown class="h-4 w-4" v-if="!header.column.getIsSorted()" />
                                            <ArrowUp class="h-4 w-4"
                                                v-else-if="header.column.getIsSorted() === 'asc'" />
                                            <ArrowDown class="h-4 w-4" v-else />
                                        </div>
                                    </div>
                                </TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            <template v-if="table.getRowModel().rows?.length">
                                <TableRow v-for="row in table.getRowModel().rows" :key="row.id"
                                    :data-state="row.getIsSelected() && 'selected'"
                                    class="cursor-pointer hover:bg-muted/50" @click="verDetalleCliente(row.original)">
                                    <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id"
                                        :class="getColumnWidthClass(cell.column.id)">
                                        <div class="truncate">
                                            <component :is="() => h(FlexRender, {
                                                render: cell.column.columnDef.cell,
                                                props: cell.getContext()
                                            })" />
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </template>
                            <template v-else>
                                <TableRow>
                                    <TableCell :colspan="columns.length" class="h-24 text-center">
                                        <div class="flex flex-col items-center justify-center py-4">
                                            <FileX class="h-10 w-10 text-muted-foreground mb-2" />
                                            <p class="text-sm text-muted-foreground">No hay préstamos disponibles
                                            </p>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            </template>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </CardContent>

        <CardFooter>
            <div class="w-full">
                <div class="flex flex-col sm:flex-row items-center justify-between gap-4 py-2">
                    <div class="text-sm text-muted-foreground">
                        Mostrando
                        <span class="font-medium">{{ table.getRowModel().rows.length }}</span>
                        de
                        <span class="font-medium">{{ filteredData.length }}</span> préstamos
                    </div>

                    <div class="flex flex-wrap items-center gap-4 justify-center sm:justify-end">
                        <!-- Filas por página -->
                        <div class="flex items-center space-x-2">
                            <p class="text-sm font-medium">Filas</p>
                            <Select v-model="pageSize"
                                @update:modelValue="(value) => table.setPageSize(Number(value) || 10)">
                                <SelectTrigger class="h-8 w-16">
                                    <SelectValue :placeholder="pageSizeString" />
                                </SelectTrigger>
                                <SelectContent side="top">
                                    <SelectItem v-for="size in [5, 10, 20, 50]" :key="size" :value="size">
                                        {{ size }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <!-- Paginación -->
                        <template v-if="totalPageCount > 0">
                            <p class="text-sm font-medium whitespace-nowrap">
                                Página {{ table.getState().pagination.pageIndex + 1 }} de {{ totalPageCount }}
                            </p>

                            <div class="flex items-center space-x-1">
                                <Button variant="outline" class="hidden h-8 w-8 p-0 md:flex"
                                    :disabled="!table.getCanPreviousPage()" @click="table.setPageIndex(0)"
                                    aria-label="Primera página">
                                    <DoubleArrowLeftIcon class="h-4 w-4" />
                                </Button>
                                <Button variant="outline" class="h-8 w-8 p-0" :disabled="!table.getCanPreviousPage()"
                                    @click="table.previousPage()" aria-label="Página anterior">
                                    <ChevronLeftIcon class="h-4 w-4" />
                                </Button>
                                <Button variant="outline" class="h-8 w-8 p-0" :disabled="!table.getCanNextPage()"
                                    @click="table.nextPage()" aria-label="Página siguiente">
                                    <ChevronRightIcon class="h-4 w-4" />
                                </Button>
                                <Button variant="outline" class="hidden h-8 w-8 p-0 md:flex"
                                    :disabled="!table.getCanNextPage()"
                                    @click="table.setPageIndex(table.getPageCount() - 1)" aria-label="Última página">
                                    <DoubleArrowRightIcon class="h-4 w-4" />
                                </Button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </CardFooter>
    </Card>
</template>
<script setup lang="ts">
import { h } from 'vue';
import { FlexRender } from '@tanstack/vue-table';
import { useClienteTable } from './typsPrestamos/listcliente';
import { columns } from './typsPrestamos/columns';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'
import { Toaster } from '@/components/ui/toast';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow
} from '@/components/ui/table';
import DataTableViewOptions from './DataTableViewOptions.vue';

import {
    RefreshCcw,
    ArrowUpDown,
    ArrowUp,
    ArrowDown,
    Download
} from 'lucide-vue-next';
import {
    ChevronRightIcon,
    ChevronLeftIcon,
    DoubleArrowLeftIcon,
    DoubleArrowRightIcon
} from "@radix-icons/vue";

import AddPrestamos from './AddPrestamos.vue';

// Initialize the client table logic
const {
    table,
    searchQuery,
    pageSize,
    pageSizeString,
    totalPageCount,
    refreshData,
    onSearchChange,
    verDetalleCliente,
    editarCliente,
    eliminarCliente,
    agregarNuevoCliente,
    getColumnWidthClass,
    filteredData
} = useClienteTable();
</script>

<style scoped>
:deep(.truncate) {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

:deep(th),
:deep(td) {
    padding: 0.5rem 0.75rem;
}

:deep(.table-container) {
    width: 100%;
    overflow-x: auto;
}
</style>
