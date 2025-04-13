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
                <AddPrestamos variant="default" size="sm" @click="agregarNuevoCliente" />
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
                            <div
                                class="flex items-center justify-between space-x-2"
                                v-if="!header.isPlaceholder"
                            >
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
                            @click="verDetalleCliente(row.original)"
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
                                No hay clientes disponibles.
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>

        <div class="flex items-center justify-between space-x-2 py-4">
            <div class="flex-1 text-sm text-muted-foreground">
                Mostrando
                <span class="font-medium">{{ table.getRowModel().rows.length }}</span>
                de
                <span class="font-medium">{{ filteredData.length }}</span> clientes
            </div>
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <p class="text-sm font-medium">Filas por página</p>
                    <Select
                        v-model="pageSize"
                            @update:modelValue="(value) => table.setPageSize(Number(value) || 10)"
                    >
                        <SelectTrigger class="h-8 w-[70px]">
                            <SelectValue :placeholder="pageSizeString" />
                        </SelectTrigger>
                        <SelectContent side="top">
                            <SelectItem
                                v-for="size in [5, 10, 20, 50]"
                                :key="size"
                                :value="size"
                            >
                                {{ size }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <template v-if="totalPageCount > 0">
                    <p class="text-sm font-medium">
                        Página {{ table.getState().pagination.pageIndex + 1 }} de {{ totalPageCount }}
                    </p>

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
import { h } from 'vue';
import { FlexRender } from '@tanstack/vue-table';
import { useClienteTable } from './typsPrestamos/listcliente';
import { columns } from './typsPrestamos/columns';

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
    Plus
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

:deep(th), :deep(td) {
    padding: 0.5rem 0.75rem;
}

:deep(.table-container) {
    width: 100%;
    overflow-x: auto;
}
</style>
