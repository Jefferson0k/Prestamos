import { ref, computed, onMounted, watch } from 'vue';
import {
    useVueTable,
    getCoreRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    getFilteredRowModel,
    SortingState,
    ColumnFiltersState,
    VisibilityState,
} from '@tanstack/vue-table';
import { useToast } from '@/components/ui/toast';
import { columns } from './columns';

export interface Cliente {
    id: number;
    id_cliente: number;
    dni: string;
    NombreCompleto: string;
    fecha_inicio: string;
    fecha_vencimiento: string;
    capital: number;
    numero_cuotas: number;
    estado_cliente: number;
    recomendacion: string;
    tasa_interes_diario: number;
}

export function useClienteTable() {

    const { toast } = useToast();
    const clientes = ref<Cliente[]>([]);
    const isLoading = ref(true);
    const searchQuery = ref('');
    const pageSize = ref(10);
    const currentPage = ref(0);
    const sorting = ref<SortingState>([]);
    const columnVisibility = ref<VisibilityState>({
        'id': false,
        'id_cliente': false,
        'recomendacion': false,
    });
    const columnFilters = ref<ColumnFiltersState>([]);

    const pageSizeString = computed(() => String(pageSize.value));

    const filteredData = computed(() => {
        if (!searchQuery.value) return clientes.value;

        return clientes.value.filter(cliente => {
            const searchLower = searchQuery.value.toLowerCase();
            return (
                cliente.NombreCompleto.toLowerCase().includes(searchLower) ||
                cliente.dni.includes(searchQuery.value) ||
                cliente.id.toString().includes(searchQuery.value)
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

    // Methods
    const fetchClientes = async () => {
        isLoading.value = true;
        try {
            const response = await fetch('/prestamo');
            const result = await response.json();

            if (result && result.data) {
                clientes.value = (result.data as Cliente[]).map(cliente => ({
                    ...cliente,
                    capital: Number(cliente.capital),
                    tasa_interes_diario: Number(cliente.tasa_interes_diario)
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
            description: "Los clientes han sido actualizados.",
        });
    };

    const onSearchChange = () => {
        table.resetPageIndex(true);
    };

    const verDetalleCliente = (cliente: Cliente) => {
        toast({
            title: "Detalles del Cliente",
            description: `Cliente ID: ${cliente.id}, Nombre: ${cliente.NombreCompleto}`,
        });
    };

    const editarCliente = (cliente: Cliente) => {
        toast({
            title: "Editar Cliente",
            description: `Editando cliente ID: ${cliente.id}`,
        });
    };

    const eliminarCliente = (cliente: Cliente) => {
        toast({
            title: "Eliminar Cliente",
            description: `Eliminando cliente ID: ${cliente.id}`,
            variant: "destructive",
        });
    };

    const agregarNuevoCliente = () => {
        toast({
            title: "Nuevo Cliente",
            description: "Funcionalidad de agregar cliente próximamente.",
        });
    };

    watch([searchQuery, clientes], () => {
        table.resetPageIndex(true);
    }, { deep: true });

    onMounted(() => {
        fetchClientes();
    });

    const getColumnWidthClass = (columnId: string) => {
        const columnWidths = {
        };

        return columnWidths[columnId as keyof typeof columnWidths] || '';
    };

    return {
        clientes,
        isLoading,
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
    };
}
