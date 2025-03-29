<template>
    <div class="grid grid-cols-1gap-6">
        <Toaster />
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-2">
                <Input placeholder="Buscar clientes..." class="h-8 w-[250px]" v-model="searchQuery"
                    @input="onSearchChange" />
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
                        <TableHead v-for="header in headerGroup.headers" :key="header.id"
                            :class="[
                                header.column.getCanSort() ? 'cursor-pointer select-none' : '',
                                getColumnWidthClass(header.column.id)
                            ]"
                            @click="header.column.getCanSort() ? header.column.toggleSorting() : null">
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
                        <TableRow v-for="row in table.getRowModel().rows"
                            :key="row.id"
                            :data-state="row.getIsSelected() && 'selected'"
                            class="cursor-pointer hover:bg-muted/50"
                            @click="openClienteDetails(row.original)">
                            <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id"
                                :class="getColumnWidthClass(cell.column.id)">
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
                    <Select v-model="pageSize" @update:modelValue="table.setPageSize($event)">
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
                        <Button variant="outline" class="hidden h-8 w-8 p-0 lg:flex" :disabled="!table.getCanPreviousPage()"
                            @click="table.setPageIndex(0)">
                            <DoubleArrowLeftIcon class="h-4 w-4" />
                        </Button>
                        <Button variant="outline" class="h-8 w-8 p-0" :disabled="!table.getCanPreviousPage()"
                            @click="table.previousPage()">
                            <ChevronLeftIcon class="h-4 w-4" />
                        </Button>
                        <Button variant="outline" class="h-8 w-8 p-0" :disabled="!table.getCanNextPage()"
                            @click="table.nextPage()">
                            <ChevronRightIcon class="h-4 w-4" />
                        </Button>
                        <Button variant="outline" class="hidden h-8 w-8 p-0 lg:flex" :disabled="!table.getCanNextPage()"
                            @click="table.setPageIndex(table.getPageCount() - 1)">
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
    ColumnDef,
    ColumnFiltersState,
    SortingState,
    VisibilityState,
    FlexRender
} from '@tanstack/vue-table';
import { Toaster } from '@/components/ui/toast';
import { useToast } from '@/components/ui/toast';
import AddCliente from './AddCliente.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Checkbox } from '@/components/ui/checkbox';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import DataTableViewOptions from './DataTableViewOptions.vue';
import {
    Loader2,
    RefreshCcw,
    ChevronLeft,
    ChevronRight,
    MoreHorizontal,
    ArrowUpDown,
    ArrowUp,
    ArrowDown
} from 'lucide-vue-next';
import { ChevronRightIcon, ChevronLeftIcon, DoubleArrowLeftIcon, DoubleArrowRightIcon } from "@radix-icons/vue";

const getColumnWidthClass = (columnId: string) => {
    const columnWidths = {
        'select': 'w-10',
        'dni': 'w-28',
        'foto': 'w-14',
        'nombres': 'w-10 min-w-[20px]',
        'apellidos': 'w-40 min-w-[120px]',
        'celular': 'w-32',
        'direccion': 'w-48 min-w-[180px] max-w-md',
        'centro_trabajo': 'w-40 min-w-[150px] max-w-md',
        'estado': 'w-28',
        'fecha_inicio': 'w-32',
        'fecha_vencimiento': 'w-32',
        'tasa_interes_diario': 'w-36',
        'capital_inicial': 'w-32',
        'capital_del_mes': 'w-32',
        'capital_actual': 'w-32',
        'interes_actual': 'w-32',
        'interes_total': 'w-32',
        'total': 'w-28',
        'numero_cuotas': 'w-24',
        'recomendacion': 'w-48 min-w-[180px] max-w-xs',
        'actions': 'w-10'
    };

    return columnWidths[columnId as keyof typeof columnWidths] || '';
};

