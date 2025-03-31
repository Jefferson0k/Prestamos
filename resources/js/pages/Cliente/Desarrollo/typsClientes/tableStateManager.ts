import { ColumnDef, getCoreRowModel, getPaginationRowModel, getSortedRowModel, useVueTable } from '@tanstack/vue-table';
import { computed, ref, Ref, watch, onMounted } from 'vue';
import { Cliente } from './typesCliente';
import { useClienteService } from './clienteService';

export const useTableState = (columns: ColumnDef<Cliente>[], searchQueryRef: Ref<string>) => {
    const { fetchClientes } = useClienteService();

    // Estados de la tabla
    const clientes = ref<Cliente[]>([]);
    const totalClientes = ref(0);
    const sorting = ref([]);
    const columnVisibility = ref({
        direccion: false,
        centro_trabajo: false,
        recomendacion: false,
        foto: false,
        celular: false,
    });
    const pageSize = ref(10);
    const currentPage = ref(0);
    const isLoading = ref(false);

    // Obtener datos del backend
    const fetchData = async () => {
        isLoading.value = true;
        try {
            const { data, pagination } = await fetchClientes(currentPage.value + 1, pageSize.value, searchQueryRef.value);
            clientes.value = data;
            totalClientes.value = pagination.total; // Se corrige aquí
            
        } finally {
            isLoading.value = false;
        }
    };

    // Observar cambios en paginación y búsqueda
    watch([currentPage, pageSize, searchQueryRef], () => {
        fetchData();
    });

    onMounted(() => {
        fetchData();
    });

    // Total de páginas
    const totalPageCount = computed(() => Math.ceil(totalClientes.value / pageSize.value));

    // Configuración de la tabla
    const table = useVueTable({
        get data() {
            return clientes.value;
        },
        columns,
        state: {
            get sorting() {
                return sorting.value;
            },
            get columnVisibility() {
                return columnVisibility.value;
            },
            get pagination() {
                return {
                    pageIndex: currentPage.value,
                    pageSize: pageSize.value,
                };
            },
        },
        onPaginationChange: (updater) => {
            const updated = typeof updater === 'function'
                ? updater({ pageIndex: currentPage.value, pageSize: pageSize.value })
                : updater;

            currentPage.value = updated.pageIndex;
            pageSize.value = updated.pageSize;
        },
        getCoreRowModel: getCoreRowModel(),
        getPaginationRowModel: getPaginationRowModel(),
        getSortedRowModel: getSortedRowModel(),
        manualPagination: true, // Se usa paginación del backend
    });

    return {
        table,
        pageSize,
        currentPage,
        totalPageCount,
        isLoading,
    };
};