interface Cliente {
    id: number;
    nombres: string;
    apellidos: string;
    direccion: string;
    centro_trabajo: string;
    celular: string;
    dni: string;
    fecha_inicio: string | null;
    fecha_vencimiento: string | null;
    tasa_interes_diario: number | null;
    capital_inicial: number | null;
    capital_del_mes: number;
    capital_actual: number;
    interes_actual: number;
    interes_total: number;
    total: number;
    numero_cuotas: number | null;
    estado_cliente: string | null;
    recomendacion: string | null;
    foto: string;
}

const { toast } = useToast();
const clientes = ref<Cliente[]>([]);
const isLoading = ref(true);
const searchQuery = ref('');
const selectedCliente = ref<Cliente | null>(null);
const isClienteDetailsOpen = ref(false);
const pageSize = ref(10);
const currentPage = ref(0);

const pageSizeString = computed(() => String(pageSize.value));

const sorting = ref<SortingState>([]);

const columnVisibility = ref<VisibilityState>({
    'direccion': false,
    'centro_trabajo': false,
    'recomendacion': false,
    'foto': false,
    'celular': false,
});

const columnFilters = ref<ColumnFiltersState>([]);

const columns = [
    {
        id: 'select',
        header: ({ table }) => h(Checkbox, {
            'checked': table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate'),
            'onUpdate:checked': value => table.toggleAllPageRowsSelected(!!value),
            'ariaLabel': 'Select all',
        }),
        cell: ({ row }) => h(Checkbox, {
            'checked': row.getIsSelected(),
            'onUpdate:checked': (value) => {
                row.toggleSelected(!!value);
                event?.stopPropagation();
            },
            'ariaLabel': 'Select row',
            'onClick': (e) => e.stopPropagation()
        }),
        enableSorting: false,
        enableHiding: false,
    },
    {
        id: 'dni',
        accessorKey: 'dni',
        header: () => 'DNI',
        cell: (info) => info.getValue(),
        enableSorting: true,
    },
    {
        id: 'foto',
        accessorKey: 'foto',
        header: () => 'Foto',
        cell: ({ row }) => h('div', {}, [
            h(Avatar, {}, {
                default: () => [
                    h(AvatarImage, {
                        src: row.original.foto || '',
                        alt: `Foto de ${row.original.nombres}`
                    }),
                    h(AvatarFallback, {}, () => getInitials(row.original.nombres, row.original.apellidos))
                ]
            })
        ]),
        enableSorting: true,
        enableHiding: true,
    },
    {
        id: 'nombre_completo',
        accessorKey: 'nombre_completo',
        header: () => 'Nombre completo',
        cell: (info) => info.getValue(),
        enableSorting: true,
    },
    {
        id: 'celular',
        accessorKey: 'celular',
        header: () => 'Celular',
        cell: (info) => info.getValue(),
        enableSorting: true,
        enableHiding: true,    },
    {
        id: 'direccion',
        accessorKey: 'direccion',
        header: () => 'Dirección',
        cell: (info) => info.getValue(),
        enableSorting: true,
        enableHiding: true,
    },
    {
        id: 'centro_trabajo',
        accessorKey: 'centro_trabajo',
        header: () => 'Centro de Trabajo',
        cell: (info) => info.getValue(),
        enableSorting: true,
        enableHiding: true,
    },
    {
        id: 'estado',
        accessorKey: 'estado_cliente',
        header: () => 'Estado',
        cell: ({ row }) => h(Badge, {
            variant: getEstadoBadgeVariant(row.original.estado_cliente)
        }, () => row.original.estado_cliente || 'Sin estado'),
        enableSorting: true,
    },
    {
        id: 'fecha_inicio',
        accessorKey: 'fecha_inicio',
        header: () => 'Inicio',
        cell: ({ row }) => row.original.fecha_inicio || '11-11-2021',
        enableSorting: true,
    },
    {
        id: 'fecha_vencimiento',
        accessorKey: 'fecha_vencimiento',
        header: () => 'Vencimiento',
        cell: ({ row }) => row.original.fecha_vencimiento || '11-11-2021',
        enableSorting: true,
    },
    {
        id: 'tasa_interes_diario',
        accessorKey: 'tasa_interes_diario',
        header: () => 'Interés Diario',
        cell: ({ row }) => row.original.tasa_interes_diario ? `${row.original.tasa_interes_diario}%` : 'N/A',
        enableSorting: true,
    },
    {
        id: 'capital_inicial',
        accessorKey: 'capital_inicial',
        header: () => 'Capital Inicial',
        cell: ({ row }) => row.original.capital_inicial || 'N/A',
        enableSorting: true,
    },
    {
        id: 'capital_del_mes',
        accessorKey: 'capital_del_mes',
        header: () => 'Capital del Mes',
        cell: ({ row }) => row.original.capital_del_mes || 'N/A',
        enableSorting: true,
    },
    {
        id: 'capital_actual',
        accessorKey: 'capital_actual',
        header: () => 'Capital Actual',
        cell: ({ row }) => row.original.capital_actual || 'N/A',
        enableSorting: true,
    },
    {
        id: 'interes_actual',
        accessorKey: 'interes_actual',
        header: () => 'Interés Actual',
        cell: ({ row }) => row.original.interes_actual || 'N/A',
        enableSorting: true,
    },
    {
        id: 'interes_total',
        accessorKey: 'interes_total',
        header: () => 'Interés Total',
        cell: ({ row }) => row.original.interes_total || 'N/A',
        enableSorting: true,
    },
    {
        id: 'total',
        accessorKey: 'total',
        header: () => 'Total',
        cell: ({ row }) => row.original.total || 'N/A',
        enableSorting: true,
    },
    {
        id: 'numero_cuotas',
        accessorKey: 'numero_cuotas',
        header: () => 'Nº de Cuotas',
        cell: ({ row }) => row.original.numero_cuotas || 'N/A',
        enableSorting: true,
    },
    {
        id: 'recomendacion',
        accessorKey: 'recomendacion',
        header: () => 'Recomendación',
        cell: ({ row }) => row.original.recomendacion || 'Sin recomendación',
        enableSorting: true,
        enableHiding: true,
    },
    {
        id: 'actions',
        header: () => '',
        cell: ({ row }) => h('div', { onClick: (e) => e.stopPropagation() }, [
            h(DropdownMenu, {}, {
                default: () => [
                    h(DropdownMenuTrigger, { asChild: true }, () =>
                        h(Button, { variant: 'ghost', class: 'h-8 w-8 p-0' }, () =>
                            h(MoreHorizontal, { class: 'h-4 w-4' })
                        )
                    ),
                    h(DropdownMenuContent, { align: 'end' }, () => [
                        h(DropdownMenuItem, { onClick: () => viewClienteDetails(row.original) }, () => 'Ver detalles'),
                        h(DropdownMenuItem, { onClick: () => editCliente(row.original) }, () => 'Editar'),
                        h(DropdownMenuSeparator),
                        h(DropdownMenuItem, {
                            onClick: () => deleteCliente(row.original),
                            class: 'text-destructive focus:text-destructive'
                        }, () => 'Eliminar')
                    ])
                ]
            })
        ]),
        enableSorting: false,
        enableHiding: false,
    },
];

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

const totalPageCount = computed(() => {
    if (!filteredData.value.length) return 0;
    return Math.ceil(filteredData.value.length / pageSize.value);
});

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

watch([searchQuery, clientes], () => {
    table.resetPageIndex(true);
}, { deep: true });

const fetchClientes = async () => {
    isLoading.value = true;
    try {
        const response = await fetch('/cliente');
        const result = await response.json();
        if (result && result.data) {
            clientes.value = result.data.map(cliente => ({
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

const getInitials = (firstName: string = '', lastName: string = '') => {
    return `${firstName.charAt(0) || ''}${lastName.charAt(0) || ''}`.toUpperCase() || 'NA';
};


const getEstadoBadgeVariant = (estado: number | null) => {
    if (estado === 1) return "success"; // Verde para "Paga"
    if (estado === 2) return "destructive"; // Rojo para "Moroso"
    return "secondary"; // Otro estado
};

const openClienteDetails = (cliente: Cliente) => {
    selectedCliente.value = cliente;
    isClienteDetailsOpen.value = true;
};

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

const onSearchChange = () => {
    table.resetPageIndex(true);
};

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
